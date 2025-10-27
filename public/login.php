<?php
require_once 'layout/header.php';

// Jika sudah login, redirect ke index
if (is_logged_in()) {
    redirect(BASE_URL . '/index.php');
}
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Login</h1>

    <!-- Menampilkan pesan error jika ada -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Menampilkan pesan sukses (dari registrasi) -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>/../app/process_login.php" method="POST">
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
            <input type="email" id="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-6">
            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
            <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200">Login</button>
    </form>
    <p class="text-center text-gray-600 mt-6">
        Belum punya akun? <a href="register.php" class="text-blue-500 hover:underline">Register di sini</a>
    </p>
</div>

<?php require_once 'layout/footer.php'; ?>
