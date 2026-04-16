<?php
session_start();
include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: register.php");
    exit;
}

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if (empty($username) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "Semua bidang wajib diisi.";
    header("Location: register.php");
    exit;
}

if (strlen($username) < 3) {
    $_SESSION['error'] = "Nama pengguna minimal 3 karakter.";
    header("Location: register.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Kata sandi minimal 6 karakter.";
    header("Location: register.php");
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Konfirmasi kata sandi tidak cocok.";
    header("Location: register.php");
    exit;
}

$check_query = "SELECT * FROM users WHERE username = '$username'";
$check_result = mysqli_query($conn, $check_query);
if (!$check_result) {
    $_SESSION['error'] = "Kesalahan database: " . mysqli_error($conn);
    header("Location: register.php");
    exit;
}

if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['error'] = "Nama pengguna sudah digunakan. Silakan pilih nama lain.";
    header("Location: register.php");
    exit;
}

$insert_query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')";
$insert_result = mysqli_query($conn, $insert_query);

if ($insert_result) {
    $_SESSION['success'] = "Pendaftaran berhasil. Silakan masuk.";
    header("Location: login.php");
    exit;
} else {
    $_SESSION['error'] = "Gagal mendaftar: " . mysqli_error($conn);
    header("Location: register.php");
    exit;
}
?>