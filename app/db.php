<?php

// INI ADALAH BAGIAN DEBUGGING
echo "<div style='background: #333; color: white; padding: 10px; font-family: monospace;'>";
echo "<strong>DEBUGGING PATH DARI app/db.php</strong><br>";

// Ini adalah jalur file config yang kita cari
$config_path = __DIR__ . '/../config/config.php';
echo "Mencari file config di: " . $config_path . "<br>";

// Kita cek apakah file itu benar-benar ada
if (file_exists($config_path)) {
    echo "<strong style='color: #00FF00;'>SUKSES:</strong> File config.php DITEMUKAN.<br>";
    
    // Jika ditemukan, kita coba load
    require_once $config_path;
    
    // Kita cek apakah konstanta-nya ter-load
    if (defined('DB_HOST')) {
        echo "<strong style='color: #00FF00;'>SUKSES:</strong> Konstanta DB_HOST berhasil di-load. Isinya: " . DB_HOST;
    } else {
        echo "<strong style='color: #FF0000;'>ERROR:</strong> File config.php ada, TAPI sepertinya ISINYA KOSONG atau tidak mendefinisikan DB_HOST.";
    }
    
} else {
    echo "<strong style='color: #FF0000;'>GAGAL TOTAL:</strong> File config.php TIDAK DITEMUKAN di jalur itu.<br>";
    echo "Pastikan folder 'config' kamu sejajar (satu level) dengan folder 'app'.";
}
echo "</div>";
// AKHIR BAGIAN DEBUGGING


// --- Kode Asli ---

// Memuat konfigurasi (ini seharusnya sudah di-load di atas, tapi kita taruh lagi)
require_once __DIR__ . '/../config/config.php';

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Membuat instance PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Jika koneksi berhasil (khusus untuk debug)
    echo "<div style='background: #E0FFE0; color: #006400; padding: 10px; font-family: monospace;'><strong>KONEKSI PDO KE DATABASE BERHASIL!</strong></div>";

} catch (PDOException $e) {
    // Menampilkan error jika koneksi gagal
    die("<div style='background: #FFEEEE; color: #D8000C; padding: 10px; font-family: monospace;'><strong>Koneksi ke database gagal:</strong> " . $e->getMessage() . "</div>");
}

