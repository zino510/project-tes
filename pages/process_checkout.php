<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $nama_penerima = htmlspecialchars($_POST['nama_penerima']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $metode_pembayaran = $_POST['metode_pembayaran'];

    // Ambil detail produk
    $query = $conn->prepare("SELECT harga FROM product WHERE id = ?");
    $query->bind_param("i", $product_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 0) {
        echo "Produk tidak ditemukan.";
        exit();
    }

    $product = $result->fetch_assoc();
    $harga = $product['harga'];
    
    // Mulai transaksi database
    $conn->begin_transaction();

    try {
        // 1️⃣ Simpan transaksi di tabel `transactions`
        $query = $conn->prepare("INSERT INTO transactions (user_id, total_harga, status) VALUES (?, ?, 'pending')");
        $query->bind_param("id", $user_id, $harga);
        $query->execute();
        $transaction_id = $conn->insert_id;

        // 2️⃣ Simpan detail barang di tabel `transaction_details`
        $query = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, harga) VALUES (?, ?, 1, ?)");
        $query->bind_param("iid", $transaction_id, $product_id, $harga);
        $query->execute();

        // Commit transaksi
        $conn->commit();

        header("Location: order_success.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Terjadi kesalahan: " . $e->getMessage();
    }
} else {
    echo "Akses tidak diizinkan.";
}
?>
