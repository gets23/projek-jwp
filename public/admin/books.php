<?php
// File: public/admin/books.php - Halaman CRUD Buku (Admin)

require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/functions.php';
require_once __DIR__ . '/../../app/db.php';

requireAdmin();

$pdo = getConnection();
$page_title = "Manajemen Buku | " . APP_NAME;
require_once __DIR__ . '/../layout/header.php';

$action = $_GET['action'] ?? 'list';
$book_id = $_GET['id'] ?? null;
$book = [];

try {
    $books = getAll('books');

    if ($action === 'edit' && $book_id) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id");
        $stmt->bindParam(':id', $book_id);
        $stmt->execute();
        $book = $stmt->fetch();
        if (!$book) {
            $_SESSION['error_message'] = "Buku tidak ditemukan.";
            redirect('admin/books.php');
        }
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Gagal memuat data: " . $e->getMessage();
    redirect('admin/dashboard.php');
}

?>

<div class="container mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Manajemen Toko Buku</h1>

    <?php if ($action === 'list'): ?>
        <!-- Tampilan LIST BUKU -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700">Daftar Semua Buku</h2>
            <a href="?action=create" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition duration-200">
                + Tambah Buku Baru
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Penulis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($books)): ?>
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada buku.</td></tr>
                    <?php else: ?>
                        <?php foreach ($books as $buku): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($buku['title']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?= htmlspecialchars($buku['author']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp <?= number_format($buku['price'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 <?= $buku['stock'] == 0 ? 'text-red-500 font-bold' : '' ?>"><?= $buku['stock'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="?action=edit&id=<?= $buku['id'] ?>" class="text-teal-600 hover:text-teal-900 mr-3">Edit</a>
                                    <a href="<?= BASE_URL ?>/app/process_book.php?action=delete&id=<?= $buku['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')"
                                       class="text-red-600 hover:text-red-900">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: // Aksi CREATE atau EDIT ?>
        <!-- Tampilan FORM CREATE/EDIT BUKU -->
        <div class="w-full max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $action === 'create' ? 'Input Buku Baru' : 'Edit Buku: ' . htmlspecialchars($book['title'] ?? '') ?></h2>
            
            <form action="<?= BASE_URL ?>/app/process_book.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="<?= $action ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $book_id ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 text-sm font-medium mb-2">Judul Buku</label>
                        <input type="text" id="title" name="title" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                            value="<?= htmlspecialchars($book['title'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label for="author" class="block text-gray-700 text-sm font-medium mb-2">Penulis</label>
                        <input type="text" id="author" name="author" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                            value="<?= htmlspecialchars($book['author'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label for="price" class="block text-gray-700 text-sm font-medium mb-2">Harga (Rp)</label>
                        <input type="number" id="price" name="price" step="0.01" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                            value="<?= htmlspecialchars($book['price'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label for="stock" class="block text-gray-700 text-sm font-medium mb-2">Stok</label>
                        <input type="number" id="stock" name="stock" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                            value="<?= htmlspecialchars($book['stock'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="synopsis" class="block text-gray-700 text-sm font-medium mb-2">Sinopsis</label>
                    <textarea id="synopsis" name="synopsis" rows="5" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                              placeholder="Sinopsis singkat buku..."><?= htmlspecialchars($book['synopsis'] ?? '') ?></textarea>
                </div>

                <div class="flex space-x-4">
                    <button type="submit"
                            class="bg-teal-600 text-white py-2 px-4 rounded-lg hover:bg-teal-700 transition duration-200 font-semibold shadow-md">
                        <?= $action === 'create' ? 'Simpan Buku' : 'Update Buku' ?>
                    </button>
                    <a href="<?= BASE_URL ?>/admin/books.php"
                       class="bg-gray-400 text-white py-2 px-4 rounded-lg hover:bg-gray-500 transition duration-200 font-semibold shadow-md">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
