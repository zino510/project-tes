<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login']);
    exit;
}

// Cek parameter product_id
if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID produk diperlukan']);
    exit;
}

$product_id = intval($_POST['product_id']);
$user_id = $_SESSION['user_id'];

// Verifikasi kepemilikan produk dan ambil path gambar
$stmt = $conn->prepare("SELECT gambar FROM product WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan atau Anda tidak memiliki akses']);
    exit;
}

$product = $result->fetch_assoc();

// Hapus gambar dari server
if (file_exists('../' . $product['gambar'])) {
    unlink('../' . $product['gambar']);
}

// Hapus produk dari database
$stmt = $conn->prepare("DELETE FROM product WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);

if ($stmt->execute()) {
    // Log aktivitas penghapusan
    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'delete_product', ?)");
    $description = "Deleted product ID: " . $product_id;
    $log_stmt->bind_param("is", $user_id, $description);
    $log_stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Produk berhasil dihapus'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menghapus produk'
    ]);
}