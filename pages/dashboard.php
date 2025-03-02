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

// Close prepared statement
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    [data-theme="dark"] .navbar {
        background: var(--card-bg);
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    [data-theme="dark"] .product-card {
        background: var(--card-bg);
        border: 1px solid rgba(255,255,255,0.1);
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

    @media (max-width: 768px) {
        .mobile-nav {
            display: flex;
            justify-content: space-around;
        }

        body {
            padding-bottom: 70px;
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

    <!-- Products Grid -->
    <div class="container py-4">
        <h2 class="h4 mb-4">Produk Terbaru</h2>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-6 col-md-3">
                <div class="product-card" 
                     data-category="<?php echo htmlspecialchars($row['kategori']); ?>" 
                     data-condition="<?php echo htmlspecialchars($row['kondisi']); ?>"
                     data-product-id="<?php echo $row['id']; ?>">
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
                                <button class="btn-custom btn-outline btn-danger" 
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
        // Fungsi untuk format dua digit
        function padZero(num) {
            return num < 10 ? '0' + num : num;
        }

        // Fungsi untuk update waktu live
        function updateLiveTime() {
            const timeElement = document.getElementById('live-time');
            if (timeElement) {
                const baseTime = new Date('2025-03-02T17:25:13Z');
                const now = new Date();
                const timeDiff = now.getTime() - new Date().getTime();
                const currentTime = new Date(baseTime.getTime() + timeDiff);

                const year = currentTime.getUTCFullYear();
                const month = padZero(currentTime.getUTCMonth() + 1);
                const day = padZero(currentTime.getUTCDate());
                const hours = padZero(currentTime.getUTCHours());
                const minutes = padZero(currentTime.getUTCMinutes());
                const seconds = padZero(currentTime.getUTCSeconds());

                const formattedTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                timeElement.textContent = formattedTime;
            }
        }

        // Update waktu setiap detik
        setInterval(updateLiveTime, 1000);
        updateLiveTime();

        // Dark Mode Toggle
        function initDarkMode() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const htmlElement = document.documentElement;
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                htmlElement.setAttribute('data-theme', savedTheme);
                updateDarkModeButton(savedTheme === 'dark');
            } else if (prefersDarkScheme.matches) {
                htmlElement.setAttribute('data-theme', 'dark');
                updateDarkModeButton(true);
            }

            darkModeToggle.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateDarkModeButton(newTheme === 'dark');
            });

            function updateDarkModeButton(isDark) {
                const icon = darkModeToggle.querySelector('i');
                const text = darkModeToggle.querySelector('span');
                
                icon.classList.remove('fa-sun', 'fa-moon');
                icon.classList.add(isDark ? 'fa-sun' : 'fa-moon');
                text.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            }
        }

        // Initialize dark mode
        initDarkMode();

        // Fungsi untuk update tampilan stok
        function updateProductStock(productId, newStock) {
            const productCard = document.querySelector(`[data-product-id="${productId}"]`);
            if (productCard) {
                const stockCount = productCard.querySelector('.stock-count');
                const stockBadge = productCard.querySelector('.stock-badge');
                const buyButton = productCard.querySelector('.btn-custom.btn-primary');
                
                if (stockCount) {
                    stockCount.textContent = newStock;
                    stockCount.parentElement.classList.add('stock-update');
                    
                    setTimeout(() => {
                        stockCount.parentElement.classList.remove('stock-update');
                    }, 500);
                    
                    if (newStock > 0) {
                        stockBadge.classList.remove('out-of-stock');
                        stockBadge.classList.add('in-stock');
                        stockBadge.querySelector('i').classList.remove('fa-times-circle');
                        stockBadge.querySelector('i').classList.add('fa-check-circle');
                        
                        if (buyButton) {
                            buyButton.disabled = false;
                            buyButton.innerHTML = '<i class="fas fa-shopping-cart"></i><span>Beli Sekarang</span>';
                        }
                    } else {
                        stockBadge.classList.remove('in-stock');
                        stockBadge.classList.add('out-of-stock');
                        stockBadge.querySelector('i').classList.remove('fa-check-circle');
                        stockBadge.querySelector('i').classList.add('fa-times-circle');
                        
                        if (buyButton) {
                            buyButton.disabled = true;
                            buyButton.innerHTML = '<i class="fas fa-times"></i><span>Stok Habis</span>';
                        }
                    }
                }
            }
        }

        // Fungsi untuk cek update stok
        function checkStockUpdates() {
            fetch('../actions/check_stock_updates.php')
                .then(response => response.json())
                .then(data => {
                    if (data.updates) {
                        data.updates.forEach(update => {
                            updateProductStock(update.product_id, update.stock);
                        });
                    }
                })
                .catch(error => console.error('Error checking stock updates:', error));
        }

        // Cek update stok setiap 30 detik
        setInterval(checkStockUpdates, 30000);
        checkStockUpdates();

        // Rating form handling
        const ratingForm = document.getElementById('ratingForm');
        if (ratingForm) {
            ratingForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const rating = document.querySelector('input[name="rating"]:checked');
                if (!rating) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rating Diperlukan',
                        text: 'Silakan pilih rating terlebih dahulu'
                    });
                    return;
                }

                try {
                    const submitButton = this.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

                    const formData = new FormData(this);

                    const response = await fetch('../actions/submit_rating.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
                        modal.hide();
                        
                        await Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        
                        window.location.reload();
                    } else {
                        throw new Error(result.message || 'Gagal menyimpan rating');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: error.message || 'Terjadi kesalahan saat menyimpan rating'
                    });
                } finally {
                    const submitButton = this.querySelector('button[type="submit"]');
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Submit Rating';
                }
            });
        }

        // Delete product function
        window.deleteProduct = function(productId, productName) {
            Swal.fire({
                title: 'Hapus Produk?',
                text: `Apakah Anda yakin ingin menghapus "${productName}"? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const formData = new FormData();
                        formData.append('product_id', productId);
                        
                        const response = await fetch('../actions/delete_product.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        if (!response.ok) {
                            throw new Error('Terjadi kesalahan saat menghapus produk');
                        }
                        
                        const result = await response.json();
                        
                        if (!result.success) {
                            throw new Error(result.message || 'Gagal menghapus produk');
                        }
                        
                        return result;
                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.value.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                        if (productCard) {
                            productCard.closest('.col-6').remove();
                        }
                        window.location.reload();
                    });
                }
            });
        }

        // Open rating modal function
        window.openRatingModal = function(productId) {
            document.getElementById('ratingForm').reset();
            document.getElementById('product_id').value = productId;
            document.querySelectorAll('.stars label').forEach(star => {
                star.style.color = '#ddd';
            });
            const ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
            ratingModal.show();
        }

        // Star rating visual feedback
        const ratingStars = document.querySelectorAll('.stars label');
        ratingStars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const siblings = [...this.parentElement.children];
                const starValue = this.getAttribute('for').replace('rate', '');
                siblings.forEach(sibling => {
                    if (sibling.tagName === 'LABEL') {
                        sibling.style.color = sibling.getAttribute('for').replace('rate', '') <= starValue 
                            ? '#ffd700' 
                            : '#ddd';
                    }
                });
            });

            star.addEventListener('mouseout', function() {
                const checkedInput = document.querySelector('input[name="rating"]:checked');
                const siblings = [...this.parentElement.children];
                siblings.forEach(sibling => {
                    if (sibling.tagName === 'LABEL') {
                        sibling.style.color = checkedInput && sibling.getAttribute('for').replace('rate', '') <= checkedInput.value 
                            ? '#ffd700' 
                            : '#ddd';
                    }
                });
            });
        });

        // Handle search input
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value.trim();
                    if (searchTerm) {
                        window.location.href = `search.php?q=${encodeURIComponent(searchTerm)}`;
                    }
                }
            });
        }

        // Handle session timeout
        let sessionTimeout;
        function resetSessionTimeout() {
            clearTimeout(sessionTimeout);
            sessionTimeout = setTimeout(() => {
                Swal.fire({
                    title: 'Sesi Akan Berakhir',
                    text: 'Anda akan keluar dalam 1 menit. Ingin tetap di halaman ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tetap',
                    cancelButtonText: 'Keluar',
                    timer: 60000,
                    timerProgressBar: true
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer || result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = '../actions/logout.php?logout=true';
                    } else {
                        fetch('../actions/reset_session.php');
                    }
                });
            }, 29 * 60 * 1000); // 29 menit
        }

        // Initialize session timeout
        resetSessionTimeout();
        document.addEventListener('mousemove', resetSessionTimeout);
        document.addEventListener('keypress', resetSessionTimeout);

        // Handle offline/online status
        window.addEventListener('online', function() {
            Swal.fire({
                icon: 'success',
                title: 'Terhubung Kembali',
                text: 'Koneksi internet telah dipulihkan',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        });

        window.addEventListener('offline', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Koneksi',
                text: 'Periksa koneksi internet Anda',
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
        });
    });
    </script>
</body>
</html>
                