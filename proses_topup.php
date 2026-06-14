<?php
session_start();
include_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu!'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$amount = intval($_POST['amount']);

if ($amount < 10000) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Jumlah top up tidak valid! Minimal top up adalah Rp 10.000.'];
    header("Location: index.php?page=topup");
    exit;
}

// Tambahkan saldo pengguna di tabel users
$query_user = mysqli_query($conn, "
    UPDATE users 
    SET Balance = Balance + $amount 
    WHERE UserID = '$user_id'
");

if ($query_user) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Top up berhasil! Saldo Anda telah ditambahkan sebesar Rp ' . number_format($amount, 0, ',', '.') . '.'];
    header("Location: index.php?page=collections");
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Top up gagal! Terjadi kesalahan pada server.'];
    header("Location: index.php?page=topup");
}
exit;
?>
