<?php
/**
 * SPEED MODIF ACF - API GESTION DES CHAMPS
 * API REST pour CRUD des champs ACF
 */

require_once 'config.php';

// Headers CORS et JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Connexion à la base de données
 */
function getConnection() {
    global $dsn, $username, $password_db, $options;
    try {
        $pdo = new PDO($dsn, $username, $password_db, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Erreur de connexion DB: ' . $e->getMessage());
        return false;
    }
}

/**
 * Vérification JWT simple
 */
function verifyJWT($token) {
    if (!$token) return false;
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    
    try {
        $payload = json_decode(base64url_decode($parts[1]), true);
        
        // Vérifier expiration
        if ($payload['exp'] < time()) return false;
        
        // Vérifier signature
        $signature = base64url_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], JWT_SECRET, true));
        if ($signature !== $parts[2]) return false;
        
        return $payload;
    } catch (Exception $e) {
        return false;
    }
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Récupérer l'utilisateur depuis le token
 */
function getCurrentUser() {
    $headers = getallheaders();
    $token = null;
    
    // Vérifier header Authorization
    if (isset($headers['Authorization'])) {
        $matches = [];
        if (preg_match('/Bearer\\s+(.*)$/i', $headers['Authorization'], $matches)) {
            $token = $matches[1];
        }
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token manquant']);
        exit();
    }
    
    $payload = verifyJWT($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token invalide']);
        exit();
    }
    
    return $payload;
}

/**
 * Récupérer tous les champs d'une section
 */
function getFieldsBySection($section = null) {
    $pdo = getConnection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
    
    try {
        if ($section) {
            $stmt = $pdo->prepare("
                SELECT f.*, u.name as last_modified_by_name
                FROM sma_acf_fields f
                LEFT JOIN sma_users u ON f.last_modified_by = u.id
                WHERE f.section = ? AND f.is_active = 1
                ORDER BY f.field_group, f.field_name
            ");
            $stmt->execute([$section]);
        } else {
            $stmt = $pdo->prepare("
                SELECT f.*, u.name as last_modified_by_name
                FROM sma_acf_fields f
                LEFT JOIN sma_users u ON f.last_modified_by = u.id
                WHERE f.is_active = 1
                ORDER BY f.section, f.field_group, f.field_name
            ");
            $stmt->execute();
        }
        
        $fields = $stmt->fetchAll();
        
        return [
            'success' => true,
            'data' => $fields,
            'count' => count($fields)
        ];
        
    } catch (PDOException $e) {
        error_log('Erreur get fields: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
}

/**
 * Mettre à jour un champ
 */
function updateField($fieldId, $newValue, $userId) {
    $pdo = getConnection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
    
    try {
        // Récupérer l'ancienne valeur pour l'historique
        $stmt = $pdo->prepare("SELECT field_value FROM sma_acf_fields WHERE id = ?");
        $stmt->execute([$fieldId]);
        $oldField = $stmt->fetch();
        
        if (!$oldField) {
            return ['success' => false, 'message' => 'Champ non trouvé'];
        }
        
        $oldValue = $oldField['field_value'];
        
        // Mettre à jour le champ
        $stmt = $pdo->prepare("
            UPDATE sma_acf_fields 
            SET field_value = ?, last_modified_by = ?, last_modified_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newValue, $userId, $fieldId]);
        
        // Ajouter à l'historique
        $stmt = $pdo->prepare("
            INSERT INTO sma_field_history (field_id, user_id, action, old_value, new_value, ip_address)
            VALUES (?, ?, 'update', ?, ?, ?)
        ");
        $stmt->execute([
            $fieldId, 
            $userId, 
            $oldValue, 
            $newValue, 
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
        
        return [
            'success' => true, 
            'message' => 'Champ mis à jour avec succès',
            'field_id' => $fieldId
        ];
        
    } catch (PDOException $e) {
        error_log('Erreur update field: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
}

/**
 * Récupérer l'historique des modifications
 */
function getFieldHistory($limit = 20) {
    $pdo = getConnection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                h.*,
                f.field_label,
                u.name as user_name
            FROM sma_field_history h
            JOIN sma_acf_fields f ON h.field_id = f.id
            JOIN sma_users u ON h.user_id = u.id
            ORDER BY h.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $history = $stmt->fetchAll();
        
        return [
            'success' => true,
            'data' => $history,
            'count' => count($history)
        ];
        
    } catch (PDOException $e) {
        error_log('Erreur get history: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
}

/**
 * Recherche dans les champs
 */
function searchFields($query) {
    $pdo = getConnection();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
    
    try {
        $searchTerm = "%{$query}%";
        $stmt = $pdo->prepare("
            SELECT f.*, u.name as last_modified_by_name
            FROM sma_acf_fields f
            LEFT JOIN sma_users u ON f.last_modified_by = u.id
            WHERE f.is_active = 1 
            AND (f.field_label LIKE ? OR f.field_value LIKE ? OR f.field_group LIKE ?)
            ORDER BY f.section, f.field_group, f.field_name
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $fields = $stmt->fetchAll();
        
        return [
            'success' => true,
            'data' => $fields,
            'count' => count($fields),
            'query' => $query
        ];
        
    } catch (PDOException $e) {
        error_log('Erreur search fields: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur serveur'];
    }
}

// === ROUTAGE PRINCIPAL ===

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // Vérifier l'authentification pour toutes les requêtes sauf OPTIONS
    if ($method !== 'OPTIONS') {
        $currentUser = getCurrentUser();
        $userId = $currentUser['user_id'];
    }
    
    switch ($method) {
        case 'GET':
            // GET /api/fields - Tous les champs
            // GET /api/fields?section=identite - Champs d'une section
            // GET /api/fields?search=logo - Recherche
            // GET /api/fields?history=1 - Historique
            
            if (isset($_GET['search'])) {
                $result = searchFields($_GET['search']);
            } elseif (isset($_GET['history'])) {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $result = getFieldHistory($limit);
            } elseif (isset($_GET['section'])) {
                $result = getFieldsBySection($_GET['section']);
            } else {
                $result = getFieldsBySection();
            }
            break;
            
        case 'PUT':
            // PUT /api/fields - Mettre à jour un champ
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['field_id']) || !isset($input['field_value'])) {
                http_response_code(400);
                $result = ['success' => false, 'message' => 'Données manquantes'];
                break;
            }
            
            $result = updateField($input['field_id'], $input['field_value'], $userId);
            break;
            
        case 'POST':
            // POST /api/fields/batch
