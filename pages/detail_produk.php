<?php
session_start();
include '../config/database.php';

if (!isset($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit();
}

$id = intval($_GET['id']);
$query = $conn->prepare("SELECT * FROM product WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo "Produk tidak ditemukan.";
    exit();
}

$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['nama_produk']); ?> - Detail Produk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .product-image {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-beli {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <!-- Gambar Produk -->
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" class="product-image">
        </div>

        <!-- Detail Produk -->
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($row['nama_produk']); ?></h2>
            <h4 class="text-danger">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></p>

            <!-- Tombol Beli -->
            <a href="checkout.php?id=<?php echo $row['id']; ?>" class="btn btn-beli">Beli Sekarang</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
