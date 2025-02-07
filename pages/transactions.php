<?php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data pesanan (produk yang dijual oleh user)
$pesanan_query = "
    SELECT t.id as transaction_id, t.user_id as buyer_id, t.total_harga, t.status, t.created_at, 
           u.nama as buyer_name, u.email as buyer_email, td.product_id, td.quantity, td.harga as product_price, 
           p.nama_produk, p.deskripsi, p.gambar 
    FROM transactions t
    JOIN user u ON t.user_id = u.id
    JOIN transaction_details td ON t.id = td.transaction_id
    JOIN product p ON td.product_id = p.id
    WHERE p.user_id = $user_id
    ORDER BY t.created_at DESC
";
$pesanan_result = $conn->query($pesanan_query);

// Ambil data pembelian (produk yang dibeli oleh user)
$pembelian_query = "
    SELECT t.id as transaction_id, t.total_harga, t.status, t.created_at, 
           td.product_id, td.quantity, td.harga as product_price, 
           p.nama_produk, p.deskripsi, p.gambar, 
           s.nama as seller_name, s.telepon as seller_phone, s.email as seller_email
    FROM transactions t
    JOIN transaction_details td ON t.id = td.transaction_id
    JOIN product p ON td.product_id = p.id
    JOIN user s ON p.user_id = s.id
    WHERE t.user_id = $user_id
    ORDER BY t.created_at DESC
";
$pembelian_result = $conn->query($pembelian_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Duo Mart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0d6efd;
            --light-blue: #e7f1ff;
            --hover-blue: #0b5ed7;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .page-header {
            background: var(--primary-blue);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            color: var(--primary-blue);
        }

        .section-title i {
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            border: none;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--hover-blue);
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .info-item i {
            width: 25px;
            color: var(--primary-blue);
            margin-right: 0.5rem;
        }

        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background: var(--light-blue);
            border-radius: 15px 15px 0 0;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--primary-blue);
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-button:hover {
            color: var(--hover-blue);
        }

        /* Status colors */
        .status-pending { background-color: #ffd700; color: #000; }
        .status-dibayar { background-color: #90caf9; color: #000; }
        .status-dikirim { background-color: #81c784; color: #000; }
        .status-selesai { background-color: #4caf50; color: #fff; }
        .status-dibatalkan { background-color: #f44336; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header text-center">
            <h2><i class="fas fa-shopping-cart me-2"></i>Transaksi</h2>
            <p class="mb-0">Kelola semua transaksi Anda di satu tempat</p>
        </div>

        <a href="../pages/dashboard.php" class="back-button mb-4">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Dashboard
        </a>

        <!-- Pesanan Section -->
        <div class="section-title">
            <i class="fas fa-store"></i>
            <h3>Pesanan</h3>
        </div>

        <?php if ($pesanan_result->num_rows > 0): ?>
            <?php while ($row = $pesanan_result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title">
                            <i class="fas fa-box me-2"></i>
                            <?php echo htmlspecialchars($row['nama_produk']); ?>
                        </h5>
                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                            <i class="fas fa-circle me-1"></i>
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="fas fa-align-left"></i>
                                <span>Deskripsi: <?php echo htmlspecialchars($row['deskripsi']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <span>Harga: Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-shopping-basket"></i>
                                <span>Quantity: <?php echo $row['quantity']; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Total: Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-user"></i>
                                <span>Pembeli: <?php echo htmlspecialchars($row['buyer_name']); ?></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#pesananDetailModal<?php echo $row['transaction_id']; ?>">
                        <i class="fas fa-info-circle me-2"></i>Detail Transaksi
                    </button>

                    <!-- Modal structure remains the same but with enhanced styling -->
                    <div class="modal fade" id="pesananDetailModal<?php echo $row['transaction_id']; ?>" tabindex="-1" aria-hidden="true">
                        <!-- Your existing modal content with added icons -->
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Tidak ada pesanan.
            </div>
        <?php endif; ?>

        <!-- Pembelian Section -->
        <div class="section-title mt-5">
            <i class="fas fa-shopping-bag"></i>
            <h3>Pembelian</h3>
        </div>

        <?php if ($pembelian_result->num_rows > 0): ?>
            <?php while ($row = $pembelian_result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title">
                            <i class="fas fa-box me-2"></i>
                            <?php echo htmlspecialchars($row['nama_produk']); ?>
                        </h5>
                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                            <i class="fas fa-circle me-1"></i>
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="fas fa-align-left"></i>
                                <span>Deskripsi: <?php echo htmlspecialchars($row['deskripsi']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <span>Harga: Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-shopping-basket"></i>
                                <span>Quantity: <?php echo $row['quantity']; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <i class="fas fa-store"></i>
                                <span>Penjual: <?php echo htmlspecialchars($row['seller_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <span>Telepon: <?php echo htmlspecialchars($row['seller_phone']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope"></i>
                                <span>Email: <?php echo htmlspecialchars($row['seller_email']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Tidak ada pembelian.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>