<!DOCTYPE html>
<html>
<head>
    <title>Test Sync Direct</title>
</head>
<body>
    <h1>🔧 Test Sync Direct WordPress</h1>
    
    <?php
    require_once 'api/config.php';
    
    echo "<h2>1. Configuration actuelle</h2>";
    echo "<ul>";
    echo "<li>Post ID: " . WP_POST_ID . "</li>";
    echo "<li>Host: " . WP_HOST . "</li>";
    echo "<li>DB: " . WP_DATABASE . "</li>";
    echo "</ul>";
    
    try {
        echo "<h2>2. Connexion WordPress</h2>";
        $wp_pdo = new PDO("mysql:host=" . WP_HOST . ";dbname=" . WP_DATABASE . ";charset=utf8mb4", WP_USER, WP_PASSWORD);
        $wp_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connexion WordPress OK<br>";
        
        echo "<h2>3. Champs SMA disponibles</h2>";
        $stmt = $pdo->query("SELECT id, field_key, field_label, field_value FROM sma_acf_fields WHERE is_active = 1 LIMIT 5");
        $sma_fields = $stmt->fetchAll();
        
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Key</th><th>Label</th><th>Value</th><th>Test</th></tr>";
        foreach ($sma_fields as $field) {
            echo "<tr>";
            echo "<td>{$field['id']}</td>";
            echo "<td>{$field['field_key']}</td>";
            echo "<td>{$field['field_label']}</td>";
            echo "<td>" . substr($field['field_value'], 0, 30) . "...</td>";
            echo "<td><a href='?test_field={$field['id']}'>🧪 Tester ce champ</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (isset($_GET['test_field'])) {
            $test_id = (int)$_GET['test_field'];
            echo "<h2>4. Test du champ ID $test_id</h2>";
            
            // Récupérer le champ
            $stmt = $pdo->prepare("SELECT field_key, field_value FROM sma_acf_fields WHERE id = ?");
            $stmt->execute([$test_id]);
            $field = $stmt->fetch();
            
            if ($field) {
                $field_key = $field['field_key'];
                $new_value = "TEST SYNC " . date('H:i:s');
                
                echo "<p><strong>Field Key:</strong> $field_key</p>";
                echo "<p><strong>Ancienne valeur:</strong> {$field['field_value']}</p>";
                echo "<p><strong>Nouvelle valeur:</strong> $new_value</p>";
                
                // 1. UPDATE dans SMA
                echo "<h3>4.1 Update SMA</h3>";
                $stmt = $pdo->prepare("UPDATE sma_acf_fields SET field_value = ? WHERE id = ?");
                if ($stmt->execute([$new_value, $test_id])) {
                    echo "✅ SMA mis à jour<br>";
                } else {
                    echo "❌ Erreur update SMA<br>";
                }
                
                // 2. Chercher dans WordPress
                echo "<h3>4.2 Recherche dans WordPress</h3>";
                $stmt = $wp_pdo->prepare("SELECT meta_id, meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = ?");
                $stmt->execute([WP_POST_ID, $field_key]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    echo "✅ Meta field trouvé - ID: {$existing['meta_id']}<br>";
                    echo "📝 Valeur actuelle: {$existing['meta_value']}<br>";
                    
                    // UPDATE WordPress
                    echo "<h3>4.3 Update WordPress</h3>";
                    $stmt = $wp_pdo->prepare("UPDATE wp_postmeta SET meta_value = ? WHERE meta_id = ?");
                    if ($stmt->execute([$new_value, $existing['meta_id']])) {
                        echo "✅ WordPress mis à jour !<br>";
                        
                        // Vérification finale
                        $stmt = $wp_pdo->prepare("SELECT meta_value FROM wp_postmeta WHERE meta_id = ?");
                        $stmt->execute([$existing['meta_id']]);
                        $final_value = $stmt->fetchColumn();
                        
                        echo "<h3>4.4 Vérification finale</h3>";
                        if ($final_value === $new_value) {
                            echo "🎉 <strong>SYNCHRONISATION RÉUSSIE !</strong><br>";
                            echo "✅ Valeur dans WordPress: $final_value<br>";
                        } else {
                            echo "❌ Synchronisation échouée<br>";
                            echo "Expected: $new_value<br>";
                            echo "Got: $final_value<br>";
                        }
                    } else {
                        echo "❌ Erreur update WordPress<br>";
                    }
                } else {
                    echo "❌ Meta field '$field_key' NOT FOUND pour post " . WP_POST_ID . "<br>";
                    echo "<strong>🔧 SOLUTION:</strong> Créez ce champ dans WordPress admin !<br>";
                    
                    // Essayer de créer le meta field
                    echo "<h3>4.3 Création meta field</h3>";
                    $stmt = $wp_pdo->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)");
                    if ($stmt->execute([WP_POST_ID, $field_key, $new_value])) {
                        echo "✅ Meta field créé !<br>";
                        echo "🎉 <strong>SYNCHRONISATION RÉUSSIE (nouveau champ) !</strong><br>";
                    } else {
                        echo "❌ Impossible de créer le meta field<br>";
                    }
                }
            } else {
                echo "❌ Champ SMA non trouvé<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <h2>5. Meta fields existants pour le post <?= WP_POST_ID ?></h2>
    <?php
    try {
        $stmt = $wp_pdo->prepare("SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ? ORDER BY meta_key");
        $stmt->execute([WP_POST_ID]);
        $metas = $stmt->fetchAll();
        
        if (count($metas) > 0) {
            echo "<table border='1'><tr><th>Meta Key</th><th>Meta Value</th></tr>";
            foreach ($metas as $meta) {
                $preview = strlen($meta['meta_value']) > 50 ? substr($meta['meta_value'], 0, 50) . '...' : $meta['meta_value'];
                echo "<tr><td>{$meta['meta_key']}</td><td>$preview</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ Aucun meta field pour ce post !</p>";
        }
    } catch (Exception $e) {
        echo "<p>Erreur: " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>
