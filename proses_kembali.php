<?php
session_start();
include_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu!'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$rental_id = intval($_GET['id']);

// Cek kecocokan data rental aktif milik user
$rental_query = mysqli_query($conn, "
    SELECT * FROM rental 
    WHERE RentalID = '$rental_id' AND UserID = '$user_id' AND Status = 'active'
");
$rental = mysqli_fetch_assoc($rental_query);

if (!$rental) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Data transaksi rental tidak ditemukan atau sudah dikembalikan!'];
    header("Location: index.php?page=collections");
    exit;
}

// Update status rental menjadi returned (Kembali)
// Stok game dipulihkan dan logout sessionlog dicatat otomatis lewat Trigger database after_rental_update_trg
$return_rental = mysqli_query($conn, "
    UPDATE rental 
    SET Status = 'returned', End_Time = NOW() 
    WHERE RentalID = '$rental_id'
");

if ($return_rental) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Game berhasil dikembalikan! Silakan berikan ulasan tentang pengalaman bermain Anda.'];
    header("Location: index.php?page=profile");
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal mengembalikan game! Terjadi kesalahan pada server.'];
    header("Location: index.php?page=collections");
}
exit;
?>
