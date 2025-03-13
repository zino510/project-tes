<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Set zona waktu WIB (GMT+7)

// Data waktu yang diberikan
$utc_time = '2025-03-08 13:41:40';  // Waktu UTC terbaru
$timestamp = strtotime($utc_time);
$wib_time = date('Y-m-d H:i:s', strtotime('+7 hours', $timestamp)); // Konversi ke WIB

// Konfigurasi default
define('CURRENT_TIME_UTC', $utc_time);
define('CURRENT_TIME_WIB', $wib_time);
define('DEFAULT_USERNAME', 'zino510'); // Username yang sedang login

$current_time = $wib_time; // Menggunakan waktu WIB
$current_user = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : 'Guest';

// Include database configuration
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DuoMart - Platform e-commerce modern untuk pengalaman belanja online terbaik">
    <meta name="keywords" content="duomart, belanja online, e-commerce, toko online">
    <title>DuoMart - Future of Shopping</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <link rel="shortcut icon" href="favicon/favicon.ico" type="image/x-icon">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
    /* Variabel Warna dan Tema */
    :root {
        --primary: #3b82f6;
        --secondary: #8b5cf6;
        --accent: #06b6d4;
        --background: #0f172a;
        --text-light: #f8fafc;
        --text-dark: #1e293b;
        --gradient: linear-gradient(45deg, var(--primary), var(--secondary));
        --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* Reset dan Style Dasar */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--background);
        color: var(--text-light);
        overflow-x: hidden;
        padding-top: 60px;
        line-height: 1.6;
    }

    /* Komponen Waktu WIB */
    .time-display {
        position: fixed;
        top: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-size: 0.9rem;
        z-index: 1000;
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: var(--shadow);
    }

    .time-display i {
        color: var(--primary);
        font-size: 1.1rem;
    }

    /* Navbar Modern */
    .navbar {
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1rem 0;
        transition: all 0.3s ease;
    }

    .navbar.scrolled {
        padding: 0.5rem 0;
        box-shadow: var(--shadow);
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-light) !important;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .navbar-brand i {
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-link {
        color: var(--text-light) !important;
        opacity: 0.8;
        transition: all 0.3s ease;
        padding: 0.5rem 1rem !important;
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: var(--gradient);
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .nav-link:hover::after {
        width: 100%;
    }

    /* Hero Section */
    .hero-section {
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        padding: 120px 0;
        background: linear-gradient(135deg, 
            rgba(15, 23, 42, 0.9),
            rgba(15, 23, 42, 0.95)
        );
    }

    .hero-title {
        font-size: 4.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        line-height: 1.2;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: titleFadeIn 1s ease-out;
    }

    @keyframes titleFadeIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Button Styles */
    .btn-gradient {
        background: var(--gradient);
        border: none;
        color: white;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .btn-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent, 
            rgba(255, 255, 255, 0.2), 
            transparent
        );
        transition: 0.5s;
    }

    .btn-gradient:hover::before {
        left: 100%;
    }

    /* Feature Cards - Fixed Spacing */
.feature-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2.5rem;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
    margin-bottom: 60px; /* Increased bottom margin to prevent overlap */
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--gradient);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.feature-card:hover::before {
    transform: scaleX(1);
}

.feature-card:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.08);
}

.feature-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    background: var(--gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.feature-icon i {
    font-size: 2rem;
    color: white;
}

/* Added container spacing for features section */
.features-section {
    padding: 100px 0 160px 0; /* Increased bottom padding */
    margin-bottom: 80px; /* Added margin bottom */
}

/* Added container spacing for about section */
.about-section {
    padding: 100px 0;
    margin-top: 80px; /* Added margin top */
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .feature-card {
        margin-bottom: 40px;
    }
    
    .features-section {
        padding: 80px 0 120px 0;
        margin-bottom: 60px;
    }
    
    .about-section {
        margin-top: 60px;
    }
}

@media (max-width: 768px) {
    .feature-card {
        margin-bottom: 30px;
    }
    
    .features-section {
        padding: 60px 0 100px 0;
        margin-bottom: 40px;
    }
    
    .about-section {
        margin-top: 40px;
    }
}
        /* Rating Section Styles */
        .rating-section {
        padding: 6rem 0;
        background: rgba(255, 255, 255, 0.02);
        position: relative;
        overflow: hidden;
    }

    .rating-container {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 3rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced Rating Form */
    .rating-form {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .rating-form:hover {
        background: rgba(255, 255, 255, 0.05);
        transform: translateY(-5px);
    }

    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        font-size: 2.5rem;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label {
        color: #ffd700;
        transform: scale(1.1);
    }

    /* Stats Cards Animation */
    .stats-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stats-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--gradient);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .stats-card:hover::after {
        transform: scaleX(1);
    }

    .stats-number {
        font-size: 3rem;
        font-weight: 700;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    /* Review List Enhancements */
    .review {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .review::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background: var(--gradient);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .review:hover::before {
        transform: scaleY(1);
    }

    .review:hover {
        transform: translateX(10px);
        background: rgba(255, 255, 255, 0.05);
    }

    /* Toast Notification Enhancement */
    .toast-notification {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 1rem 2rem;
        color: var(--text-light);
        z-index: 1000;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .toast-notification.show {
        transform: translateY(0);
        opacity: 1;
    }

    .toast-notification.success {
        border-left: 4px solid #10B981;
    }

    .toast-notification.error {
        border-left: 4px solid #EF4444;
    }

    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .hero-title {
            font-size: 3.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
        }
        
        .stats-card {
            margin-bottom: 1.5rem;
        }

        .feature-card {
            margin-bottom: 2rem;
        }
    }

    @media (max-width: 768px) {
        .hero-section {
            padding: 80px 0;
        }
        
        .hero-title {
            font-size: 2.75rem;
        }
        
        .hero-buttons {
            flex-direction: column;
            gap: 1rem;
        }

        .hero-buttons .btn {
            width: 100%;
        }

        .rating-container {
            padding: 1.5rem;
        }

        .star-rating label {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .time-display {
            display: none;
        }

        .navbar-brand {
            font-size: 1.25rem;
        }

        .rating-form {
            padding: 1.5rem;
        }

        .review {
            padding: 1.5rem;
        }
    }

    /* Loading Animation */
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .loading {
        background: linear-gradient(90deg,
            rgba(255, 255, 255, 0.03) 25%,
            rgba(255, 255, 255, 0.08) 50%,
            rgba(255, 255, 255, 0.03) 75%
        );
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }

    /* Tambahkan atau update CSS berikut di dalam tag <style> */

/* Mobile Responsive Enhancements */
@media (max-width: 991px) {
    body {
        padding-top: 76px; /* Sesuaikan padding top untuk mobile */
    }

    /* Hero Section Mobile */
    .hero-section {
        padding: 60px 0;
        text-align: center;
    }

    .hero-title {
        font-size: 2.8rem;
        line-height: 1.3;
        margin-bottom: 1rem;
    }

    .hero-subtitle {
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .hero-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 0 1rem;
    }

    .hero-image {
        margin-top: 3rem;
        padding: 0 1rem;
    }

    /* Features Section Mobile */
    .features-section {
        padding: 60px 0;
    }

    .feature-card {
        margin-bottom: 30px;
        padding: 2rem;
    }

    .feature-icon {
        width: 60px;
        height: 60px;
    }

    .feature-icon i {
        font-size: 1.5rem;
    }

    /* About Section Mobile */
    .about-section {
        padding: 60px 0;
        text-align: center;
    }

    .about-content {
        margin-bottom: 3rem;
    }

    .stats-card {
        margin-bottom: 2rem;
    }

    .stats-number {
        font-size: 2.5rem;
    }

    /* Reviews Section Mobile */
    .rating-section {
        padding: 60px 0;
    }

    .rating-container {
        padding: 1.5rem;
    }

    .rating-form {
        padding: 1.5rem;
    }

    .star-rating label {
        font-size: 2rem;
    }

    /* Navbar Mobile */
    .navbar {
        padding: 0.5rem 0;
    }

    .navbar-brand {
        font-size: 1.3rem;
    }

    .navbar-toggler {
        border: none;
        padding: 0.5rem;
        color: var(--text-light);
    }

    .navbar-collapse {
        background: rgba(15, 23, 42, 0.98);
        padding: 1rem;
        border-radius: 10px;
        margin-top: 0.5rem;
    }

    .nav-link {
        padding: 0.8rem 1rem !important;
        text-align: center;
    }

    /* Button Mobile */
    .btn-gradient {
        width: 100%;
        padding: 0.8rem 1.5rem;
        font-size: 1rem;
    }
}

@media (max-width: 768px) {
    /* Additional adjustments for smaller devices */
    .hero-title {
        font-size: 2.3rem;
    }

    .section-header h2 {
        font-size: 1.8rem;
    }

    .section-header p {
        font-size: 1rem;
    }

    .review {
        padding: 1.2rem;
    }

    /* Improve spacing */
    .container {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    /* Stats cards in single column */
    .stats-card {
        max-width: 280px;
        margin-left: auto;
        margin-right: auto;
    }
}

@media (max-width: 576px) {
    /* Adjustments for extra small devices */
    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .feature-card h3 {
        font-size: 1.3rem;
    }

    .section-header {
        margin-bottom: 2rem;
    }

    /* Toast position adjustment */
    .toast-notification {
        left: 1rem;
        right: 1rem;
        bottom: 1rem;
        width: auto;
    }

    /* Rating form adjustments */
    .star-rating label {
        font-size: 1.8rem;
    }

    .rating-form h4 {
        font-size: 1.2rem;
    }
}

/* Additional helper classes */
.text-center-mobile {
    text-align: center;
}

.hide-mobile {
    display: none;
}

@media (min-width: 992px) {
    .text-center-mobile {
        text-align: left;
    }

    .hide-mobile {
        display: block;
    }
}

/* Fix for sticky navbar on iOS */
@supports (-webkit-overflow-scrolling: touch) {
    .navbar.fixed-top {
        position: sticky;
    }
    
    body {
        -webkit-overflow-scrolling: touch;
    }
}
</style>
</head>
<body>
    <!-- Time Display -->
    <div class="time-display">
        <i class="fas fa-clock"></i>
        <span id="live-time"><?php echo $wib_time; ?></span>
    </div>

    <!-- Animated Background -->
    <div class="animated-background">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Enhanced Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#" data-aos="fade-right">
                <i class="fas fa-shopping-bag"></i>
                DuoMart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reviews">Ulasan</a>
                    </li>
                    <?php if($current_user === 'Guest'): ?>
<li class="nav-item">
    <a class="nav-link" href="pages/login.php">Masuk</a>  <!-- Updated path -->
</li>
<li class="nav-item">
    <a class="btn btn-gradient ms-2" href="pages/register.php">Daftar</a>  <!-- Updated path -->
</li>
<?php else: ?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-user-circle me-1"></i>
        <?php echo htmlspecialchars($current_user); ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="pages/profile.php">Profil</a></li>
        <li><a class="dropdown-item" href="pages/orders.php">Pesanan</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="actions/logout.php">Keluar</a></li>
    </ul>
</li>
<?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="hero-content">
                        <h1 class="hero-title">Selamat Datang di DuoMart</h1>
                        <p class="hero-subtitle">Pengalaman belanja online terbaik dengan inovasi teknologi modern</p>
                        <div class="hero-buttons">
                            <a href="#features" class="btn btn-gradient">Jelajahi Fitur</a>
                            <a href="#about" class="btn btn-outline">Pelajari Lebih Lanjut</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="400">
    <div class="hero-image">
        <img src=" https://img.freepik.com/free-vector/online-shopping-banner-mobile-app-templates-concept-flat-design_1150-34865.jpg" 
             alt="DuoMart Shopping Experience" 
             class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Mengapa Memilih DuoMart?</h2>
                <p>Temukan keunggulan berbelanja bersama kami</p>
            </div>
            <div class="row">
                <!-- Feature 1: Fast Delivery -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3>Pengiriman Cepat</h3>
                        <p>Barang sampai dengan aman dan cepat ke lokasi Anda</p>
                    </div>
                </div>
                <!-- Feature 2: Secure Shopping -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Belanja Aman</h3>
                        <p>Transaksi terjamin dengan sistem keamanan terbaik</p>
                    </div>
                </div>
                <!-- Feature 3: Best Deals -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3>Penawaran Terbaik</h3>
                        <p>Dapatkan diskon dan promo menarik setiap hari</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

     <!-- About Section -->
     <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="about-content">
                        <h2 class="section-title">Tentang DuoMart</h2>
                        <p class="mb-4">
                            DuoMart adalah platform e-commerce terpercaya yang menghadirkan pengalaman berbelanja online terbaik. 
                            Dengan berbagai pilihan produk berkualitas dari penjual terpercaya, kami berkomitmen memberikan 
                            layanan terbaik dengan transaksi yang aman dan pengiriman yang andal.
                        </p>
                        
                        <!-- Statistics Cards -->
                        <div class="row">
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                                <div class="stats-card">
                                    <div class="stats-number" data-count="10000">10K+</div>
                                    <div class="stats-label">Produk</div>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                                <div class="stats-card">
                                    <div class="stats-number" data-count="5000">5K+</div>
                                    <div class="stats-label">Pelanggan</div>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                                <div class="stats-card">
                                    <div class="stats-number">99%</div>
                                    <div class="stats-label">Kepuasan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
    <div class="about-image">
        <img src="https://img.freepik.com/free-vector/business-team-discussing-ideas-startup_74855-4380.jpg" 
             alt="DuoMart Stats" 
             class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="rating-section" id="reviews">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Ulasan Pengguna</h2>
                <p>Apa kata mereka tentang DuoMart?</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="rating-container">
                        <?php if($current_user !== 'Guest'): ?>
                        <!-- Review Form -->
                        <div class="rating-form" data-aos="fade-up">
                            <h4 class="mb-3">Bagikan Pengalaman Anda</h4>
                            <form id="ratingForm" action="actions/submit_rating.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Rating Anda</label>
                                    <div class="star-rating">
                                        <?php for($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                                        <label for="star<?php echo $i; ?>">
                                            <i class="fas fa-star"></i>
                                        </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ulasan Anda</label>
                                    <textarea class="form-control" name="review" rows="3" 
                                        placeholder="Bagikan pengalaman berbelanja Anda di DuoMart..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-gradient">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Ulasan
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <!-- Rating Summary -->
                        <?php
                        // Get average rating and total reviews
                        $stats_query = "SELECT 
                            ROUND(AVG(rating), 1) as avg_rating,
                            COUNT(*) as total_reviews,
                            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                            FROM ratings";
                        
                        $stats_result = $conn->query($stats_query);
                        $stats = $stats_result->fetch_assoc();
                        
                        $avg_rating = $stats['avg_rating'] ?? 0;
                        $total_reviews = $stats['total_reviews'] ?? 0;
                        ?>

                        <div class="rating-summary" data-aos="fade-up">
                            <div class="rating-average">
                                <h3><?php echo number_format($avg_rating, 1); ?></h3>
                                <div class="rating-stars">
                                    <?php
                                    $full_stars = floor($avg_rating);
                                    $half_star = $avg_rating - $full_stars > 0.4;
                                    
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $full_stars) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i == $full_stars + 1 && $half_star) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <p class="text-muted">Berdasarkan <?php echo $total_reviews; ?> ulasan</p>
                            </div>

                            <!-- Rating Distribution -->
                            <div class="rating-distribution">
                                <?php for ($i = 5; $i >= 1; $i--): 
                                    $star_count = $stats["{$i}_star"] ?? 0;
                                    $percentage = $total_reviews > 0 ? 
                                        ($star_count / $total_reviews) * 100 : 0;
                                ?>
                                <div class="rating-bar">
                                    <span class="rating-label"><?php echo $i; ?> bintang</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="rating-count"><?php echo $star_count; ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div class="reviews-section">
                            <?php
                            // Pagination setup
                            $reviews_per_page = 5;
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $offset = ($page - 1) * $reviews_per_page;

                            // Get reviews with user info
                            $reviews_query = "SELECT 
                                r.*, 
                                u.username,
                                u.foto as user_avatar
                                FROM ratings r
                                LEFT JOIN user u ON r.user_id = u.id
                                ORDER BY r.created_at DESC
                                LIMIT ? OFFSET ?";

                            $stmt = $conn->prepare($reviews_query);
                            $stmt->bind_param("ii", $reviews_per_page, $offset);
                            $stmt->execute();
                            $reviews = $stmt->get_result();
                            ?>

                            <?php if ($reviews->num_rows > 0): ?>
                                <?php while ($review = $reviews->fetch_assoc()): 
                                    $review_date = date('d M Y', strtotime($review['created_at']));
                                    $username = htmlspecialchars($review['username'] ?? 'Anonymous');
                                    $avatar_text = strtoupper(substr($username, 0, 1));
                                ?>
                                <div class="review" data-aos="fade-up">
                                    <div class="review-header">
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?php echo ($i <= $review['rating']) ? 'fas' : 'far'; ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="review-date"><?php echo $review_date; ?></span>
                                    </div>
                                    
                                    <?php if (!empty($review['review'])): ?>
                                    <div class="review-text">
                                        <?php echo htmlspecialchars($review['review']); ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="review-author">
                                        <div class="author-avatar">
                                            <?php echo $avatar_text; ?>
                                        </div>
                                        <span><?php echo $username; ?></span>
                                    </div>
                                </div>
                                <?php endwhile; ?>

                                <!-- Pagination -->
                                <?php if ($total_reviews > $reviews_per_page): ?>
                                <nav aria-label="Review pagination" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= ceil($total_reviews / $reviews_per_page); $i++): ?>
                                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>#reviews">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>

                            <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">Belum ada ulasan. Jadilah yang pertama memberi ulasan!</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Toast Notification -->
    <div class="toast-notification" id="toast">
        <div class="toast-content"></div>
    </div>

     <!-- Scripts -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Inisialisasi AOS (Animate On Scroll)
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Update Waktu WIB Real-time
            function updateWIBTime() {
                const now = new Date();
                // Menambah 7 jam untuk WIB
                now.setHours(now.getHours() + 7);
                
                // Format waktu WIB
                const options = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                };
                
                const timeStr = now.toLocaleString('id-ID', options)
                    .replace(',', '')
                    .replace(/\./g, ':');
                
                document.getElementById('live-time').textContent = timeStr;
            }

            // Update waktu setiap detik
            updateWIBTime();
            setInterval(updateWIBTime, 1000);

            // Animasi Rating Bars
            const ratingBars = document.querySelectorAll('.progress-bar');
            ratingBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });

            // Smooth Scroll untuk Navigasi
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Form Rating Handler
            const ratingForm = document.getElementById('ratingForm');
            if (ratingForm) {
                ratingForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    fetch('actions/submit_rating.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Terima kasih atas ulasan Anda!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            throw new Error(data.message || 'Gagal mengirim ulasan');
                        }
                    })
                    .catch(error => {
                        showToast(error.message, 'error');
                    });
                });
            }

            // Interaksi Rating Bintang
            const starLabels = document.querySelectorAll('.star-rating label');
            starLabels.forEach(label => {
                label.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('for').replace('star', '');
                    highlightStars(rating);
                });

                label.addEventListener('mouseout', function() {
                    const selectedRating = document.querySelector('input[name="rating"]:checked');
                    highlightStars(selectedRating ? selectedRating.value : 0);
                });
            });

            function highlightStars(rating) {
                starLabels.forEach(label => {
                    const starRating = label.getAttribute('for').replace('star', '');
                    if (starRating <= rating) {
                        label.style.color = '#ffd700';
                    } else {
                        label.style.color = '#666';
                    }
                });
            }

            // Fungsi Toast Notification
            window.showToast = function(message, type = 'success') {
                const toast = document.getElementById('toast');
                const toastContent = toast.querySelector('.toast-content');
                
                toastContent.textContent = message;
                toast.className = `toast-notification ${type} show`;
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            };

            // Navbar Scroll Effect
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Animasi Statistik
            const statsNumbers = document.querySelectorAll('.stats-number[data-count]');
            statsNumbers.forEach(number => {
                const target = parseInt(number.getAttribute('data-count'));
                let current = 0;
                const increment = target / 50; // Kecepatan animasi
                const updateNumber = () => {
                    if (current < target) {
                        current += increment;
                        number.textContent = Math.ceil(current).toLocaleString() + '+';
                        requestAnimationFrame(updateNumber);
                    } else {
                        number.textContent = target.toLocaleString() + '+';
                    }
                };
                const observer = new IntersectionObserver(
                    (entries) => {
                        if (entries[0].isIntersecting) {
                            updateNumber();
                            observer.unobserve(number);
                        }
                    },
                    { threshold: 0.5 }
                );
                observer.observe(number);
            });
        });
    </script>
</body>
</html>

<?php
// Membersihkan koneksi database
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>