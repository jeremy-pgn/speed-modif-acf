// Crée check_wordpress_fields.php
<?php
try {
    $pdo = new PDO("mysql:host=host.docker.internal;dbname=wordpress_db", 'root', 'root');
    
    echo "<h1>🔍 Champs ACF WordPress Existants</h1>";
    
    // Récupérer tous les meta_key pour la page d'accueil
    $stmt = $pdo->prepare("
        SELECT meta_key, meta_value 
        FROM wp_postmeta 
        WHERE post_id = 1 
        AND (meta_key LIKE '%logo%' 
         OR meta_key LIKE '%mobile%' 
         OR meta_key LIKE '%header%'
         OR meta_key LIKE '%titre%'
         OR meta_key LIKE 'field_%')
        ORDER BY meta_key
    ");
    $stmt->execute();
    $fields = $stmt->fetchAll();
    
    if (count($fields) > 0) {
        echo "<h3>✅ Champs WordPress trouvés :</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Meta Key (WordPress)</th><th>Valeur</th></tr>";
        
        foreach ($fields as $field) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($field['meta_key']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($field['meta_value']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Aucun champ ACF trouvé dans WordPress</p>";
        echo "<p>💡 Il faut d'abord créer des champs ACF dans WordPress !</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
