<?php
// Enrutador simple para el sistema DebiHaby profesionalizado
session_start();

if (isset($_SESSION['id_usuario'])) {
    header('Location: vistas/dashboard.php');
} else {
    header('Location: vistas/login.php');
}
exit;
