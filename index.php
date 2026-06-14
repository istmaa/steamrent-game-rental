<?php
session_start();
// sembunyikan notice dan warning untuk visual yang bersih
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

include_once 'includes/config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Validasi halaman-halaman terproteksi
$protected_pages = ['collections', 'profile', 'rental-history', 'topup', 'rent'];
if (in_array($page, $protected_pages) && !isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu untuk mengakses halaman ini!'];
    header("Location: login.php");
    exit;
}

// Sertakan layout header global
include_once 'includes/header.php';

// Navigasi Router halaman
switch ($page) {
    case 'home':
        include_once 'pages/home.php';
        break;
    case 'trending':
        include_once 'pages/trending.php';
        break;
    case 'games':
        include_once 'pages/games.php';
        break;
    case 'collections':
    case 'rental-history':
        include_once 'pages/collections.php';
        break;
    case 'topup':
        include_once 'pages/topup.php';
        break;
    case 'profile':
        include_once 'pages/profile.php';
        break;
    case 'rent':
        include_once 'pages/rent.php';
        break;
    default:
        include_once 'pages/home.php';
        break;
}

// Sertakan layout footer global
include_once 'includes/footer.php';
?>