<?php
// File: app/process_register.php - Logika pemrosesan pendaftaran akun

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

// 1. Verifikasi CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = "Token Keamanan tidak valid. Coba lagi.";
    redirect('register.php');
}

// Ambil dan sanitasi input
$name = trim($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($name) || empty($email) || empty($password)) {
    $_SESSION['error_message'] = "Semua field wajib diisi.";
    redirect('register.php');
}

if (strlen($password) < 6) {
    $_SESSION['error_message'] = "Password minimal 6 karakter.";
    redirect('register.php');
}

try {
    $pdo = getConnection();
    
    // Hash password menggunakan BCRYPT
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $default_role = 'user'; // Semua pendaftar baru adalah 'user'

    // 2. Gunakan Prepared Statement untuk INSERT
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':role', $default_role);
    
    $stmt->execute();

    $_SESSION['success_message'] = "Pendaftaran berhasil! Silakan login.";
    redirect('login.php');

} catch (PDOException $e) {
    // 3. Tangani error duplikat email (code 23000)
    if ($e->getCode() == 23000) {
        $_SESSION['error_message'] = "Email ini sudah terdaftar. Silakan gunakan email lain.";
    } else {
        error_log("Register Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Pendaftaran gagal. Terjadi kesalahan sistem.";
    }
    redirect('register.php');
}
