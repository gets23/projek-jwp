<?php
// File: app/functions.php

function check_admin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        // Bisa arahkan ke login atau halaman lain
        redirect(BASE_URL . '/login.php?error=Akses ditolak');
    }
}


// Memulai session jika belum ada
function start_secure_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Redirect ke halaman lain
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Cek apakah user sudah login
function is_logged_in() {
    start_secure_session();
    return isset($_SESSION['user_id']);
}

// Cek apakah user adalah admin
function is_admin() {
    start_secure_session();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fungsi untuk proteksi halaman admin
function require_admin() {
    if (!is_admin()) {
        // Jika bukan admin, redirect ke halaman login atau halaman utama
        redirect(BASE_URL . '/login.php');
    }
}

// Fungsi untuk proteksi halaman yang butuh login (misal: keranjang)
function require_login() {
    if (!is_logged_in()) {
        redirect(BASE_URL . '/login.php');
    }
}

// Membersihkan input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
