<?php
/**
 * DebiHaby Database Setup Script
 * This script automatically executes the database.sql file to initialize or reset the system.
 */

// Basic reporting
header('Content-Type: text/plain');
echo "=== DebiHaby Database Setup ===\n\n";

// 1. Database Connection (Standard root with no password for Laragon/XAMPP)
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Connect to MySQL first (without specific DB)
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "[✓] Connected to MySQL server.\n";

    // 2. Read the SQL file
    $sqlFile = __DIR__ . '/db/database.sql';
    if (!file_exists($sqlFile)) {
        die("[X] Error: 'db/database.sql' not found. Please ensure the file exists.\n");
    }

    $sql = file_get_contents($sqlFile);
    echo "[✓] Read 'database.sql' file.\n";

    // 3. Execute the SQL commands
    // Note: Multiple queries in one exec() might fail depending on PDO driver,
    // but the following is the most efficient way to run a whole script.
    echo "[...] Executing SQL commands... (this may take a few seconds)\n";
    $pdo->exec($sql);
    echo "[✓] Database and tables created/updated successfully!\n";

    echo "\n=== Setup Complete ===\n";
    echo "You can now login with:\n";
    echo "User: user\n";
    echo "Pass: user\n";
    echo "\nURL: http://localhost/debihaby/debihaby/login.php\n";

} catch (PDOException $e) {
    echo "[X] Database Error: " . $e->getMessage() . "\n";
    echo "\nIf the Error says 'Access denied', check your MySQL user/pass in setup.php.\n";
}
