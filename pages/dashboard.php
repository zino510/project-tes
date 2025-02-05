<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM product");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Duo Mart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background: white;
            padding: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: #007bff;
        }
        .navbar-nav a {
            color: black;
            margin-right: 15px;
        }
        .btn-jual {
            background-color: #dc3545;
            color: white;
            border-radius: 20px;
            padding: 8px 16px;
        }
        .btn-profil, .btn-logout {
            border: 1px solid #007bff;
            color: #007bff;
            border-radius: 20px;
            padding: 8px 16px;
            text-decoration: none;
        }
        .btn-logout {
            border: 1px solid red;
            color: red;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            background: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        .product-price {
            color: red;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-detail {
            border: 1px solid #007bff;
            color: #007bff;
            border-radius: 20px;
            padding: 8px 16px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">Duo Mart</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#">Elektronik</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Fashion</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Perawatan Pribadi</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Anak-anak</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Keseharian</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Semua Kategori</a></li>
            </ul>
            <a href="post_barang.php" class="btn btn-jual ms-3">Jual</a>
            <a href="profile.php" class="btn btn-profil ms-2">Profil</a>
            <a href="../actions/logout.php?logout=true" class="btn btn-logout ms-2">Logout</a>
        </div>
    </div>
</nav>

<!-- Daftar Produk -->
<div class="container mt-4">
    <h3 class="fw-bold">Fresh Produk</h3>
    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <div class="product-card">
                    <img src="<?php echo $row['gambar']; ?>" alt="Produk">
                    <div class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                    <div class="product-price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                    <p><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                    <a href="detail_produk.php?id=<?php echo $row['id']; ?>" class="btn btn-detail">Lihat Detail</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
