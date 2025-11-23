<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

// Lire X-CSRF-Token
$hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!$hdr) {
  $all = function_exists('getallheaders') ? getallheaders() : [];
  $hdr = $all['X-CSRF-Token'] ?? '';
}

if (!hash_equals($_SESSION['csrf'] ?? '', $hdr)) {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success'=>false,'message'=>'Méthode non autorisée']);
  exit;
}



// Récupération et nettoyage des données du formulaire
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validation des champs obligatoires
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email et password requis']);
    exit;
}

try {
    // Requête pour récupérer l'utilisateur actif avec l'email fourni
    $stmt = $pdo->prepare("SELECT id, name, password FROM sma_users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si l'utilisateur existe dans la base
    if ($user) {
        $isValidPassword = false;       
       
        // Vérification du hash avec password_verify 
        if (password_verify($password, $user['password'])) {
            $isValidPassword = true;
        }        
        
        // Si le mot de passe est correct
        if ($isValidPassword) {
            // Création de la session utilisateur
            $_SESSION['logged'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            
            // Retour de succès
            echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
        } else {
            // Mot de passe incorrect
            echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
        }
    } else {
        // Utilisateur non trouvé ou inactif
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    }
    
} catch (Exception $e) {
    // Gestion des erreurs
   error_log('Auth error: '.$e->getMessage());    
    echo json_encode(['success' => false, 'message' => 'Erreur interne.']);
    exit;
}
?>
