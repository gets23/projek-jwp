<?php
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../../app/functions.php';

// Proteksi halaman
require_admin();
?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-4xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>
    <p class="text-lg text-gray-600 mb-8">Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Anda memiliki akses admin.</p>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="manage_articles.php" class="block bg-blue-500 hover:bg-blue-600 text-white p-6 rounded-lg shadow-md transition duration-200">
            <h2 class="text-2xl font-bold mb-2">Manajemen Artikel</h2>
            <p>Tambah, edit, atau hapus artikel blog.</p>
        </a>
        <a href="manage_books.php" class="block bg-green-500 hover:bg-green-600 text-white p-6 rounded-lg shadow-md transition duration-200">
            <h2 class="text-2xl font-bold mb-2">Manajemen Buku</h2>
            <p>Tambah, edit, atau hapus buku yang dijual.</p>
        </a>
        <!-- Bisa ditambahkan link ke manajemen kontak, user, dll. -->
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
