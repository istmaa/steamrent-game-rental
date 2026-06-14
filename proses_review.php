<?php
session_start();
include 'koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($game_id <= 0 || $rating < 1 || $rating > 5) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Input ulasan tidak valid!'];
        header("Location: index.php?page=collections");
        exit;
    }

    // Ensure the user has actually rented the game
    $rent_check_q = mysqli_query($conn, "SELECT id FROM rentals WHERE user_id = '$user_id' AND game_id = '$game_id'");
    if (!$rent_check_q || mysqli_num_rows($rent_check_q) == 0) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Anda hanya dapat memberikan ulasan untuk game yang pernah Anda sewa!'];
        header("Location: index.php?page=collections");
        exit;
    }

    // Guard against duplicate reviews
    $check_q = mysqli_query($conn, "SELECT id FROM reviews WHERE user_id = '$user_id' AND game_id = '$game_id'");
    if ($check_q && mysqli_num_rows($check_q) > 0) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Anda sudah memberikan ulasan untuk game ini sebelumnya.'];
        header("Location: index.php?page=collections");
        exit;
    }

    // Insert review
    $stmt = mysqli_prepare($conn, "INSERT INTO reviews (user_id, game_id, rating, comment) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiis", $user_id, $game_id, $rating, $comment);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Ulasan Anda berhasil dikirim! Terima kasih atas masukannya.'];
    } else {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal mengirimkan ulasan. Silakan coba lagi.'];
    }
    
    mysqli_stmt_close($stmt);
    header("Location: index.php?page=collections");
    exit;
} else {
    header("Location: index.php");
    exit;
}
