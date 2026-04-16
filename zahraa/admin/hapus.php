<?php
session_start();  // ← HARUS ADA INI DI AWAL!
include "../config/database.php";

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi ID
if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Ambil data foto dulu sebelum dihapus
$query = "SELECT foto FROM mading WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    // Hapus file foto jika ada
    if (!empty($row['foto'])) {
        $filePath = "../uploads/" . $row['foto'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

// Hapus data dari database
$delete_query = "DELETE FROM mading WHERE id = $id";
$result = mysqli_query($conn, $delete_query);

if ($result) {
    $_SESSION['success'] = "Mading berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus mading: " . mysqli_error($conn);
}

header("Location: dashboard.php");
exit;
?>