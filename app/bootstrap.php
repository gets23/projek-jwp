<?php
// File: app/bootstrap.php
// Ini adalah file "pemuat" utama untuk seluruh aplikasi.
// Semua file lain akan memanggil file INI SAJA.

// 1. Muat Konfigurasi (BASE_URL, Info DB)
// Kita butuh __DIR__ untuk path yang absolut dan aman
require_once __DIR__ . '/../config/config.php';

// 2. Mulai Session (Harus sebelum output HTML)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Muat Fungsi-Fungsi Pembantu (redirect, check_admin)
// File ini sekarang AMAN menggunakan BASE_URL karena config.php sudah dimuat
require_once __DIR__ . '/functions.php';

// 4. Muat Koneksi Database (Membuat variabel $conn)
// File ini sekarang AMAN menggunakan konstanta DB_HOST dll.
require_once __DIR__ . '/db.php';

// Semua sudah siap. $conn, BASE_URL, dan semua fungsi sekarang tersedia.
?>
