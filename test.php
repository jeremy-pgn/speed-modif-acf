<?php

// Génération du jeton (config.php)
session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
//Vérification (field.php)
require_once __DIR__ . '/config.php';
if (in_array(
    $_SERVER['REQUEST_METHOD'],
    ['POST', 'PUT'],
    true
)) {
    $hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$hdr) {
        $h = function_exists('getallheaders')
            ? getallheaders() : [];
        $hdr = $h['X-CSRF-Token'] ?? '';
    }
    if (!hash_equals($_SESSION['csrf'] ?? '', $hdr)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
          ['success' => false, 'message' => 'Invalid CSRF token']
        );
        exit;
    }
}


// requête préparée contre injection SQL
$stmt = $pdo->prepare(
    "SELECT id, name, password FROM sma_users 
    WHERE email = ? AND is_active = 1"
);
$stmt->execute([$email]);
