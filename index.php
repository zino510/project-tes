<!DOCTYPE html>
<html lang="id">
<head>

<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="icon" type="image/x-icon" href="assets/favicon.ico">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duomart - Future of Shopping</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #7c3aed;
            --accent: #06b6d4;
            --background: #0f172a;
            --text-light: #e2e8f0;
            --text-dark: #1e293b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--background), #1e293b);
            color: var(--text-light);
            overflow-x: hidden;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-light) !important;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 100px 0;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(37, 99, 235, 0.1), rgba(124, 58, 237, 0.1));
            z-index: -1;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleAnimation 2s ease-in-out infinite alternate;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Animated Button */
        .btn-custom {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 30px;
            padding: 1rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            color: white;
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--secondary), var(--accent));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-custom:hover::before {
            opacity: 1;
        }

        /* Info Section */
        .info-section {
            padding: 100px 0;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin: 50px 0;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .info-card i {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .info-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .info-card p {
            font-size: 1rem;
            color: var(--text-light);
            opacity: 0.8;
        }

        /* Animations */
        @keyframes titleAnimation {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(-5px);
            }
        }

        /* Floating Elements Animation */
        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            opacity: 0.1;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(400px, -400px) rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-element" style="width: 300px; height: 300px; top: 10%; left: -150px;"></div>
    <div class="floating-element" style="width: 200px; height: 200px; top: 60%; right: -100px;"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shopping-bag me-2"></i>
                Duomart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/register.php">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/dashboard.php">
                            <i class="fas fa-chart-line me-2"></i>Dashboard
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content text-center">
            <h1 class="hero-title">Welcome to DuoMart</h1>
            <p class="hero-subtitle">Discover a new way of buying and selling in our innovative marketplace</p>
            <a class="btn btn-custom btn-lg" href="pages/register.php">
                <span class="position-relative">
                    <i class="fas fa-rocket me-2"></i>
                    Start Your Journey
                </span>
            </a>
        </div>
    </section>

    <!-- Info Sections -->
    <div class="container">
        <div class="info-section">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="info-card">
                        <i class="fas fa-gift"></i>
                        <h3>Exclusive Benefits</h3>
                        <p>Join now and enjoy premium features, special deals, and a seamless shopping experience that puts you ahead of the curve.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card">
                        <i class="fas fa-cube"></i>
                        <h3>Diverse Categories</h3>
                        <p>Explore our vast selection of products across multiple categories, from cutting-edge technology to trending fashion items.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Secure Transactions</h3>
                        <p>Shop with confidence knowing your transactions are protected by state-of-the-art security systems and dedicated support.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>