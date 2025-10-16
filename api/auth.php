<?php
// api/auth.php

// Inclusion du fichier de configuration de la base de données
require_once 'config.php';

// Démarrage de la session pour pouvoir stocker les informations utilisateur
session_start();

// Définition de l'en-tête pour indiquer que la réponse sera en JSON
header('Content-Type: application/json');

// Vérification que la requête utilise la méthode POST (sécurité)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
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
        
        // Vérification du mot de passe avec méthodes de hachage 
        
       
        // Mot de passe hashé avec password_hash() 
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
    // Gestion des erreurs de base de données ou autres exceptions
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>
