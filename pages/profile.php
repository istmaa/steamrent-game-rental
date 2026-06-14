<?php
include_once 'includes/config.php';
include_once 'includes/auth.php'; // Keamanan sesi

$user_id = $_SESSION['user_id'];

// Handle Profile Updates (Username and Password)
if (isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $update_fields = [];
    
    // Validasi Username
    if (!empty($new_username)) {
        if (strlen($new_username) < 4) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Username minimal harus 4 karakter!'];
            header("Location: index.php?page=profile");
            exit;
        }
        
        $username_escaped = mysqli_real_escape_string($conn, $new_username);
        // Cek keunikan username
        $check_username = mysqli_query($conn, "SELECT UserID FROM users WHERE Name = '$username_escaped' AND UserID != '$user_id'");
        if (mysqli_num_rows($check_username) > 0) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Username sudah terdaftar! Silakan gunakan username lain.'];
            header("Location: index.php?page=profile");
            exit;
        }
        
        $update_fields[] = "Name = '$username_escaped'";
        $_SESSION['username'] = $new_username; // Sinkronisasi sesi
    }
    
    // Validasi Password Baru
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Password minimal harus 8 karakter!'];
            header("Location: index.php?page=profile");
            exit;
        }
        if ($new_password !== $confirm_password) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Konfirmasi password baru tidak cocok!'];
            header("Location: index.php?page=profile");
            exit;
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields[] = "Password = '$hashed_password'";
    }
    
    if (!empty($update_fields)) {
        $update_query_str = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE UserID = '$user_id'";
        if (mysqli_query($conn, $update_query_str)) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Pengaturan akun Anda berhasil diperbarui!'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal memperbarui pengaturan! Terjadi kesalahan server.'];
        }
    } else {
        $_SESSION['toast'] = ['type' => 'info', 'message' => 'Tidak ada perubahan profil yang disimpan.'];
    }
    
    header("Location: index.php?page=profile");
    exit;
}

// Ambil info data user terbaru
$profile_query = mysqli_query($conn, "SELECT Name, FullName, Email, CreatedAt, Profile_Image FROM users WHERE UserID = '$user_id'");
$user = mysqli_fetch_assoc($profile_query);

if (!$user) {
    echo "<p class='text-danger'>User tidak ditemukan.</p>";
    exit;
}
?>

<div class="row g-4 mb-4 animate-fade-in text-white">
    <div class="col-12">
        <div class="user-box p-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-bold text-white mb-1"><i class="bi bi-person-circle text-accent me-2"></i>Pengaturan Akun</h4>
                <p class="text-secondary small mb-0">Kelola kredensial akun, keamanan kata sandi, dan foto profil Anda.</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 text-white">
    <!-- Left Column: Avatar & Account Metadata Info (Read Only) -->
    <div class="col-12 col-md-5 col-lg-4 animate-fade-in">
        <div class="user-box p-4 rounded-4 text-center d-flex flex-column align-items-center glass-panel">
            
            <!-- Avatar Display & Upload Form -->
            <div class="position-relative mb-3">
                <?php if (!empty($user['Profile_Image']) && file_exists('uploads/profile/' . $user['Profile_Image'])): ?>
                    <img src="uploads/profile/<?php echo htmlspecialchars($user['Profile_Image']); ?>" class="rounded-circle border border-accent border-3" style="width: 120px; height: 120px; object-fit: cover;" alt="Avatar">
                <?php else: ?>
                    <i class="bi bi-person-circle text-accent" style="font-size: 110px;"></i>
                <?php endif; ?>
            </div>

            <!-- Avatar Uploader -->
            <form action="proses_upload.php" method="POST" enctype="multipart/form-data" class="w-100 text-start border-bottom border-secondary border-opacity-25 pb-3 mb-3">
                <label class="form-label text-white small fw-bold d-block mb-2 text-center" style="font-size: 12px;">Ganti Foto Profil</label>
                <div class="mb-2">
                    <input type="file" name="profile_image" class="form-control form-control-sm bg-dark border-secondary text-white" accept="image/*" style="font-size: 11px;" required>
                </div>
                <button type="submit" name="submit_avatar" class="btn btn-sm bg-accent w-100 fw-bold py-1.5" style="font-size: 11px;">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Perbarui Avatar
                </button>
            </form>

            <!-- Metadata Info Box -->
            <div class="w-100 text-start small text-secondary d-flex flex-column gap-2 mt-2">
                <div class="d-flex justify-content-between">
                    <span>UserID:</span>
                    <span class="text-white fw-medium">#<?php echo $user_id; ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Tanggal Bergabung:</span>
                    <span class="text-white fw-medium"><?php echo date('d M Y', strtotime($user['CreatedAt'])); ?></span>
                </div>
            </div>

        </div>
    </div>

    <!-- Right Column: Account Information Form (Editable fields) -->
    <div class="col-12 col-md-7 col-lg-8 animate-fade-in">
        <div class="user-box p-4 rounded-4 glass-panel h-100 border border-secondary">
            <h5 class="fw-bold text-white mb-4"><i class="bi bi-shield-lock text-accent me-2"></i>Informasi & Keamanan Akun</h5>
            
            <form action="index.php?page=profile" method="POST">
                <div class="row g-3">
                    
                    <!-- Full Name (Read-Only) -->
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Nama Lengkap</label>
                            <input type="text" class="form-control auth-form-control bg-dark border-secondary text-white-50" value="<?php echo htmlspecialchars($user['FullName']); ?>" readonly style="cursor: not-allowed; opacity: 0.75;">
                            <small class="text-secondary" style="font-size: 10px;">Nama lengkap tidak dapat diubah.</small>
                        </div>
                    </div>

                    <!-- Email Address (Read-Only) -->
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium">Alamat Email</label>
                            <input type="email" class="form-control auth-form-control bg-dark border-secondary text-white-50" value="<?php echo htmlspecialchars($user['Email']); ?>" readonly style="cursor: not-allowed; opacity: 0.75;">
                            <small class="text-secondary" style="font-size: 10px;">Email utama tidak dapat diubah.</small>
                        </div>
                    </div>

                    <!-- Username (Editable) -->
                    <div class="col-12">
                        <div class="mb-3 border-top border-secondary border-opacity-25 pt-3">
                            <label class="form-label text-white small fw-bold">Nama Pengguna (Username)</label>
                            <input type="text" name="username" class="form-control auth-form-control bg-dark border-secondary text-white" value="<?php echo htmlspecialchars($user['Name']); ?>" placeholder="Masukkan username baru" required>
                            <small class="text-secondary" style="font-size: 10px;">Digunakan untuk masuk log (login) ke dalam sistem.</small>
                        </div>
                    </div>

                    <!-- Password Fields (Editable) -->
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label text-white small fw-medium">Kata Sandi Baru</label>
                            <input type="password" name="new_password" class="form-control auth-form-control bg-dark border-secondary text-white" placeholder="Kosongkan jika tidak ingin diubah">
                        </div>
                    </div>

                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <label class="form-label text-white small fw-medium">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="confirm_password" class="form-control auth-form-control bg-dark border-secondary text-white" placeholder="Ulangi kata sandi baru Anda">
                        </div>
                    </div>

                </div>

                <div class="mt-4 pt-2 border-top border-secondary border-opacity-25">
                    <button type="submit" name="update_profile" class="btn bg-accent fw-bold px-4 py-2 rounded-3 hover-scale">
                        Simpan Perubahan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
