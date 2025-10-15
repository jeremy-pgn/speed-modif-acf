<?php
require_once 'config.php';

echo "<h2>🔍 Debug SMA - Base de données</h2>";

try {
    // Test connexion
    echo "<p>✅ Connexion à la base : OK</p>";
    
    // Lister tous les utilisateurs
    $stmt = $pdo->query("SELECT id, email, password, name, is_active FROM sma_users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>👥 Utilisateurs dans la base :</h3>";
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Password</th><th>Name</th><th>Active</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['password'] . "</td>";
            echo "<td>" . $user['name'] . "</td>";
            echo "<td>" . ($user['is_active'] ? 'OUI' : 'NON') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Test d'authentification
        echo "<h3>🧪 Test d'authentification</h3>";
        echo "<form method='POST'>";
        echo "<p>Email: <input name='test_email' value='{$users[0]['email']}' style='width: 200px;'></p>";
        echo "<p>Password: <input name='test_password' value='{$users[0]['password']}' style='width: 200px;'></p>";
        echo "<button type='submit'>Tester ces identifiants</button>";
        echo "</form>";
        
        if ($_POST['test_email'] ?? false) {
            $email = $_POST['test_email'];
            $password = $_POST['test_password'];
            
            $stmt = $pdo->prepare("SELECT id, name FROM sma_users WHERE email = ? AND password = ? AND is_active = 1");
            $stmt->execute([$email, $password]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "<p style='color: green;'>✅ Test réussi ! Utilisateur trouvé : " . $result['name'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Test échoué ! Utilisateur non trouvé.</p>";
                
                // Test plus poussé
                $stmt2 = $pdo->prepare("SELECT id, email, password FROM sma_users WHERE email = ?");
                $stmt2->execute([$email]);
                $user_check = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($user_check) {
                    echo "<p style='color: orange;'>⚠️ Utilisateur trouvé mais mot de passe incorrect.</p>";
                    echo "<p>Password en base : '{$user_check['password']}'</p>";
                    echo "<p>Password saisi : '{$password}'</p>";
                } else {
                    echo "<p style='color: red;'>❌ Email non trouvé dans la base.</p>";
                }
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Aucun utilisateur trouvé !</p>";
        
        // Créer un utilisateur de test
        echo "<h3>🛠️ Créer un utilisateur de test</h3>";
        echo "<form method='POST'>";
        echo "<input type='hidden' name='create_user' value='1'>";
        echo "<button type='submit' style='padding: 10px; background: green; color: white; border: none;'>Créer utilisateur test</button>";
        echo "</form>";
        
        if ($_POST['create_user'] ?? false) {
            try {
                $stmt = $pdo->prepare("INSERT INTO sma_users (email, password, name, is_active) VALUES (?, ?, ?, 1)");
                $stmt->execute(['admin@test.com', 'admin123', 'Admin Test']);
                echo "<p style='color: green;'>✅ Utilisateur créé ! Email: admin@test.com, Password: admin123</p>";
                echo "<script>location.reload();</script>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Erreur création : " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>
