<?php
require_once 'includes/config.php';

echo "<h2>DebiHaby Database Migration</h2>";

try {
    // Read the SQL file
    $sql = file_get_contents('db/database.sql');
    
    if (!$sql) {
        throw new Exception("Could not find db/database.sql");
    }

    // Since database.sql might contain multiple statements, 
    // and PDO::exec can handle multiple statements in MySQL
    $pdo->exec($sql);
    
    echo "<div style='color: green; padding: 10px; border: 1px solid green;'>";
    echo "✅ Database migrated successfully!<br>";
    echo "New 'courses' table created and 25 lessons inserted.";
    echo "</div>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "❌ Error migrating database: " . $e->getMessage() . "<br>";
    echo "</div>";
}
