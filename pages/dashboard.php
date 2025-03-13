<?php
session_start();

// Konfigurasi waktu dan user default
define('CURRENT_TIME_UTC', '2025-03-07 02:49:08');
define('DEFAULT_USERNAME', 'zino510');

// Include database configuration
require_once '../config/database.php';

// Fungsi untuk mendapatkan ikon kategori
function getIkonKategori($kategori) {
    $ikon = [
        'Elektronik' => 'fa-laptop',
        'Fashion' => 'fa-tshirt',
        'Kesehatan' => 'fa-heartbeat',
        'Makanan' => 'fa-utensils',
        'Perabotan' => 'fa-couch',
        'Olahraga' => 'fa-running',
        'Pendidikan' => 'fa-book',
        'Lainnya' => 'fa-box'
    ];
    
    return $ikon[$kategori] ?? 'fa-tag';
}

// Fungsi untuk sanitasi input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proteksi CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Token CSRF tidak valid');
    }
}

// Cek koneksi database dengan error handling yang lebih baik
try {
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
} catch (Exception $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Prepare statement untuk data user dengan error handling
try {
    $stmt = $conn->prepare("SELECT id, username, nama, email, foto, created_at FROM user WHERE id = ?");
    if (!$stmt) {
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $result_user = $stmt->get_result();
    $user_data = $result_user->fetch_assoc();

    // Set nilai default jika data pengguna tidak ditemukan
    $user_login = $user_data['username'] ?? DEFAULT_USERNAME;

} catch (Exception $e) {
    die("Error saat mengambil data pengguna: " . $e->getMessage());
}

// Set timezone dan konversi waktu
date_default_timezone_set('Asia/Jakarta');

try {
    $datetime_utc = new DateTime(CURRENT_TIME_UTC, new DateTimeZone('UTC'));
    $datetime_jakarta = clone $datetime_utc;
    $datetime_jakarta->setTimezone(new DateTimeZone('Asia/Jakarta'));
    $current_time_jakarta = $datetime_jakarta->format('Y-m-d H:i:s');
} catch (Exception $e) {
    die("Error saat mengatur waktu: " . $e->getMessage());
}

// Header kontrol cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Query untuk produk dengan prepared statement
// Query untuk produk dengan rating
try {
    $query_produk = $conn->prepare("
        SELECT 
            p.*, 
            u.username as nama_penjual,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(DISTINCT r.id) as total_ratings,  -- Menghitung jumlah rating
            GROUP_CONCAT(DISTINCT r.review) as reviews -- Mengumpulkan semua review
        FROM product p 
        LEFT JOIN user u ON p.user_id = u.id 
        LEFT JOIN ratings r ON p.id = r.product_id
        GROUP BY p.id
        ORDER BY p.created_at DESC 
        LIMIT 12
    ");

    if (!$query_produk->execute()) {
        throw new Exception($query_produk->error);
    }

    $result = $query_produk->get_result();

} catch (Exception $e) {
    die("Error saat mengambil data produk: " . $e->getMessage());
}

// Query untuk kategori dengan prepared statement
try {
    $query_kategori = $conn->prepare("SELECT DISTINCT kategori FROM product ORDER BY kategori");
    
    if (!$query_kategori->execute()) {
        throw new Exception($query_kategori->error);
    }

    $result_kategori = $query_kategori->get_result();
    $kategori = [];
    while ($kat = $result_kategori->fetch_assoc()) {
        $kategori[] = $kat['kategori'];
    }

} catch (Exception $e) {
    die("Error saat mengambil data kategori: " . $e->getMessage());
}

// Tutup prepared statements
if (isset($stmt)) $stmt->close();
if (isset($query_produk)) $query_produk->close();
if (isset($query_kategori)) $query_kategori->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2980b9">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <title>Beranda - Duo Mart</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">

    <link rel="manifest" href="manifest.json">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
    :root {
        --primary: #2980b9;
        --secondary: #3498db;
        --accent: #e74c3c;
        --success: #2ecc71;
        --warning: #f1c40f;
        --dark: #2c3e50;
        --light: #ecf0f1;
        --white: #ffffff;
        
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
        --shadow-lg: 0 8px 16px rgba(0,0,0,0.15);
        
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
        padding-bottom: calc(60px + env(safe-area-inset-bottom));
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

    /* Search Input */
    .search-input {
        border-radius: var(--radius-full);
        padding: 0.5rem 1rem;
        padding-right: 2.5rem;
        border: 1px solid var(--light);
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.1);
    }

    /* Filter Section */
    .filter-section {
        background: var(--white);
        padding: 1.5rem;
        margin: 1rem 0;
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
        border-bottom: 2px solid var(--light);
        padding-bottom: 0.5rem;
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

    /* Product Grid */
    #container-produk {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        padding: 1rem 0;
    }

    .item-produk {
        transition: all 0.3s ease;
    }

    /* Product Card */
    .kartu-produk {
        background: var(--white);
        border-radius: var(--radius-md);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: var(--shadow-sm);
    }

    .kartu-produk:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    /* Product Image */
    .container-gambar-produk {
        position: relative;
        padding-top: 100%;
        overflow: hidden;
    }

    .gambar-produk {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .kartu-produk:hover .gambar-produk {
        transform: scale(1.1);
    }

    /* Product Info */
    .info-produk {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-grow: 1;
    }

    .judul-produk {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .info-penjual {
        font-size: 0.9rem;
        color: var(--primary);
    }

    .harga-produk {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
    }

    /* Stock Badge */
    .info-stok {
        padding: 0.5rem;
        border-radius: var(--radius-sm);
        background-color: var(--light);
    }

    .badge-stok {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.875rem;
        font-weight: 500;
    }

    .badge-stok.tersedia {
        background-color: rgba(46, 204, 113, 0.1);
        color: var(--success);
    }

    .badge-stok.habis {
        background-color: rgba(231, 76, 60, 0.1);
        color: var(--accent);
    }

    /* Buttons */
    .button-container {
        display: flex;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 1rem;
    }

    .btn-custom {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-weight: 500;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        flex: 1;
    }

    .btn-custom:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-primary {
        background-color: var(--primary);
        color: var(--white);
    }

    .btn-primary:hover {
        background-color: var(--secondary);
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid var(--primary);
        color: var(--primary);
    }

    .btn-outline:hover {
        background-color: var(--primary);
        color: var(--white);
    }

    /* Mobile Navigation */
    .nav-mobile {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--white);
        padding: 0.75rem 1rem;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
    }

    @media (max-width: 768px) {
        .nav-mobile {
            display: flex;
            justify-content: space-around;
        }

        body {
            padding-bottom: 70px;
        }

        #container-produk {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
    }

    @media (max-width: 576px) {
        #container-produk {
            grid-template-columns: 1fr;
        }

        .button-container {
            flex-direction: column;
        }

        .btn-custom {
            width: 100%;
        }
    }

    /* Dark Mode */
    [data-theme="dark"] {
        --primary: #3498db;
        --secondary: #2980b9;
        --dark: #ecf0f1;
        --light: #2c3e50;
        --white: #1a1a1a;
        --bg-color: #121212;
    }

    [data-theme="dark"] body {
        background: var(--bg-color);
        color: var(--dark);
    }

    [data-theme="dark"] .navbar,
    [data-theme="dark"] .kartu-produk,
    [data-theme="dark"] .nav-mobile {
        background: var(--white);
        border: 1px solid rgba(255,255,255,0.1);
    }

    /* Rating Styles */
.rating-info {
    margin: 0.5rem 0;
    padding: 0.5rem;
    background-color: var(--light);
    border-radius: var(--radius-sm);
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.rating-stars {
    display: inline-flex;
    gap: 0.25rem;
}

.rating-stars .fa-star {
    color: #ddd;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.rating-stars .fa-star.active {
    color: #f1c40f;
}

.rating-text {
    font-size: 0.85rem;
    color: var(--dark);
}

.rating-text small {
    opacity: 0.8;
}

[data-theme="dark"] .rating-info {
    background-color: var(--light);
}

[data-theme="dark"] .rating-text {
    color: var(--white);
}
    </style>
</head>
<body>
    <!-- Navigasi Mobile -->
    <div class="nav-mobile d-md-none">
        <a href="../index.php" class="item-nav active">
            <i class="fas fa-home"></i>
            <span>Beranda</span>
        </a>
        <a href="my_product.php" class="item-nav">
            <i class="fas fa-box"></i>
            <span>Produk</span>
        </a>
        <a href="post_barang.php" class="item-nav">
            <i class="fas fa-plus-circle"></i>
            <span>Tambah</span>
        </a>
        <a href="purchases.php" class="item-nav">
            <i class="fas fa-shopping-bag"></i>
            <span>Pesanan</span>
        </a>
        <a href="profile.php" class="item-nav">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
    </div>

    <!-- Navbar -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                <img src="../Desain_tanpa_judul-removebg-preview.png" alt="Duo Mart Logo" style="height: 40px;">
                Duo Mart
                </a>

            <!-- Search Bar Desktop -->
            <div class="d-none d-lg-block mx-auto" style="min-width: 300px;">
                <div class="position-relative">
                    <input type="text" class="form-control search-input" 
                           placeholder="Cari produk..." 
                           aria-label="Cari produk">
                    <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                </div>
            </div>

            <!-- Menu Desktop -->
            <div class="d-none d-lg-flex align-items-center gap-3">
                <button id="tombolModeGelap" class="btn-custom btn-outline">
                    <i class="fas fa-moon"></i>
                    <span class="d-none d-xl-inline ms-1">Mode Gelap</span>
                </button>

                <div class="dropdown">
                    <button class="btn-custom btn-primary dropdown-toggle" type="button" 
                            id="dropdownMenuButton" data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user_login); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li>
                            <a class="dropdown-item" href="my_products.php">
                                <i class="fas fa-box me-2"></i>Produk Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="post_barang.php">
                                <i class="fas fa-plus-circle me-2"></i>Tambah Produk
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="purchases.php">
                                <i class="fas fa-shopping-bag me-2"></i>Pesanan
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-cog me-2"></i>Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="../actions/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Bar Mobile -->
    <div class="container d-lg-none mt-3">
        <div class="position-relative">
            <input type="text" class="form-control search-input" 
                   placeholder="Cari produk..." 
                   aria-label="Cari produk">
            <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="container mt-4">
        <div class="filter-section">
            <div class="filter-container">
                <!-- Filter Kategori -->
                <div class="filter-group">
                    <div class="filter-label">
                        <i class="fas fa-tag"></i>
                        <span>Kategori</span>
                    </div>
                    <div class="filter-options kategori-filter">
    <div class="filter-option active" data-filter="semua">
        <i class="fas fa-th"></i>
        <span>Semua</span>
    </div>
                        <?php foreach ($kategori as $kat): ?>
                        <div class="filter-option" data-filter="<?php echo htmlspecialchars($kat); ?>">
                            <i class="fas <?php echo getIkonKategori($kat); ?>"></i>
                            <span><?php echo htmlspecialchars($kat); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filter Kondisi -->
                <div class="filter-group">
                    <div class="filter-label">
                        <i class="fas fa-star"></i>
                        <span>Kondisi</span>
                    </div>
                    <div class="filter-options kondisi-filter">
                        <div class="filter-option active" data-filter="semua">
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
            </div>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="container py-4">
        <h2 class="h4 mb-4">Produk Terbaru</h2>
        <div id="container-produk">
            <?php if($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <div class="item-produk" 
                     data-kategori="<?php echo htmlspecialchars($row['kategori']); ?>" 
                     data-kondisi="<?php echo htmlspecialchars($row['kondisi']); ?>"
                     data-id-produk="<?php echo $row['id']; ?>">
                    <div class="kartu-produk">
                        <div class="container-gambar-produk">
                            <img src="../<?php echo $row['gambar']; ?>" 
                                 alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                                 class="gambar-produk"
                                 loading="lazy"
                                 decoding="async">
                        </div>
                        <div class="info-produk">
                            <h3 class="judul-produk">
                                <?php echo htmlspecialchars($row['nama_produk']); ?>
                            </h3>
                            <div class="info-penjual">
                                <i class="fas fa-store me-1"></i>
                                <span><?php echo htmlspecialchars($row['nama_penjual']); ?></span>
                            </div>
                            <div class="harga-produk">
                                Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                            </div>
                            
                            <!-- Rating Info -->
<div class="rating-info">
    <div class="rating-stars">
        <?php
        $avg_rating = round($row['avg_rating'], 1);
        for($i = 1; $i <= 5; $i++): 
        ?>
            <i class="fas fa-star <?php echo $i <= $avg_rating ? 'active' : ''; ?>"></i>
        <?php endfor; ?>
    </div>
    <span class="rating-text">
        <?php if($row['total_ratings'] > 0): ?>
            <?php echo $avg_rating; ?> 
            <small>(<?php echo $row['total_ratings']; ?> ulasan)</small>
        <?php else: ?>
            <small>Belum ada ulasan</small>
        <?php endif; ?>
    </span>
</div>

                            <!-- Informasi Stok -->
                            <div class="info-stok">
                                <span class="badge-stok <?php echo $row['stock'] > 0 ? 'tersedia' : 'habis'; ?>">
                                    <i class="fas <?php echo $row['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                    Stok: <?php echo $row['stock']; ?>
                                </span>
                            </div>

                            <!-- Container Tombol -->
                            <div class="button-container">
                                <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                                    <!-- Tombol Pemilik -->
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" 
                                       class="btn-custom btn-primary">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </a>
                                    <button type="button" 
                                            class="btn-custom btn-outline"
                                            onclick="hapusProduk(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                        <span>Hapus</span>
                                    </button>
                                <?php else: ?>
                                    <!-- Tombol Pembeli -->
                                    <?php if($row['stock'] > 0): ?>
                                        <a href="checkout.php?id=<?php echo $row['id']; ?>" 
                                           class="btn-custom btn-primary">
                                            <i class="fas fa-shopping-cart"></i>
                                            <span>Beli</span>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn-custom btn-outline" disabled>
                                            <i class="fas fa-times"></i>
                                            <span>Stok Habis</span>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <a href="detail_produk.php?id=<?php echo $row['id']; ?>" 
                                   class="btn-custom btn-outline">
                                    <i class="fas fa-eye"></i>
                                    <span>Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada produk yang tersedia.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Token CSRF
    const tokenCSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const containerProduk = document.getElementById('container-produk');
    
    // Fungsi update rating produk
    function updateProductRating(productId, avgRating, totalRatings) {
        const productCard = document.querySelector(`.item-produk[data-id-produk="${productId}"]`);
        if (productCard) {
            const stars = productCard.querySelectorAll('.rating-stars .fa-star');
            const ratingText = productCard.querySelector('.rating-text');
            
            // Update bintang
            stars.forEach((star, index) => {
                if (index < Math.floor(avgRating)) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
            
            // Update teks rating
            ratingText.innerHTML = `
                ${avgRating.toFixed(1)} 
                <small>(${totalRatings} ulasan)</small>
            `;
        }
    }

    // Event listener untuk update rating
    window.addEventListener('message', function(event) {
        if (event.data.type === 'ratingUpdate') {
            updateProductRating(
                event.data.productId,
                event.data.avgRating,
                event.data.totalRatings
            );
        }
    });

    // Filter Elements
    const filterKategori = document.querySelectorAll('.kategori-filter .filter-option');
    const filterKondisi = document.querySelectorAll('.kondisi-filter .filter-option');
    const inputPencarian = document.querySelectorAll('.search-input');
    
    // Filter State
    let filterAktif = {
        kategori: 'semua',
        kondisi: 'semua',
        pencarian: ''
    };
    
    // Fungsi untuk menerapkan filter
    function terapkanFilter() {
        const semuaProduk = containerProduk.querySelectorAll('.item-produk');
        const kataKunci = filterAktif.pencarian.toLowerCase().trim();
        let adaProdukTerlihat = false;
        
        semuaProduk.forEach(produk => {
            const kategoriProduk = produk.dataset.kategori;
            const kondisiProduk = produk.dataset.kondisi;
            const judulProduk = produk.querySelector('.judul-produk').textContent.toLowerCase();
            
            const cocokKategori = filterAktif.kategori === 'semua' || kategoriProduk === filterAktif.kategori;
            const cocokKondisi = filterAktif.kondisi === 'semua' || kondisiProduk === filterAktif.kondisi;
            const cocokPencarian = kataKunci === '' || judulProduk.includes(kataKunci);
            
            if (cocokKategori && cocokKondisi && cocokPencarian) {
                produk.style.display = 'block';
                adaProdukTerlihat = true;
            } else {
                produk.style.display = 'none';
            }
        });

        // Update tampilan jika tidak ada produk
        const pesanKosong = containerProduk.querySelector('.pesan-kosong');
        if (!adaProdukTerlihat) {
            if (!pesanKosong) {
                containerProduk.insertAdjacentHTML('beforeend', `
                    <div class="col-12 text-center py-5 pesan-kosong">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Tidak ada produk yang sesuai dengan filter yang dipilih.
                        </div>
                    </div>
                `);
            }
        } else if (pesanKosong) {
            pesanKosong.remove();
        }
    }
    
    // Event Listeners untuk Filter
    filterKategori.forEach(filter => {
        filter.addEventListener('click', function(e) {
            e.preventDefault();
            filterKategori.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            filterAktif.kategori = this.getAttribute('data-filter');
            terapkanFilter();
        });
    });
    
    filterKondisi.forEach(filter => {
        filter.addEventListener('click', function(e) {
            e.preventDefault();
            filterKondisi.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            filterAktif.kondisi = this.getAttribute('data-filter');
            terapkanFilter();
        });
    });
    
    // Pencarian dengan Debounce
    let timeoutPencarian;
    inputPencarian.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(timeoutPencarian);
            timeoutPencarian = setTimeout(() => {
                filterAktif.pencarian = this.value;
                terapkanFilter();
            }, 300);
        });
    });

    // Fungsi Hapus Produk
    window.hapusProduk = function(idProduk, namaProduk) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Anda yakin ingin menghapus produk "${namaProduk}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#3498db',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', idProduk);
                formData.append('csrf_token', tokenCSRF);

                fetch('../actions/delete_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const elemenProduk = document.querySelector(
                            `.item-produk[data-id-produk="${data.product_id}"]`
                        );
                        if (elemenProduk) {
                            elemenProduk.remove();
                            terapkanFilter();
                            
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message || 'Produk berhasil dihapus',
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
                        confirmButtonColor: '#3498db'
                    });
                });
            }
        });
    };

    // Mode Gelap
    const tombolModeGelap = document.getElementById('tombolModeGelap');
    const html = document.documentElement;
    
    if (localStorage.getItem('modeGelap') === 'aktif') {
        html.setAttribute('data-theme', 'dark');
        updateTombolModeGelap(true);
    }
    
    function updateTombolModeGelap(isGelap) {
        if (tombolModeGelap) {
            const icon = tombolModeGelap.querySelector('i');
            const text = tombolModeGelap.querySelector('span');
            
            icon.className = isGelap ? 'fas fa-sun' : 'fas fa-moon';
            if (text) text.textContent = isGelap ? 'Mode Terang' : 'Mode Gelap';
        }
    }
    
    if (tombolModeGelap) {
        tombolModeGelap.addEventListener('click', function() {
            const isGelap = html.getAttribute('data-theme') === 'dark';
            html.setAttribute('data-theme', isGelap ? '' : 'dark');
            localStorage.setItem('modeGelap', isGelap ? 'nonaktif' : 'aktif');
            updateTombolModeGelap(!isGelap);
        });
    }

    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // Inisialisasi filter saat halaman dimuat
    terapkanFilter();
});
</script>
</body>
</html> 