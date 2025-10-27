<?php
$page_title = "Manajemen Artikel";

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';

// Proteksi halaman, hanya untuk admin
require_admin();

// Ambil pesan sukses/error dari session (jika ada)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil semua artikel dari database, gabung (JOIN) dengan nama author
try {
    $pdo = getPDO();
    // Menggunakan LEFT JOIN untuk jaga-jaga jika user author terhapus
    $stmt = $pdo->query(
        "SELECT articles.id, articles.title, articles.created_at, users.name AS author_name 
         FROM articles 
         LEFT JOIN users ON articles.author_id = users.id 
         ORDER BY articles.created_at DESC"
    );
    $articles = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Gagal mengambil data artikel: " . $e->getMessage();
    $articles = [];
}

?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Manajemen Artikel</h2>
        <a href="article_form.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-medium hover:bg-indigo-700">
            + Tambah Artikel Baru
        </a>
    </div>

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Pada</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            Belum ada artikel.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($article['title']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700"><?php echo htmlspecialchars($article['author_name'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700"><?php echo date('d M Y, H:i', strtotime($article['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="article_form.php?id=<?php echo $article['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <span class="text-gray-300 mx-1">|</span>
                                <a href="../app/process_article.php?action=delete&id=<?php echo $article['id']; ?>" 
                                   class="text-red-600 hover:text-red-900"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?');">
                                   Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/../includes/footer.php';
?>
```eof

---

### 2. `public/admin/article_form.php` (Formulir 'Create' & 'Update')

File ini pintar. Ia bisa berfungsi sebagai formulir "Tambah Baru" dan "Edit".
* Jika dibuka (`article_form.php`), ia akan jadi form kosong (Create).
* Jika dibuka dengan ID (`article_form.php?id=5`), ia akan mengambil data artikel #5 dan mengisi formnya (Update).

```php:article_form.php:public/admin/article_form.php
<?php
require_once __DIR__ . '/../includes/header.php'; // Panggil header DULU
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';

// Proteksi halaman
require_admin();

// Inisialisasi variabel
$article_id = $_GET['id'] ?? null;
$is_edit_mode = ($article_id !== null);
$page_title = $is_edit_mode ? "Edit Artikel" : "Tambah Artikel Baru";

$article_title = '';
$article_content = '';

// Ambil data form dari session jika ada error (saat redirect)
$form_data = $_SESSION['form_data'] ?? [];
$article_title = $form_data['title'] ?? '';
$article_content = $form_data['content'] ?? '';
unset($_SESSION['form_data']);

// Ambil pesan error jika ada
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// Jika ini mode Edit, ambil data dari DB
if ($is_edit_mode && empty($form_data)) { // Hanya fetch jika tidak ada form data (bukan redirect error)
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT title, content FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch();

        if ($article) {
            $article_title = $article['title'];
            $article_content = $article['content'];
        } else {
            // Artikel tidak ditemukan, redirect dengan error
            $_SESSION['error_message'] = "Artikel tidak ditemukan.";
            header("Location: articles.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "Gagal mengambil data: " . $e->getMessage();
    }
}

// Set $page_title di header (karena $page_title ditentukan SETELAH header.php dipanggil)
// Ini trik untuk update title di template header
echo "<script>document.title = '" . htmlspecialchars($page_title) . " | Super Web';</script>";
echo "<script>document.querySelector('header h1').textContent = '" . htmlspecialchars($page_title) . "';</script>";

?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-3xl mx-auto">
    
    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form action="../app/process_article.php" method="POST" class="space-y-6">
        
        <?php if ($is_edit_mode): ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($article_id); ?>">
        <?php else: ?>
            <input type="hidden" name="action" value="create">
        <?php endif; ?>

        <div>
            <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Judul Artikel</label>
            <div class="mt-2">
                <input id="title" name="title" type="text" required
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                       value="<?php echo htmlspecialchars($article_title); ?>">
            </div>
        </div>

        <div>
            <label for="content" class="block text-sm font-medium leading-6 text-gray-900">Konten</I</label>
            <div class="mt-2">
                <textarea id="content" name="content" rows="10" required
                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                ><?php echo htmlspecialchars($article_content); ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-x-4">
            <a href="articles.php" class="text-sm font-semibold leading-6 text-gray-900">Batal</a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <?php echo $is_edit_mode ? 'Update Artikel' : 'Simpan Artikel'; ?>
            </button>
        </div>

    </form>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/../includes/footer.php';
?>
```eof

---

### 3. `app/process_article.php` (Logika Backend C-U-D)

Ini adalah "dapur"-nya. File ini tidak menampilkan HTML. Ia hanya menerima data, memprosesnya ke database, lalu mengarahkan (redirect) user kembali ke halaman admin.

```php:process_article.php:app/process_article.php
<?php
/**
 * Proses Backend CRUD Artikel
 * File ini menangani Create, Update, dan Delete.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Hanya admin yang boleh mengakses file ini
require_admin();

// Tentukan PDO
try {
    $pdo = getPDO();
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// -----------------------------------------------------------------
// Aksi CREATE (dari method POST)
// -----------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'create') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author_id = $_SESSION['user_id']; // Ambil ID admin yang sedang login

    // Validasi
    if (empty($title) || empty($content)) {
        // Simpan data form dan error ke session, lalu redirect kembali
        $_SESSION['error_message'] = "Judul dan Konten wajib diisi.";
        $_SESSION['form_data'] = ['title' => $title, 'content' => $content];
        header("Location: ../public/admin/article_form.php");
        exit();
    }

    // Lolos validasi, masukkan ke database
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO articles (title, content, author_id) VALUES (?, ?, ?)"
        );
        $stmt->execute([$title, $content, $author_id]);

        // Set pesan sukses dan redirect
        $_SESSION['success_message'] = "Artikel baru berhasil ditambahkan!";
        header("Location: ../public/admin/articles.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        $_SESSION['form_data'] = ['title' => $title, 'content' => $content];
        header("Location: ../public/admin/article_form.php");
        exit();
    }
}

// -----------------------------------------------------------------
// Aksi UPDATE (dari method POST)
// -----------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'update') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // Validasi
    if (empty($id) || empty($title) || empty($content)) {
        $_SESSION['error_message'] = "Semua field wajib diisi.";
        $_SESSION['form_data'] = ['title' => $title, 'content' => $content];
        header("Location: ../public/admin/article_form.php?id=" . $id);
        exit();
    }

    // Lolos validasi, update database
    try {
        $stmt = $pdo->prepare(
            "UPDATE articles SET title = ?, content = ? WHERE id = ?"
        );
        $stmt->execute([$title, $content, $id]);

        $_SESSION['success_message'] = "Artikel berhasil diperbarui!";
        header("Location: ../public/admin/articles.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        $_SESSION['form_data'] = ['title' => $title, 'content' => $content];
        header("Location: ../public/admin/article_form.php?id=" . $id);
        exit();
    }
}

// -----------------------------------------------------------------
// Aksi DELETE (dari method GET)
// -----------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_GET['action'] == 'delete') {
    $id = $_GET['id'] ?? null;

    if (empty($id)) {
        $_SESSION['error_message'] = "ID Artikel tidak valid.";
        header("Location: ../public/admin/articles.php");
        exit();
    }

    // Hapus dari database
    try {
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success_message'] = "Artikel berhasil dihapus!";
        header("Location: ../public/admin/articles.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header("Location: ../public/admin/articles.php");
        exit();
    }
}

// Jika tidak ada aksi yang cocok
$_SESSION['error_message'] = "Aksi tidak valid.";
header("Location: ../public/admin/articles.php");
exit();