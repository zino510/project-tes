<?php
session_start();
include '../config/database.php';

// Cek jika user belum login
if (!isset($_SESSION['user_id'])) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    header("Location: login.php");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$result = $conn->query("SELECT * FROM product");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Duo Mart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <style>
        :root {
            --primary-color: #2980b9;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --text-color: #2c3e50;
            --light-bg: #ecf0f1;
            --white: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #d5e5ee 100%);
            color: var(--text-color);
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            background: var(--white);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(52, 152, 219, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-color) !important;
            position: relative;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        /* Button Styles */
        .btn-custom {
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .btn-sell {
            background: var(--danger-color);
            color: var(--white);
            border: none;
        }

        .btn-sell:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-profile {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-profile:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .btn-logout {
            background: transparent;
            border: 2px solid var(--danger-color);
            color: var(--danger-color);
        }

        .btn-logout:hover {
            background: var(--danger-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Product Card Styles */
        .product-section {
            padding: 2rem 0;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-color);
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            position: relative;
            overflow: hidden;
            padding-top: 75%;
        }

        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-details {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-description {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .btn-detail {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-detail:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Category Pills */
        .category-pills {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding: 1rem 0;
            margin-bottom: 2rem;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .category-pills::-webkit-scrollbar {
            display: none;
        }

        .category-pill {
            background: var(--white);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            color: var(--text-color);
            transition: all 0.3s ease;
            white-space: nowrap;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .category-pill:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .product-card {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            
            .btn-custom {
                margin: 0.5rem 0;
                width: 100%;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-store me-2"></i>
            Duo Mart
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="post_barang.php" class="btn btn-custom btn-sell">
                    <i class="fas fa-plus-circle me-2"></i>Jual
                </a>
                <a href="profile.php" class="btn btn-custom btn-profile">
                    <i class="fas fa-user me-2"></i>Profil
                </a>
                <a href="transactions.php" class="btn btn-custom btn-profile">
                    <i class="fas fa-shopping-bag me-2"></i>Orders
                </a>
                <a href="../actions/logout.php?logout=true" class="btn btn-custom btn-logout">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Categories -->
<div class="container mt-4">
    <div class="category-pills">
        <div class="category-pill active">Semua</div>
        <div class="category-pill">Elektronik</div>
        <div class="category-pill">Fashion</div>
        <div class="category-pill">Perawatan Pribadi</div>
        <div class="category-pill">Anak-anak</div>
        <div class="category-pill">Keseharian</div>
    </div>
</div>

<!-- Products -->
<div class="container product-section">
    <h2 class="section-title">Produk Terbaru</h2>
    <div class="row g-4">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-3">
                <div class="product-card">
                    <div class="product-image">
                        <img src="../<?php echo $row['gambar']; ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                    </div>
                    <div class="product-details">
                        <h3 class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <div class="product-price">
                            <i class="fas fa-tag me-2"></i>
                            Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                        </div>
                        <p class="product-description">
                            <?php echo htmlspecialchars(substr($row['deskripsi'], 0, 100)) . '...'; ?>
                        </p>
                        <a href="detail_produk.php?id=<?php echo $row['id']; ?>" class="btn-detail">
                            <i class="fas fa-eye me-2"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.onpageshow = function(event) {
    if (event.persisted) {
        window.location.reload();
    }
};

window.history.forward();
function noBack() {
    window.history.forward();
}
</script>
</body>
</html>