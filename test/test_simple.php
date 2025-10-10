<?php
echo "<h1>Test Connexion Simple</h1>";

// Test 1 : Connexion root vide
try {
    $pdo = new PDO("mysql:host=host.docker.internal;port=3306;dbname=sma_database", 'root', '');
    echo "✅ Connexion ROOT réussie (password vide)<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sma_users");
    $result = $stmt->fetch();
    echo "✅ Users trouvés : " . $result['count'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erreur ROOT vide : " . $e->getMessage() . "<br>";
    
    // Test 2 : Connexion root root
    try {
        $pdo = new PDO("mysql:host=host.docker.internal;port=3306;dbname=sma_database", 'root', 'root');
        echo "✅ Connexion ROOT/ROOT réussie<br>";
        
    } catch (Exception $e2) {
        echo "❌ Erreur ROOT/ROOT : " . $e2->getMessage() . "<br>";
        
        // Test 3 : Connexion localhost
        try {
            $pdo = new PDO("mysql:host=localhost;port=3306;dbname=sma_database", 'root', '');
            echo "✅ Connexion LOCALHOST réussie<br>";
            
        } catch (Exception $e3) {
            echo "❌ Erreur LOCALHOST : " . $e3->getMessage() . "<br>";
        }
    }
}
?>
