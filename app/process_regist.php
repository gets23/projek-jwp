<?php
// File: app/process_login.php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php'; // Ini akan memulai session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        redirect(BASE_URL . '/login.php?error=Email dan password wajib diisi');
    }

    // 1. Ambil data user berdasarkan email (Prepared Statement)
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // 2. Dapatkan hasilnya
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // 3. Ambil data user
        $user = $result->fetch_assoc();

        // 4. Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Sukses! Simpan data user ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Redirect ke dashboard admin jika rolenya 'admin'
            if ($user['role'] === 'admin') {
                redirect(BASE_URL . '/admin/dashboard.php');
            } else {
                redirect(BASE_URL . '/index.php');
            }
        } else {
            // Password salah
            redirect(BASE_URL . '/login.php?error=Email atau password salah');
        }
    } else {
        // Email tidak ditemukan
        redirect(BASE_URL . '/login.php?error=Email atau password salah');
    }
    
    $stmt->close();
    $conn->close();

} else {
    redirect(BASE_URL . '/login.php');
}

