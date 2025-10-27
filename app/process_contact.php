<?php
/**
 * Proses Backend Formulir Kontak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

// Hanya proses jika request method-nya POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil dan sanitasi data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];

    // 2. Validasi
    if (empty($name)) {
        $errors[] = "Nama wajib diisi.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }
    if (empty($message)) {
        $errors[] = "Pesan wajib diisi.";
    }

    // 3. Jika ada error, redirect kembali
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode(' ', $errors);
        $_SESSION['form_data'] = $_POST;
        header("Location: ../public/contact.php");
        exit();
    }

    // 4. Proses ke Database jika tidak ada error
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);

        // Registrasi berhasil, redirect ke login
        $_SESSION['success_message'] = "Terima kasih! Pesan Anda telah terkirim.";
        header("Location: ../public/contact.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Gagal mengirim pesan: " . $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        header("Location: ../public/contact.php");
        exit();
    }
} else {
    // Jika bukan POST, redirect
    header("Location: ../public/contact.php");
    exit();
}