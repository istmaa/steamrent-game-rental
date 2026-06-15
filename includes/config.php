<?php
// Sesuaikan dengan konfigurasi database masing-masing server
$conn = mysqli_connect(
    "localhost",
    "username",
    "password",
    "database"
);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>