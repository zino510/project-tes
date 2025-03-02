<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID produk diperlukan']);
    exit;
}

$product_id = intval($_POST['product_id']);
$user_id = $_SESSION['user_id'];

// Verifikasi kepemilikan produk
$stmt = $conn->prepare("SELECT user_id, gambar, stock FROM product WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
    exit;
}

$product = $result->fetch_assoc();
if ($product['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses ke produk ini']);
    exit;
}

// Handle upload gambar baru jika ada
$gambar_path = $product['gambar'];
if (isset($_FILES['gambar']) && $_FILES['gambar']['size'] > 0) {
    $upload_dir = '../uploads/';
    $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung']);
        exit;
    }
    
    // Validasi ukuran file (max 5MB)
    if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)']);
        exit;
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
        $gambar_path = 'uploads/' . $new_filename;
        // Hapus gambar lama
        if (file_exists('../' . $product['gambar'])) {
            unlink('../' . $product['gambar']);
        }
    }
}

// Hitung stok baru berdasarkan tipe adjustment
$final_stock = $product['stock']; // Nilai default adalah stok saat ini
if (isset($_POST['adjustment_type']) && isset($_POST['stock_adjustment'])) {
    $adjustment = intval($_POST['stock_adjustment']);
    switch ($_POST['adjustment_type']) {
        case 'add':
            $final_stock = $product['stock'] + $adjustment;
            break;
        case 'subtract':
            $final_stock = max(0, $product['stock'] - $adjustment);
            break;
        case 'set':
            $final_stock = max(0, $adjustment);
            break;
    }
}

try {
    $conn->begin_transaction();

    // Update produk termasuk stok
    $stmt = $conn->prepare("UPDATE product SET 
        nama_produk = ?,
        deskripsi = ?,
        harga = ?,
        kategori = ?,
        kondisi = ?,
        gambar = ?,
        stock = ?,
        updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND user_id = ?");

    $stmt->bind_param("ssdsssiis", 
        $_POST['nama_produk'],
        $_POST['deskripsi'],
        $_POST['harga'],
        $_POST['kategori'],
        $_POST['kondisi'],
        $gambar_path,
        $final_stock,
        $product_id,
        $user_id
    );

    $stmt->execute();

    // Catat perubahan stok jika ada
    if (isset($_POST['adjustment_type']) && isset($_POST['stock_adjustment']) && $_POST['stock_adjustment'] > 0) {
        $stock_stmt = $conn->prepare("INSERT INTO stock_history (product_id, adjustment_type, adjustment_amount, final_stock, user_id) VALUES (?, ?, ?, ?, ?)");
        $stock_stmt->bind_param("isiii", 
            $product_id,
            $_POST['adjustment_type'],
            $_POST['stock_adjustment'],
            $final_stock,
            $user_id
        );
        $stock_stmt->execute();
    }

    // Log aktivitas update
    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, 'update_product', ?)");
    $description = "Updated product ID: " . $product_id . " (Stock: " . $final_stock . ")";
    $log_stmt->bind_param("is", $user_id, $description);
    $log_stmt->execute();

    $conn->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Produk berhasil diupdate',
        'new_stock' => $final_stock
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate produk: ' . $e->getMessage()]);
}

$conn->close();