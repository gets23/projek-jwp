<?php
// File: app/auth.php - Fungsi autentikasi dan pengecekan hak akses

require_once __DIR__ . '/../config/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Anda harus login untuk mengakses halaman ini.";
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error_message'] = "Akses ditolak. Hanya Admin yang dapat mengakses halaman ini.";
        redirect('index.php');
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
