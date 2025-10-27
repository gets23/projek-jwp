<?php
// File: public/admin/manage_articles.php

require_once __DIR__ . '/../../app/bootstrap.php';

check_admin(); // Pastikan hanya admin yang bisa akses

$page_title = "Manage Articles";
$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

// --- LOGIKA FORM (CREATE, UPDATE, DELETE) ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Aksi: Hapus Artikel
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM articles WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id); // "i" berarti integer
        if ($stmt->execute()) {
            redirect(BASE_URL . '/admin/manage_articles.php?success=Artikel berhasil dihapus');
        } else {
            redirect(BASE_URL . '/admin/manage_articles.php?error=Gagal menghapus artikel: ' . $stmt->error);
        }
        $stmt->close();
    }
    
    // Aksi: Tambah atau Edit Artikel
    $title = $_POST['title'];
    $content = $_POST['content'];
    $id = $_POST['id'] ?? null;
    $author_id = $_SESSION['user_id'];

    if (empty($title) || empty($content)) {
        redirect(BASE_URL . '/admin/manage_articles.php?error=Judul dan konten wajib diisi');
    }

    // Jika ada ID, berarti UPDATE
    if ($id) {
        $sql = "UPDATE articles SET title = ?, content = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $content, $id); // "ssi" = string, string, integer
        if ($stmt->execute()) {
            redirect(BASE_URL . '/admin/manage_articles.php?success=Artikel berhasil diperbarui');
        } else {
            redirect(BASE_URL . '/admin/manage_articles.php?error=Gagal memperbarui: ' . $stmt->error);
        }
    } 
    // Jika tidak ada ID, berarti CREATE
    else {
        $sql = "INSERT INTO articles (title, content, author_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $content, $author_id);
        if ($stmt->execute()) {
            redirect(BASE_URL . '/admin/manage_articles.php?success=Artikel berhasil ditambahkan');
        } else {
            redirect(BASE_URL . '/admin/manage_articles.php?error=Gagal menambah: ' . $stmt->error);
        }
    }
    $stmt->close();
}

// --- LOGIKA BACA (READ) ---

// Ambil data artikel untuk diedit (jika ada)
$edit_article = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql_edit = "SELECT * FROM articles WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("i", $edit_id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    $edit_article = $result_edit->fetch_assoc();
    $stmt_edit->close();
}

// Ambil semua artikel untuk ditampilkan di tabel
// Ini adalah query sederhana, tidak perlu prepared statement
$sql_all = "SELECT articles.*, users.name AS author_name 
            FROM articles 
            JOIN users ON articles.author_id = users.id 
            ORDER BY articles.created_at DESC";
$result_all = $conn->query($sql_all);
$articles = $result_all->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container mx-auto mt-10 p-5">
    <h1 class="text-3xl font-bold mb-6">Manage Articles</h1>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Form Tambah / Edit Artikel -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-4"><?php echo $edit_article ? 'Edit Artikel' : 'Tambah Artikel Baru'; ?></h2>
        <form action="manage_articles.php" method="POST">
            <!-- Hidden input untuk ID (jika sedang edit) -->
            <input type="hidden" name="id" value="<?php echo $edit_article['id'] ?? ''; ?>">
            
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($edit_article['title'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>
            <div class="mb-4">
                <label for="content" class="block text-sm font-medium text-gray-700">Konten</label>
                <textarea id="content" name="content" rows="6" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required><?php echo htmlspecialchars($edit_article['content'] ?? ''); ?></textarea>
            </div>
            <div>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <?php echo $edit_article ? 'Perbarui Artikel' : 'Simpan Artikel'; ?>
                </button>
                <?php if ($edit_article): ?>
                    <a href="manage_articles.php" class="ml-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Batal Edit
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Artikel -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penulis</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada artikel.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($article['title']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($article['content'], 0, 50)) . '...'; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($article['author_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($article['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="manage_articles.php?edit_id=<?php echo $article['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <!-- Form untuk Hapus -->
                                <form action="manage_articles.php" method="POST" class="inline-block ml-4" onsubmit="return confirm('Yakin ingin menghapus artikel ini?');">
                                    <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                    <button type="submit" name="delete" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

