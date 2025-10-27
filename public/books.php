<?php
$page_title = "Toko Buku";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../app/db.php';

// Ambil pesan sukses/error dari session (misalnya, setelah berhasil tambah ke keranjang)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil semua data buku dari database
try {
    $pdo = getPDO();
    // Ambil buku yang stoknya lebih dari 0
    $stmt = $pdo->query(
        "SELECT id, title, synopsis, price, stock 
         FROM books 
         WHERE stock > 0 
         ORDER BY created_at DESC"
    );
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Gagal mengambil data buku: " . $e->getMessage();
    $books = [];
}

?>

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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($books)): ?>
        <p class="text-gray-500 col-span-full text-center">Belum ada buku yang dijual saat ini.</p>
    <?php else: ?>
        <?php foreach ($books as $book): ?>
            <div class="bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden flex flex-col">
                <div class="bg-gray-200 h-48 w-full flex items-center justify-center text-gray-400">
                    [Gambar Cover Buku]
                </div>
                
                <div class="p-4 flex flex-col flex-grow">
                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($book['title']); ?></h3>
                    
                    <p class="text-gray-700 text-sm mb-4 flex-grow">
                        <?php 
                        if (strlen($book['synopsis']) > 100) {
                            echo htmlspecialchars(substr($book['synopsis'], 0, 100)) . '...';
                        } else {
                            echo htmlspecialchars($book['synopsis']);
                        }
                        ?>
                    </p>
                    
                    <div class="mt-auto">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-lg font-bold text-indigo-600">
                                Rp <?php echo number_format($book['price'], 0, ',', '.'); ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                Stok: <?php echo htmlspecialchars($book['stock']); ?>
                            </span>
                        </div>
                        
                        <form action="../app/process_cart.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <input type="hidden" name="quantity" value="1"> <button type="submit" 
                                    class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                + Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/includes/footer.php';
?>
```eof

---

### 2. `app/process_cart.php` (Logika "Tambah ke Keranjang")

Ini adalah file "dapur" yang menangani logika saat user menekan tombol "+ Tambah ke Keranjang". File ini akan:
1.  Mengecek apakah user sudah login (karena keranjang terikat `user_id`).
2.  Mengecek apakah buku masih ada stoknya.
3.  Menambahkan buku ke tabel `cart` di database.

```php:process_cart.php:app/process_cart.php
<?php
/**
 * Proses Backend Keranjang Belanja (Cart)
 * File ini menangani Add, Update, Remove item dari cart.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Untuk memanipulasi keranjang, user HARUS login
// Kita panggil require_login() tapi kita tangani redirect-nya secara manual
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Anda harus login untuk menambahkan barang ke keranjang.";
    header("Location: ../public/login.php");
    exit();
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Tentukan PDO
try {
    $pdo = getPDO();
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// -----------------------------------------------------------------
// Aksi ADD (Tambah ke Keranjang)
// -----------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'add') {
    
    $book_id = $_POST['book_id'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1); // Ambil kuantitas, default 1

    if (empty($book_id) || $quantity <= 0) {
        $_SESSION['error_message'] = "Permintaan tidak valid.";
        header("Location: ../public/books.php");
        exit();
    }

    try {
        // 1. Cek Stok Buku
        $stmt_stock = $pdo->prepare("SELECT stock, title FROM books WHERE id = ?");
        $stmt_stock->execute([$book_id]);
        $book = $stmt_stock->fetch();

        if (!$book) {
            $_SESSION['error_message'] = "Buku tidak ditemukan.";
            header("Location: ../public/books.php");
            exit();
        }
        
        if ($book['stock'] < $quantity) {
             $_SESSION['error_message'] = "Maaf, stok buku '" . htmlspecialchars($book['title']) . "' tidak mencukupi (sisa " . $book['stock'] . ").";
            header("Location: ../public/books.php");
            exit();
        }

        // 2. Cek apakah buku sudah ada di keranjang user
        $stmt_cart = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
        $stmt_cart->execute([$user_id, $book_id]);
        $existing_item = $stmt_cart->fetch();

        if ($existing_item) {
            // Jika sudah ada, UPDATE quantity-nya
            $new_quantity = $existing_item['quantity'] + $quantity;
            
            // Cek lagi apakah totalnya melebihi stok
            if ($book['stock'] < $new_quantity) {
                 $_SESSION['error_message'] = "Maaf, Anda tidak bisa menambahkan lebih banyak buku '" . htmlspecialchars($book['title']) . "'. Stok tidak mencukupi.";
                 header("Location: ../public/books.php");
                 exit();
            }

            $stmt_update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt_update->execute([$new_quantity, $existing_item['id']]);

        } else {
            // Jika belum ada, INSERT data baru
            $stmt_insert = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)");
            $stmt_insert->execute([$user_id, $book_id, $quantity]);
        }

        // 3. Berhasil
        $_SESSION['success_message'] = "Buku '" . htmlspecialchars($book['title']) . "' berhasil ditambahkan ke keranjang!";
        header("Location: ../public/books.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header("Location: ../public/books.php");
        exit();
    }
}

// -----------------------------------------------------------------
// Aksi REMOVE (Hapus dari Keranjang)
// -----------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_GET['action'] == 'remove') {
    
    $cart_id = $_GET['cart_id'] ?? null;

    if (empty($cart_id)) {
        $_SESSION['error_message'] = "Permintaan tidak valid.";
        header("Location: ../public/cart.php");
        exit();
    }

    try {
        // Hapus item dari keranjang HANYA jika milik user yang sedang login
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Item berhasil dihapus dari keranjang.";
        } else {
            $_SESSION['error_message'] = "Item tidak ditemukan atau Anda tidak berhak menghapusnya.";
        }
        header("Location: ../public/cart.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header("Location: ../public/cart.php");
        exit();
    }
}


// Jika tidak ada aksi yang cocok
$_SESSION['error_message'] = "Aksi tidak valid.";
header("Location: ../public/books.php");
exit();

$page_title = "Keranjang Belanja";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';

// Halaman ini wajib login
require_login();

// Ambil pesan sukses/error dari session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil data keranjang milik user
$cart_items = [];
$total_price = 0;
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "SELECT cart.id AS cart_id, cart.quantity, books.id AS book_id, books.title, books.price, books.stock
         FROM cart
         JOIN books ON cart.book_id = books.id
         WHERE cart.user_id = ?"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();

    // Hitung total harga
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

} catch (PDOException $e) {
    $error_message = "Gagal mengambil data keranjang: " . $e->getMessage();
}

?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Keranjang Belanja Anda</h2>

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

    <?php if (empty($cart_items)): ?>
        <p class="text-gray-500 text-center">Keranjang belanja Anda kosong.</p>
        <div class="text-center mt-4">
            <a href="books.php" class="text-indigo-600 hover:underline font-medium">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <div class="flow-root">
            <ul role="list" class="-my-6 divide-y divide-gray-200">
                <?php foreach ($cart_items as $item): ?>
                    <li class="flex py-6">
                        <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                            <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-400 text-xs">
                                [Gambar]
                            </div>
                        </div>

                        <div class="ml-4 flex flex-1 flex-col">
                            <div>
                                <div class="flex justify-between text-base font-medium text-gray-900">
                                    <h3>
                                        <a href="#"><?php echo htmlspecialchars($item['title']); ?></a>
                                    </h3>
                                    <p class="ml-4">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Harga Satuan: Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                            </div>
                            <div class="flex flex-1 items-end justify-between text-sm">
                                <p class="text-gray-500">Qty <?php echo htmlspecialchars($item['quantity']); ?></p>

                                <div class="flex">
                                    <a href="../app/process_cart.php?action=remove&cart_id=<?php echo $item['cart_id']; ?>"
                                       type="button" 
                                       class="font-medium text-red-600 hover:text-red-500"
                                       onclick="return confirm('Yakin ingin menghapus item ini dari keranjang?');">
                                        Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="border-t border-gray-200 mt-8 pt-6">
            <div class="flex justify-between text-base font-medium text-gray-900">
                <p>Subtotal</p>
                <p>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></p>
            </div>
            <p class="mt-0.5 text-sm text-gray-500">Pajak dan ongkir akan dihitung saat checkout (jika ada).</p>
            <div class="mt-6">
                <a href="#" 
                   class="flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700">
                    Lanjut ke Checkout
                </a>
            </div>
            <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                <p>
                    atau
                    <a href="books.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Lanjut Belanja
                        <span aria-hidden="true"> &rarr;</span>
                    </a>
                </p>
            </div>
        </div>
    <?php endif; ?>

</div>


<?php
// Panggil footer
require_once __DIR__ . '/includes/footer.php';
?>
```eof

---

Selesai! Sekarang kamu punya sistem keranjang belanja yang fungsional.

User bisa:
1.  Melihat-lihat buku di `books.php`.
2.  Menekan tombol "Tambah ke Keranjang" (yang akan diproses oleh `process_cart.php`).
3.  Melihat isi keranjang mereka di `cart.php`.
4.  Menghapus item dari `cart.php`.

Semua ini sudah terhubung dengan database dan session login user.

Mau lanjut ke halaman publik untuk **Artikel** dan **Formulir Kontak**?