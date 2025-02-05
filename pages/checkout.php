<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_GET['id']);

$query = $conn->prepare("SELECT * FROM product WHERE id = ?");
$query->bind_param("i", $product_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo "Produk tidak ditemukan.";
    exit();
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($product['nama_produk']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .checkout-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-bayar {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">Checkout</h2>
    <div class="row">
        <!-- Detail Produk -->
        <div class="col-md-6">
            <div class="checkout-box">
                <h4>Detail Produk</h4>
                <img src="<?php echo htmlspecialchars($product['gambar']); ?>" alt="Produk" class="img-fluid rounded mb-3">
                <h5><?php echo htmlspecialchars($product['nama_produk']); ?></h5>
                <h4 class="text-danger">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($product['deskripsi'])); ?></p>
            </div>
        </div>

        <!-- Form Checkout -->
        <div class="col-md-6">
            <div class="checkout-box">
                <h4>Informasi Pengiriman</h4>
                <form action="process_checkout.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <label>Nama Penerima:</label>
                    <input type="text" name="nama_penerima" class="form-control mb-3" required>
                    
                    <label>Alamat Lengkap:</label>
                    <textarea name="alamat" class="form-control mb-3" required></textarea>

                    <label>Metode Pembayaran:</label>
                    <select name="metode_pembayaran" class="form-control mb-3" required>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="COD">Cash on Delivery (COD)</option>
                        <option value="E-Wallet">E-Wallet</option>
                    </select>

                    <button type="submit" class="btn btn-bayar w-100">Bayar Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
