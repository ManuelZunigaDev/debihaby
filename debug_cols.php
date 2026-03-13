<?php
require_once 'includes/config.php';
try {
    $tables = ['usuarios', 'estadisticas_usuario', 'courses', 'lessons', 'user_progress', 'myths', 'news', 'expert_questions'];
    foreach ($tables as $table) {
        echo "--- Tabla: $table ---\n";
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "Columna: " . $row['Field'] . "\n";
            }
        } catch (Exception $e) {
            echo "Error o tabla no existe: $table\n";
        }
        echo "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
