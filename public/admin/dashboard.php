<?php
$page_title = "Admin Dashboard";

// Panggil header DULU
require_once __DIR__ . '/../includes/header.php';

// Panggil auth SETELAH header (karena header panggil config/session)
require_once __DIR__ . '/../../app/auth.php';

// Proteksi halaman ini, hanya untuk admin
require_admin(); 
?>

<!-- Konten Halaman Admin -->
<div class="bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Admin Dashboard</h2>
    <p class="text-gray-700">
        Selamat datang di dashboard admin, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.
    </p>
    <p class="mt-4 text-gray-700">
        Dari sini, Anda akan bisa mengelola:
    </p>
    <ul class="list-disc list-inside mt-4 space-y-2 text-gray-700">
        <li>
            <a href="articles.php" class="text-indigo-600 hover:underline">Manajemen Artikel</a> (Belum dibuat)
        </li>
        <li>
            <a href="books.php" class="text-indigo-600 hover:underline">Manajemen Buku</a> (Belum dibuat)
        </li>
        <li>
            <a href="role_management.php" class="text-indigo-600 hover:underline">Manajemen Role</a> (Belum dibuat)
        </li>
    </ul>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/../includes/footer.php';
?>
