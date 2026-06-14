<?php
// cek login user secara global
if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu untuk mengakses halaman ini!'];
    header("Location: login.php");
    exit;
}
?>
