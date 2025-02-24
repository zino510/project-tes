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

// Cek apakah request adalah XMLHttpRequest
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    include '../config/database.php';

    // Debug log
    error_log("Rating submission started");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Session data: " . print_r($_SESSION, true));

    // Validasi session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Silakan login terlebih dahulu');
    }

    // Validasi input
    if (!isset($_POST['product_id']) || !isset($_POST['rating'])) {
        throw new Exception('Data rating tidak lengkap');
    }

    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
    $review = isset($_POST['review']) ? trim($_POST['review']) : '';
    $user_id = $_SESSION['user_id'];
    $user = $_SESSION['user_login'];

    // Validasi nilai
    if ($product_id === false || $rating === false || $rating < 1 || $rating > 5) {
        throw new Exception('Data rating tidak valid');
    }

    // Escape string untuk review
    $review = mysqli_real_escape_string($conn, $review);

    // Begin transaction
    mysqli_begin_transaction($conn);

    // Check if product exists
    $check_product = mysqli_prepare($conn, "SELECT id FROM product WHERE id = ?");
    if (!$check_product) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($check_product, "i", $product_id);
    mysqli_stmt_execute($check_product);
    $result = mysqli_stmt_get_result($check_product);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception('Produk tidak ditemukan');
    }

    // Check existing rating
    $check_rating = mysqli_prepare($conn, "SELECT id FROM ratings WHERE user_id = ? AND product_id = ?");
    if (!$check_rating) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($check_rating, "ii", $user_id, $product_id);
    mysqli_stmt_execute($check_rating);
    $rating_exists = mysqli_num_rows(mysqli_stmt_get_result($check_rating)) > 0;

    if ($rating_exists) {
        // Update existing rating
        $update_stmt = mysqli_prepare($conn, 
            "UPDATE ratings 
             SET rating = ?, 
                 review = ?, 
                 updated_at = CURRENT_TIMESTAMP 
             WHERE user_id = ? AND product_id = ?"
        );
        if (!$update_stmt) {
            throw new Exception('Database error: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($update_stmt, "isii", $rating, $review, $user_id, $product_id);
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception('Gagal mengupdate rating: ' . mysqli_error($conn));
        }
    } else {
        // Insert new rating
        $insert_stmt = mysqli_prepare($conn, 
            "INSERT INTO ratings 
             (product_id, user_id, user, rating, review, created_at) 
             VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"
        );
        if (!$insert_stmt) {
            throw new Exception('Database error: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($insert_stmt, "iisis", $product_id, $user_id, $user, $rating, $review);
        if (!mysqli_stmt_execute($insert_stmt)) {
            throw new Exception('Gagal menyimpan rating: ' . mysqli_error($conn));
        }
    }

    // Update average rating
    $update_avg = mysqli_prepare($conn, 
        "UPDATE product p 
         SET rating = (
             SELECT ROUND(AVG(rating), 1) 
             FROM ratings 
             WHERE product_id = ?
         ) 
         WHERE p.id = ?"
    );
    if (!$update_avg) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($update_avg, "ii", $product_id, $product_id);
    if (!mysqli_stmt_execute($update_avg)) {
        throw new Exception('Gagal mengupdate rata-rata rating: ' . mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Rating berhasil disimpan'
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($conn) && mysqli_ping($conn)) {
        mysqli_rollback($conn);
    }

    error_log("Error in submit_rating.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} finally {
    // Close all statements
    if (isset($check_product)) mysqli_stmt_close($check_product);
    if (isset($check_rating)) mysqli_stmt_close($check_rating);
    if (isset($update_stmt)) mysqli_stmt_close($update_stmt);
    if (isset($insert_stmt)) mysqli_stmt_close($insert_stmt);
    if (isset($update_avg)) mysqli_stmt_close($update_avg);
    
    // Close connection
    if (isset($conn)) mysqli_close($conn);
}
?>