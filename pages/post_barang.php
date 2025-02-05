<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $user_id = $_SESSION['user_id']; // Ambil user_id dari sesi

    // Pastikan folder uploads ada
    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $gambar_path = $upload_dir . basename($_FILES['gambar']['name']);
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $gambar_path)) {
        // Simpan hanya path relatif ke dalam database
        $gambar = "uploads/" . basename($_FILES['gambar']['name']);

        // Perbaiki query agar menyertakan user_id
        $stmt = $conn->prepare("INSERT INTO product (nama_produk, deskripsi, harga, kategori, gambar, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdssi", $nama_produk, $deskripsi, $harga, $kategori, $gambar, $user_id);

        if ($stmt->execute()) {
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Gagal mengunggah gambar.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jual Barang</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <h2>Jual Barang</h2>
    <form action="post_barang.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="nama_produk" placeholder="Nama Barang" required>
        <textarea name="deskripsi" placeholder="Deskripsi" required></textarea>
        <input type="number" name="harga" placeholder="Harga" required>
        <input type="file" name="gambar" required>
        <button type="submit">Jual</button>
    </form>
</body>
</html>
