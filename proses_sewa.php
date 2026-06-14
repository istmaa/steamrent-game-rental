<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu!'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = intval($_POST['game_id']);
$duration = intval($_POST['duration']);

if ($duration < 1 || $duration > 72) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Durasi sewa tidak valid! Durasi sewa minimal adalah 1 jam dan maksimal adalah 72 jam.'];
    header("Location: index.php?page=rent&game_id=" . $game_id);
    exit;
}

// Get game details
$game_query = mysqli_query($conn, "SELECT * FROM games WHERE id = '$game_id'");
$game = mysqli_fetch_assoc($game_query);

if (!$game) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Game tidak ditemukan!'];
    header("Location: index.php");
    exit;
}

if ($game['stock'] <= 0) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Stok game sedang habis!'];
    header("Location: index.php");
    exit;
}

// Calculate total price using the SQL function
$price_query = mysqli_query($conn, "SELECT fn_calculate_rental_price('$game_id', '$duration') AS total_price");
$price_data = mysqli_fetch_assoc($price_query);
$total_price = intval($price_data['total_price']);

// Get user balance
$user_query = mysqli_query($conn, "SELECT balance FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

if ($user['balance'] < $total_price) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Saldo tidak mencukupi! Silakan lakukan top up terlebih dahulu.'];
    header("Location: index.php?page=topup");
    exit;
}

// Perform transaction via Stored Procedure
$status_code = -1;
$status_message = "Terjadi kesalahan pada database.";

$stmt = mysqli_prepare($conn, "CALL sp_rent_game(?, ?, ?, @status_code, @status_message)");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $game_id, $duration);
    if (mysqli_stmt_execute($stmt)) {
        $res_params = mysqli_query($conn, "SELECT @status_code AS status_code, @status_message AS status_message");
        if ($res_params) {
            $out_params = mysqli_fetch_assoc($res_params);
            $status_code = intval($out_params['status_code']);
            $status_message = $out_params['status_message'];
        }
    }
}

if ($status_code === 0) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Sewa game berhasil! Silakan cek di riwayat rental Anda.'];
    header("Location: index.php?page=collections");
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menyewa game! ' . $status_message];
    header("Location: index.php?page=rent&game_id=" . $game_id);
}
exit;
?>
