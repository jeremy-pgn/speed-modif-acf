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
     * Vérifier les identifiants (requête préparée pour la base)
     */
    public function login($email, $password) {
        try {
            // D'abord vérifier l'admin par défaut (comme avant)
            if ($email === 'admin@speedmodifacf.com' && $password === 'password') {
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'token' => 'temp_token_123',
                    'user' => [
                        'id' => 1,
                        'email' => $email,
                        'name' => 'Administrateur',
                        'role' => 'admin'
                    ]
                ];
            }
            
            // Ensuite chercher dans la base de données (requête préparée)
            $stmt = $this->pdo->prepare("
                SELECT id, email, password, name, role 
                FROM sma_users 
                WHERE email = ? AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Vérifier le mot de passe (si c'est hashé dans la base)
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    return [
                        'success' => true,
                        'message' => 'Connexion réussie',
                        'token' => 'temp_token_123',
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
