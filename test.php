<?php
require_once 'api/config.php';

echo "<h2>🔧 Test connexion WordPress</h2>";

try {
    // Test connexion WordPress
    $wp_pdo = new PDO("mysql:host=$wp_host;dbname=$wp_db;charset=utf8mb4", $wp_user, $wp_pass);
    $wp_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Connexion WordPress : OK</p>";
    
    // Lister les posts
    $stmt = $wp_pdo->query("SELECT ID, post_title, post_type FROM wp_posts WHERE post_status = 'publish' LIMIT 10");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📄 Posts disponibles :</h3>";
    echo "<table border='1'><tr><th>ID</th><th>Titre</th><th>Type</th></tr>";
    foreach ($posts as $post) {
        $style = ($post['ID'] == $wp_post_id) ? 'background: lightgreen;' : '';
        echo "<tr style='$style'><td>{$post['ID']}</td><td>{$post['post_title']}</td><td>{$post['post_type']}</td></tr>";
    }
    echo "</table>";
    
    // Lister les meta du post configuré
    echo "<h3>📋 Meta fields du post {$wp_post_id} :</h3>";
    $stmt = $wp_pdo->prepare("SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key NOT LIKE '_%' AND meta_value != '' LIMIT 20");
    $stmt->execute([$wp_post_id]);
    $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($metas) > 0) {
        echo "<table border='1'><tr><th>Clé</th><th>Valeur</th></tr>";
        foreach ($metas as $meta) {
            $preview = strlen($meta['meta_value']) > 50 ? substr($meta['meta_value'], 0, 50) . '...' : $meta['meta_value'];
            echo "<tr><td>{$meta['meta_key']}</td><td>{$preview}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Aucun meta field trouvé pour ce post !</p>";
    }
    
    // Test d'écriture
    echo "<h3>✏️ Test écriture :</h3>";
    echo "<form method='POST'>";
    echo "<p>Clé : <input name='test_key' value='sma_test' style='width: 200px;'></p>";
    echo "<p>Valeur : <input name='test_value' value='Test depuis SMA - " . date('H:i:s') . "' style='width: 300px;'></p>";
    echo "<button type='submit'>Écrire dans WordPress</button>";
    echo "</form>";
    
    if ($_POST['test_key'] ?? false) {
        $test_key = $_POST['test_key'];
        $test_value = $_POST['test_value'];
        
        $stmt = $wp_pdo->prepare("
            INSERT INTO wp_postmeta (post_id, meta_key, meta_value) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)
        ");
        
        if ($stmt->execute([$wp_post_id, $test_key, $test_value])) {
            echo "<p style='color: green;'>✅ Écriture réussie ! Rechargez pour voir.</p>";
        } else {
            echo "<p style='color: red;'>❌ Érreur écriture.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
