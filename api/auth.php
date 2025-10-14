<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

/**
 * Classe simple pour la gestion des utilisateurs (POO basique)
 */
class SimpleAuth {
    private $pdo;
    
    public function __construct() {
        global $dsn, $username, $password_db, $options;
        try {
            $this->pdo = new PDO($dsn, $username, $password_db, $options);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Erreur de connexion');
        }
    }
    
    /**
     * Vérifier les identifiants (uniquement via base de données)
     */
    public function login($email, $password) {
        try {
            // Rechercher uniquement dans la base de données
            $stmt = $this->pdo->prepare("
                SELECT id, email, password, name, role 
                FROM sma_users 
                WHERE email = ? AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Vérifier le mot de passe (hashé ou texte brut)
                $password_valid = false;
                
                if (password_verify($password, $user['password'])) {
                    // Mot de passe hashé avec password_hash()
                    $password_valid = true;
                } elseif ($password === $user['password']) {
                    // Mot de passe en texte brut (pour compatibilité)
                    $password_valid = true;
                }
                
                if ($password_valid) {
                    // Mettre à jour la date de dernière connexion
                    $updateStmt = $this->pdo->prepare("
                        UPDATE sma_users 
                        SET last_login = NOW() 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$user['id']]);
                    
                    return [
                        'success' => true,
                        'message' => 'Connexion réussie',
                        'token' => 'temp_token_123',  // Token fixe simple
                        'user' => [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'name' => $user['name'],
                            'role' => $user['role']
                        ]
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'Identifiants invalides'];
            
        } catch (PDOException $e) {
            error_log('Erreur SQL: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur'];
        }
    }
}

// === TRAITEMENT PRINCIPAL ===
try {
    $auth = new SimpleAuth();
    
    // Récupérer les données
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation basique
    if (empty($email) || empty($password)) {
        throw new Exception('Email et mot de passe requis');
    }
    
    // Validation format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email invalide');
    }
    
    // Authentification
    $result = $auth->login($email, $password);
    
    // Réponse
    if (!$result['success']) {
        http_response_code(401);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
