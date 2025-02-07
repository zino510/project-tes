<?php
session_start();
include '../config/database.php';

if (!isset($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit();
}

$id = intval($_GET['id']);
$query = $conn->prepare("SELECT * FROM product WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo "Produk tidak ditemukan.";
    exit();
}

$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['nama_produk']); ?> - Detail Produk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --success-color: #2ecc71;
            --text-color: #34495e;
            --light-gray: #ecf0f1;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.4rem;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.3s ease;
        }

        .back-btn:hover {
            color: var(--accent-color);
            transform: translateX(-5px);
        }

        .product-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: auto;
            transition: transform 0.5s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .product-details {
            padding: 2rem;
        }

        .product-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .price-tag {
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 1.2rem;
        }

        .product-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-custom {
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-buy {
            background: var(--success-color);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-buy:hover {
            background: #27ae60;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
            color: white;
        }

        .btn-dashboard {
            background: var(--primary-color);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }

        .btn-dashboard:hover {
            background: #234567;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(44, 62, 80, 0.4);
            color: white;
        }

        /* Badge untuk status stok */
        .stock-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
        }

        /* Animasi */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .product-container {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .product-details {
                padding: 1rem 0;
            }

            .product-title {
                font-size: 1.8rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-custom {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="container">
        <a href="../pages/dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Dashboard</span>
        </a>
        <span class="navbar-brand">Detail Produk</span>
    </div>
</nav>

<div class="container">
    <div class="product-container">
        <div class="row">
            <!-- Gambar Produk -->
            <div class="col-md-6">
                <div class="product-image-container">
                    <img src="../<?php echo htmlspecialchars($row['gambar']); ?>" 
                         alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                         class="product-image">
                    <div class="stock-badge">
                        <i class="fas fa-box"></i> Stok Tersedia
                    </div>
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="col-md-6">
                <div class="product-details">
                    <h1 class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h1>
                    
                    <div class="product-price">
                        <span class="price-tag">
                            <i class="fas fa-tag"></i>
                            Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                        </span>
                    </div>

                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?>
                    </div>

                    <div class="action-buttons">
                        <a href="checkout.php?id=<?php echo $row['id']; ?>" class="btn btn-custom btn-buy">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Beli Sekarang
                        </a>
                        <a href="../pages/dashboard.php" class="btn btn-custom btn-dashboard">
                            <i class="fas fa-home me-2"></i>
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>