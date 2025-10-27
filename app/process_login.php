<?php
// app/process_login.php
include __DIR__ . '/../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: ../public/login.php?error=' . urlencode('Email & password wajib diisi'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../public/login.php?error=' . urlencode('Email tidak valid'));
    exit;
}

// ambil user berdasarkan email
$stmt = mysqli_prepare($conn, "SELECT id, name, password_hash, role FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id, $name, $password_hash, $role);
$found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$found) {
  // jangan jelaskan lebih detil (keamanan)
    header('Location: ../public/login.php?error=' . urlencode('Email atau password salah'));
    exit;
}

if (!password_verify($password, $password_hash)) {
    header('Location: ../public/login.php?error=' . urlencode('Email atau password salah'));
    exit;
}

// login sukses: set session
session_regenerate_id(true);
$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_role'] = $role;

// redirect berdasarkan role
if ($role === 'admin') {
    header('Location: ../public/admin/admin_dashboard.php');
    exit;
} else {
    header('Location: ../public/users/users_dashboard.php');
    exit;
}