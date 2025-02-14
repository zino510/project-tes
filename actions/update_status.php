<?php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if ( !isset( $_SESSION[ 'user_id' ] ) ) {
    header( 'Location: login.php' );
    exit();
}

// Ambil data dari form
$transaction_id = isset( $_POST[ 'transaction_id' ] ) ? $_POST[ 'transaction_id' ] : '';
$status = isset( $_POST[ 'status' ] ) ? $_POST[ 'status' ] : '';

// Update status transaksi
if ( $transaction_id && $status ) {
    $stmt = $conn->prepare( 'UPDATE transactions SET status = ? WHERE id = ?' );
    $stmt->bind_param( 'si', $status, $transaction_id );

    if ( $stmt->execute() ) {
        header( 'Location: ../pages/transactions.php?status=updated' );
        exit();
    } else {
        die( 'Error updating status: ' . $conn->error );
    }
} else {
    die( 'Invalid input.' );
}
?>