<?php
// koneksi database steamrent
$conn = mysqli_connect(
    "localhost",
    "u169077025_db_steamrent",
    "Steamrent2026",
    "u169077025_db_steamrent"
);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>