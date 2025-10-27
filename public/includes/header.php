<?php
// File ini akan dipanggil di setiap awal halaman public
// Pastikan config.php dipanggil sebelum header ini
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Super Web'; ?></title>
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="h-full">
    <div class="min-h-full">
        <nav class="bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-white font-bold text-lg">SuperWeb</span>
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <!-- Link Navigasi -->
                                <a href="/index.php" class="bg-gray-900 text-white rounded-md px-3 py-2 text-sm font-medium">Home</a>
                                <a href="/articles.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Artikel</a>
                                <a href="/books.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Toko Buku</a>
                                <a href="/contact.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Kontak</a>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <span class="text-gray-300 mr-4">Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                                <?php if ($_SESSION['role_id'] == 1): ?>
                                    <a href="/admin/dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Admin Dashboard</a>
                                <?php endif; ?>
                                <a href="/logout.php" class="bg-red-600 text-white hover:bg-red-700 rounded-md px-3 py-2 text-sm font-medium">Logout</a>
                            <?php else: ?>
                                <a href="/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Login</a>
                                <a href="/register.php" class="ml-4 bg-indigo-600 text-white hover:bg-indigo-700 rounded-md px-3 py-2 text-sm font-medium">Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?php echo $page_title ?? 'Selamat Datang'; ?></h1>
            </div>
        </header>
        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <!-- Konten Halaman Mulai Di Sini -->
