<?php
// update_profile.php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil user_id dari sesi
$user_id = $_SESSION['user_id'];

// Ambil data dari form
$nama     = isset($_POST['nama']) ? $_POST['nama'] : '';
$username = isset($_POST['username']) ? $_POST['username'] : '';
$telepon  = isset($_POST['telepon']) ? $_POST['telepon'] : '';
$bio      = isset($_POST['bio']) ? $_POST['bio'] : '';

// Siapkan variabel untuk menyimpan nama file foto baru (jika ada)
$fotoName = null;

// Pastikan folder uploads sudah dibuat, misalnya /uploads di root project
$uploadDir = '../uploads/';

// Periksa apakah ada file foto yang diupload
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Dapatkan informasi file
    $tempName    = $_FILES['foto']['tmp_name'];
    $originalName = $_FILES['foto']['name'];
    
    // Buat nama yang unik untuk file (opsional)
    $fotoName = time() . '_' . $originalName;
    $destination = $uploadDir . $fotoName;
    
    // Pindahkan file yang diupload ke folder tujuan
    if (!move_uploaded_file($tempName, $destination)) {
        // Jika gagal upload
        die("Gagal mengupload foto profil.");
    }
}

// Buat query base untuk update profile
// Periksa apakah foto baru diupload
if ($fotoName !== null) {
    // Update dengan foto
    $sql = "UPDATE user 
            SET nama = ?, username = ?, telepon = ?, foto = ?, bio = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nama, $username, $telepon, $fotoName, $bio, $user_id);
} else {
    // Update tanpa mengganti foto
    $sql = "UPDATE user 
            SET nama = ?, username = ?, telepon = ?, bio = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nama, $username, $telepon, $bio, $user_id);
}

// Jalankan query
if ($stmt->execute()) {
    // Berhasil update
    header("Location: ../pages/profile.php?status=updated");
    exit();
} else {
    die("Terjadi kesalahan saat mengupdate profil: " . $conn->error);
}