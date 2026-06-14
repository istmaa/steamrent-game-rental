<?php
session_start();
include 'koneksi.php';

$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Preserve entered data (except password fields)
$_SESSION['register_old'] = [
    'fullname' => $fullname,
    'username' => $username,
    'email' => $email
];
$_SESSION['register_errors'] = [];

// 1. Fullname validation
if (empty($fullname)) {
    $_SESSION['register_errors']['fullname'] = 'Nama lengkap wajib diisi!';
}

// 2. Username validation
if (empty($username)) {
    $_SESSION['register_errors']['username'] = 'Username wajib diisi!';
} elseif (strlen($username) < 4) {
    $_SESSION['register_errors']['username'] = 'Username minimal harus 4 karakter!';
} else {
    // Username unique check
    $check_username = mysqli_query($conn, "SELECT id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
    if (mysqli_num_rows($check_username) > 0) {
        $_SESSION['register_errors']['username'] = 'Username sudah terdaftar! Silakan gunakan username lain.';
    }
}

// 3. Email validation
if (empty($email)) {
    $_SESSION['register_errors']['email'] = 'Email wajib diisi!';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_errors']['email'] = 'Format email tidak valid!';
} else {
    // Email unique check
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['register_errors']['email'] = 'Email sudah terdaftar! Silakan gunakan email lain.';
    }
}

// 4. Password validation
if (empty($password)) {
    $_SESSION['register_errors']['password'] = 'Password wajib diisi!';
} elseif (strlen($password) < 8) {
    $_SESSION['register_errors']['password'] = 'Password minimal harus 8 karakter!';
} elseif (!preg_match('/[A-Za-z]/', $password)) {
    $_SESSION['register_errors']['password'] = 'Password harus mengandung huruf!';
} elseif (!preg_match('/[0-9]/', $password)) {
    $_SESSION['register_errors']['password'] = 'Password harus mengandung angka!';
}

// 5. Confirm password validation
if (empty($confirm)) {
    $_SESSION['register_errors']['confirm_password'] = 'Konfirmasi password wajib diisi!';
} elseif ($password !== $confirm) {
    $_SESSION['register_errors']['confirm_password'] = 'Konfirmasi password tidak sesuai!';
}

// If any errors exist, redirect back
if (!empty($_SESSION['register_errors'])) {
    header("Location: register.php");
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = mysqli_query($conn, "INSERT INTO users(fullname, username, email, password, balance) VALUES('" . mysqli_real_escape_string($conn, $fullname) . "', '" . mysqli_real_escape_string($conn, $username) . "', '" . mysqli_real_escape_string($conn, $email) . "', '$hashed_password', 0)");

if ($query) {
    // Clear old data on success
    unset($_SESSION['register_old']);
    unset($_SESSION['register_errors']);
    $_SESSION['register_success'] = 'Registrasi berhasil! Silakan login.';
    header("Location: login.php");
    exit;
} else {
    $_SESSION['register_errors']['general'] = 'Registrasi gagal! Terjadi kesalahan pada server.';
    header("Location: register.php");
    exit;
}
?>