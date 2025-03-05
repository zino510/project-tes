<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Check if product ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID produk diperlukan']);
    exit;
}

$product_id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];

try {
    // First, check if the product exists and belongs to the user
    $stmt = $conn->prepare("SELECT user_id, gambar FROM product WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        exit;
    }

    if ($product['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus produk ini']);
        exit;
    }

    // Begin transaction
    $conn->begin_transaction();

    // 1. Delete from stock_history first (Foreign Key)
    $stmt = $conn->prepare("DELETE FROM stock_history WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    // 2. Delete from ratings (if exists)
    $stmt = $conn->prepare("DELETE FROM ratings WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    // 3. Delete product image if exists
    if ($product['gambar'] && file_exists("../{$product['gambar']}")) {
        if (!unlink("../{$product['gambar']}")) {
            throw new Exception('Gagal menghapus file gambar produk');
        }
    }

    // 4. Finally delete the product
    $stmt = $conn->prepare("DELETE FROM product WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Produk berhasil dihapus',
            'product_id' => $product_id
        ]);
    } else {
        throw new Exception('Gagal menghapus produk dari database');
    }

} catch (Exception $e) {
    if ($conn->connect_error) {
        $error_message = "Koneksi database gagal: " . $conn->connect_error;
    } else {
        $error_message = $e->getMessage();
    }
    
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menghapus produk',
        'error_details' => $error_message
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>