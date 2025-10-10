<?php
require_once 'api/config.php';

try {
    $pdo = new PDO($dsn, $username, $password_db, $options);
    echo "<h2>✅ Speed Modif ACF - Test Base de Données</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sma_users");
    $result = $stmt->fetch();
    echo "<p>👥 Utilisateurs : " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sma_acf_fields");
    $result = $stmt->fetch();
    echo "<p>🔧 Champs ACF : " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT email, name, role FROM sma_users");
    echo "<h3>Comptes disponibles :</h3>";
    while ($user = $stmt->fetch()) {
        echo "<p>📧 " . $user['email'] . " - " . $user['name'] . " (" . $user['role'] . ")</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>🎯 Ton app est prête ! Vas sur :</strong></p>";
    echo "<p><a href='index.html' target='_blank'>🚀 http://localhost:8080/index.html</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erreur</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
