<?php
session_start();
include '../config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// CSRF Protection 
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
}

$user_id = $_SESSION['user_id'];

// Get user data from database
$stmt = $conn->prepare("SELECT id, username, nama, email, foto, created_at FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user_data = $result_user->fetch_assoc();

// Set default values if user data not found
$user_login = $user_data['username'] ?? 'zino510';

// Set current UTC time
$current_time_utc = '2025-03-02 17:22:40';

// Set timezone to Jakarta/GMT+7
date_default_timezone_set('Asia/Jakarta');

// Convert UTC to GMT+7
$datetime_utc = new DateTime($current_time_utc, new DateTimeZone('UTC'));
$datetime_jakarta = clone $datetime_utc;
$datetime_jakarta->setTimezone(new DateTimeZone('Asia/Jakarta'));
$current_time_jakarta = $datetime_jakarta->format('Y-m-d H:i:s');

// Cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get products sorted by newest first with seller information
$result = $conn->query("SELECT p.*, u.username as seller_name 
                       FROM product p 
                       LEFT JOIN user u ON p.user_id = u.id 
                       ORDER BY p.created_at DESC 
                       LIMIT 12");

// Get all distinct categories from the database
$categories_result = $conn->query("SELECT DISTINCT kategori FROM product ORDER BY kategori");
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category['kategori'];
}

// Close prepared statement
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard - Duo Mart</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    <link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
    :root {
        /* Colors */
        --primary: #2980b9;
        --secondary: #3498db;
        --accent: #e74c3c;
        --success: #2ecc71;
        --warning: #f1c40f;
        --dark: #2c3e50;
        --light: #ecf0f1;
        --white: #ffffff;
        
        /* Shadows */
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
        --shadow-lg: 0 8px 16px rgba(0,0,0,0.15);
        
        /* Border Radius */
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 20px;
        --radius-full: 9999px;
    }

    /* Base Styles */
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, var(--light) 0%, #d5e5ee 100%);
        color: var(--dark);
        min-height: 100vh;
    }

    /* Navbar Styles */
    .navbar {
        background: var(--white);
        padding: 1rem 0;
        box-shadow: var(--shadow-md);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
    }

    /* Updated Filter Section Styles */
.filter-section {
    background: var(--white);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}

.filter-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.filter-label {
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--light);
}

.filter-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.625rem;
}

.filter-option {
    cursor: pointer;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
    background-color: var(--light);
    font-size: 0.875rem;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.filter-option:hover {
    background-color: rgba(41, 128, 185, 0.1);
    border-color: var(--primary);
}

.filter-option.active {
    background-color: var(--primary);
    color: var(--white);
    font-weight: 500;
}

.filter-reset {
    padding: 0.625rem 1.25rem;
    background: transparent;
    border: 1.5px solid var(--primary);
    color: var(--primary);
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    margin-left: auto;
    height: fit-content;
    align-self: flex-end;
}

.filter-reset:hover {
    background: var(--primary);
    color: var(--white);
}

.filter-reset i {
    transition: transform 0.3s ease;
}

.filter-reset:hover i {
    transform: rotate(-180deg);
}

/* Dark Mode Adjustments */
[data-theme="dark"] .filter-section {
    background: var(--card-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .filter-label {
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .filter-option {
    background-color: rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .filter-option:hover {
    background-color: rgba(52, 152, 219, 0.2);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .filter-section {
        padding: 1rem;
    }

    .filter-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .filter-group {
        width: 100%;
    }

    .filter-reset {
        width: 100%;
        justify-content: center;
        margin-top: 1rem;
    }

    .filter-options {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 0.5rem;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .filter-options::-webkit-scrollbar {
        display: none;
    }
}

/* Product Grid Layout */
#products-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    width: 100%;
    transition: all 0.3s ease;
}

.product-item {
    opacity: 1;
    transform: scale(1);
    transition: opacity 0.3s ease, transform 0.3s ease;
    animation: fadeIn 0.3s ease;
}

.product-item.product-hidden {
    display: none !important;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Responsive grid adjustments */
@media (max-width: 1200px) {
    #products-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    #products-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    #products-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
}

    /* Product Card Styles - Updated */
    .product-card {
        background: var(--white);
        border-radius: var(--radius-md);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: var(--shadow-sm);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .product-image-container {
        position: relative;
        padding-top: 100%;
        overflow: hidden;
    }

    .product-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.1);
    }

    .product-info {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    /* Product Title Style */
    .product-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        line-height: 1.4;
        height: 2.8em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    /* Product Description Style */
    .product-description {
        font-size: 0.875rem;
        color: var(--dark);
        opacity: 0.8;
        margin: 0.5rem 0;
        height: 3em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        flex-shrink: 0;
    }

    /* Seller Info Style */
    .seller-info {
        font-size: 0.9rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
        height: 1.5em;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    /* Product Category Style */
    .product-category {
        font-size: 0.8rem;
        color: var(--dark);
        opacity: 0.7;
        margin-bottom: 0.5rem;
        height: 1.2em;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    /* Product Price Style */
    .product-price {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
        margin: 0.5rem 0;
        height: 1.5em;
    }

    /* Stock Info Container */
    .stock-info {
        margin: 0.5rem 0;
        padding: 0.5rem;
        border-radius: var(--radius-sm);
        background-color: var(--light);
        height: 2.5em;
        display: flex;
        align-items: center;
    }

    /* Button Container */
    .button-container {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    /* Button Styles */
    .btn-custom {
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-full);
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        height: 2.5rem;
    }

    .btn-custom:disabled {
        background-color: #e0e0e0;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .btn-primary {
        background: var(--primary);
        color: var(--white);
        border: none;
    }

    .btn-outline {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }

    .btn-outline:hover {
        background: var(--primary);
        color: var(--white);
    }

    /* Stock Badge Styles */
    .stock-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.9rem;
        font-weight: 600;
    }

    .stock-badge.in-stock {
        background-color: rgba(46, 204, 113, 0.1);
        color: var(--success);
    }

    .stock-badge.out-of-stock {
        background-color: rgba(231, 76, 60, 0.1);
        color: var(--accent);
    }

    .stock-count {
        font-weight: 700;
        margin-left: 0.25rem;
    }

    /* Product Transition for filtering */
    .product-transition {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    
    .product-hidden {
        opacity: 0;
        transform: scale(0.8);
        height: 0;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    /* Dark Mode Styles */
    [data-theme="dark"] {
        --primary: #3498db;
        --secondary: #2980b9;
        --accent: #e74c3c;
        --success: #2ecc71;
        --warning: #f1c40f;
        --dark: #ecf0f1;
        --light: #2c3e50;
        --white: #1a1a1a;
        --text-color: #ecf0f1;
        --bg-color: #121212;
        --card-bg: #1e1e1e;
    }

    [data-theme="dark"] body {
        background: linear-gradient(135deg, var(--bg-color) 0%, #1a242f 100%);
        color: var(--text-color);
    }

    [data-theme="dark"] .navbar, 
    [data-theme="dark"] .filter-section {
        background: var(--card-bg);
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    [data-theme="dark"] .product-card {
        background: var(--card-bg);
        border: 1px solid rgba(255,255,255,0.1);
    }

    [data-theme="dark"] .filter-option {
        background-color: var(--light);
        color: var(--dark);
    }

    [data-theme="dark"] .filter-option:hover {
        background-color: rgba(52, 152, 219, 0.2);
    }

    [data-theme="dark"] .filter-option.active {
        background-color: var(--primary);
        color: var(--white);
    }

   /* Mobile Navigation */
.mobile-nav {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--white);
    padding: 0.75rem;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.mobile-nav .nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--dark);
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.mobile-nav .dark-mode-toggle {
    background: transparent;
    border: none;
    color: var(--dark);
    font-size: 0.8rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.mobile-nav .nav-item i {
    font-size: 1.2rem;
    margin-bottom: 0.25rem;
}

.mobile-nav .nav-item:hover,
.mobile-nav .nav-item:active,
.mobile-nav .dark-mode-toggle:hover,
.mobile-nav .dark-mode-toggle:active {
    color: var(--primary);
}


    /* Rating Stars */
    .stars {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }
    
    .stars input {
        display: none;
    }
    
    .stars label {
        cursor: pointer;
        font-size: 25px;
        color: #ccc;
        transition: color 0.2s;
        margin-right: 5px;
    }
    
    .stars label:hover,
    .stars label:hover ~ label,
    .stars input:checked ~ label {
        color: #f1c40f;
    }

    @media (max-width: 768px) {
        .mobile-nav {
            display: flex;
            justify-content: space-around;
        }

        body {
            padding-bottom: 70px;
        }
        
        .filter-container {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a href="../index.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="my_products.php" class="nav-item">
            <i class="fas fa-box"></i>
            <span>Produk Saya</span>
        </a>
        <a href="post_barang.php" class="nav-item">
            <i class="fas fa-plus-circle"></i>
            <span>Jual</span>
        </a>
        <a href="transactions.php" class="nav-item">
            <i class="fas fa-shopping-bag"></i>
            <span>Orders</span>
        </a>
        <a href="profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-store me-2"></i>
                Duo Mart
            </a>
            <div class="search-container mx-auto d-none d-lg-block">
                <div class="position-relative">
                    <input type="text" class="search-input form-control" placeholder="Cari produk..." style="min-width: 300px;">
                    <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>
            </div>
            <div class="w-100 mt-2 d-lg-none">
                <div class="position-relative">
                    <input type="text" class="search-input form-control" placeholder="Cari produk...">
                    <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>
            </div>
            <div class="ms-auto d-none d-lg-flex gap-3">
                <button id="darkModeToggle" class="btn-custom btn-outline">
                    <i class="fas fa-moon"></i>
                    <span class="d-none d-md-inline ms-1">Dark Mode</span>
                </button>

                <div class="dropdown">
                    <button class="btn-custom btn-primary dropdown-toggle" type="button" id="sellerMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-box me-1"></i>
                        Menu Penjual
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="sellerMenuButton">
                        <li>
                            <a class="dropdown-item" href="../pages/my_products.php">
                                <i class="fas fa-boxes me-2"></i>
                                Produk Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="../pages/post_barang.php">
                                <i class="fas fa-plus-circle me-2"></i>
                                Tambah Produk
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="../pages/sales_report.php">
                            <i class="fas fa-chart-line me-2"></i>
                                Laporan Penjualan
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="mobile-nav">
    <a href="../index.php" class="nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="my_products.php" class="nav-item">
        <i class="fas fa-box"></i>
        <span>Produk Saya</span>
    </a>
    <a href="post_barang.php" class="nav-item">
        <i class="fas fa-plus-circle"></i>
        <span>Jual</span>
    </a>
    <a href="transactions.php" class="nav-item">
        <i class="fas fa-shopping-bag"></i>
        <span>Orders</span>
    </a>
    <a href="profile.php" class="nav-item">
        <i class="fas fa-user"></i>
        <span>Profil</span>
    </a>
    <button id="mobileDarkModeToggle" class="nav-item dark-mode-toggle">
        <i class="fas fa-moon"></i>
        <span>Mode</span>
    </button>
</div>
 <!-- Three Dots Menu -->
 <div class="dropdown">
                    <button class="btn-custom btn-outline" type="button" id="moreMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="moreMenuButton">
                        <!-- User Info Section -->
                        <div class="user-info">
                            <div class="username">@<?php echo htmlspecialchars($user_login); ?></div>
                            <div class="last-login">
                                <small>
                                    <i class="fas fa-clock me-1"></i>
                                    <span id="live-time"></span> (GMT+7)
                                </small>
                            </div>
                        </div>
                        <li>
                            <a class="dropdown-item" href="transactions.php">
                                <i class="fas fa-shopping-bag me-2"></i>
                                Orders
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>
                                Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="../actions/logout.php?logout=true">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

<!-- Filter Section -->
<div class="container mt-3">
    <div class="filter-section">
        <div class="filter-container">
            <div class="filter-group">
                <div class="filter-label">
                    <i class="fas fa-tag"></i>
                    <span>Kategori</span>
                </div>
                <div class="filter-options category-filters">
                    <div class="filter-option active" data-filter="all">
                        <i class="fas fa-th"></i>
                        <span>Semua</span>
                    </div>
                    <div class="filter-option" data-filter="Elektronik">
                        <i class="fas fa-laptop"></i>
                        <span>Elektronik</span>
                    </div>
                    <div class="filter-option" data-filter="Fashion">
                        <i class="fas fa-tshirt"></i>
                        <span>Fashion</span>
                    </div>
                    <div class="filter-option" data-filter="Kesehatan">
                        <i class="fas fa-heartbeat"></i>
                        <span>Kesehatan</span>
                    </div>
                    <div class="filter-option" data-filter="Makanan">
                        <i class="fas fa-utensils"></i>
                        <span>Makanan</span>
                    </div>
                    <div class="filter-option" data-filter="Lainnya">
                        <i class="fas fa-ellipsis-h"></i>
                        <span>Lainnya</span>
                    </div>
                </div>
            </div>
            
            <div class="filter-group">
                <div class="filter-label">
                    <i class="fas fa-star"></i>
                    <span>Kondisi</span>
                </div>
                <div class="filter-options condition-filters">
                    <div class="filter-option active" data-filter="all">
                        <i class="fas fa-check-circle"></i>
                        <span>Semua</span>
                    </div>
                    <div class="filter-option" data-filter="Baru">
                        <i class="fas fa-box"></i>
                        <span>Baru</span>
                    </div>
                    <div class="filter-option" data-filter="Bekas">
                        <i class="fas fa-recycle"></i>
                        <span>Bekas</span>
                    </div>
                </div>
            </div>
            
            <button id="resetFilters" class="filter-reset">
                <i class="fas fa-undo-alt"></i>
                <span>Reset Filter</span>
            </button>
        </div>
    </div>
</div>

    <!-- Products Grid -->
    <div class="container py-4">
    <h2 class="h4 mb-4">Produk Terbaru</h2>
    <div id="products-container">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="product-item product-transition" 
             data-category="<?php echo htmlspecialchars($row['kategori']); ?>" 
             data-condition="<?php echo htmlspecialchars($row['kondisi']); ?>"
             data-product-id="<?php echo $row['id']; ?>">
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="../<?php echo $row['gambar']; ?>" 
                             alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                             class="product-image">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <div class="seller-info">
                            <i class="fas fa-store me-1"></i>
                            <span><?php echo htmlspecialchars($row['seller_name']); ?></span>
                        </div>
                        <span class="product-category">
                            <i class="fas fa-tag me-1"></i>
                            <?php echo $row['kategori']; ?>
                        </span>
                        <div class="product-price">
                            Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                        </div>
                        
                        <!-- Stock Information -->
                        <div class="stock-info">
                            <span class="stock-badge <?php echo $row['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                <i class="fas <?php echo $row['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                Stok: <span class="stock-count"><?php echo $row['stock']; ?></span>
                            </span>
                        </div>

                        <p class="product-description">
                            <?php echo htmlspecialchars(substr($row['deskripsi'], 0, 100)) . '...'; ?>
                        </p>

                        <!-- Button Container -->
                        <div class="button-container">
                            <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                                <!-- Tombol untuk pemilik produk -->
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" 
                                   class="btn-custom btn-primary">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit Produk</span>
                                </a>
                                <button type="button" 
        class="btn-custom btn-outline btn-danger" 
        onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>')">
    <i class="fas fa-trash-alt"></i>
    <span>Hapus Produk</span>
</button>
                            <?php else: ?>
                                <!-- Tombol untuk pembeli -->
                                <?php if($row['stock'] > 0): ?>
                                    <a href="checkout.php?id=<?php echo $row['id']; ?>" 
                                       class="btn-custom btn-primary">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span>Beli Sekarang</span>
                                    </a>
                                <?php else: ?>
                                    <button class="btn-custom btn-outline" disabled>
                                        <i class="fas fa-times"></i>
                                        <span>Stok Habis</span>
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn-custom btn-outline" 
                                        onclick="openRatingModal(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-star"></i>
                                    <span>Beri Rating</span>
                                </button>
                            <?php endif; ?>

                            <a href="detail_produk.php?id=<?php echo $row['id']; ?>" 
                               class="btn-custom btn-outline">
                                <i class="fas fa-eye"></i>
                                <span>Lihat Detail</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModalLabel">Beri Rating</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ratingForm" method="post">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="user_login" value="<?php echo htmlspecialchars($user_login); ?>">
                        <input type="hidden" name="product_id" id="product_id">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="stars">
                                <input type="radio" name="rating" value="5" id="rate5">
                                <label for="rate5">&#9733;</label>
                                <input type="radio" name="rating" value="4" id="rate4">
                                <label for="rate4">&#9733;</label>
                                <input type="radio" name="rating" value="3" id="rate3">
                                <label for="rate3">&#9733;</label>
                                <input type="radio" name="rating" value="2" id="rate2">
                                <label for="rate2">&#9733;</label>
                                <input type="radio" name="rating" value="1" id="rate1">
                                <label for="rate1">&#9733;</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="review" class="form-label">Review (Opsional)</label>
                            <textarea class="form-control" name="review" id="review" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Rating</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // --- CSRF TOKEN INITIALIZATION ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    if (!csrfToken) {
        console.error('CSRF token not found in meta tag');
    }
    
    // --- TIME FUNCTIONS ---
    function padZero(num) {
        return num < 10 ? '0' + num : num;
    }

    function updateLiveTime() {
        const timeElement = document.getElementById('live-time');
        if (!timeElement) return;
        
        // Menggunakan waktu Jakarta dari server
        const jakartaTime = new Date('2025-03-04 21:18:18'); // Waktu Jakarta (UTC+7)
        jakartaTime.setSeconds(jakartaTime.getSeconds() + timeDiff);
        
        const hours = padZero(jakartaTime.getHours());
        const minutes = padZero(jakartaTime.getMinutes());
        const seconds = padZero(jakartaTime.getSeconds());
        
        timeElement.textContent = `${hours}:${minutes}:${seconds}`;
    }
    
    let timeDiff = 0;
    const timeInterval = setInterval(() => {
        timeDiff++;
        updateLiveTime();
    }, 1000);
    
    updateLiveTime();

    // --- DARK MODE FUNCTIONALITY ---
    const darkModeToggle = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;
    
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'enabled') {
        htmlElement.setAttribute('data-theme', 'dark');
        if (darkModeToggle) {
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i><span class="d-none d-md-inline ms-1">Light Mode</span>';
        }
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const isDark = htmlElement.getAttribute('data-theme') === 'dark';
            if (isDark) {
                htmlElement.removeAttribute('data-theme');
                localStorage.setItem('darkMode', 'disabled');
                this.innerHTML = '<i class="fas fa-moon"></i><span class="d-none d-md-inline ms-1">Dark Mode</span>';
            } else {
                htmlElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('darkMode', 'enabled');
                this.innerHTML = '<i class="fas fa-sun"></i><span class="d-none d-md-inline ms-1">Light Mode</span>';
            }
        });
    }

    // --- DELETE PRODUCT FUNCTIONALITY ---
    window.deleteProduct = function(productId, productName) {
        if (!csrfToken) {
            Swal.fire({
                title: 'Error!',
                text: 'CSRF token tidak ditemukan',
                icon: 'error',
                confirmButtonColor: '#e74c3c'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Anda yakin ingin menghapus produk "${productName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#3498db',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', productId);
                formData.append('csrf_token', csrfToken);

                fetch('../actions/delete_product.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const productElement = document.querySelector(`.product-item[data-product-id="${productId}"]`);
                        if (productElement) {
                            productElement.style.opacity = '0';
                            productElement.style.transform = 'scale(0.8)';
                            setTimeout(() => {
                                productElement.remove();
                                const visibleProducts = document.querySelectorAll('.product-item:not(.product-hidden)');
                                if (visibleProducts.length === 0) {
                                    const productsContainer = document.getElementById('products-container');
                                    if (productsContainer) {
                                        productsContainer.innerHTML = `
                                            <div class="col-12 text-center py-5">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Tidak ada produk yang tersedia.
                                                </div>
                                            </div>
                                        `;
                                    }
                                }
                            }, 300);

                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Produk berhasil dihapus',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        throw new Error(data.message || 'Gagal menghapus produk');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Terjadi kesalahan saat menghapus produk',
                        icon: 'error',
                        confirmButtonColor: '#e74c3c'
                    });
                });
            }
        });
    };

    // --- FILTER FUNCTIONALITY ---
    const categoryFilters = document.querySelectorAll('.category-filters .filter-option');
    const conditionFilters = document.querySelectorAll('.condition-filters .filter-option');
    const resetButton = document.getElementById('resetFilters');
    const searchInputs = document.querySelectorAll('.search-input');
    
    let currentFilters = {
        category: 'all',
        condition: 'all',
        search: ''
    };
    
    function applyFilters() {
    const productsContainer = document.getElementById('products-container');
    const productItems = document.querySelectorAll('.product-item');
    const searchTerm = currentFilters.search.toLowerCase().trim();
    let visibleCount = 0;
    
    // First pass: determine visibility
    productItems.forEach(item => {
        const category = item.getAttribute('data-category');
        const condition = item.getAttribute('data-condition');
        const productTitle = item.querySelector('.product-title').textContent.toLowerCase();
        const productDescription = item.querySelector('.product-description').textContent.toLowerCase();
        const sellerInfo = item.querySelector('.seller-info').textContent.toLowerCase();
        
        const matchesCategory = currentFilters.category === 'all' || category === currentFilters.category;
        const matchesCondition = currentFilters.condition === 'all' || condition === currentFilters.condition;
        const matchesSearch = searchTerm === '' || 
                            productTitle.includes(searchTerm) || 
                            productDescription.includes(searchTerm) ||
                            sellerInfo.includes(searchTerm) ||
                            category.toLowerCase().includes(searchTerm);
        
        // Prepare for transition
        if (matchesCategory && matchesCondition && matchesSearch) {
            item.style.opacity = '0';
            item.style.transform = 'scale(0.95)';
            item.classList.remove('product-hidden');
            visibleCount++;
            
            // Trigger reflow
            item.offsetHeight;
            
            // Apply visible state
            item.style.opacity = '1';
            item.style.transform = 'scale(1)';
        } else {
            item.style.opacity = '0';
            item.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                item.classList.add('product-hidden');
            }, 300);
        }
    });

    // Handle no results state
    if (visibleCount === 0) {
        if (!document.getElementById('no-products-message')) {
            const noProductsMessage = document.createElement('div');
            noProductsMessage.id = 'no-products-message';
            noProductsMessage.className = 'grid-full-width';
            noProductsMessage.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Tidak ada produk yang sesuai dengan filter yang dipilih.
                </div>
            `;
            productsContainer.appendChild(noProductsMessage);
        }
    } else {
        const noProductsMessage = document.getElementById('no-products-message');
        if (noProductsMessage) {
            noProductsMessage.remove();
        }
    }

    // Force layout recalculation to maintain grid
    productsContainer.style.display = 'none';
    productsContainer.offsetHeight; // Force reflow
    productsContainer.style.display = 'grid';
    
    // Update grid layout after transitions
    setTimeout(() => {
        const visibleItems = document.querySelectorAll('.product-item:not(.product-hidden)');
        visibleItems.forEach((item, index) => {
            item.style.gridColumn = 'auto';
        });
    }, 350);
}

// Add this CSS to your existing styles
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    #products-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        width: 100%;
        transition: all 0.3s ease;
    }

    .product-item {
        opacity: 1;
        transform: scale(1);
        transition: all 0.3s ease-in-out;
    }

    .product-hidden {
        display: none !important;
    }

    .grid-full-width {
        grid-column: 1 / -1;
        padding: 2rem;
    }

    @media (max-width: 1200px) {
        #products-container {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
    }

    @media (max-width: 768px) {
        #products-container {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }
    }

    @media (max-width: 576px) {
        #products-container {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
    }
`;
document.head.appendChild(styleSheet);

// The rest of your event listeners remain the same
categoryFilters.forEach(filter => {
    filter.addEventListener('click', function() {
        categoryFilters.forEach(f => f.classList.remove('active'));
        this.classList.add('active');
        currentFilters.category = this.getAttribute('data-filter');
        applyFilters();
    });
});

conditionFilters.forEach(filter => {
    filter.addEventListener('click', function() {
        conditionFilters.forEach(f => f.classList.remove('active'));
        this.classList.add('active');
        currentFilters.condition = this.getAttribute('data-filter');
        applyFilters();
    });
});

// Search input handling remains the same
let searchTimeout = null;
searchInputs.forEach(input => {
    input.addEventListener('input', function() {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        searchTimeout = setTimeout(() => {
            currentFilters.search = this.value;
            applyFilters();
        }, 300);
    });

    // Sync search inputs
    input.addEventListener('input', function() {
        const searchValue = this.value;
        searchInputs.forEach(otherInput => {
            if (otherInput !== this) {
                otherInput.value = searchValue;
            }
        });
    });
});

// Reset button handling
if (resetButton) {
    resetButton.addEventListener('click', function() {
        currentFilters.category = 'all';
        currentFilters.condition = 'all';
        
        searchInputs.forEach(input => {
            input.value = '';
        });
        currentFilters.search = '';
        
        categoryFilters.forEach(f => {
            f.classList.toggle('active', f.getAttribute('data-filter') === 'all');
        });
        
        conditionFilters.forEach(f => {
            f.classList.toggle('active', f.getAttribute('data-filter') === 'all');
        });
        
        applyFilters();
    });
}
                
                resetButton.classList.add('filter-reset-active');
                setTimeout(() => {
                    resetButton.classList.remove('filter-reset-active');
                }, 300);
            });
        
        
        // --- RATING MODAL FUNCTIONALITY ---
        window.openRatingModal = function(productId) {
            document.getElementById('product_id').value = productId;
            const ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
            ratingModal.show();
        };
        
        const ratingForm = document.getElementById('ratingForm');
        if (ratingForm) {
            ratingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                const rating = formData.get('rating');
                if (!rating) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Silakan pilih rating terlebih dahulu.',
                        icon: 'error',
                        confirmButtonColor: '#2980b9'
                    });
                    return;
                }
                
                fetch('../actions/submit_rating.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const ratingModal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
                    ratingModal.hide();
                    
                    if (data.success) {
                        Swal.fire({
                            title: 'Sukses!',
                            text: 'Rating Anda telah berhasil disimpan.',
                            icon: 'success',
                            confirmButtonColor: '#2980b9'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Terjadi kesalahan saat menyimpan rating.',
                            icon: 'error',
                            confirmButtonColor: '#2980b9'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat menyimpan rating.',
                        icon: 'error',
                        confirmButtonColor: '#2980b9'
                    });
                });
            });
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            clearInterval(timeInterval);
        });

    </script>
    </body>
    </html>