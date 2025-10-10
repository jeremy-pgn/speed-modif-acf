<?php
require_once 'api/config.php';

echo "<h1>🔄 Test Synchronisation WordPress</h1>";

try {
    // Test connexion WordPress
    $wp_pdo = new PDO("mysql:host=host.docker.internal;dbname=wordpress_db;charset=utf8mb4", 'root', 'root');
    echo "<p>✅ Connexion WordPress réussie</p>";
    
    // Lister les champs ACF existants
    $stmt = $wp_pdo->prepare("
        SELECT meta_key, meta_value 
        FROM wp_postmeta 
        WHERE post_id = 1 AND meta_key LIKE '%logo%'
        LIMIT 5
    ");
    $stmt->execute();
    $fields = $stmt->fetchAll();
    
    echo "<h3>Champs WordPress existants :</h3>";
    foreach ($fields as $field) {
        echo "<p>🔧 {$field['meta_key']} = {$field['meta_value']}</p>";
    }
    
    // Test d'écriture
    $stmt = $wp_pdo->prepare("
        INSERT INTO wp_postmeta (post_id, meta_key, meta_value) 
        VALUES (1, 'test_sma_sync', ?) 
        ON DUPLICATE KEY UPDATE meta_value = ?
    ");
    $testValue = 'Test sync ' . date('Y-m-d H:i:s');
    $stmt->execute([$testValue, $testValue]);
    
    echo "<p>✅ Test d'écriture réussi : $testValue</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
