<?php
// api/csrf.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';  // démarre session + token
echo json_encode(['csrf' => $_SESSION['csrf']]);
