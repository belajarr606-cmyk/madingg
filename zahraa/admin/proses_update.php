<?php
session_start();  // ← HARUS ADA INI DI AWAL!
include "../config/database.php";

// Cek login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../admin/dashboard.php");
    exit;
}

// Ambil data form
$id = (int)$_POST['id'];
$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$isi = mysqli_real_escape_string($conn, $_POST['isi']);
$penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
$delete_foto = isset($_POST['delete_foto']) ? true : false;

// Validasi input
if (empty($judul) || empty($isi) || empty($penulis)) {
    $_SESSION['error'] = "Semua field wajib diisi!";
    header("Location: ../admin/edit.php?id=$id");
    exit;
}

// Ambil data foto lama
$query = "SELECT foto FROM mading WHERE id = $id";
$result = mysqli_query($conn, $query);
$old_data = mysqli_fetch_assoc($result);
$old_foto = $old_data['foto'];

$foto = $old_foto; // Default pakai foto lama

// Proses upload foto baru
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $target_dir = "../uploads/";
    
    // Buat folder uploads jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['foto']['name']);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Validasi tipe file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error'] = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan!";
        header("Location: ../admin/edit.php?id=$id");
        exit;
    }
    
    // Validasi ukuran file (maks 2MB)
    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Ukuran file maksimal 2MB!";
        header("Location: ../admin/edit.php?id=$id");
        exit;
    }
    
    // Upload file baru
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        // Hapus foto lama jika ada
        if (!empty($old_foto) && file_exists($target_dir . $old_foto)) {
            unlink($target_dir . $old_foto);
        }
        $foto = $file_name;
    } else {
        $_SESSION['error'] = "Gagal upload foto!";
        header("Location: ../admin/edit.php?id=$id");
        exit;
    }
}

// Jika centang hapus foto
if ($delete_foto && !empty($old_foto)) {
    // Hapus file foto
    if (file_exists("../uploads/" . $old_foto)) {
        unlink("../uploads/" . $old_foto);
    }
    $foto = ''; // Set foto jadi kosong di database
}

// Update ke database (MySQL version)
$query = "UPDATE mading SET 
          judul = '$judul', 
          isi = '$isi', 
          penulis = '$penulis', 
          foto = '$foto' 
          WHERE id = $id";

$result = mysqli_query($conn, $query);

if ($result) {
    $_SESSION['success'] = "Mading berhasil diupdate! ✨";
    header("Location: ../admin/dashboard.php");
    exit;
} else {
    $_SESSION['error'] = "Gagal mengupdate mading: " . mysqli_error($conn);
    header("Location: ../admin/edit.php?id=$id");
    exit;
}
?>