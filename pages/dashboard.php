<?php
// pages/dashboard.php
session_start();
include '../config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM product");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <h2>Dashboard</h2>
    <a href="post_barang.php">Jual Barang</a> | <a href="profile.php">Profil</a> | <a href="../actions/logout.php?logout=true">Logout</a>
    <h3>Daftar Barang</h3>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <h4><?php echo $row['nama_produk']; ?></h4>
                <p><?php echo $row['deskripsi']; ?></p>
                <p>Harga: Rp <?php echo number_format($row['harga'], 2); ?></p>
                <img src="<?php echo $row['gambar']; ?>" width="100">
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>