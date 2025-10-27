<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "projek_jwp";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>