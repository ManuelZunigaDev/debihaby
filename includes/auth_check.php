<?php
// Role-Based Access Control Helper

/**
 * Check if a user is logged in and optionally verify their role
 * @param string|null $rolRequerido The role required to access the page ('admin' or 'student')
 */
function protegerPagina($rolRequerido = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['id_usuario'])) {
        header('Location: login.php');
        exit;
    }

    if ($rolRequerido) {
        // We assume 'rol' is stored in the session for performance
        // If not, it should be fetched once and stored
        if (!isset($_SESSION['rol_usuario']) || $_SESSION['rol_usuario'] !== $rolRequerido) {
            header('Location: dashboard.php?error=unauthorized');
            exit;
        }
    }
}

/**
 * Basic CSRF protection placeholder for forms
 */
function getCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
