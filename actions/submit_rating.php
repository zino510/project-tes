<?php
// Matikan error reporting untuk output
error_reporting(0);
ini_set('display_errors', 0);

// Tapi tetap log error untuk debugging
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-error.log');

session_start();

// Pastikan semua output sebelum JSON dibersihkan
ob_clean();

header('Content-Type: application/json');

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    require_once '../config/database.php';

    // Debug log
    error_log("Rating submission started");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Session data: " . print_r($_SESSION, true));

    // Validasi session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Silakan login terlebih dahulu');
    }

    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }

    // Validasi input
    if (!isset($_POST['product_id']) || !isset($_POST['rating'])) {
        throw new Exception('Data rating tidak lengkap');
    }

    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
    $review = isset($_POST['review']) ? trim($_POST['review']) : '';
    $user_id = $_SESSION['user_id'];

    // Validasi nilai
    if ($product_id === false || $rating === false || $rating < 1 || $rating > 5) {
        throw new Exception('Data rating tidak valid');
    }

    // Begin transaction
    $conn->begin_transaction();

    // Check if product exists and user is not the seller
    $check_product = $conn->prepare("
        SELECT id, user_id 
        FROM product 
        WHERE id = ?
    ");
    $check_product->bind_param("i", $product_id);
    $check_product->execute();
    $product_result = $check_product->get_result();
    
    if ($product_result->num_rows === 0) {
        throw new Exception('Produk tidak ditemukan');
    }

    $product = $product_result->fetch_assoc();
    if ($product['user_id'] == $user_id) {
        throw new Exception('Anda tidak dapat memberikan rating pada produk sendiri');
    }

    // Check existing rating
    $check_rating = $conn->prepare("
        SELECT id 
        FROM ratings 
        WHERE user_id = ? AND product_id = ?
    ");
    $check_rating->bind_param("ii", $user_id, $product_id);
    $check_rating->execute();
    $rating_exists = $check_rating->get_result()->num_rows > 0;

    if ($rating_exists) {
        // Update existing rating
        $stmt = $conn->prepare("
            UPDATE ratings 
            SET rating = ?, 
                review = ?, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->bind_param("isii", $rating, $review, $user_id, $product_id);
    } else {
        // Insert new rating
        $stmt = $conn->prepare("
            INSERT INTO ratings 
            (product_id, user_id, rating, review, created_at) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->bind_param("iiis", $product_id, $user_id, $rating, $review);
    }

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan rating');
    }

    // Get updated rating statistics
    $stats = $conn->prepare("
        SELECT AVG(rating) as avg_rating, 
               COUNT(*) as total_ratings 
        FROM ratings 
        WHERE product_id = ?
    ");
    $stats->bind_param("i", $product_id);
    $stats->execute();
    $rating_stats = $stats->get_result()->fetch_assoc();

    // Commit transaction
    $conn->commit();

    // Return success response with updated stats
    echo json_encode([
        'success' => true,
        'message' => 'Rating berhasil disimpan',
        'avgRating' => round($rating_stats['avg_rating'], 1),
        'totalRatings' => $rating_stats['total_ratings']
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }

    error_log("Error in submit_rating.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} finally {
    // Close all statements
    if (isset($check_product)) $check_product->close();
    if (isset($check_rating)) $check_rating->close();
    if (isset($stmt)) $stmt->close();
    if (isset($stats)) $stats->close();
    
    // Close connection
    if (isset($conn)) $conn->close();
}
?>