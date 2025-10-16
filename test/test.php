<?php
echo "<h1>🚀 Speed Modif ACF - Docker OK!</h1>";

// Test MySQL Docker
try {
    $pdo = new PDO('mysql:host=database;dbname=sma_database', 'root', 'root');
    echo "<p style='color: green;'>✅ MySQL Docker : Connecté</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ MySQL : " . $e->getMessage() . "</p>";
}
?>
