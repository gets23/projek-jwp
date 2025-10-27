<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

start_secure_session();
require_login(); // Wajib login untuk aksi keranjang

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

try {
    if ($action === 'add' && isset($_POST['book_id'])) {
        $book_id = $_POST['book_id'];
        $quantity = $_POST['quantity'] ?? 1;

        // Cek apakah item sudah ada di keranjang
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
        $existing_item = $stmt->fetch();

        if ($existing_item) {
            // Jika sudah ada, tambahkan quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
            $_SESSION['success_message'] = "Kuantitas buku di keranjang diperbarui.";
        } else {
            // Jika belum ada, tambahkan item baru
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $book_id, $quantity]);
            $_SESSION['success_message'] = "Buku berhasil ditambahkan ke keranjang.";
        }
        redirect(BASE_URL . '/index.php'); // Redirect kembali ke index

    } elseif ($action === 'update' && isset($_POST['cart_id'])) {
        $cart_id = $_POST['cart_id'];
        $quantity = $_POST['quantity'];

        if ($quantity < 1) {
             $_SESSION['error_message'] = "Kuantitas tidak boleh kurang dari 1.";
        } else {
            // (Opsional) Cek stok di sini
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$quantity, $cart_id, $user_id]);
            $_SESSION['success_message'] = "Kuantitas berhasil diperbarui.";
        }
        redirect(BASE_URL . '/cart.php');

    } elseif ($action === 'remove' && isset($_POST['cart_id'])) {
        $cart_id = $_POST['cart_id'];
        
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        $_SESSION['success_message'] = "Item berhasil dihapus dari keranjang.";
        redirect(BASE_URL . '/cart.php');

    } else {
        $_SESSION['error_message'] = "Aksi tidak valid.";
        redirect(BASE_URL . '/cart.php');
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    redirect(BASE_URL . '/cart.php');
}
