<?php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil user_id dari sesi
$user_id = $_SESSION['user_id'];

// Periksa apakah form telah dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $transaction_id = $_POST['transaction_id'];
    $new_status = $_POST['new_status'];

    // Periksa apakah status baru valid
    $valid_statuses = ['pending', 'dibayar', 'dikirim', 'selesai', 'dibatalkan'];
    if (!in_array($new_status, $valid_statuses)) {
        $_SESSION['error_msg'] = "Status tidak valid.";
        header("Location: ../pages/transactions.php");
        exit();
    }

    // Perbarui status transaksi
    $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ? AND id IN (SELECT td.transaction_id FROM transaction_details td JOIN product p ON td.product_id = p.id WHERE p.user_id = ?)");
    $stmt->bind_param("sii", $new_status, $transaction_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Status transaksi berhasil diperbarui!";
    } else {
        $_SESSION['error_msg'] = "Gagal memperbarui status transaksi: " . $stmt->error;
    }
    $stmt->close();

    // Arahkan kembali ke halaman transaksi
    header("Location: ../pages/transactions.php");
    exit();
}

// Jika tidak ada form yang dikirimkan, arahkan kembali ke halaman transaksi
header("Location: ../pages/transactions.php");
exit();
?>