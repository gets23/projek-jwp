<?php
require_once 'layout/header.php';
require_once __DIR__ . '/../app/functions.php';
require_once __DIR__ . '/../app/db.php';

// Wajib login
require_login();
$user_id = $_SESSION['user_id'];
$total_price = $_POST['total_price'] ?? 0;
$items_purchased = [];

// Logika checkout sederhana:
// 1. Ambil semua item dari keranjang user
// 2. Tampilkan sebagai "struk"
// 3. Kurangi stok (Opsional, tapi penting)
// 4. Kosongkan keranjang user

try {
    // 1. Ambil item
    $stmt = $pdo->prepare("
        SELECT cart.quantity, books.title, books.price
        FROM cart
        JOIN books ON cart.book_id = books.id
        WHERE cart.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $items_purchased = $stmt->fetchAll();

    if (empty($items_purchased) && $total_price == 0) {
        // Jika keranjang kosong dan tidak ada total, redirect
        redirect(BASE_URL . '/cart.php');
    }
    
    // 2. (Opsional) Kurangi stok
    // Loop $items_purchased dan jalankan query UPDATE stok
    // ... (dilewati untuk kesederhanaan)

    // 3. Kosongkan keranjang
    $stmt_clear = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt_clear->execute([$user_id]);

} catch (PDOException $e) {
    echo "Error checkout: " . $e->getMessage();
    // Sebaiknya ada penanganan transaksi (commit/rollback) di sini
}

?>

<div class="max-w-2xl mx-auto bg-white p-10 rounded-lg shadow-md border">
    <h1 class="text-4xl font-bold mb-4 text-center text-green-600">Checkout Berhasil!</h1>
    <p class="text-lg text-gray-600 text-center mb-8">Terima kasih atas pembelian Anda, <?php echo htmlspecialchars($_SESSION['user_name']); ?>.</p>

    <div class="border-t border-b border-gray-300 py-6 my-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Struk Pembelian</h2>
        
        <div class="space-y-3 mb-6">
            <?php foreach ($items_purchased as $item): ?>
                <div class="flex justify-between items-center text-gray-700">
                    <span>
                        <?php echo htmlspecialchars($item['title']); ?>
                        (x <?php echo $item['quantity']; ?>)
                    </span>
                    <span class="font-medium">
                        Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="border-t border-gray-300 pt-4 mt-4 flex justify-between items-center">
            <span class="text-xl font-bold text-gray-900">Total Pembayaran:</span>
            <span class="text-2xl font-bold text-green-600">
                Rp <?php echo number_format($total_price, 0, ',', '.'); ?>
            </span>
        </div>
    </div>
    
    <p class="text-center text-gray-500 text-sm">
        Ini adalah struk digital sederhana. Keranjang Anda telah dikosongkan.
    </p>
    <div class="text-center mt-8">
        <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
            Kembali ke Home
        </a>
    </div>
</div>


<?php require_once 'layout/footer.php'; ?>
