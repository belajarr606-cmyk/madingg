<?php
session_start();  // ← TAMBAHKAN INI DI AWAL!
include "../config/database.php";

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Validasi ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$isi = mysqli_real_escape_string($conn, $_POST['isi']);
$penulis = mysqli_real_escape_string($conn, $_POST['penulis']);

// Cek apakah ada file baru yang diupload
if (!empty($_FILES['foto']['name'])) {
    $fileName = $_FILES['foto']['name'];
    $tmpName = $_FILES['foto']['tmp_name'];
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $namaFile = time() . "." . $ext;
    move_uploaded_file($tmpName, "../uploads/" . $namaFile);

    // Update dengan foto baru
    $query = "UPDATE mading SET judul='$judul', isi='$isi', penulis='$penulis', foto='$namaFile' WHERE id=$id";
} else {
    // Update tanpa ganti foto
    $query = "UPDATE mading SET judul='$judul', isi='$isi', penulis='$penulis' WHERE id=$id";
}

// ⚠️ BARIS INI DIGANTI - pake mysqli_query
$result = mysqli_query($conn, $query);

if ($result) {
    $_SESSION['success'] = "Mading berhasil diupdate! ✨";
} else {
    $_SESSION['error'] = "Gagal mengupdate mading: " . mysqli_error($conn);
}

header("Location: ../admin/dashboard.php");
exit;
?>