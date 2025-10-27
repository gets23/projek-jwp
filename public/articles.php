<?php
$page_title = "Artikel Terbaru";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../app/db.php';

// Ambil semua artikel dari database, gabung (JOIN) dengan nama author
try {
    $pdo = getPDO();
    $stmt = $pdo->query(
        "SELECT articles.id, articles.title, articles.content, articles.created_at, users.name AS author_name 
         FROM articles 
         JOIN users ON articles.author_id = users.id 
         ORDER BY articles.created_at DESC"
    );
    $articles = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Gagal mengambil data artikel: " . $e->getMessage();
    $articles = [];
}
?>

<?php if (!empty($error_message)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
        <p><?php echo htmlspecialchars($error_message); ?></p>
    </div>
<?php endif; ?>

<div class="space-y-8 max-w-4xl mx-auto">
    <?php if (empty($articles)): ?>
        <p class="text-gray-500 text-center">Belum ada artikel yang dipublikasikan.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <article class="bg-white p-6 sm:p-8 rounded-lg shadow-md border border-gray-200">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                    <?php echo htmlspecialchars($article['title']); ?>
                </h2>
                
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <span>Oleh <?php echo htmlspecialchars($article['author_name']); ?></span>
                    <span class="mx-2">&middot;</span>
                    <span><?php echo date('d F Y', strtotime($article['created_at'])); ?></span>
                </div>
                
                <div class="text-gray-700 leading-relaxed prose prose-indigo max-w-none">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>

                </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/includes/footer.php';
?>