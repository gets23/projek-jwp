<?php
// File: app/process_contact.php - Logika pengiriman pesan kontak

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contact.php');
}

// 1. Verifikasi CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = "Token Keamanan tidak valid. Coba lagi.";
    redirect('contact.php');
}

// Ambil dan sanitasi input
$name = trim($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    $_SESSION['error_message'] = "Semua field wajib diisi.";
    redirect('contact.php');
}

try {
    $pdo = getConnection();
    
    // 2. Gunakan Prepared Statement untuk INSERT
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)");
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':message', $message);
    
    $stmt->execute();

    $_SESSION['success_message'] = "Pesan Anda berhasil terkirim! Kami akan segera merespon.";
    redirect('contact.php');

} catch (PDOException $e) {
    error_log("Contact Form Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Gagal mengirim pesan. Terjadi kesalahan sistem.";
    redirect('contact.php');
}
