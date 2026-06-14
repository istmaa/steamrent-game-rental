<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$user_balance = 0;
$profile_image = null;
$user_display_name = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // ambil data user terbaru dari database
    $user_query = mysqli_query($conn, "SELECT Name, Balance, Profile_Image FROM users WHERE UserID = '$user_id'");
    if ($user_data = mysqli_fetch_assoc($user_query)) {
        $user_balance = $user_data['Balance'];
        $profile_image = $user_data['Profile_Image'];
        $user_display_name = $user_data['Name'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SteamRent - Premium Game Rental</title>
    <!-- Google Fonts Poppins / Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-vh-100">

    <!-- Preloader Screen -->
    <div id="preloader">
        <div class="loader-spinner-wrapper">
            <div class="loader-spinner"></div>
            <div class="loader-text">SteamRent</div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php
    // Flash dynamic toast notification if set in session
    if (isset($_SESSION['toast'])) {
        $t = $_SESSION['toast'];
        $t_type = $t['type'];
        $t_msg = mysqli_real_escape_string($conn, $t['message']);
        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('$t_type', '$t_msg');
            });
        </script>";
        unset($_SESSION['toast']);
    }
    ?>

    <div class="d-flex flex-column flex-md-row min-vh-100">

        <!-- Sidebar Navigation -->
        <aside class="sidebar glass-panel p-4 d-flex flex-column flex-shrink-0">
            <div class="mb-4 text-center text-md-start">
                <a href="index.php?page=home" class="text-decoration-none">
                    <h2 class="fw-bold m-0 text-white">Steam<span class="text-accent">Rent</span></h2>
                </a>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- User Profile Card in Sidebar -->
                <a href="index.php?page=profile" class="user-box p-3 mb-4 rounded-3 d-flex align-items-center gap-3 text-decoration-none">
                    <?php if (!empty($profile_image) && file_exists('uploads/profile/' . $profile_image)): ?>
                        <img src="uploads/profile/<?php echo htmlspecialchars($profile_image); ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="Profile Picture">
                    <?php else: ?>
                        <i class="bi bi-person-circle fs-2 text-accent"></i>
                    <?php endif; ?>
                    <div class="text-start">
                        <div class="fw-bold text-white lh-1">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </div>
                        <small class="text-accent fw-semibold" style="font-size: 12px;">
                            Saldo: Rp <?php echo number_format($user_balance, 0, ',', '.'); ?>
                        </small>
                    </div>
                </a>
            <?php else: ?>
                <!-- Guest Card in Sidebar -->
                <a href="login.php" class="user-box p-3 mb-4 rounded-3 d-flex align-items-center gap-3 text-decoration-none">
                    <i class="bi bi-person-circle fs-2 text-secondary"></i>
                    <div class="text-start">
                        <div class="fw-bold text-white lh-1">Masuk / Login</div>
                        <small class="text-accent fw-semibold" style="font-size: 12px;">Akses Akun Anda</small>
                    </div>
                </a>
            <?php endif; ?>

            <!-- Theme Toggle Button -->
            <div class="mb-4">
                <button id="themeToggle" class="btn btn-outline-light btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-sun-fill"></i> Bright Mode
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="nav flex-column gap-2 mb-auto text-center text-md-start">
                <a class="nav-link nav-item-custom <?php echo ($page == 'home') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=home">
                    <i class="bi bi-house-door-fill"></i> Beranda
                </a>
                <a class="nav-link nav-item-custom <?php echo ($page == 'trending') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=trending">
                    <i class="bi bi-graph-up-arrow"></i> Sedang Tren
                </a>
                <a class="nav-link nav-item-custom <?php echo ($page == 'games') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=games">
                    <i class="bi bi-controller"></i> Katalog Game
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link nav-item-custom <?php echo ($page == 'collections') ? 'active text-white' : 'text-secondary'; ?> px-3 py-2 rounded-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2" href="index.php?page=collections">
                        <i class="bi bi-collection-play-fill"></i> Koleksi Game
                    </a>
                <?php endif; ?>
            </nav>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="mt-4 text-danger text-decoration-none fw-medium text-center text-md-start px-3 py-2 rounded-2 w-100 d-flex align-items-center justify-content-center justify-content-md-start gap-2" style="background-color: rgba(239, 68, 68, 0.1); transition: 0.3s;">
                    <i class="bi bi-box-arrow-left"></i> Keluar Akun
                </a>
            <?php endif; ?>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow-1 p-4 p-md-5 overflow-auto">
