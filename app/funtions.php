<?php

require_once __DIR__ . '/db.php';

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function verifyCsrfToken($token) {
    if (empty($token) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    // Hapus token setelah digunakan untuk mencegah penggunaan ulang
    unset($_SESSION['csrf_token']);
    return true;
}

function redirect($path = '') {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit();
}

function getAll($table) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM {$table}");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Panggil fungsi untuk memastikan CSRF token dibuat
generateCsrfToken();
