<?php
define('DB_HOST', 'localhost');
define('DB_NOMBRE', 'debihaby_db');
define('DB_USUARIO', 'root');
define('DB_CONTRASENA', '');
define('DB_CODIFICACION', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NOMBRE . ";charset=" . DB_CODIFICACION;
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USUARIO, DB_CONTRASENA, $opciones);
} catch (\PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
