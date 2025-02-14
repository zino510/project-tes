<?php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update status transaksi jika ada permintaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $transaction_id = $_POST['transaction_id'];
    $new_status = $_POST['new_status'];

    $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ? AND id IN (SELECT td.transaction_id FROM transaction_details td JOIN product p ON td.product_id = p.id WHERE p.user_id = ?)");
    $stmt->bind_param("sii", $new_status, $transaction_id, $user_id);
    if ($stmt->execute()) {
        header("Location: transaksi.php");
        exit();
    } else {
        echo "Error updating status: " . $stmt->error;
    }
    $stmt->close();
}

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
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center justify-center">
                <i class="fas fa-shopping-cart mr-2"></i>Transaksi
            </h2>
            <p class="text-gray-600">Kelola semua transaksi Anda di satu tempat</p>
        </div>

        <a href="../pages/dashboard.php" class="inline-flex items-center mb-6 text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali ke Dashboard
        </a>

        <!-- Pesanan Section -->
        <div class="mb-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-store text-xl text-gray-700 mr-2"></i>
                <h3 class="text-2xl font-semibold text-gray-800">Pesanan</h3>
            </div>

            <?php if ($pesanan_result->num_rows > 0): ?>
                <?php while ($row = $pesanan_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md mb-4 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h5 class="text-xl font-semibold flex items-center">
                            <i class="fas fa-box mr-2 text-gray-600"></i>
                            <?php echo htmlspecialchars($row['nama_produk']); ?>
                        </h5>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php
                            $status_colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'dibayar' => 'bg-blue-100 text-blue-800',
                                'dikirim' => 'bg-purple-100 text-purple-800',
                                'selesai' => 'bg-green-100 text-green-800',
                                'dibatalkan' => 'bg-red-100 text-red-800'
                            ];
                            echo $status_colors[strtolower($row['status'])] ?? 'bg-gray-100 text-gray-800';
                        ?>">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-align-left w-6"></i>
                                <span><?php echo htmlspecialchars($row['deskripsi']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-tag w-6"></i>
                                <span>Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-shopping-basket w-6"></i>
                                <span>Quantity: <?php echo $row['quantity']; ?></span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-money-bill-wave w-6"></i>
                                <span>Total: Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-user w-6"></i>
                                <span>Pembeli: <?php echo htmlspecialchars($row['buyer_name']); ?></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200" data-bs-toggle="modal" data-bs-target="#pesananDetailModal<?php echo $row['transaction_id']; ?>">
                        <i class="fas fa-info-circle mr-2"></i>Detail Transaksi
                    </button>

                    <!-- Modal structure -->
                    <div class="modal fade" id="pesananDetailModal<?php echo $row['transaction_id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-lg shadow-xl">
                                <div class="modal-header border-b border-gray-200 p-4">
                                    <h5 class="text-xl font-semibold">Detail Transaksi</h5>
                                    <button type="button" class="text-gray-400 hover:text-gray-500" data-bs-dismiss="modal" aria-label="Close">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="modal-body p-4 space-y-3">
                                    <!-- Modal content -->
                                    <div class="space-y-3">
                                        <div class="flex items-center text-gray-700">
                                            <i class="fas fa-align-left w-6"></i>
                                            <span>Deskripsi: <?php echo htmlspecialchars($row['deskripsi']); ?></span>
                                        </div>
                                        <!-- ... other modal content ... -->
                                        <form method="post" action="../pages/update_status.php" class="mt-4">
                                            <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                                            <select name="new_status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                                <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="dibayar" <?php echo $row['status'] === 'dibayar' ? 'selected' : ''; ?>>Dibayar</option>
                                                <option value="dikirim" <?php echo $row['status'] === 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                                <option value="selesai" <?php echo $row['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                <option value="dibatalkan" <?php echo $row['status'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                            </select>
                                            <button type="submit" name="update_status" class="mt-3 w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                                Update Status
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-blue-100 text-blue-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-info-circle mr-2"></i>Tidak ada pesanan.
                </div>
            <?php endif; ?>
        </div>

        <!-- Pembelian Section -->
        <div class="mt-12">
            <div class="flex items-center mb-4">
                <i class="fas fa-shopping-bag text-xl text-gray-700 mr-2"></i>
                <h3 class="text-2xl font-semibold text-gray-800">Pembelian</h3>
            </div>

            <?php if ($pembelian_result->num_rows > 0): ?>
                <?php while ($row = $pembelian_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-md mb-4 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h5 class="text-xl font-semibold flex items-center">
                            <i class="fas fa-box mr-2 text-gray-600"></i>
                            <?php echo htmlspecialchars($row['nama_produk']); ?>
                        </h5>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $status_colors[strtolower($row['status'])] ?? 'bg-gray-100 text-gray-800'; ?>">
                            <i class="fas fa-circle text-xs mr-1"></i>
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-align-left w-6"></i>
                                <span>Deskripsi: <?php echo htmlspecialchars($row['deskripsi']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-tag w-6"></i>
                                <span>Harga: Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-shopping-basket w-6"></i>
                                <span>Quantity: <?php echo $row['quantity']; ?></span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-store w-6"></i>
                                <span>Penjual: <?php echo htmlspecialchars($row['seller_name']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-phone w-6"></i>
                                <span>Telepon: <?php echo htmlspecialchars($row['seller_phone']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-envelope w-6"></i>
                                <span>Email: <?php echo htmlspecialchars($row['seller_email']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-blue-100 text-blue-700 px-4 py-3 rounded-lg">
                    <i class="fas fa-info-circle mr-2"></i>Tidak ada pembelian.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>