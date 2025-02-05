<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="text-center mt-5">
    <h2>✅ Pesanan Berhasil!</h2>
    <p>Terima kasih telah berbelanja. Kami akan segera memproses pesanan Anda.</p>
    <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
</body>
</html>
