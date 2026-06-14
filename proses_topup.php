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
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'QRIS';
$payment_method_escaped = mysqli_real_escape_string($conn, $payment_method);

if ($amount < 10000) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Jumlah top up tidak valid! Minimal top up adalah Rp 10.000.'];
    header("Location: index.php?page=collections");
    exit;
}

// Tambahkan saldo pengguna di tabel users
$query_user = mysqli_query($conn, "
    UPDATE users 
    SET Balance = Balance + $amount 
    WHERE UserID = '$user_id'
");

if ($query_user) {
    // Catat log riwayat top up di database
    mysqli_query($conn, "
        INSERT INTO topup (UserID, Amount, Payment_Method)
        VALUES ('$user_id', '$amount', '$payment_method_escaped')
    ");

    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Top up berhasil! Saldo Anda telah ditambahkan sebesar Rp ' . number_format($amount, 0, ',', '.') . '.'];
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Top up gagal! Terjadi kesalahan pada server.'];
}

header("Location: index.php?page=collections");
exit;
?>
