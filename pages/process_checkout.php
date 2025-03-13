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
    $quantity = intval($_POST['quantity']); // Ambil quantity dari form
    // Update stok
$new_stock = $product['stock'] - $quantity;

    // Validasi input
    if (empty($nama_penerima) || empty($alamat)) {
        $_SESSION['error'] = "Nama penerima dan alamat harus diisi!";
        header("Location: checkout.php?id=" . $product_id);
        exit();
    }

    // Format alamat lengkap dengan nama penerima
    $formatted_address = "Penerima: " . $nama_penerima . "\n" . $alamat;

    // Ambil detail produk
    $query = $conn->prepare("SELECT harga, stock FROM product WHERE id = ?");
    $query->bind_param("i", $product_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['error'] = "Produk tidak ditemukan.";
        header("Location: checkout.php?id=" . $product_id);
        exit();
    }

    $product = $result->fetch_assoc();
    $harga = $product['harga'];
    $total_harga = $harga * $quantity; // Hitung total harga berdasarkan quantity
    
    // Mulai transaksi database
    $conn->begin_transaction();

    try {
        // 1️⃣ Simpan transaksi di tabel `transactions` dengan shipping_address
        $query = $conn->prepare("
            INSERT INTO transactions 
            (user_id, total_harga, status, shipping_address) 
            VALUES (?, ?, 'pending', ?)
        ");
        $query->bind_param("ids", $user_id, $total_harga, $formatted_address);
        $query->execute();
        $transaction_id = $conn->insert_id;

        // 2️⃣ Simpan detail barang di tabel `transaction_details`
        $query = $conn->prepare("
            INSERT INTO transaction_details 
            (transaction_id, product_id, quantity, harga) 
            VALUES (?, ?, ?, ?)
        ");
        $query->bind_param("iiid", $transaction_id, $product_id, $quantity, $harga);
        $query->execute();

        // 3️⃣ Update stok produk
        $new_stock = $product['stock'] - $quantity; // Kurangi stok sesuai quantity
        if ($new_stock < 0) {
            throw new Exception("Stok produk tidak mencukupi");
        }
        
        $query = $conn->prepare("UPDATE product SET stock = ? WHERE id = ?");
        $query->bind_param("ii", $new_stock, $product_id);
        $query->execute();

        // Commit transaksi
        $conn->commit();

        // Redirect berdasarkan metode pembayaran
        if ($metode_pembayaran === "E-Wallet") {
            header("Location: qris.php?transaction_id=" . $transaction_id);
        } else {
            header("Location: order_success.php?id=" . $transaction_id);
        }
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: checkout.php?id=" . $product_id);
        exit();
    }
} else {
    $_SESSION['error'] = "Akses tidak diizinkan.";
    header("Location: dashboard.php");
    exit();
}
?>