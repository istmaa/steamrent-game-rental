<?php
session_start();
include 'koneksi.php';

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

// Start transaction or run queries sequentially
$query_topup = mysqli_query($conn, "INSERT INTO topups(user_id, amount) VALUES('$user_id', '$amount')");
$query_user = mysqli_query($conn, "UPDATE users SET balance = balance + $amount WHERE id = '$user_id'");

if ($query_topup && $query_user) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Top up berhasil! Saldo Anda telah ditambahkan sebesar Rp ' . number_format($amount, 0, ',', '.') . '.'];
    header("Location: index.php?page=collections");
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Top up gagal! Terjadi kesalahan pada server.'];
    header("Location: index.php?page=topup");
}
exit;
?>
