<?php
/**
 * Proses Backend Registrasi
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

$errors = [];
$name = '';
$email = '';

// Hanya proses jika request method-nya POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil dan sanitasi data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Validasi
    if (empty($name)) {
        $errors[] = "Nama wajib diisi.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek apakah email sudah terdaftar
    if (empty($errors)) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email sudah terdaftar.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
        }
    }

    // 3. Proses ke Database jika tidak ada error
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Default role_id = 2 (user)
            $default_role_id = 2; 

            $pdo = getPDO();
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $default_role_id]);

            // Registrasi berhasil, redirect ke login
            $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
            header("Location: ../public/login.php");
            exit();

        } catch (PDOException $e) {
            $errors[] = "Gagal mendaftar: " . $e->getMessage();
        }
    }

    // 4. Jika ada error, simpan di session dan redirect kembali ke form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = ['name' => $name, 'email' => $email];
        header("Location: ../public/register.php");
        exit();
    }
} else {
    // Jika bukan POST, redirect
    header("Location: ../public/register.php");
    exit();
}
