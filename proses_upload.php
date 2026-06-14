<?php
session_start();
include_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu!'];
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['submit_avatar'])) {
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] == UPLOAD_ERR_NO_FILE) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan pilih file gambar terlebih dahulu!'];
        header("Location: index.php?page=profile");
        exit;
    }

    $file = $_FILES['profile_image'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($file_ext, $allowed)) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Format file tidak didukung! Hanya diperbolehkan format JPG, JPEG, PNG, dan WEBP.'];
        header("Location: index.php?page=profile");
        exit;
    }

    if ($file_error !== 0) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Terjadi kesalahan saat mengunggah file!'];
        header("Location: index.php?page=profile");
        exit;
    }

    if ($file_size > 2097152) { // 2 MB
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Ukuran file terlalu besar! Maksimal ukuran file adalah 2 MB.'];
        header("Location: index.php?page=profile");
        exit;
    }

    if (!is_dir('uploads/profile')) {
        mkdir('uploads/profile', 0777, true);
    }

    $new_file_name = "avatar_" . $user_id . "_" . time() . "." . $file_ext;
    $upload_destination = 'uploads/profile/' . $new_file_name;

    // Ambil nama foto profil lama untuk dihapus dari disk
    $old_avatar_query = mysqli_query($conn, "SELECT Profile_Image FROM users WHERE UserID = '$user_id'");
    $old_avatar_data = mysqli_fetch_assoc($old_avatar_query);
    $old_avatar = $old_avatar_data ? $old_avatar_data['Profile_Image'] : '';

    if (move_uploaded_file($file_tmp, $upload_destination)) {
        // Update di database
        $update_query = mysqli_query($conn, "UPDATE users SET Profile_Image = '$new_file_name' WHERE UserID = '$user_id'");
        
        if ($update_query) {
            // Hapus file lama jika ada
            if (!empty($old_avatar) && file_exists('uploads/profile/' . $old_avatar)) {
                unlink('uploads/profile/' . $old_avatar);
            }
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Foto profil berhasil diperbarui!'];
            header("Location: index.php?page=profile");
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menyimpan data foto profil di database!'];
            header("Location: index.php?page=profile");
        }
    } else {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal memindahkan file ke direktori tujuan!'];
        header("Location: index.php?page=profile");
    }
} else {
    header("Location: index.php?page=profile");
}
exit;
?>
