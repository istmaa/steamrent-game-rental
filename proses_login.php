<?php
session_start();
include 'koneksi.php';

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$_SESSION['login_old_username'] = $username;
$_SESSION['login_errors'] = [];

// Validation: username/email required
if (empty($username)) {
    $_SESSION['login_errors']['username'] = 'Nama Pengguna atau Email wajib diisi!';
}

// Validation: password required
if (empty($password)) {
    $_SESSION['login_errors']['password'] = 'Kata Sandi wajib diisi!';
}

if (!empty($_SESSION['login_errors'])) {
    header("Location: login.php");
    exit;
}

$username_escaped = mysqli_real_escape_string($conn, $username);
$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username_escaped' OR email='$username_escaped'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    if (password_verify($password, $data['password'])) {
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        
        // Clean session memory on success
        unset($_SESSION['login_old_username']);
        unset($_SESSION['login_errors']);

        header("Location: index.php");
        exit;
    } else {
        $_SESSION['login_errors']['password'] = 'Password salah!';
        header("Location: login.php");
        exit;
    }
} else {
    $_SESSION['login_errors']['username'] = 'Akun tidak ditemukan!';
    header("Location: login.php");
    exit;
}
?>