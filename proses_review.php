<?php
session_start();
include_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu!'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = intval($_POST['game_id']);
$rating = intval($_POST['rating']);
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($rating < 1 || $rating > 5) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Rating tidak valid! Harus bernilai antara 1 s/d 5.'];
    header("Location: index.php?page=profile");
    exit;
}

// Validasi duplikasi ulasan
$check_query = mysqli_query($conn, "SELECT ReviewID FROM reviews WHERE UserID = '$user_id' AND GameID = '$game_id'");
if (mysqli_num_rows($check_query) > 0) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Anda sudah pernah memberikan ulasan untuk game ini!'];
    header("Location: index.php?page=profile");
    exit;
}

$comment_escaped = mysqli_real_escape_string($conn, $comment);

// Simpan ulasan di database
$query = mysqli_query($conn, "
    INSERT INTO reviews (UserID, GameID, Rating, Comment) 
    VALUES ('$user_id', '$game_id', '$rating', '$comment_escaped')
");

if ($query) {
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Ulasan Anda berhasil dikirim! Terima kasih atas masukan Anda.'];
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal mengirimkan ulasan! Terjadi kesalahan server.'];
}

header("Location: index.php?page=profile");
exit;
?>
