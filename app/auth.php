<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('e')) {
    require_once __DIR__ . '/functions.php';
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

function current_user_name() {
    return $_SESSION['user_name'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /Pert3-web-blog/public/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin() {
    require_login();
    if (current_user_role() != 'admin') {
        header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
        echo "<h1>403 - Akses ditolak</h1><p>Halaman ini hanya untuk admin.</p>";
        exit;
    }
}