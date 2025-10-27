<?php
$page_title = "Manajemen Buku";

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';

// Proteksi halaman, hanya untuk admin
require_admin();

// Ambil pesan sukses/error dari session (jika ada)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil semua data buku dari database, gabung (JOIN) dengan nama author
try {
    $pdo = getPDO();
    // Menggunakan LEFT JOIN untuk jaga-jaga jika user author terhapus
    $stmt = $pdo->query(
        "SELECT books.id, books.title, books.price, books.stock, users.name AS author_name 
         FROM books 
         LEFT JOIN users ON books.author_id = users.id 
         ORDER BY books.created_at DESC"
    );
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Gagal mengambil data buku: " . $e->getMessage();
    $books = [];
}

?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Manajemen Buku</h2>
        <a href="book_form.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-medium hover:bg-indigo-700">
            + Tambah Buku Baru
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author (Admin)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Belum ada buku.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($book['title']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700"><?php echo htmlspecialchars($book['author_name'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">Rp <?php echo number_format($book['price'], 0, ',', '.'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700"><?php echo htmlspecialchars($book['stock']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="book_form.php?id=<?php echo $book['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <span class="text-gray-300 mx-1">|</span>
                                <a href="../app/process_book.php?action=delete&id=<?php echo $book['id']; ?>" 
                                   class="text-red-600 hover:text-red-900"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
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

### 2. `public/admin/book_form.php` (Formulir 'Create' & 'Update' Buku)

Sama seperti `article_form.php`, file ini menangani "Tambah Baru" dan "Edit" buku, tapi dengan field yang berbeda (sinopsis, harga, stok).

```php:book_form.php:public/admin/book_form.php
<?php
require_once __DIR__ . '/../includes/header.php'; // Panggil header DULU
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';

// Proteksi halaman
require_admin();

// Inisialisasi variabel
$book_id = $_GET['id'] ?? null;
$is_edit_mode = ($book_id !== null);
$page_title = $is_edit_mode ? "Edit Buku" : "Tambah Buku Baru";

// Inisialisasi data buku
$book_title = '';
$book_synopsis = '';
$book_price = '';
$book_stock = 0;

// Ambil data form dari session jika ada error (saat redirect)
$form_data = $_SESSION['form_data'] ?? [];
$book_title = $form_data['title'] ?? '';
$book_synopsis = $form_data['synopsis'] ?? '';
$book_price = $form_data['price'] ?? '';
$book_stock = $form_data['stock'] ?? 0;
unset($_SESSION['form_data']);

// Ambil pesan error jika ada
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// Jika ini mode Edit, ambil data dari DB
if ($is_edit_mode && empty($form_data)) { // Hanya fetch jika tidak ada form data (bukan redirect error)
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT title, synopsis, price, stock FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        if ($book) {
            $book_title = $book['title'];
            $book_synopsis = $book['synopsis'];
            $book_price = $book['price'];
            $book_stock = $book['stock'];
        } else {
            // Buku tidak ditemukan, redirect dengan error
            $_SESSION['error_message'] = "Buku tidak ditemukan.";
            header("Location: books.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "Gagal mengambil data: " . $e->getMessage();
    }
}

// Set $page_title di header (karena $page_title ditentukan SETELAH header.php dipanggil)
echo "<script>document.title = '" . htmlspecialchars($page_title) . " | Super Web';</script>";
echo "<script>document.querySelector('header h1').textContent = '" . htmlspecialchars($page_title) . "';</script>";

?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-3xl mx-auto">
    
    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form action="../app/process_book.php" method="POST" class="space-y-6">
        
        <?php if ($is_edit_mode): ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($book_id); ?>">
        <?php else: ?>
            <input type="hidden" name="action" value="create">
        <?php endif; ?>

        <div>
            <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Judul Buku</label>
            <div class="mt-2">
                <input id="title" name="title" type="text" required
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                       value="<?php echo htmlspecialchars($book_title); ?>">
            </div>
        </div>

        <div>
            <label for="synopsis" class="block text-sm font-medium leading-6 text-gray-900">Sinopsis</label>
            <div class="mt-2">
                <textarea id="synopsis" name="synopsis" rows="6" required
                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                ><?php echo htmlspecialchars($book_synopsis); ?></textarea>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-3">
                <label for="price" class="block text-sm font-medium leading-6 text-gray-900">Harga (Rp)</label>
                <div class="mt-2">
                    <input type="number" name="price" id="price" min="0" step="1000" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           value="<?php echo htmlspecialchars($book_price); ?>"
                           placeholder="Contoh: 50000">
                </div>
            </div>
            <div class="sm:col-span-3">
                <label for="stock" class="block text-sm font-medium leading-6 text-gray-900">Stok</label>
                <div class="mt-2">
                    <input type="number" name="stock" id="stock" min="0" step="1" required
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                           value="<?php echo htmlspecialchars($book_stock); ?>"
                           placeholder="Contoh: 10">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-x-4">
            <a href="books.php" class="text-sm font-semibold leading-6 text-gray-900">Batal</a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <?php echo $is_edit_mode ? 'Update Buku' : 'Simpan Buku'; ?>
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

### 3. `app/process_book.php` (Logika Backend C-U-D Buku)

Ini adalah file "dapur" untuk buku. Ia menerima data dari `book_form.php` dan mengolahnya ke database.

```php:process_book.php:app/process_book.php
<?php
/**
 * Proses Backend CRUD Buku
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
    $synopsis = trim($_POST['synopsis'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $author_id = $_SESSION['user_id']; // Ambil ID admin yang sedang login

    // Validasi
    if (empty($title) || empty($synopsis) || $price === '' || $stock === '') {
        // Simpan data form dan error ke session, lalu redirect kembali
        $_SESSION['error_message'] = "Semua field wajib diisi.";
        $_SESSION['form_data'] = $_POST;
        header("Location: ../public/admin/book_form.php");
        exit();
    }

    // Lolos validasi, masukkan ke database
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO books (title, synopsis, price, stock, author_id) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$title, $synopsis, $price, $stock, $author_id]);

        // Set pesan sukses dan redirect
        $_SESSION['success_message'] = "Buku baru berhasil ditambahkan!";
        header("Location: ../public/admin/books.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        header("Location: ../public/admin/book_form.php");
        exit();
    }
}

// -----------------------------------------------------------------
// Aksi UPDATE (dari method POST)
// -----------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'update') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $synopsis = trim($_POST['synopsis'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');

    // Validasi
    if (empty($id) || empty($title) || empty($synopsis) || $price === '' || $stock === '') {
        $_SESSION['error_message'] = "Semua field wajib diisi.";
        $_SESSION['form_data'] = $_POST;
        header("Location: ../public/admin/book_form.php?id=" . $id);
        exit();
    }

    // Lolos validasi, update database
    try {
        $stmt = $pdo->prepare(
            "UPDATE books SET title = ?, synopsis = ?, price = ?, stock = ? WHERE id = ?"
        );
        $stmt->execute([$title, $synopsis, $price, $stock, $id]);

        $_SESSION['success_message'] = "Buku berhasil diperbarui!";
        header("Location: ../public/admin/books.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        header("Location: ../public/admin/book_form.php?id=" . $id);
        exit();
    }
}

// -----------------------------------------------------------------
// Aksi DELETE (dari method GET)
// -----------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_GET['action'] == 'delete') {
    $id = $_GET['id'] ?? null;

    if (empty($id)) {
        $_SESSION['error_message'] = "ID Buku tidak valid.";
        header("Location: ../public/admin/books.php");
        exit();
    }

    // Hapus dari database
    try {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success_message'] = "Buku berhasil dihapus!";
        header("Location: ../public/admin/books.php");
        exit();

    } catch (PDOException $e) {
        // Cek jika error karena foreign key (buku ada di keranjang)
        if ($e->getCode() == '23000') {
             $_SESSION['error_message'] = "Gagal menghapus buku. Buku ini mungkin ada di keranjang belanja user.";
        } else {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        }
        header("Location: ../public/admin/books.php");
        exit();
    }
}

// Jika tidak ada aksi yang cocok
$_SESSION['error_message'] = "Aksi tidak valid.";
header("Location: ../public/admin/books.php");
exit();