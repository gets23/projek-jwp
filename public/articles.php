<?php
// File: public/articles.php - Halaman Daftar Artikel Publik

require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/db.php';

$page_title = "Blog Terbaru | " . APP_NAME;
require_once 'layout/header.php';

$pdo = getConnection();

try {
    // Ambil semua artikel, gabungkan dengan nama penulis
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.title, a.content, a.created_at, u.name as author_name
        FROM articles a
        JOIN users u ON a.author_id = u.id
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $articles = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Tangani error database
    $articles = [];
    $error = "Terjadi kesalahan saat memuat artikel.";
}
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-gray-800 mb-8 border-b-2 pb-2">Semua Artikel Blog</h1>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= $error ?></span>
        </div>
    <?php elseif (empty($articles)): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">Belum ada artikel yang dipublikasikan.</span>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($articles as $article): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transition duration-300 hover:shadow-2xl">
                    <div class="p-6">
                        <span class="text-sm text-indigo-500 font-semibold"><?= date('d M Y', strtotime($article['created_at'])) ?></span>
                        <h2 class="text-2xl font-bold text-gray-900 mt-2 mb-3 line-clamp-2">
                            <?= htmlspecialchars($article['title']) ?>
                        </h2>
                        <p class="text-gray-600 line-clamp-3 mb-4">
                            <?= htmlspecialchars(strip_tags($article['content'])) ?>
                        </p>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500">Penulis: <span class="font-medium"><?= htmlspecialchars($article['author_name']) ?></span></p>
                            <!-- Di sini seharusnya mengarah ke single article view (article_detail.php?id=...) -->
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 font-semibold transition duration-150">
                                Baca Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
