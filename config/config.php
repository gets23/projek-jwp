<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "kuliah_blog";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>