<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu!'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$rental_id = intval($_GET['id']);

// Get rental details
$rental_query = mysqli_query($conn, "SELECT * FROM rentals WHERE id = '$rental_id' AND user_id = '$user_id' AND status = 'active'");
$rental = mysqli_fetch_assoc($rental_query);

if (!$rental) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Data transaksi rental tidak ditemukan atau sudah dikembalikan!'];
    header("Location: index.php?page=collections");
    exit;
}

$game_id = $rental['game_id'];

// Perform returning actions (game stock is automatically incremented via after_rental_update trigger)
$return_rental = mysqli_query($conn, "UPDATE rentals SET status = 'returned', return_date = NOW() WHERE id = '$rental_id'");
$increase_stock = true; // Handled automatically by database trigger

if ($return_rental && $increase_stock) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Game berhasil dikembalikan! Silakan berikan ulasan tentang pengalaman Anda bermain.'];
    header("Location: index.php?page=collections");
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal mengembalikan game! Terjadi kesalahan server.'];
    header("Location: index.php?page=collections");
}
exit;
?>
