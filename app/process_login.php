<?php
// File: app/process_login.php - Logika pemrosesan login

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

// 1. Verifikasi CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = "Token Keamanan tidak valid. Coba lagi.";
    redirect('login.php');
}

// Ambil dan sanitasi input
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error_message'] = "Email dan password wajib diisi.";
    redirect('login.php');
}

try {
    $pdo = getConnection();
    
    // 2. Gunakan Prepared Statement untuk mencegah SQL Injection
    $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        // 3. Verifikasi Password
        if (password_verify($password, $user['password'])) {
            // Login Berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['success_message'] = "Selamat datang, " . htmlspecialchars($user['name']) . "! Anda berhasil login.";

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            // Password salah
            $_SESSION['error_message'] = "Email atau password salah.";
            redirect('login.php');
        }
    } else {
        // User tidak ditemukan
        $_SESSION['error_message'] = "Email atau password salah.";
        redirect('login.php');
    }

} catch (PDOException $e) {
    // Log error (sebaiknya di-log ke file, bukan ditampilkan ke user)
    error_log("Login Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    redirect('login.php');
}
