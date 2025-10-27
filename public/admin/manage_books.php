<?php
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../../app/functions.php';
require_once __DIR__ . '/../../app/db.php';

// Proteksi halaman
require_admin();

$user_id = $_SESSION['user_id'];
$edit_mode = false;
$book_to_edit = null;

// Logika CRUD
try {
    // 1. PROSES CREATE / UPDATE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_book'])) {
        $title = sanitize_input($_POST['title']);
        $synopsis = sanitize_input($_POST['synopsis']);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
        $book_id = $_POST['book_id'] ?? null;

        if ($book_id) {
            // Update
            $stmt = $pdo->prepare("UPDATE books SET title = ?, synopsis = ?, price = ?, stock = ? WHERE id = ? AND author_id = ?");
            $stmt->execute([$title, $synopsis, $price, $stock, $book_id, $user_id]);
            $_SESSION['success_message'] = "Buku berhasil diperbarui.";
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO books (title, synopsis, price, stock, author_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $synopsis, $price, $stock, $user_id]);
            $_SESSION['success_message'] = "Buku berhasil ditambahkan.";
        }
        redirect(BASE_URL . '/admin/manage_books.php');
    }

    // 2. PROSES DELETE
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $book_id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ? AND author_id = ?");
        $stmt->execute([$book_id, $user_id]);
        $_SESSION['success_message'] = "Buku berhasil dihapus.";
        redirect(BASE_URL . '/admin/manage_books.php');
    }

    // 3. PROSES EDIT (Load data ke form)
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $edit_mode = true;
        $book_id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND author_id = ?");
        $stmt->execute([$book_id, $user_id]);
        $book_to_edit = $stmt->fetch();
        if (!$book_to_edit) {
            $edit_mode = false;
            $_SESSION['error_message'] = "Buku tidak ditemukan atau Anda tidak punya hak akses.";
        }
    }

    // 4. READ (Tampilkan semua buku)
    $stmt = $pdo->prepare("SELECT * FROM books WHERE author_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $books = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Buku</h1>

    <!-- Menampilkan pesan sukses/error -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mb-6" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Form Tambah/Edit Buku -->
    <form action="manage_books.php" method="POST" class="mb-10 bg-gray-50 p-6 rounded-lg border">
        <h2 class="text-2xl font-semibold mb-4"><?php echo $edit_mode ? 'Edit Buku' : 'Tambah Buku Baru'; ?></h2>
        <?php if ($edit_mode): ?>
            <input type="hidden" name="book_id" value="<?php echo $book_to_edit['id']; ?>">
        <?php endif; ?>
        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-medium mb-2">Judul Buku</label>
            <input type="text" id="title" name="title" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $edit_mode ? htmlspecialchars($book_to_edit['title']) : ''; ?>" required>
        </div>
        <div class="mb-4">
            <label for="synopsis" class="block text-gray-700 font-medium mb-2">Sinopsis</label>
            <textarea id="synopsis" name="synopsis" rows="5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><?php echo $edit_mode ? htmlspecialchars($book_to_edit['synopsis']) : ''; ?></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="price" class="block text-gray-700 font-medium mb-2">Harga (Rp)</label>
                <input type="number" id="price" name="price" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $edit_mode ? htmlspecialchars($book_to_edit['price']) : ''; ?>" required>
            </div>
            <div>
                <label for="stock" class="block text-gray-700 font-medium mb-2">Stok</label>
                <input type="number" id="stock" name="stock" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo $edit_mode ? htmlspecialchars($book_to_edit['stock']) : ''; ?>" required>
            </div>
        </div>
        <button type="submit" name="submit_book" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
            <?php echo $edit_mode ? 'Update Buku' : 'Simpan Buku'; ?>
        </button>
        <?php if ($edit_mode): ?>
            <a href="manage_books.php" class="ml-4 text-gray-600 hover:underline">Batal Edit</a>
        <?php endif; ?>
    </form>

    <!-- Daftar Buku -->
    <h2 class="text-2xl font-semibold mb-4">Daftar Buku Anda</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-3 px-4 border-b text-left text-gray-700">Judul</th>
                    <th class="py-3 px-4 border-b text-left text-gray-700">Harga</th>
                    <th class="py-3 px-4 border-b text-left text-gray-700">Stok</th>
                    <th class="py-3 px-4 border-b text-left text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="4" class="py-4 px-4 text-center text-gray-500">Anda belum menjual buku.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($book['title']); ?></td>
                            <td class="py-3 px-4 border-b">Rp <?php echo number_format($book['price'], 0, ',', '.'); ?></td>
                            <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($book['stock']); ?></td>
                            <td class="py-3 px-4 border-b">
                                <a href="?action=edit&id=<?php echo $book['id']; ?>" class="text-blue-500 hover:underline mr-3">Edit</a>
                                <a href="?action=delete&id=<?php echo $book['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
