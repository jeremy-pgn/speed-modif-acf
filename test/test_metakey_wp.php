<?php
echo "<h1>🔍 TOUS les Meta_Keys WordPress (SANS FILTRE)</h1>";

try {
    $pdo = new PDO("mysql:host=host.docker.internal;dbname=wordpress_db", 'root', 'root');
    echo "<p>✅ Connexion WordPress réussie</p>";
    
    // TOUT récupérer, sans aucun filtre
    $stmt = $pdo->prepare("
        SELECT meta_key, meta_value, post_id
        FROM wp_postmeta 
        ORDER BY post_id, meta_key
    ");
    $stmt->execute();
    $all_fields = $stmt->fetchAll();
    
    echo "<h2>📋 TOUS les Champs (" . count($all_fields) . " trouvés) :</h2>";
    
    foreach ($all_fields as $field) {
        $value_short = strlen($field['meta_value']) > 50 ? 
                      substr($field['meta_value'], 0, 50) . "..." : 
                      $field['meta_value'];
        
        echo "<p><strong>Post {$field['post_id']}</strong> : <strong>{$field['meta_key']}</strong> = " . htmlspecialchars($value_short) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
