<?php
// File: public/admin/articles.php - Halaman CRUD Artikel (Admin)

require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/functions.php';
require_once __DIR__ . '/../../app/db.php';

// Wajibkan Admin untuk mengakses
requireAdmin();

$pdo = getConnection();
$page_title = "Manajemen Artikel | " . APP_NAME;
require_once __DIR__ . '/../layout/header.php';

// --- Logika Halaman CRUD Sederhana ---
$action = $_GET['action'] ?? 'list';
$article_id = $_GET['id'] ?? null;
$article = [];

try {
    // Pengambilan data untuk list
    $articles = getAll('articles');

    // Jika ada aksi edit
    if ($action === 'edit' && $article_id) {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
        $stmt->bindParam(':id', $article_id);
        $stmt->execute();
        $article = $stmt->fetch();
        if (!$article) {
            $_SESSION['error_message'] = "Artikel tidak ditemukan.";
            redirect('admin/articles.php');
        }
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Gagal memuat data: " . $e->getMessage();
    redirect('admin/dashboard.php');
}

?>

<div class="container mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Manajemen Artikel</h1>

    <?php if ($action === 'list'): ?>
        <!-- Tampilan LIST ARTIKEL -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Daftar Semua Artikel</h2>
            <a href="?action=create" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition duration-200">
                + Tambah Artikel Baru
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tanggal Buat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($articles)): ?>
                        <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">Belum ada artikel.</td></tr>
                    <?php else: ?>
                        <?php foreach ($articles as $art): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($art['title']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?= date('d M Y', strtotime($art['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="?action=edit&id=<?= $art['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                    <a href="<?= BASE_URL ?>/app/process_article.php?action=delete&id=<?= $art['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')"
                                       class="text-red-600 hover:text-red-900">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: // Aksi CREATE atau EDIT ?>
        <!-- Tampilan FORM CREATE/EDIT ARTIKEL -->
        <div class="w-full max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $action === 'create' ? 'Buat Artikel Baru' : 'Edit Artikel: ' . htmlspecialchars($article['title'] ?? '') ?></h2>
            
            <form action="<?= BASE_URL ?>/app/process_article.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="<?= $action ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $article_id ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-medium mb-2">Judul Artikel</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           value="<?= htmlspecialchars($article['title'] ?? '') ?>">
                </div>

                <div class="mb-6">
                    <label for="content" class="block text-gray-700 text-sm font-medium mb-2">Konten Artikel</label>
                    <textarea id="content" name="content" rows="10" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Tulis konten artikel di sini..."><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                </div>

                <div class="flex space-x-4">
                    <button type="submit"
                            class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200 font-semibold shadow-md">
                        <?= $action === 'create' ? 'Simpan Artikel' : 'Update Artikel' ?>
                    </button>
                    <a href="<?= BASE_URL ?>/admin/articles.php"
                       class="bg-gray-400 text-white py-2 px-4 rounded-lg hover:bg-gray-500 transition duration-200 font-semibold shadow-md">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
