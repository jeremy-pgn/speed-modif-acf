<?php
require_once 'api/config.php';

echo "<h1>🔍 Test Connexion WordPress</h1>";

// Test 1 : Connexion
try {
    $wp_dsn = "mysql:host=" . WP_HOST . ";dbname=" . WP_DATABASE . ";charset=utf8mb4";
    $wp_pdo = new PDO($wp_dsn, WP_USER, WP_PASSWORD);
    echo "<p>✅ Connexion WordPress réussie</p>";
    
    // Test 2 : WP_POST_ID
    echo "<p>🎯 WP_POST_ID configuré : " . WP_POST_ID . "</p>";
    
    // Test 3 : Vérifier si le post existe
    $stmt = $wp_pdo->prepare("SELECT post_title FROM wp_posts WHERE ID = ?");
    $stmt->execute([WP_POST_ID]);
    $post = $stmt->fetch();
    
    if ($post) {
        echo "<p>✅ Post ID " . WP_POST_ID . " trouvé : " . $post['post_title'] . "</p>";
    } else {
        echo "<p>❌ Post ID " . WP_POST_ID . " non trouvé !</p>";
    }
    
    // Test 4 : Test d'insertion
    echo "<h2>🧪 Test d'Insertion</h2>";
    $test_key = 'test_debug_sync';
    $test_value = 'Test ' . date('Y-m-d H:i:s');
    
    $stmt = $wp_pdo->prepare("
        INSERT INTO wp_postmeta (post_id, meta_key, meta_value) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)
    ");
    $result = $stmt->execute([WP_POST_ID, $test_key, $test_value]);
    
    if ($result) {
        echo "<p>✅ Insertion test réussie</p>";
        
        // Vérifier l'insertion
        $stmt = $wp_pdo->prepare("
            SELECT meta_value FROM wp_postmeta 
            WHERE post_id = ? AND meta_key = ?
        ");
        $stmt->execute([WP_POST_ID, $test_key]);
        $inserted = $stmt->fetch();
        
        if ($inserted) {
            echo "<p>✅ Valeur récupérée : " . $inserted['meta_value'] . "</p>";
        } else {
            echo "<p>❌ Valeur non trouvée après insertion</p>";
        }
        
    } else {
        echo "<p>❌ Insertion test échouée</p>";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
