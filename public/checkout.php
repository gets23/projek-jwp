<?php
$page_title = "Checkout";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';

// Halaman ini wajib login
require_login();

// Ambil pesan error dari session (jika ada, misal: stok habis)
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// Ambil data keranjang milik user
$cart_items = [];
$total_price = 0;
$total_items = 0;
$stok_aman = true;
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

    if (empty($cart_items)) {
        // Jika keranjang kosong, tidak perlu checkout
        header("Location: books.php");
        exit();
    }

    // Hitung total dan cek stok
    foreach ($cart_items as $item) {
        if ($item['quantity'] > $item['stock']) {
            $stok_aman = false;
        }
        $total_price += $item['price'] * $item['quantity'];
        $total_items += $item['quantity'];
    }

} catch (PDOException $e) {
    $error_message = "Gagal mengambil data keranjang: " . $e->getMessage();
}

?>

<div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Review Pesanan Anda</h2>

    <!-- Tampilkan Pesan Error (misal: stok habis saat dicek ulang) -->
    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Peringatan jika stok tidak aman -->
    <?php if (!$stok_aman): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
            <p><strong>Peringatan!</strong> Stok untuk beberapa barang di keranjang Anda telah berubah. Silakan <a href="cart.php" class="font-bold underline">kembali ke keranjang</a> untuk menyesuaikan.</p>
        </div>
    <?php endif; ?>

    <!-- Daftar Item -->
    <div class="flow-root mb-6">
        <ul role="list" class="-my-6 divide-y divide-gray-200">
            <?php foreach ($cart_items as $item): ?>
                <li class="flex py-6">
                    <div class="ml-4 flex flex-1 flex-col">
                        <div>
                            <div class="flex justify-between text-base font-medium text-gray-900">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="ml-4">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Qty: <?php echo htmlspecialchars($item['quantity']); ?></p>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Ringkasan Total -->
    <div class="border-t border-gray-200 pt-6">
        <div class="flex justify-between text-base font-medium text-gray-900">
            <p>Total (<?php echo $total_items; ?> item)</p>
            <p>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></p>
        </div>
        <p class="mt-0.5 text-sm text-gray-500">Anda akan mendapatkan struk setelah konfirmasi.</p>
        
        <!-- Form Konfirmasi -->
        <form action="../app/process_order.php" method="POST" class="mt-6">
            <input type="hidden" name="action" value="create_order">
            
            <!-- Tombol ini HANYA aktif jika stok aman -->
            <button type="submit" 
                    class="flex w-full items-center justify-center rounded-md border border-transparent bg-green-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-green-700 <?php if (!$stok_aman) echo 'opacity-50 cursor-not-allowed'; ?>"
                    <?php if (!$stok_aman) echo 'disabled'; ?>>
                Konfirmasi dan Buat Pesanan
            </button>
        </form>
        
        <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
            <p>
                atau
                <a href="cart.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Kembali ke Keranjang
                </a>
            </p>
        </div>
    </div>
</div>

<?php
// Panggil footer
require_once __DIR__ . '/../includes/footer.php';
?>
