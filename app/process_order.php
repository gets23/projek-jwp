<?php
/**
 * Proses Backend Checkout (Membuat Order)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Aksi ini wajib login
require_login();
$user_id = $_SESSION['user_id'];

// Hanya proses jika request method-nya POST dan aksinya 'create_order'
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'create_order') {
    
    $pdo = getPDO();

    try {
        // ===================================================
        // MULAI TRANSAKSI DATABASE
        // Ini memastikan semua query berhasil, atau tidak sama sekali.
        $pdo->beginTransaction();
        // ===================================================

        // 1. Ambil semua item di keranjang user
        $stmt_cart = $pdo->prepare(
            "SELECT cart.quantity, books.id AS book_id, books.title, books.price, books.stock
             FROM cart
             JOIN books ON cart.book_id = books.id
             WHERE cart.user_id = ? AND cart.quantity > 0"
        );
        $stmt_cart->execute([$user_id]);
        $cart_items = $stmt_cart->fetchAll();

        if (empty($cart_items)) {
            // Jika keranjang kosong, batalkan
            throw new Exception("Keranjang Anda kosong.");
        }

        $total_amount = 0;

        // 2. Validasi Stok dan Hitung Total
        foreach ($cart_items as $item) {
            if ($item['quantity'] > $item['stock']) {
                // Jika stok tiba-tiba habis, batalkan
                throw new Exception("Maaf, stok untuk buku '" . htmlspecialchars($item['title']) . "' tidak mencukupi (sisa " . $item['stock'] . ").");
            }
            $total_amount += $item['price'] * $item['quantity'];
        }

        // 3. Buat catatan di tabel `orders`
        $stmt_order = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
        $stmt_order->execute([$user_id, $total_amount]);
        
        // Ambil ID dari order yang baru saja dibuat
        $order_id = $pdo->lastInsertId();

        // 4. Pindahkan item dari keranjang ke `order_items` DAN kurangi stok
        $stmt_item = $pdo->prepare(
            "INSERT INTO order_items (order_id, book_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)"
        );
        $stmt_stock = $pdo->prepare(
            "UPDATE books SET stock = stock - ? WHERE id = ?"
        );

        foreach ($cart_items as $item) {
            // Masukkan ke order_items
            $stmt_item->execute([
                $order_id,
                $item['book_id'],
                $item['quantity'],
                $item['price'] // Ini adalah 'price_at_purchase'
            ]);
            
            // Kurangi stok
            $stmt_stock->execute([$item['quantity'], $item['book_id']]);
        }

        // 5. Kosongkan keranjang user
        $stmt_clear_cart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt_clear_cart->execute([$user_id]);

        // ===================================================
        // SEMUA BERHASIL, KONFIRMASI TRANSAKSI
        $pdo->commit();
        // ===================================================

        // Simpan ID order ke session untuk ditampilkan di halaman "struk"
        $_SESSION['last_order_id'] = $order_id;
        
        // Redirect ke halaman sukses
        header("Location: ../public/order_success.php");
        exit();

    } catch (Exception $e) {
        // ===================================================
        // TERJADI ERROR, BATALKAN SEMUA PERUBAHAN
        $pdo->rollBack();
        // ===================================================
        
        // Kirim pesan error kembali ke halaman checkout
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: ../public/checkout.php");
        exit();
    }

} else {
    // Jika akses langsung atau aksi salah
    header("Location: ../public/checkout.php");
    exit();
}
