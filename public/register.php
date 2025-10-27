<?php
// File: public/register.php - Halaman Pendaftaran Akun
$page_title = "Daftar Akun | " . APP_NAME;
require_once 'layout/header.php';
?>

<div class="container mx-auto px-4 py-12 flex justify-center">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-2xl">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Daftar Akun Baru</h2>
        
        <form action="<?= BASE_URL ?>/app/process_register.php" method="POST">
            
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-medium mb-2">Nama Lengkap</label>
                <input type="text" id="name" name="name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Masukkan Nama Anda">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="contoh@mail.com">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Minimal 6 karakter">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-200 font-semibold shadow-md">
                Daftar
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600">
            Sudah punya akun? <a href="<?= BASE_URL ?>/login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Login di sini</a>
        </p>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
