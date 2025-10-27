<?php
require_once 'layout/header.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/db.php';

// Halaman ini wajib login
require_login();
$user_id = $_SESSION['user_id'];
$cart_items = [];
$total_price = 0;

try {
    // Ambil semua item di keranjang user
    $stmt = $pdo->prepare("
        SELECT cart.id AS cart_id, cart.quantity, books.id AS book_id, books.title, books.price, books.stock
        FROM cart
        JOIN books ON cart.book_id = books.id
        WHERE cart.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    // Hitung total harga
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Gagal mengambil data keranjang: " . $e->getMessage();
}
?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Keranjang Belanja Anda</h1>

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

    <?php if (empty($cart_items)): ?>
        <p class="text-gray-600 text-lg text-center">Keranjang Anda masih kosong. <a href="index.php" class="text-blue-500 hover:underline">Mulai belanja!</a></p>
    <?php else: ?>
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Daftar Item -->
            <div class="w-full lg:w-2/3 space-y-4">
                <?php foreach ($cart_items as $item): ?>
                    <div class="flex items-center bg-gray-50 p-4 rounded-lg shadow-sm border">
                        <div class="flex-grow">
                            <h2 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($item['title']); ?></h2>
                            <p class="text-gray-600">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?> / pcs</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Form Update Kuantitas -->
                            <form action="<?php echo BASE_URL; ?>/../app/process_cart.php" method="POST" class="flex items-center">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="w-16 px-3 py-1 border rounded-md text-center">
                                <button type="submit" class="ml-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-1 px-3 rounded-md">Update</button>
                            </form>
                            <!-- Form Hapus -->
                            <form action="<?php echo BASE_URL; ?>/../app/process_cart.php" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Ringkasan Belanja -->
            <div class="w-full lg:w-1/3 bg-gray-100 p-6 rounded-lg shadow-md h-fit">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Ringkasan</h2>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600">Total Harga:</span>
                    <span class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                </div>
                <form action="checkout.php" method="POST">
                    <!-- Kirim data total untuk ditampilkan di struk -->
                    <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Lanjut ke Checkout
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
