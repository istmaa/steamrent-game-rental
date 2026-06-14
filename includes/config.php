<?php
// Sesuaikan dengan konfigurasi database masing-masing server
$conn = mysqli_connect(
    "localhost",
    "YOUR_DB_USERNAME",
    "YOUR_DB_PASSWORD",
    "YOUR_DB_NAME"
);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>