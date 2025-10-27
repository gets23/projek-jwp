<?php
$page_title = "Selamat Datang di Super Web";
// Panggil header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Konten Halaman -->
<div class="bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Halo, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Pengunjung'; ?>!</h2>
    <p class="text-gray-700">
        Ini adalah halaman utama Super Web kamu.
    </p>
    <p class="mt-4 text-gray-700">
        Saat ini, sistem registrasi dan login sudah berfungsi.
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
            Anda login sebagai <strong>Admin</strong>. Anda bisa mengakses <a href="admin/dashboard.php" class="text-indigo-600 hover:underline">Admin Dashboard</a>.
        <?php elseif (isset($_SESSION['user_id'])): ?>
             Anda login sebagai <strong>User</strong>.
        <?php else: ?>
            Silakan <a href="login.php" class="text-indigo-600 hover:underline">Login</a> atau <a href="register.php" class="text-indigo-600 hover:underline">Register</a> untuk memulai.
        <?php endif; ?>
    </p>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/includes/footer.php';
?>
