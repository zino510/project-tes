<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['transaction_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    exit();
}

$transaction_id = intval($_POST['transaction_id']);
$status = $_POST['status'];
$user_id = $_SESSION['user_id'];

// Pastikan hanya user yang membuat transaksi yang bisa mengupdate
$query = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ? AND user_id = ?");
$query->bind_param("sii", $status, $transaction_id, $user_id);
$query->execute();

http_response_code(200);