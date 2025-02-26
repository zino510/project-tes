<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['transaction_id'])) {
    header("Location: dashboard.php");
    exit();
}

$transaction_id = intval($_GET['transaction_id']);
$user_id = $_SESSION['user_id'];

// Ambil detail transaksi
$query = $conn->prepare("
    SELECT t.*, td.quantity, p.nama_produk, p.harga 
    FROM transactions t 
    JOIN transaction_details td ON t.id = td.transaction_id 
    JOIN product p ON td.product_id = p.id 
    WHERE t.id = ? AND t.user_id = ?
");
$query->bind_param("ii", $transaction_id, $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$transaction = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update status pembayaran
    $query = $conn->prepare("UPDATE transactions SET status = 'dibayar' WHERE id = ? AND user_id = ?");
    $query->bind_param("ii", $transaction_id, $user_id);
    
    if ($query->execute()) {
        $_SESSION['success_message'] = "Pembayaran berhasil dikonfirmasi!";
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Terjadi kesalahan saat mengkonfirmasi pembayaran.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- [CSS styles from qris.php] -->
</head>
<body>

<div class="qris-container">
    <div class="qris-header">
        <h3>
            <i class="fas fa-check-circle me-2"></i>
            Konfirmasi Pembayaran
        </h3>
    </div>

    <div class="transaction-details">
        <div class="transaction-item">
            <span>Order ID:</span>
            <span><?php echo $order_id; ?></span>
        </div>
        <div class="transaction-item">
            <span>Produk:</span>
            <span><?php echo htmlspecialchars($order['nama_produk']); ?></span>
        </div>
        <div class="transaction-item">
            <span>Total Pembayaran:</span>
            <span>Rp <?php echo number_format($order['harga'], 0, ',', '.'); ?></span>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>

    <div class="instruction-box">
        <h4>Pastikan Anda telah melakukan pembayaran</h4>
        <p>Dengan mengklik tombol konfirmasi di bawah, Anda menyatakan bahwa:</p>
        <ul class="instruction-list">
            <li><i class="fas fa-check me-2"></i>Telah melakukan pembayaran sesuai nominal</li>
            <li><i class="fas fa-check me-2"></i>Pembayaran dilakukan untuk Order ID yang benar</li>
            <li><i class="fas fa-check me-2"></i>Pembayaran dilakukan sebelum batas waktu</li>
        </ul>
    </div>

    <form method="POST" action="">
        <button type="submit" class="btn-confirm">
            <i class="fas fa-check-circle me-2"></i>
            Konfirmasi Pembayaran
        </button>
    </form>

    <a href="qris.php?product_id=<?php echo $order['product_id']; ?>" class="btn-back">
        <i class="fas fa-arrow-left me-2"></i>
        Kembali
    </a>
</div>

</body>
</html>