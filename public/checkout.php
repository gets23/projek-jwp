<?php
// File: public/checkout.php - Halaman Keranjang Belanja dan Proses Struk

require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php'; // Digunakan untuk mendapatkan user ID

requireLogin(); // Wajibkan user login untuk checkout

$page_title = "Keranjang & Checkout | " . APP_NAME;
require_once 'layout/header.php';

$pdo = getConnection();

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$book_id = $_POST['book_id'] ?? null;
$csrf_token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 1);

// Cek apakah ada data transaksi terakhir di session (untuk tampilan struk)
$checkout_success = isset($_SESSION['last_transaction']);
$transaction_data = $_SESSION['last_transaction'] ?? [];


// --- Logika Keranjang (Add/Remove/Clear) ---
// Note: Perlu generate CSRF token baru di sini karena token lama sudah terpakai
generateCsrfToken(); 

if ($action && verifyCsrfToken($csrf_token)) {
    // Kosongkan detail transaksi terakhir jika ada aksi lain selain checkout yang berhasil
    unset($_SESSION['last_transaction']);

    if ($action === 'add' && $book_id) {
        // Logika Tambah ke Keranjang
        $stmt = $pdo->prepare("SELECT id, title, price, stock FROM books WHERE id = :id");
        $stmt->bindParam(':id', $book_id);
        $stmt->execute();
        $book = $stmt->fetch();

        if ($book) {
            $current_qty = $_SESSION['cart'][$book_id]['quantity'] ?? 0;
            $new_qty = $current_qty + $quantity;

            if ($new_qty <= $book['stock']) {
                $_SESSION['cart'][$book_id] = [
                    'id' => $book['id'],
                    'title' => $book['title'],
                    'price' => (float)$book['price'],
                    'quantity' => $new_qty
                ];
                $_SESSION['success_message'] = "Buku ditambahkan ke keranjang!";
            } else {
                $_SESSION['error_message'] = "Stok buku tidak mencukupi (Tersedia: {$book['stock']}).";
            }
        }
    } elseif ($action === 'remove' && $book_id) {
        unset($_SESSION['cart'][$book_id]);
        $_SESSION['success_message'] = "Buku dihapus dari keranjang.";
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
        $_SESSION['success_message'] = "Keranjang berhasil dikosongkan.";
    } elseif ($action === 'checkout') {
        // --- Logika Proses Checkout dan Struk ---
        if (!empty($_SESSION['cart'])) {
            try {
                $pdo->beginTransaction();

                $total_amount = 0;
                foreach ($_SESSION['cart'] as $item) {
                    $total_amount += $item['price'] * $item['quantity'];
                }

                // 1. Simpan Transaksi Utama
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, total_amount, status) VALUES (:user_id, :total_amount, 'completed')");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':total_amount', $total_amount);
                $stmt->execute();
                $transaction_id = $pdo->lastInsertId();

                $transaction_details = [];
                // 2. Simpan Detail Transaksi dan Update Stok
                foreach ($_SESSION['cart'] as $item) {
                    // Simpan Detail
                    $stmt_detail = $pdo->prepare("INSERT INTO transaction_details (transaction_id, book_id, quantity, unit_price) VALUES (:tid, :bid, :qty, :price)");
                    $stmt_detail->bindParam(':tid', $transaction_id);
                    $stmt_detail->bindParam(':bid', $item['id']);
                    $stmt_detail->bindParam(':qty', $item['quantity']);
                    $stmt_detail->bindParam(':price', $item['price']);
                    $stmt_detail->execute();

                    // Update Stok
                    $stmt_stock = $pdo->prepare("UPDATE books SET stock = stock - :qty WHERE id = :bid AND stock >= :qty");
                    $stmt_stock->bindParam(':qty', $item['quantity']);
                    $stmt_stock->bindParam(':bid', $item['id']);
                    $stmt_stock->execute();
                    
                    // Kumpulkan detail untuk struk
                    $transaction_details[] = [
                        'title' => $item['title'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity']
                    ];
                }

                $pdo->commit();
                
                // Simpan detail transaksi ke SESSION untuk ditampilkan setelah redirect
                $_SESSION['last_transaction'] = [
                    'id' => $transaction_id,
                    'details' => $transaction_details,
                    'total_amount' => $total_amount,
                    'date' => date('d M Y H:i:s')
                ];

                $_SESSION['cart'] = [];
                $_SESSION['success_message'] = "Checkout Berhasil! Struk transaksi siap dicetak/dilihat.";

            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Checkout Error: " . $e->getMessage());
                $_SESSION['error_message'] = "Checkout gagal: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = "Keranjang Anda kosong.";
        }
        // Redirect untuk menghilangkan POST data dan menampilkan struk melalui session
        redirect('checkout.php');
    }
}
// Ambil data buku untuk ditampilkan di keranjang (jika ada)
$cart_items = $_SESSION['cart'];
$total_all = 0;

// Setelah redirect dan status checkout sukses, ambil data dari session
if ($checkout_success) {
    $transaction_id_display = $transaction_data['id'];
    $transaction_details = $transaction_data['details'];
    $final_total = $transaction_data['total_amount'];
    $transaction_date = $transaction_data['date'];
    // Hapus data dari session setelah diambil agar struk tidak muncul lagi saat refresh
    unset($_SESSION['last_transaction']);
}

?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-gray-800 mb-6 border-b pb-2">Keranjang Belanja</h1>

    <?php if ($checkout_success): ?>
        <!-- Tampilan Struk Pembelian (Setelah Checkout) -->
        <div class="max-w-xl mx-auto bg-green-50 border-4 border-green-300 p-8 rounded-xl shadow-2xl">
            <div class="text-center mb-6">
                <h2 class="text-3xl font-extrabold text-green-700">Pembelian Berhasil!</h2>
                <p class="text-lg text-gray-600 mt-2">Nomor Transaksi: <span class="font-bold">#<?= $transaction_id_display ?></span></p>
                <p class="text-lg text-gray-600">Tanggal: <?= $transaction_date ?></p>
            </div>

            <h3 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">Detail Struk:</h3>
            
            <table class="min-w-full mb-6">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 text-left text-sm font-medium text-gray-600">Buku</th>
                        <th class="py-2 text-center text-sm font-medium text-gray-600">Qty</th>
                        <th class="py-2 text-right text-sm font-medium text-gray-600">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Pastikan $transaction_details adalah array sebelum perulangan
                    foreach ($transaction_details as $detail): 
                    ?>
                        <tr class="border-b">
                            <td class="py-2 text-sm text-gray-900"><?= htmlspecialchars($detail['title']) ?></td>
                            <td class="py-2 text-center text-sm text-gray-700"><?= $detail['quantity'] ?></td>
                            <td class="py-2 text-right text-sm text-gray-700">Rp <?= number_format($detail['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="flex justify-between items-center pt-4 border-t border-gray-400">
                <p class="text-xl font-bold text-gray-800">TOTAL BAYAR:</p>
                <p class="text-2xl font-extrabold text-green-700">Rp <?= number_format($final_total, 0, ',', '.') ?></p>
            </div>
            
            <a href="<?= BASE_URL ?>/index.php" class="mt-6 block text-center bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 font-semibold">
                Kembali ke Beranda
            </a>
        </div>
    <?php elseif (empty($cart_items)): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative text-center">
            <span class="block sm:inline">Keranjang belanja Anda kosong.</span>
            <p class="mt-2"><a href="<?= BASE_URL ?>/books.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Lanjut belanja di Toko Buku</a></p>
        </div>
    <?php else: ?>
        <!-- Tampilan Keranjang Belanja -->
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-2/3">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($cart_items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total_all += $subtotal;
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($item['title']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center"><?= $item['quantity'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="?action=remove&id=<?= $item['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                            class="text-red-600 hover:text-red-900">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <a href="<?= BASE_URL ?>/books.php" class="mt-4 inline-block text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Lanjut Belanja
                </a>
            </div>

            <div class="lg:w-1/3">
                <div class="bg-white p-6 rounded-xl shadow-2xl border border-teal-100 sticky top-4">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Ringkasan Pesanan</h2>
                    
                    <div class="flex justify-between mb-4">
                        <p class="text-lg text-gray-600">Total Item:</p>
                        <p class="text-lg font-semibold text-gray-800"><?= count($cart_items) ?></p>
                    </div>

                    <div class="flex justify-between items-center py-4 border-t-2 border-teal-300">
                        <p class="text-xl font-bold text-gray-800">Total Belanja:</p>
                        <p class="text-3xl font-extrabold text-teal-600">Rp <?= number_format($total_all, 0, ',', '.') ?></p>
                    </div>

                    <!-- Tombol Checkout -->
                    <form action="<?= BASE_URL ?>/public/checkout.php" method="POST" class="mt-6">
                        <input type="hidden" name="action" value="checkout">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit"
                                class="w-full bg-teal-600 text-white py-3 rounded-lg hover:bg-teal-700 transition duration-200 font-bold shadow-md transform hover:scale-[1.01]">
                            Proses Checkout & Buat Struk
                        </button>
                    </form>

                    <!-- Tombol Kosongkan Keranjang -->
                    <form action="<?= BASE_URL ?>/public/checkout.php" method="POST" class="mt-3">
                        <input type="hidden" name="action" value="clear">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit"
                                class="w-full text-red-500 border border-red-500 py-2 rounded-lg hover:bg-red-50 transition duration-200 font-semibold">
                            Kosongkan Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
