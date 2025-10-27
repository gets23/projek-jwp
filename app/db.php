<?php
// File: app/db.php - Fungsi untuk mendapatkan koneksi PDO

require_once __DIR__ . '/../config/config.php';

/**
 * Mendapatkan koneksi ke database menggunakan PDO.
 * @return PDO Koneksi database.
 */
function getConnection() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (\PDOException $e) {
        // Hentikan aplikasi jika koneksi gagal
        die("Koneksi database gagal: " . $e->getMessage());
    }
}
