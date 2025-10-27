<?php
$page_title = "Pesanan Berhasil";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';

// Halaman ini wajib login
require_login();

// Ambil order_id dari session
$order_id = $_SESSION['last_order_id'] ?? null;

// Jika tidak ada order_id (misal: user refresh, atau akses langsung)
if (!$order_id) {
    header("Location: index.php"); // Arahkan ke home
    exit();
}

// Hapus session agar tidak bisa di-refresh
unset($_SESSION['last_order_id']);

// Ambil data order dan item-nya
try {
    $pdo = getPDO();
    
    // 1. Ambil data order utama
    $stmt_order = $pdo->prepare(
        "SELECT orders.id, orders.total_amount, orders.order_date, users.name, users.email
         FROM orders 
         JOIN users ON orders.user_id = users.id
         WHERE orders.id = ? AND orders.user_id = ?"
    );
    $stmt_order->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt_order->fetch();

    if (!$order) {
        // Jika order tidak ditemukan (bukan milik user ini)
        throw new Exception("Order tidak ditemukan.");
    }

    // 2. Ambil item-item di order tersebut
    $stmt_items = $pdo->prepare(
        "SELECT oi.quantity, oi.price_at_purchase, b.title
         FROM order_items oi
         JOIN books b ON oi.book_id = b.id
         WHERE oi.order_id = ?"
    );
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll();

} catch (Exception $e) {
    // Jika error, redirect ke home
    header("Location: index.php");
    exit();
}
?>

<div class="bg-white p-8 sm:p-12 rounded-lg shadow-lg max-w-2xl mx-auto border-4 border-green-500">
    
    <div class="text-center">
        <!-- Icon Sukses (SVG) -->
        <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <h2 class="text-3xl font-bold text-gray-900 mt-4">Pesanan Berhasil!</h2>
        <p class="text-gray-600 mt-2">Terima kasih atas pembelian Anda, <?php echo htmlspecialchars($order['name']); ?>.</p>
        <p class="text-sm text-gray-500">Struk ini telah dikirimkan ke email Anda (<?php echo htmlspecialchars($order['email']); ?>) - (fitur pura-pura).</p>
    </div>

    <!-- Detail Struk -->
    <div class="mt-8 border-t border-b border-gray-200 py-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h3>
        
        <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>Nomor Pesanan:</span>
            <span class="font-medium text-gray-900">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        <div class="flex justify-between text-sm text-gray-600 mb-4">
            <span>Tanggal Pesanan:</span>
            <span class="font-medium text-gray-900"><?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?></span>
        </div>

        <!-- Daftar Item -->
        <div class="flow-root mt-6">
            <ul role="list" class="-my-4 divide-y divide-gray-200">
                <?php foreach ($order_items as $item): ?>
                    <li class="flex py-4">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p class="mt-1 text-sm text-gray-500">
                                <?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?>
                            </p>
                        </div>
                        <p class="font-medium text-gray-900">
                            Rp <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 0, ',', '.'); ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Total -->
    <div class="mt-6">
        <div class="flex justify-between text-lg font-bold text-gray-900">
            <p>Total Bayar</p>
            <p>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="books.php" class="font-medium text-indigo-600 hover:text-indigo-500">
            &larr; Kembali Belanja
        </a>
    </div>

</div>

<?php
// Panggil footer
require_once __DIR__ . '/../includes/footer.php';
?>
