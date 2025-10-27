<?php
/**
 * Proses Logout
 */

require_once __DIR__ . '/../config/config.php';

// Hapus semua data session
$_SESSION = [];

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit();
