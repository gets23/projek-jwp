<?php
// File: app/process_book.php - Logika CRUD Buku

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

requireAdmin(); // Wajibkan Admin

$pdo = getConnection();
$action = $_REQUEST['action'] ?? '';
$book_id = $_POST['id'] ?? $_GET['id'] ?? null;
$csrf_token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

if (!verifyCsrfToken($csrf_token)) {
    $_SESSION['error_message'] = "Token Keamanan tidak valid. Coba lagi.";
    redirect('admin/books.php');
}

try {
    if ($action === 'create') {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $synopsis = trim($_POST['synopsis']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
        $cover_image = ''; // Simplifikasi: tidak handle upload file

        if (empty($title) || empty($author) || $price === false || $stock === false) {
            throw new Exception("Data buku (Judul, Penulis, Harga, Stok) tidak valid.");
        }

        $stmt = $pdo->prepare("INSERT INTO books (title, author, synopsis, price, stock, cover_image) VALUES (:title, :author, :synopsis, :price, :stock, :cover_image)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':synopsis', $synopsis);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':cover_image', $cover_image);
        $stmt->execute();

        $_SESSION['success_message'] = "Buku baru berhasil ditambahkan.";
        redirect('admin/books.php');

    } elseif ($action === 'update' && $book_id) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $synopsis = trim($_POST['synopsis']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
        $cover_image = ''; 

        if (empty($title) || empty($author) || $price === false || $stock === false) {
            throw new Exception("Data buku (Judul, Penulis, Harga, Stok) tidak valid.");
        }

        $stmt = $pdo->prepare("UPDATE books SET title = :title, author = :author, synopsis = :synopsis, price = :price, stock = :stock WHERE id = :id");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':synopsis', $synopsis);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':id', $book_id);
        $stmt->execute();

        $_SESSION['success_message'] = "Buku berhasil diperbarui.";
        redirect('admin/books.php');

    } elseif ($action === 'delete' && $book_id) {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
        $stmt->bindParam(':id', $book_id);
        $stmt->execute();

        $_SESSION['success_message'] = "Buku berhasil dihapus.";
        redirect('admin/books.php');

    } else {
        $_SESSION['error_message'] = "Aksi tidak valid.";
        redirect('admin/books.php');
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Proses gagal: " . $e->getMessage();
    redirect('admin/books.php');
}
