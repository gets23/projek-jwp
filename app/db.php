<?php
/**
 * File Koneksi Database (PDO)
 * * Menggunakan PDO untuk koneksi yang aman.
 */

// Jangan lupa panggil config.php sebelum memanggil file ini
// require_once __DIR__ . '/../config/config.php';

function getPDO() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // Hentikan eksekusi dan tampilkan error jika koneksi gagal
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    return $pdo;
}
