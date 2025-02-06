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
    <meta charset="UTF-8">
    <title>Transaksi - Duo Mart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .container { margin-top: 20px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Transaksi</h2>
        <a href="../pages/dashboard.php" class="btn btn-secondary mb-3">Kembali ke Dashboard</a>
        <!-- Pesanan -->
        <h3>Pesanan</h3>
        <?php if ($pesanan_result->num_rows > 0): ?>
            <?php while ($row = $pesanan_result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                    <p class="card-text">Deskripsi: <?php echo htmlspecialchars($row['deskripsi']); ?></p>
                    <p class="card-text">Harga: Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></p>
                    <p class="card-text">Quantity: <?php echo $row['quantity']; ?></p>
                    <p class="card-text">Total Harga: Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></p>
                    <p class="card-text">Pembeli: <?php echo htmlspecialchars($row['buyer_name']); ?></p>
                    <p class="card-text">Status: <?php echo htmlspecialchars($row['status']); ?></p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pesananDetailModal<?php echo $row['transaction_id']; ?>">Detail Transaksi</button>

                    <!-- Modal untuk Detail Transaksi -->
                    <div class="modal fade" id="pesananDetailModal<?php echo $row['transaction_id']; ?>" tabindex="-1" aria-labelledby="pesananDetailModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="pesananDetailModalLabel">Detail Transaksi</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Nama Pembeli: <?php echo htmlspecialchars($row['buyer_name']); ?></p>
                                    <p>Email Pembeli: <?php echo htmlspecialchars($row['buyer_email']); ?></p>
                                    <p>Alamat: (Tambahkan kolom alamat pada tabel user atau transactions jika diperlukan)</p>
                                    <p>Sistem Pembayaran: (Tambahkan informasi sistem pembayaran jika diperlukan)</p>
                                </div>
                                <div class="modal-footer">
                                    <form action="../actions/update_status.php" method="POST" class="d-inline">
                                        <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                                        <select name="status" class="form-select" required>
                                            <option value="pending" <?php if ($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                            <option value="dibayar" <?php if ($row['status'] == 'dibayar') echo 'selected'; ?>>Dibayar</option>
                                            <option value="dikirim" <?php if ($row['status'] == 'dikirim') echo 'selected'; ?>>Dikirim</option>
                                            <option value="selesai" <?php if ($row['status'] == 'selesai') echo 'selected'; ?>>Selesai</option>
                                            <option value="dibatalkan" <?php if ($row['status'] == 'dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
                                        </select>
                                        <button type="submit" class="btn btn-success mt-2">Update Status</button>
                                    </form>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada pesanan.</p>
        <?php endif; ?>

        <!-- Pembelian -->
        <h3>Pembelian</h3>
        <?php if ($pembelian_result->num_rows > 0): ?>
            <?php while ($row = $pembelian_result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                    <p class="card-text">Deskripsi: <?php echo htmlspecialchars($row['deskripsi']); ?></p>
                    <p class="card-text">Harga: Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></p>
                    <p class="card-text">Quantity: <?php echo $row['quantity']; ?></p>
                    <p class="card-text">Total Harga: Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></p>
                    <p class="card-text">Status: <?php echo htmlspecialchars($row['status']); ?></p>
                    <p class="card-text">Penjual: <?php echo htmlspecialchars($row['seller_name']); ?></p>
                    <p class="card-text">Nomor Telepon: <?php echo htmlspecialchars($row['seller_phone']); ?></p>
                    <p class="card-text">Email: <?php echo htmlspecialchars($row['seller_email']); ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada pembelian.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>