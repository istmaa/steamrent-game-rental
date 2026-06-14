<?php
session_start();
include_once 'includes/config.php';

$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

$_SESSION['register_old'] = [
    'fullname' => $fullname,
    'username' => $username,
    'email' => $email
];
$_SESSION['register_errors'] = [];

if (empty($fullname)) {
    $_SESSION['register_errors']['fullname'] = 'Nama lengkap wajib diisi!';
}

if (empty($username)) {
    $_SESSION['register_errors']['username'] = 'Username wajib diisi!';
} elseif (strlen($username) < 4) {
    $_SESSION['register_errors']['username'] = 'Username minimal harus 4 karakter!';
} else {
    // Cek username unik (kolom Name)
    $username_escaped = mysqli_real_escape_string($conn, $username);
    $check_username = mysqli_query($conn, "SELECT UserID FROM users WHERE Name = '$username_escaped'");
    if (mysqli_num_rows($check_username) > 0) {
        $_SESSION['register_errors']['username'] = 'Username sudah terdaftar! Silakan gunakan username lain.';
    }
}

if (empty($email)) {
    $_SESSION['register_errors']['email'] = 'Email wajib diisi!';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_errors']['email'] = 'Format email tidak valid!';
} else {
    // Cek email unik
    $email_escaped = mysqli_real_escape_string($conn, $email);
    $check_email = mysqli_query($conn, "SELECT UserID FROM users WHERE Email = '$email_escaped'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['register_errors']['email'] = 'Email sudah terdaftar! Silakan gunakan email lain.';
    }
}

if (empty($password)) {
    $_SESSION['register_errors']['password'] = 'Password wajib diisi!';
} elseif (strlen($password) < 8) {
    $_SESSION['register_errors']['password'] = 'Password minimal harus 8 karakter!';
} elseif (!preg_match('/[A-Za-z]/', $password)) {
    $_SESSION['register_errors']['password'] = 'Password harus mengandung huruf!';
} elseif (!preg_match('/[0-9]/', $password)) {
    $_SESSION['register_errors']['password'] = 'Password harus mengandung angka!';
}

if (empty($confirm)) {
    $_SESSION['register_errors']['confirm_password'] = 'Konfirmasi password wajib diisi!';
} elseif ($password !== $confirm) {
    $_SESSION['register_errors']['confirm_password'] = 'Konfirmasi password tidak sesuai!';
}

if (!empty($_SESSION['register_errors'])) {
    header("Location: register.php");
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$fullname_escaped = mysqli_real_escape_string($conn, $fullname);

// Simpan data pendaftaran user baru (mengisi Name, Email, Password, Balance)
$query = mysqli_query($conn, "
    INSERT INTO users (Name, Email, Password, Balance) 
    VALUES ('$username_escaped', '$email_escaped', '$hashed_password', 0)
");

if ($query) {
    unset($_SESSION['register_old']);
    unset($_SESSION['register_errors']);
    $_SESSION['register_success'] = 'Registrasi berhasil! Silakan login menggunakan username Anda.';
    header("Location: login.php");
    exit;
} else {
    $_SESSION['register_errors']['general'] = 'Registrasi gagal! Terjadi kesalahan pada server: ' . mysqli_error($conn);
    header("Location: register.php");
    exit;
}
?>