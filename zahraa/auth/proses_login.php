<?php
session_start();
include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: login.php");
    exit;
}

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];
$remember = isset($_POST['remember']) ? true : false;

if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Nama pengguna dan kata sandi wajib diisi.";
    header("Location: login.php");
    exit;
}

$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if (!$result) {
    $_SESSION['error'] = "Terjadi kesalahan database: " . mysqli_error($conn);
    header("Location: login.php");
    exit;
}

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    if ($password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();

        if ($remember) {
            setcookie('username', $username, time() + (86400 * 30), "/");
        } else {
            setcookie('username', '', time() - 3600, "/");
        }

        $_SESSION['success'] = "Login berhasil. Selamat datang, " . $user['username'] . ".";

        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/dashboard.php");
        }
        exit;
    } else {
        $_SESSION['error'] = "Kata sandi salah.";
        header("Location: login.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Nama pengguna tidak ditemukan.";
    header("Location: login.php");
    exit;
}
?>