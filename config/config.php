<?php
// File: config/config.php - Konfigurasi Dasar Aplikasi

// Informasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'kuliah_blog');
define('DB_USER', 'root');
define('DB_PASS', ''); // Ganti dengan password database Anda

// Konfigurasi Aplikasi
define('APP_NAME', 'Super Web Blog & Bookshop');
define('BASE_URL', 'http://localhost/project'); // Sesuaikan dengan URL proyek Anda

// Mulai sesi (WAJIB di awal semua file yang menggunakan sesi)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
