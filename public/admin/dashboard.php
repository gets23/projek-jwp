<?php
// File: public/admin/dashboard.php - Halaman Dashboard Admin

require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/functions.php';

// Wajibkan Admin untuk mengakses halaman ini
requireAdmin();

$page_title = "Admin Dashboard | " . APP_NAME;
require_once __DIR__ . '/../layout/header.php';

// Contoh pengambilan data statistik (simulasi)
$total_articles = count(getAll('articles'));
$total_books = count(getAll('books'));
$total_contacts = count(getAll('contacts'));
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-4xl font-bold text-gray-800 mb-6 border-b pb-2">Admin Dashboard</h1>
    <p class="text-xl text-gray-600 mb-8">Halo, <span class="font-semibold text-indigo-600"><?= htmlspecialchars($_SESSION['user_name']) ?></span>. Selamat datang di pusat kontrol Anda.</p>

    <!-- Menu Cepat Admin -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card Artikel -->
        <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-indigo-500">
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Manajemen Artikel</h2>
            <p class="text-3xl font-bold text-indigo-600 mb-4"><?= $total_articles ?></p>
            <a href="<?= BASE_URL ?>/admin/articles.php" class="text-indigo-500 hover:text-indigo-700 font-medium">Kelola Artikel &rarr;</a>
        </div>

        <!-- Card Buku -->
        <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-teal-500">
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Manajemen Buku</h2>
            <p class="text-3xl font-bold text-teal-600 mb-4"><?= $total_books ?></p>
            <a href="<?= BASE_URL ?>/admin/books.php" class="text-teal-500 hover:text-teal-700 font-medium">Kelola Buku &rarr;</a>
        </div>
        
        <!-- Card Kontak -->
        <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-yellow-500">
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Pesan Masuk</h2>
            <p class="text-3xl font-bold text-yellow-600 mb-4"><?= $total_contacts ?></p>
            <a href="#" class="text-yellow-500 hover:text-yellow-700 font-medium">Lihat Pesan &rarr;</a>
        </div>

        <!-- Card Pengguna -->
        <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-pink-500">
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Pengguna</h2>
            <p class="text-3xl font-bold text-pink-600 mb-4"><?= count(getAll('users')) ?></p>
            <a href="#" class="text-pink-500 hover:text-pink-700 font-medium">Kelola Pengguna &rarr;</a>
        </div>
        
    </div>
    
    <!-- Bagian Lain -->
    <div class="mt-10 bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Informasi Sistem</h2>
        <p class="text-gray-600">Anda saat ini terdaftar sebagai role: <span class="font-bold text-indigo-600"><?= strtoupper($_SESSION['user_role']) ?></span>. Gunakan hak akses ini dengan bijak.</p>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
