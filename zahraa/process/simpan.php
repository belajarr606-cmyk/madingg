<?php
session_start();  // ← TAMBAHKAN INI DI AWAL!
include "../config/database.php";

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$isi = mysqli_real_escape_string($conn, $_POST['isi']);
$penulis = mysqli_real_escape_string($conn, $_POST['penulis']);

$namaFile = null;
if (!empty($_FILES['foto']['name'])) {
    $fileName = $_FILES['foto']['name'];
    $tmpName = $_FILES['foto']['tmp_name'];
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $namaFile = time() . "." . $ext;
    move_uploaded_file($tmpName, "../uploads/" . $namaFile);
}

// ⚠️ BARIS INI DIGANTI - pake mysqli_query
$query = "INSERT INTO mading (judul, isi, penulis, foto) VALUES ('$judul', '$isi', '$penulis', '$namaFile')";
$result = mysqli_query($conn, $query);

if ($result) {
    $_SESSION['success'] = "Mading berhasil ditambahkan! ✨";
} else {
    $_SESSION['error'] = "Gagal menambahkan mading: " . mysqli_error($conn);
}

header("Location: ../admin/dashboard.php");
exit;
?>