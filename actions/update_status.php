<?php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data dari form
$transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validasi status yang diterima
$valid_status = ['pending', 'dibayar', 'dikirim', 'selesai', 'dibatalkan'];

if ($transaction_id && $status && in_array($status, $valid_status)) {
    // Update status transaksi
    $stmt = $conn->prepare('UPDATE transactions SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $transaction_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Status transaksi berhasil diperbarui.";
        header('Location: ../pages/transactions.php?status=updated');
        exit();
    } else {
        $_SESSION['message'] = "Gagal memperbarui status transaksi: " . $stmt->error;
        header('Location: ../pages/transactions.php?status=error');
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "Input tidak valid.";
    header('Location: ../pages/transactions.php?status=invalid');
    exit();
}

$conn->close();
?>
