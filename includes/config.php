<?php
// koneksi database steamrent
$conn = mysqli_connect("localhost", "root", "", "steamrent");

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
