<?php
// File: public/books.php - Halaman Tampilan Buku Publik (Toko Buku)

require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/db.php';

$page_title = "Toko Buku | " . APP_NAME;
require_once 'layout/header.php';

$pdo = getConnection();
$books = getAll('books');

// Inisialisasi keranjang (simulasi menggunakan SESSION)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="flex justify-between items-center mb-8 border-b pb-3">
        <h1 class="text-4xl font-bold text-gray-800">Koleksi Toko Buku Kami</h1>
        <a href="<?= BASE_URL ?>/checkout.php" class="text-teal-600 hover:text-teal-800 font-semibold flex items-center bg-teal-100 p-2 rounded-lg transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            Keranjang (<?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?>)
        </a>
    </div>

    <?php if (empty($books)): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative text-center">
            <span class="block sm:inline">Maaf, belum ada buku yang tersedia saat ini.</span>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($books as $book): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col transition duration-300 hover:shadow-2xl hover:scale-[1.01]">
                    <div class="p-4 flex-grow">
                        <span class="text-xs font-semibold text-gray-500">Oleh: <?= htmlspecialchars($book['author']) ?></span>
                        <h2 class="text-xl font-bold text-gray-900 mt-1 mb-2 line-clamp-2">
                            <?= htmlspecialchars($book['title']) ?>
                        </h2>
                        <p class="text-3xl font-extrabold text-teal-600 mb-3">
                            Rp <?= number_format($book['price'], 0, ',', '.') ?>
                        </p>
                        <p class="text-gray-600 text-sm line-clamp-4 mb-4">
                            <?= htmlspecialchars($book['synopsis']) ?>
                        </p>
                    </div>

                    <div class="p-4 border-t border-gray-100">
                        <?php if ($book['stock'] > 0): ?>
                            <form action="<?= BASE_URL ?>/public/checkout.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" 
                                        class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    Tambah ke Keranjang
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-center text-red-500 font-semibold py-2">Stok Habis</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
