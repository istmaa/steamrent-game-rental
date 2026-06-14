<?php
session_start();
include 'koneksi.php';

$old_username = isset($_SESSION['login_old_username']) ? $_SESSION['login_old_username'] : '';
$errors = isset($_SESSION['login_errors']) ? $_SESSION['login_errors'] : [];
$success_msg = isset($_SESSION['register_success']) ? $_SESSION['register_success'] : '';

// Retrieve session toast if set in redirected controllers
$session_toast = isset($_SESSION['toast']) ? $_SESSION['toast'] : null;

// Clear session variables
unset($_SESSION['login_old_username']);
unset($_SESSION['login_errors']);
unset($_SESSION['register_success']);
unset($_SESSION['toast']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SteamRent - Masuk Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="min-vh-100 position-relative" style="overflow-x: hidden;">

    <!-- Canvas Background Particles -->
    <canvas id="particles-canvas"></canvas>

    <!-- Bootstrap Toasts Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php
    // Output javascript to trigger Toasts dynamically
    if ($session_toast) {
        $t_type = $session_toast['type'];
        $t_msg = mysqli_real_escape_string($conn, $session_toast['message']);
        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('$t_type', '$t_msg');
            });
        </script>";
    }
    if (!empty($success_msg)) {
        $msg = mysqli_real_escape_string($conn, $success_msg);
        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast('success', '$msg');
            });
        </script>";
    }
    if (!empty($errors)) {
        foreach ($errors as $err) {
            $err_escaped = mysqli_real_escape_string($conn, $err);
            echo "<script>
                document.addEventListener('DOMContentLoaded', () => {
                    showToast('error', '$err_escaped');
                });
            </script>";
        }
    }
    ?>

    <div class="auth-wrapper min-vh-100 d-flex">
        <!-- Banner Side -->
        <div class="auth-banner-side text-white d-none d-md-flex flex-column justify-content-center p-5 position-relative" style="flex: 1; z-index: 1;">
            <div class="mb-4 animate-fade-in">
                <h1 class="display-3 fw-bold tracking-tight">Steam<span class="text-accent">Rent</span></h1>
                <p class="text-light opacity-75 fs-5">Premium Game Rental Platform</p>
            </div>
            <p class="fs-6 mb-0 text-light opacity-90 animate-fade-in" style="max-width: 480px; line-height: 1.7;">
                Kembali nikmati akses pustaka game premium terlengkap secara instan dengan fleksibilitas penuh tanpa batas performa.
            </p>
        </div>

        <!-- Form Side (Translucent glass feel) -->
        <div class="auth-form-side glass-panel d-flex flex-column justify-content-center p-4 p-sm-5 animate-fade-in" style="width: 100%; max-width: 490px; z-index: 2;">
            <div class="auth-theme-toggle">
                <button id="themeToggle" class="btn btn-outline-light btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-sun-fill"></i> Bright Mode
                </button>
            </div>

            <div class="w-100">
                <div class="mb-4">
                    <a href="index.php" class="text-decoration-none text-accent small fw-semibold mb-3 d-inline-flex align-items-center gap-2" style="transition: all 0.2s ease;">
                        <i class="bi bi-chevron-left"></i> Kembali ke Beranda
                    </a>
                    <h2 class="fw-bold text-white mb-1">Selamat Datang</h2>
                    <p class="text-secondary small">Masuk menggunakan kredensial akun untuk memulai sesi rental Anda.</p>
                </div>

                <form action="proses_login.php" method="POST">
                    <div class="auth-input-group mb-3">
                        <label class="form-label text-white small fw-medium">Nama Pengguna / Email</label>
                        <input type="text" name="username" class="form-control auth-form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" placeholder="Masukkan nama pengguna atau email" value="<?php echo htmlspecialchars($old_username); ?>" required>
                    </div>

                    <div class="auth-input-group mb-4">
                        <label class="form-label text-white small fw-medium">Kata Sandi</label>
                        <input type="password" name="password" class="form-control auth-form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" placeholder="••••••••" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-secondary small" for="rememberMe">Ingat Saya</label>
                        </div>
                        <a href="#" class="text-accent text-decoration-none small hover-underline">Lupa Sandi?</a>
                    </div>

                    <button type="submit" class="btn bg-accent w-100 py-2.5 fw-bold rounded-3 mb-4 shadow-sm hover-scale">
                        Masuk Sekarang
                    </button>

                    <p class="text-secondary text-center small mb-0">
                        Belum terdaftar? <a href="register.php" class="text-accent text-decoration-none fw-semibold hover-underline">Daftar Akun Baru</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>