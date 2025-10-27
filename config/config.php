<?php
/**
 * File Konfigurasi Database
 * * Simpan kredensial database kamu di sini.
 */

// Mulai session di setiap halaman
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pengaturan Database
define('DB_HOST', '127.0.0.1'); // atau 'localhost'
define('DB_NAME', 'kuliah_blog');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan dengan password database kamu
define('DB_CHARSET', 'utf8mb4');

// Pengaturan Error Reporting (matikan di produksi)
error_reporting(E_ALL);
ini_set('display_errors', 1);
