<?php

/**
 * api/config.php
 * Fichier de configuration principal de l'application Speed Modif ACF
 * Gère la connexion à la base de données SMA et la synchronisation avec WordPress
 */
// api/config.php
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
session_set_cookie_params([
    'secure' => false,  // false en local HTTP
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// ========================================
// CONFIGURATION BASE DE DONNÉES SMA
// ========================================

$host = 'host.docker.internal';
$dbname = 'sma_database';
$username = 'root';
$password_db = 'root';
$port = 3306;

// Construction du DSN (Data Source Name) pour PDO
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

// Configuration des options PDO pour une connexion sécurisée
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Mode d'erreur par exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Mode de récupération associatif par défaut
    PDO::ATTR_EMULATE_PREPARES => false,                  // Désactive l'émulation des requêtes préparées
];

// ========================================
// CONNEXION À LA BASE DE DONNÉES SMA
// ========================================

try {
    $pdo = new PDO($dsn, $username, $password_db, $options);
} catch (PDOException $e) {
    error_log("Erreur connexion DB: " . $e->getMessage());
    die("Erreur de connexion à la base de données.");
}

// ========================================
// CONFIGURATION WORDPRESS
// ========================================

// Constantes pour la connexion WordPress (base de données externe)
define('WP_HOST', 'host.docker.internal');
define('WP_DATABASE', 'wordpress_db');
define('WP_USER', 'root');
define('WP_PASSWORD', 'root');
define('WP_POST_ID', 12); // ID du post WordPress à synchroniser

// Variables de compatibilité (pour faciliter la migration depuis l'ancien code)
$wp_host = WP_HOST;
$wp_db = WP_DATABASE;
$wp_user = WP_USER;
$wp_pass = WP_PASSWORD;
$wp_post_id = WP_POST_ID;

// ========================================
// CONFIGURATION DE L'APPLICATION
// ========================================

define('APP_NAME', 'Speed Modif ACF');
define('APP_VERSION', '1.0.0');
define('DEBUG_MODE', true);

// ========================================
// GESTION DU MODE DEBUG
// ========================================

if (DEBUG_MODE) {
    // Mode développement : affichage de toutes les erreurs
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Mode production : masquage des erreurs pour la sécurité
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ========================================
// SYNCHRONISATION AUTOMATIQUE WORDPRESS
// ========================================

try {
    // Connexion à la base de données WordPress
    $wp_dsn = "mysql:host=" . WP_HOST . ";dbname=" . WP_DATABASE . ";charset=utf8mb4";
    $wpPdo = new PDO($wp_dsn, WP_USER, WP_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Récupération des champs ACF depuis WordPress
    // Exclut les champs système (préfixés par '_') et les valeurs vides
    $stmt = $wpPdo->prepare("
        SELECT meta_key, meta_value 
        FROM wp_postmeta 
        WHERE post_id = ? 
        AND meta_key NOT LIKE '_%'
        AND meta_value != ''
    ");
    $stmt->execute([WP_POST_ID]);
    $wpFields = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Synchronisation des données WordPress vers la base SMA
    foreach ($wpFields as $field) {
        // Mise à jour ou insertion des champs dans la base SMA
        $updateStmt = $pdo->prepare("
            UPDATE sma_acf_fields 
            SET field_value = ?, last_modified_at = NOW() 
            WHERE field_key = ?
        ");
        $updateStmt->execute([$field['meta_value'], $field['meta_key']]);
    }
} catch (Exception $e) {
    // Gestion silencieuse des erreurs de synchronisation
    // Les erreurs sont enregistrées dans les logs sans interrompre l'application
    error_log('Erreur synchronisation WordPress: ' . $e->getMessage());
}
