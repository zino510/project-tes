<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Duomart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .hero-section {
            background: url('path-to-your-background-image.jpg') no-repeat center center;
            background-size: cover;
            color: black;
            padding: 100px 0;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .hero-section p {
            font-size: 24px;
            margin-bottom: 40px;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            padding: 10px 20px;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .info-section {
            padding: 60px 0;
        }
        .info-section h3 {
            margin-bottom: 20px;
        }
        .info-section p {
            font-size: 18px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Duomart</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="pages/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/register.php">Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/dashboard.php">Dashboard</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1 class="display-4">Selamat Datang di Duomart</h1>
        <p class="lead">Temukan dan jual produk terbaik di platform kami. Nikmati pengalaman berbelanja yang mudah dan menyenangkan.</p>
        <a class="btn btn-custom btn-lg" href="pages/register.php" role="button">Mulai Sekarang</a>
    </div>
</div>

<!-- Info Sections -->
<div class="container info-section">
    <div class="row">
        <div class="col-md-4">
            <h3>Keuntungan Bergabung</h3>
            <p>Daftar sekarang dan nikmati berbagai keuntungan berbelanja dan berjualan di Duomart kami. Mulai dari kemudahan transaksi hingga jangkauan pasar yang luas.</p>
        </div>
        <div class="col-md-4">
            <h3>Berbagai Kategori Produk</h3>
            <p>Temukan berbagai produk dari berbagai kategori. Dari elektronik, fashion, hingga kebutuhan sehari-hari. Semua bisa kamu dapatkan di sini.</p>
        </div>
        <div class="col-md-4">
            <h3>Transaksi Aman</h3>
            <p>Kami menjamin keamanan transaksi Anda dengan sistem pembayaran yang terpercaya dan dukungan pelanggan yang siap membantu kapan saja.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>