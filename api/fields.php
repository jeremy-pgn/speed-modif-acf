<?php
require_once 'config.php';

// Configuration des headers (concepts API REST)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Classe de base pour la gestion des connexions PDO
 * Applique les concepts PDO et requêtes préparées du cours
 */
class DatabaseConnection {
    private static $smaConnection = null;
    private static $wpConnection = null;
    
    public static function getSMAConnection() {
        if (self::$smaConnection === null) {
            global $dsn, $username, $password_db, $options;
            try {
                self::$smaConnection = new PDO($dsn, $username, $password_db, $options);
                self::$smaConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log('Erreur connexion SMA: ' . $e->getMessage());
                throw new Exception('Erreur de connexion à la base de données SMA');
            }
        }
        return self::$smaConnection;
    }
    
    public static function getWPConnection() {
        if (self::$wpConnection === null) {
            try {
                $wp_dsn = "mysql:host=" . WP_HOST . ";dbname=" . WP_DATABASE . ";charset=utf8mb4";
                self::$wpConnection = new PDO($wp_dsn, WP_USER, WP_PASSWORD);
                self::$wpConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log('Erreur connexion WordPress: ' . $e->getMessage());
                throw new Exception('Erreur de connexion à WordPress');
            }
        }
        return self::$wpConnection;
    }
}

/**
 * Classe d'authentification (séparation des responsabilités)
 */
class AuthManager {
    private const VALID_TOKEN = 'temp_token_123';
    
    public static function checkAuth() {
        $headers = getallheaders();
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\\s+(.*)$/i', $headers['Authorization'], $matches)) {
                $token = $matches[1];
            }
        }
        
        if ($token === self::VALID_TOKEN) {
            return ['user_id' => 1];
        }
        
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token manquant ou invalide']);
        exit();
    }
}

/**
 * Classe pour les champs (POO avec validation - concepts du cours)
 */
class Field {
    protected $id;
    protected $fieldKey;
    protected $fieldValue;
    protected $lastModifiedBy;
    
    // Getters utilisés
    public function getId() { return $this->id; }
    public function getFieldKey() { return $this->fieldKey; }
    public function getFieldValue() { return $this->fieldValue; }
    public function getLastModifiedBy() { return $this->lastModifiedBy; }
    
    // Setters avec validation (concepts du cours)
    public function setId($id) {
        if (!is_int($id) || $id <= 0) {
            throw new InvalidArgumentException('L\'ID doit être un entier positif');
        }
        $this->id = $id;
    }
    
    public function setFieldKey($key) {
        if (empty($key) || !is_string($key)) {
            throw new InvalidArgumentException('La clé du champ ne peut pas être vide');
        }
        $this->fieldKey = $key;
    }
    
    public function setFieldValue($value) {
        if (strlen($value) > 10000) {
            throw new InvalidArgumentException('La valeur du champ est trop longue (max 10000 caractères)');
        }
        $this->fieldValue = $value;
    }
    
    public function setLastModifiedBy($userId) {
        if (!is_int($userId) || $userId <= 0) {
            throw new InvalidArgumentException('L\'ID utilisateur doit être un entier positif');
        }
        $this->lastModifiedBy = $userId;
    }
    
    /**
     * Constructeur avec validation des données (concepts POO du cours)
     */
    public function __construct(array $data = []) {
        if (isset($data['id'])) $this->setId((int)$data['id']);
        if (isset($data['field_key'])) $this->setFieldKey($data['field_key']);
        if (isset($data['field_value'])) $this->setFieldValue($data['field_value']);
        if (isset($data['last_modified_by'])) $this->setLastModifiedBy((int)$data['last_modified_by']);
    }
}

/**
 * Gestionnaire pour la synchronisation WordPress (séparation des responsabilités)
 */
class WordPressSyncManager {
    
    public static function syncField($fieldKey, $newValue) {
        try {
            $pdo = DatabaseConnection::getWPConnection();
            $postId = WP_POST_ID;
            
            // Vérifier si le champ existe déjà (requête préparée)
            $stmt = $pdo->prepare("
                SELECT meta_id FROM wp_postmeta 
                WHERE post_id = ? AND meta_key = ?
                ORDER BY meta_id DESC LIMIT 1
            ");
            $stmt->execute([$postId, $fieldKey]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Mettre à jour
                $stmt = $pdo->prepare("
                    UPDATE wp_postmeta 
                    SET meta_value = ? 
                    WHERE meta_id = ?
                ");
                $result = $stmt->execute([$newValue, $existing['meta_id']]);
                $action = "updated";
            } else {
                // Insérer
                $stmt = $pdo->prepare("
                    INSERT INTO wp_postmeta (post_id, meta_key, meta_value) 
                    VALUES (?, ?, ?)
                ");
                $result = $stmt->execute([$postId, $fieldKey, $newValue]);
                $action = "created";
            }
            
            return [
                'success' => true, 
                'message' => "WordPress: {$fieldKey} {$action}",
                'field_key' => $fieldKey,
                'post_id' => $postId,
                'action' => $action
            ];
            
        } catch (PDOException $e) {
            error_log('Erreur sync WordPress: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur sync: ' . $e->getMessage()];
        }
    }
}

/**
 * Gestionnaire principal des champs (pattern Manager - CRUD du cours)
 */
class FieldManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = DatabaseConnection::getSMAConnection();
    }
    
    /**
     * Récupérer un champ (Read)
     */
    public function getField($fieldId = null) {
        try {
            if ($fieldId) {
                $stmt = $this->pdo->prepare("
                    SELECT f.*, u.name as last_modified_by_name
                    FROM sma_acf_fields f
                    LEFT JOIN sma_users u ON f.last_modified_by = u.id
                    WHERE f.id = ? AND f.is_active = 1
                ");
                $stmt->execute([$fieldId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return $result ? ['success' => true, 'data' => $result] 
                              : ['success' => false, 'message' => 'Champ non trouvé'];
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT f.*, u.name as last_modified_by_name
                    FROM sma_acf_fields f
                    LEFT JOIN sma_users u ON f.last_modified_by = u.id
                    WHERE f.is_active = 1
                    ORDER BY f.section, f.field_group, f.field_name
                ");
                $stmt->execute();
                $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return [
                    'success' => true,
                    'data' => $fields,
                    'count' => count($fields)
                ];
            }
            
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la récupération: ' . $e->getMessage());
        }
    }
    
    /**
     * Mettre à jour un champ avec synchronisation (Update)
     */
    public function updateFieldWithSync($fieldId, $newValue, $userId) {
        try {
            // Démarrer une transaction (concepts PDO avancés)
            $this->pdo->beginTransaction();
            
            // Récupérer les infos du champ
            $stmt = $this->pdo->prepare("
                SELECT field_key, field_value FROM sma_acf_fields WHERE id = ?
            ");
            $stmt->execute([$fieldId]);
            $field = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$field) {
                throw new Exception('Champ non trouvé');
            }
            
            $oldValue = $field['field_value'];
            $fieldKey = $field['field_key'];
            
            // Mettre à jour Speed Modif ACF
            $stmt = $this->pdo->prepare("
                UPDATE sma_acf_fields 
                SET field_value = ?, last_modified_by = ?, last_modified_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$newValue, $userId, $fieldId]);
            
            // Ajouter à l'historique
            $this->addHistory($fieldId, $userId, 'update', $oldValue, $newValue);
            
            // Confirmer la transaction
            $this->pdo->commit();
            
            // Synchroniser avec WordPress
            $syncResult = WordPressSyncManager::syncField($fieldKey, $newValue);
            
            return [
                'success' => true,
                'message' => 'Champ mis à jour et synchronisé',
                'sma_update' => [
                    'field_id' => $fieldId,
                    'field_key' => $fieldKey,
                    'old_value' => $oldValue,
                    'new_value' => $newValue
                ],
                'wordpress_sync' => $syncResult
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Ajouter une entrée à l'historique (méthode privée)
     */
    private function addHistory($fieldId, $userId, $action, $oldValue, $newValue) {
        $stmt = $this->pdo->prepare("
            INSERT INTO sma_field_history (field_id, user_id, action, old_value, new_value, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $fieldId, 
            $userId, 
            $action, 
            $oldValue, 
            $newValue, 
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
    }
    
    /**
     * Récupérer l'historique d'un champ
     */
    public function getFieldHistory($fieldId, $limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT h.*, u.name as user_name
                FROM sma_field_history h
                LEFT JOIN sma_users u ON h.user_id = u.id
                WHERE h.field_id = ?
                ORDER BY h.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$fieldId, $limit]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $history, 'count' => count($history)];
            
        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la récupération de l\'historique: ' . $e->getMessage());
        }
    }
}

/**
 * Contrôleur API (Architecture REST du cours)
 */
class APIController {
    private $fieldManager;
    private $user;
    
    public function __construct() {
        $this->fieldManager = new FieldManager();
        $this->user = null;
    }
    
    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            
            // Authentification (sauf pour OPTIONS)
            if ($method !== 'OPTIONS') {
                $this->user = AuthManager::checkAuth();
            }
            
            // Routage selon la méthode HTTP (concepts REST)
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    break;
                    
                case 'PUT':
                    $this->handlePut();
                    break;
                    
                default:
                    throw new Exception('Méthode HTTP non autorisée');
            }
            
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }
    
    private function handleGet() {
        if (isset($_GET['id'])) {
            $result = $this->fieldManager->getField((int)$_GET['id']);
        } elseif (isset($_GET['history']) && isset($_GET['field_id'])) {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $result = $this->fieldManager->getFieldHistory((int)$_GET['field_id'], $limit);
        } else {
            $result = $this->fieldManager->getField();
        }
        
        $this->sendResponse($result);
    }
    
    private function handlePut() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['field_id']) || !isset($input['field_value'])) {
            throw new Exception('Données manquantes (field_id et field_value requis)');
        }
        
        $fieldId = (int)$input['field_id'];
        $fieldValue = $input['field_value'];
        
        if ($fieldId <= 0) {
            throw new Exception('field_id invalide');
        }
        
        $result = $this->fieldManager->updateFieldWithSync(
            $fieldId, 
            $fieldValue, 
            $this->user['user_id']
        );
        
        $this->sendResponse($result);
    }
    
    private function sendResponse($data) {
        echo json_encode($data);
    }
    
    private function sendErrorResponse($message, $code = 500) {
        http_response_code($code);
        echo json_encode([
            'success' => false, 
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

// === POINT D'ENTRÉE PRINCIPAL ===
try {
    $controller = new APIController();
    $controller->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur système: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    error_log('Erreur API: ' . $e->getMessage());
}
?>
