<?php
require_once 'layout/header.php';
require_once __DIR__ . '/../app/db.php';

// Ambil data artikel
try {
    $stmt_articles = $pdo->query("SELECT articles.*, users.name AS author_name FROM articles JOIN users ON articles.author_id = users.id ORDER BY articles.created_at DESC LIMIT 5");
    $articles = $stmt_articles->fetchAll();
} catch (PDOException $e) {
    $articles = [];
    echo "Error mengambil artikel: " . $e->getMessage();
}

// Ambil data buku
try {
    $stmt_books = $pdo->query("SELECT * FROM books WHERE stock > 0 ORDER BY created_at DESC LIMIT 6");
    $books = $stmt_books->fetchAll();
} catch (PDOException $e) {
    $books = [];
    echo "Error mengambil buku: " . $e->getMessage();
}

?>

<div class="bg-white p-8 rounded-lg shadow-md mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-4">Selamat Datang di Super Web</h1>
    <p class="text-lg text-gray-600">Temukan artikel menarik dan buku-buku terbaik di sini.</p>
</div>

<!-- Bagian Buku -->
<section class="mb-12">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Toko Buku</h2>
    
    <!-- Menampilkan pesan sukses (dari keranjang) -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($books)): ?>
            <p class="text-gray-600 col-span-3">Belum ada buku yang dijual.</p>
        <?php else: ?>
            <?php foreach ($books as $book): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                    <div class="p-6 flex-grow">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($book['synopsis']); ?></p>
                    </div>
                    <div class="p-6 bg-gray-50 flex items-center justify-between">
                        <span class="text-xl font-bold text-blue-600">Rp <?php echo number_format($book['price'], 0, ',', '.'); ?></span>
                        <form action="<?php echo BASE_URL; ?>/../app/process_cart.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                                + Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Bagian Artikel -->
<section>
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Artikel Terbaru</h2>
    <div class="space-y-8">
        <?php if (empty($articles)): ?>
            <p class="text-gray-600">Belum ada artikel yang dipublikasikan.</p>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <article class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($article['title']); ?></h3>
                    <div class="text-sm text-gray-500 mb-4">
                        Ditulis oleh <?php echo htmlspecialchars($article['author_name']); ?> pada <?php echo date('d F Y', strtotime($article['created_at'])); ?>
                    </div>
                    <div class="text-gray-700 leading-relaxed">
                        <!-- Tampilkan sebagian konten -->
                        <?php echo nl2br(htmlspecialchars(substr($article['content'], 0, 300))); ?>...
                    </div>
                    <!-- Nanti bisa ditambahkan link ke 'article_detail.php?id=...' -->
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'layout/footer.php'; ?>
