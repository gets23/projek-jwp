<?php
/**
 * Proses Backend Login
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

$errors = [];
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2. Validasi
    if (empty($email) || empty($password)) {
        $errors[] = "Email dan password wajib diisi.";
    }

    // 3. Cek ke Database
    if (empty($errors)) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verifikasi user dan password
            if ($user && password_verify($password, $user['password'])) {
                // Login sukses!
                
                // Regenerate session ID untuk keamanan
                session_regenerate_id(true);

                // Simpan data user di session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role_id'] = $user['role_id'];

                // Redirect ke dashboard berdasarkan role
                if ($user['role_id'] == 1) { // 1 = admin
                    header("Location: ../public/admin/dashboard.php");
                } else { // user biasa
                    header("Location: ../public/index.php");
                }
                exit();

            } else {
                // User tidak ditemukan atau password salah
                $errors[] = "Email atau password salah.";
            }

        } catch (PDOException $e) {
            $errors[] = "Error database: " . $e->getMessage();
        }
    }

    // 4. Jika ada error, redirect kembali ke login
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = ['email' => $email];
        header("Location: ../public/login.php");
        exit();
    }

} else {
    header("Location: ../public/login.php");
    exit();
}
