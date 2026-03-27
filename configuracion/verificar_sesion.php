<?php


/**
 * vERIFICACION DE ROLES XD
 */
/**
 * Verifica si un usuario ha iniciado sesión y opcionalmente valida su rol.
 * @param string|null $rolRequerido El rol necesario para acceder ('admin' o 'estudiante')
 */
function protegerPagina($rolRequerido = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['id_usuario'])) {
        header('Location: login.php');
        exit;
    }

    if ($rolRequerido) {
        // Asumimos que 'rol_usuario' se guarda en la sesión para mejor rendimiento
        if (!isset($_SESSION['rol_usuario']) || $_SESSION['rol_usuario'] !== $rolRequerido) {
            header('Location: dashboard.php?error=no_autorizado');
            exit;
        }
    }
}

/**
 * Generador de token CSRF para formularios
 * para evaluar en cual vas xD 
 */
function obtenerTokenCsrf()
{
    if (!isset($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['token_csrf'];
}

function verificarTokenCsrf($token)
{
    return isset($_SESSION['token_csrf']) && hash_equals($_SESSION['token_csrf'], $token);
}
