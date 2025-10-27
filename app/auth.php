<?php
/**
 * File Helper Autentikasi
 * * Berisi fungsi untuk mengecek status login dan role.
 */

// Pastikan config.php sudah di-include (yang memanggil session_start())

/**
 * Cek apakah user sudah login.
 * Jika tidak, redirect ke halaman login.
 */
if (!function_exists('require_login')) {
    function require_login() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php"); // Sesuaikan path jika perlu
            exit();
        }
    }
}

/**
 * Cek apakah user adalah admin.
 * Jika tidak, redirect ke halaman utama atau tampilkan error.
 * * Asumsi: role_id 1 = admin
 */
if (!function_exists('require_admin')) {
    function require_admin() {
        require_login(); // Pastikan login dulu
        
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
            // Jika bukan admin, tendang ke index
            header("Location: /index.php"); // Sesuaikan path jika perlu
            exit();
        }
    }
}

/**
 * Mendapatkan data user yang sedang login dari database.
 * @return array|false Data user atau false jika tidak ditemukan.
 */
if (!function_exists('get_current_user')) {
    function get_current_user() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        try {
            // Kita panggil getPDO() di dalam sini
            // Pastikan db.php sudah di-include sebelum fungsi ini dipanggil
            $pdo = getPDO(); 
            $stmt = $pdo->prepare("SELECT id, name, email, role_id FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            // Handle error (misal: log error)
            return false;
        }
    }
}

