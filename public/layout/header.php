<?php
// File: public/layout/header.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? APP_NAME) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Menggunakan font Inter secara default */
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- Navigasi Utama -->
<header class="bg-white shadow-md">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <a href="<?= BASE_URL ?>/index.php" class="text-xl font-bold text-indigo-600"><?= APP_NAME ?></a>
        <nav class="hidden md:flex space-x-4">
            <a href="<?= BASE_URL ?>/index.php" class="text-gray-600 hover:text-indigo-600 p-2 rounded-md transition duration-150">Beranda</a>
            <a href="<?= BASE_URL ?>/articles.php" class="text-gray-600 hover:text-indigo-600 p-2 rounded-md transition duration-150">Artikel</a>
            <a href="<?= BASE_URL ?>/books.php" class="text-gray-600 hover:text-indigo-600 p-2 rounded-md transition duration-150">Toko Buku</a>
            <a href="<?= BASE_URL ?>/contact.php" class="text-gray-600 hover:text-indigo-600 p-2 rounded-md transition duration-150">Kontak</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-white bg-indigo-600 hover:bg-indigo-700 p-2 rounded-md transition duration-150 font-medium">Admin Dashboard</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/logout.php" class="text-red-500 hover:text-red-700 p-2 rounded-md transition duration-150">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="text-indigo-600 hover:text-indigo-800 p-2 rounded-md transition duration-150">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="text-white bg-indigo-500 hover:bg-indigo-600 p-2 rounded-md transition duration-150">Daftar</a>
            <?php endif; ?>
        </nav>
        
        <!-- Mobile menu button (simple display for structure) -->
        <button id="mobile-menu-button" class="md:hidden text-gray-500 hover:text-indigo-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>
</header>

<main class="flex-grow">
    <!-- Area untuk menampilkan pesan sukses/error -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-auto mt-4 max-w-4xl" role="alert">
        <span class="block sm:inline"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
    </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-auto mt-4 max-w-4xl" role="alert">
        <span class="block sm:inline"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
    </div>
    <?php endif; ?>
