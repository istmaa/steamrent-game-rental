<?php
session_start();
include_once 'includes/config.php';

$fullname_val = isset($_SESSION['register_old']['fullname']) ? $_SESSION['register_old']['fullname'] : '';
$username_val = isset($_SESSION['register_old']['username']) ? $_SESSION['register_old']['username'] : '';
$email_val = isset($_SESSION['register_old']['email']) ? $_SESSION['register_old']['email'] : '';
$errors = isset($_SESSION['register_errors']) ? $_SESSION['register_errors'] : [];

unset($_SESSION['register_errors']);
unset($_SESSION['register_old']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SteamRent - Pendaftaran Akun Baru</title>
    <!-- Google Fonts Poppins / Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-vh-100 position-relative" style="overflow-x: hidden;">

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php
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
                Dapatkan penawaran harga eksklusif, lacak progres riwayat bermain, serta akumulasi top-up saldo secara instan dalam genggaman.
            </p>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side glass-panel d-flex flex-column justify-content-center p-4 p-sm-5 animate-fade-in" style="width: 100%; max-width: 490px; z-index: 2; overflow-y: auto;">
            <div class="auth-theme-toggle">
                <button id="themeToggle" class="btn btn-outline-light btn-sm d-flex align-items-center gap-2">
                    <i class="bi bi-sun-fill"></i> Bright Mode
                </button>
            </div>

            <div class="w-100 mt-5 mt-md-0">
                <div class="mb-4">
                    <a href="index.php" class="text-decoration-none text-accent small fw-semibold mb-3 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-chevron-left"></i> Kembali ke Beranda
                    </a>
                    <h2 class="fw-bold text-white mb-1">Bergabung Sekarang</h2>
                    <p class="text-secondary small">Buat akun SteamRent Anda secara gratis dengan mengisi kelengkapan formulir di bawah.</p>
                </div>

                <form action="proses_register.php" method="POST"> 
                    <div class="auth-input-group mb-3">
                        <label class="form-label text-white small fw-medium">Nama Lengkap</label>
                        <input type="text" name="fullname" class="form-control auth-form-control <?php echo isset($errors['fullname']) ? 'is-invalid' : ''; ?>" placeholder="Masukkan nama lengkap Anda" value="<?php echo htmlspecialchars($fullname_val); ?>" required>
                    </div>

                    <div class="auth-input-group mb-3">
                        <label class="form-label text-white small fw-medium">Nama Pengguna (Username)</label>
                        <input type="text" name="username" class="form-control auth-form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" placeholder="Buat nama pengguna unik" value="<?php echo htmlspecialchars($username_val); ?>" required>
                    </div>

                    <div class="auth-input-group mb-3">
                        <label class="form-label text-white small fw-medium">Alamat Email</label>
                        <input type="email" name="email" class="form-control auth-form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" placeholder="nama@domain.com" value="<?php echo htmlspecialchars($email_val); ?>" required>
                    </div>

                    <div class="auth-input-group mb-3">
                        <label class="form-label text-white small fw-medium">Kata Sandi</label>
                        <input type="password" id="regPassword" name="password" class="form-control auth-form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" placeholder="Gunakan minimal 8 karakter" required>
                    </div>

                    <div class="auth-input-group mb-4">
                        <label class="form-label text-white small fw-medium">Konfirmasi Kata Sandi</label>
                        <input type="password" id="regConfirmPassword" name="confirm_password" class="form-control auth-form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" placeholder="Ulangi kata sandi Anda" required>

                        <!-- Password Strength Indicator Bar -->
                        <div class="password-strength-meter mt-3 mb-2" style="height: 6px; background-color: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                            <div id="strengthBar" style="height: 100%; width: 0%; background-color: #ef4444; transition: all 0.3s ease;"></div>
                        </div>

                        <!-- Real-time Password Checklist -->
                        <div class="password-checklist mt-2 p-3 rounded text-secondary">
                            <div class="fw-semibold text-white mb-2" style="font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase;">Persyaratan Kata Sandi:</div>
                            <div id="chk-length" class="d-flex align-items-center mb-2 text-danger">
                                <i class="bi bi-x-circle-fill me-2 fs-6 check-icon"></i>
                                <span>Minimal 8 karakter</span>
                            </div>
                            <div id="chk-letters" class="d-flex align-items-center mb-2 text-danger">
                                <i class="bi bi-x-circle-fill me-2 fs-6 check-icon"></i>
                                <span>Mengandung huruf (A-Z, a-z)</span>
                            </div>
                            <div id="chk-numbers" class="d-flex align-items-center mb-2 text-danger">
                                <i class="bi bi-x-circle-fill me-2 fs-6 check-icon"></i>
                                <span>Mengandung angka (0-9)</span>
                            </div>
                            <div id="chk-match" class="d-flex align-items-center text-danger">
                                <i class="bi bi-x-circle-fill me-2 fs-6 check-icon"></i>
                                <span>Konfirmasi kata sandi cocok</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn bg-accent w-100 py-2.5 fw-bold rounded-3 mb-4 shadow-sm hover-scale">
                        Daftar Akun Baru
                    </button>

                    <p class="text-secondary text-center small mb-0">
                        Sudah terdaftar? <a href="login.php" class="text-accent text-decoration-none fw-semibold hover-underline">Masuk Akun</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle & script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>