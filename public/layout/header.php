<?php
// Memulai session dan memuat fungsi
require_once __DIR__ . '/../../app/functions.php';
start_secure_session();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Web</title>
    <!-- Memuat Tailwind CSS dari CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <a href="<?php echo BASE_URL; ?>/index.php" class="text-2xl font-bold text-gray-800">SuperWeb</a>
                </div>
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="text-gray-600 hover:text-blue-500 px-3 py-2 rounded-md">Home</a>
                    <a href="<?php echo BASE_URL; ?>/cart.php" class="text-gray-600 hover:text-blue-500 px-3 py-2 rounded-md">Keranjang</a>
                    
                    <?php if (is_admin()): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="text-gray-600 hover:text-blue-500 px-3 py-2 rounded-md">Admin Dashboard</a>
                    <?php endif; ?>

                    <?php if (is_logged_in()): ?>
                        <span class="text-gray-700 px-3 py-2">Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                        <a href="<?php echo BASE_URL; ?>/../app/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition duration-200">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="text-gray-600 hover:text-blue-500 px-3 py-2 rounded-md">Login</a>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200 ml-2">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-8">
        <!-- Konten dinamis akan dimulai di sini -->
