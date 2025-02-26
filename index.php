<?php
session_start();
date_default_timezone_set('UTC');
$current_time = date('Y-m-d H:i:s');
$current_user = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : 'Guest';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
<script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DuoMart - Future of Shopping</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
    
    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #3b82f6;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --background: #0f172a;
            --text-light: #f8fafc;
            --text-dark: #1e293b;
        }

        /* Global Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-light);
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            filter: blur(100px);
            opacity: 0.15;
            animation: floatAnimation 20s infinite ease-in-out;
        }

        .particle:nth-child(1) {
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            top: 50%;
            right: -150px;
            animation-delay: -5s;
        }

        .particle:nth-child(3) {
            bottom: -150px;
            left: 50%;
            animation-delay: -10s;
        }

        @keyframes floatAnimation {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(100px, 100px) rotate(90deg);
            }
            50% {
                transform: translate(0, 200px) rotate(180deg);
            }
            75% {
                transform: translate(-100px, 100px) rotate(270deg);
            }
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-light) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-dashboard {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white !important;
            padding: 0.5rem 1.5rem !important;
        }

        /* Hero Section */
        .hero-section {
            padding: 160px 0 100px;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-title .highlight {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }

        .btn-learn {
            border: 2px solid rgba(255,255,255,0.1);
            padding: 1rem 2rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-item p {
            opacity: 0.7;
            margin: 0;
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: rgba(255,255,255,0.02);
        }

        .section-header {
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.08);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon i {
            font-size: 1.5rem;
            color: white;
        }

        /* Current User Info */
        .user-info {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            z-index: 1000;
        }

        /* Mobile Menu */
        .mobile-menu {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem;
            z-index: 1000;
        }

        .mobile-menu a {
            color: var(--text-light);
            text-decoration: none;
            text-align: center;
            flex: 1;
            padding: 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        /* Responsive Styles */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-image {
                margin-top: 3rem;
            }

            .mobile-menu {
                display: flex;
            }

            body {
                padding-bottom: 80px;
            }

            .user-info {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .hero-buttons {
                flex-direction: column;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }

            .feature-card {
                margin-bottom: 1.5rem;
            }
        }

        /* Live Time Display */
.live-time-display {
    position: fixed;
    top: 5rem;
    right: 1rem;
    background: linear-gradient(45deg, var(--primary), var(--secondary));
    padding: 1rem 1.5rem;
    border-radius: 15px;
    font-size: 1.1rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.live-time-display .date {
    font-size: 0.9rem;
    opacity: 0.8;
}

.live-time-display .time {
    font-size: 1.2rem;
    font-weight: 700;
}

@media (max-width: 768px) {
    .live-time-display {
        top: auto;
        bottom: 5rem;
        right: 1rem;
        font-size: 0.9rem;
        padding: 0.75rem 1rem;
    }
}

/* Rating Styles */
.rating-container {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    padding: 2rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-start;
    gap: 0.25rem;
}

.stars input {
    display: none;
}

.stars label {
    font-size: 2rem;
    color: #666;
    cursor: pointer;
    transition: color 0.2s ease;
}

.stars label:hover,
.stars label:hover ~ label,
.stars input:checked ~ label {
    color: #ffd700;
}

.review {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.review .stars {
    color: #ffd700;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

textarea.form-control {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--text-light);
}

textarea.form-control:focus {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    color: var(--text-light);
    box-shadow: none;
}

.btn-primary {
    background: linear-gradient(45deg, var(--primary), var(--secondary));
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
}

.alert {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--text-light);
}

.text-muted {
    color: rgba(255, 255, 255, 0.6) !important;
}

    </style>
</head>
<body>
    <!-- User Info -->
<div class="user-info">
    <i class="fas fa-user-circle me-2"></i>
    <?php echo htmlspecialchars($current_user); ?> | 
    <span id="live-time"></span>
</div>
    <!-- Animated Background -->
    <div class="animated-background">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shopping-bag"></i>
                DuoMart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if($current_user === 'Guest'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/register.php">
                            <i class="fas fa-user-plus"></i>
                            <span>Register</span>
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn-dashboard" href="pages/dashboard.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        Transform Your <span class="highlight">Shopping</span> Experience
                    </h1>
                    <p class="hero-subtitle">
                        Join thousands of satisfied users in the next generation of online marketplace. Buy, sell, and connect like never before.
                    </p>
                    <div class="hero-buttons">
                        <?php if($current_user === 'Guest'): ?>
                        <a href="pages/register.php" class="btn-custom">
                            <i class="fas fa-rocket"></i>
                            Get Started
                        </a>
                        <?php else: ?>
                        <a href="pages/dashboard.php" class="btn-custom">
                            <i class="fas fa-chart-line"></i>
                            Go to Dashboard
                        </a>
                        <?php endif; ?>
                        <a href="#features" class="btn-learn">
                            <i class="fas fa-play"></i>
                            Learn More
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <h3>10K+</h3>
                            <p>Active Users</p>
                        </div>
                        <div class="stat-item">
                            <h3>50K+</h3>
                            <p>Products</p>
                        </div>
                        <div class="stat-item">
                            <h3>99%</h3>
                            <p>Satisfaction</p>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </section>

<!-- Rating Section -->
<section class="rating-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2>Customer Ratings</h2>
            <p>See what our community thinks about DuoMart</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="rating-container">
                    <?php
                    // Koneksi ke database
                    $conn = new mysqli("localhost", "root", "Zin1234", "marketplace");

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Set karakter encoding
                    $conn->set_charset("utf8mb4");

                    // Handle form submission
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
                        if (!isset($_POST["rating"])) {
                            $error = "Rating harus dipilih!";
                        } else {
                            try {
                                $rating = filter_var($_POST["rating"], FILTER_VALIDATE_INT);
                                $review = trim($_POST["review"] ?? '');
                                $user_id = $_SESSION['user_id'];
                                $user_login = $_SESSION['user_login'] ?? 'Anonymous';

                                if ($rating === false || $rating < 1 || $rating > 5) {
                                    throw new Exception("Rating tidak valid");
                                }

                                $stmt = $conn->prepare("INSERT INTO ratings (user_id, user, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())");
                                if (!$stmt) {
                                    throw new Exception("Prepare failed: " . $conn->error);
                                }

                                $stmt->bind_param("isis", $user_id, $user_login, $rating, $review);
                                
                                if (!$stmt->execute()) {
                                    throw new Exception("Execute failed: " . $stmt->error);
                                }

                                $_SESSION['rating_success'] = true;
                                header("Location: " . $_SERVER['PHP_SELF']);
                                exit;
                            } catch (Exception $e) {
                                $error = $e->getMessage();
                            }
                        }
                    }

                    // Get average rating
                    $stmt = $conn->prepare("SELECT ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as total FROM ratings");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $rating_data = $result->fetch_assoc();
                    $average_rating = $rating_data['avg_rating'] ?? 0;
                    $total_reviews = $rating_data['total'] ?? 0;
                    $stmt->close();
                    ?>

                    <div class="rating-summary mb-4">
                        <h3>⭐ <?php echo number_format($average_rating, 1); ?> 
                            <small>(<?php echo $total_reviews; ?> reviews)</small>
                        </h3>
                    </div>

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form method="POST" id="ratingForm" class="mb-4">
                            <div class="form-group">
                                <label class="d-block mb-2">Rate Your Experience:</label>
                                <div class="stars">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" 
                                               id="star<?php echo $i; ?>" required>
                                        <label for="star<?php echo $i; ?>">&#9733;</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="review" class="form-label">Your Review (Optional):</label>
                                <textarea name="review" id="review" class="form-control" 
                                          rows="3" placeholder="Share your experience..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3 w-100">
                                Submit Review
                            </button>
                        </form>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-info">
                            Please <a href="pages/login.php">login</a> to submit a review
                        </div>
                    <?php endif; ?>

                    <!-- Reviews Section -->
                    <div class="reviews-section">
                        <h4 class="mb-3">Recent Reviews</h4>
                        <?php
                        $stmt = $conn->prepare("
                            SELECT rating, review, user, created_at 
                            FROM ratings 
                            ORDER BY created_at DESC
                        ");
                        $stmt->execute();
                        $reviews = $stmt->get_result();

                        if ($reviews->num_rows > 0):
                            while ($review = $reviews->fetch_assoc()):
                                $user_display = $review['user'] ?? 'Anonymous';
                                $review_text = $review['review'] ?? 'No comment';
                                $created_date = date('d M Y, H:i', strtotime($review['created_at']));
                        ?>
                            <div class="review mb-3">
                                <div class="stars">
                                    <?php 
                                    $rating = (int)$review['rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $rating) ? "★" : "☆";
                                    }
                                    ?>
                                </div>
                                <p class="review-text mb-1"><?php echo htmlspecialchars($review_text); ?></p>
                                <small class="text-muted">
                                    By <?php echo htmlspecialchars($user_display); ?> 
                                    on <?php echo $created_date; ?>
                                </small>
                            </div>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <p class="text-muted">No reviews yet. Be the first to review!</p>
                        <?php 
                        endif;
                        $stmt->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>

<!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header text-center">
                <h2>Why Choose DuoMart?</h2>
                <p>Experience the future of online shopping with our innovative features</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure Transactions</h3>
                        <p>Advanced encryption and secure payment methods to protect your transactions</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Quick and reliable delivery service to get your products as soon as possible</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Dedicated customer support team ready to assist you anytime</p>


                        <script>
// Live Time Update
function updateTime() {
    const now = new Date();
    
    // Format date
    const dateOptions = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    const dateStr = now.toLocaleDateString('en-US', dateOptions);
    
    // Format time
    const timeOptions = { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: false 
    };
    const timeStr = now.toLocaleTimeString('en-US', timeOptions);
    
    // Update DOM
    document.getElementById('live-date').textContent = dateStr;
    document.getElementById('live-time').textContent = timeStr;

    // Add animation class every second
    document.querySelector('.live-time-display').classList.add('pulse');
    setTimeout(() => {
        document.querySelector('.live-time-display').classList.remove('pulse');
    }, 500);
}

// Update immediately and then every second
updateTime();
setInterval(updateTime, 1000);

// Add pulse animation
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    .pulse {
        animation: pulse 0.5s ease-in-out;
    }
    .live-time-display {
        transition: transform 0.3s ease;
    }
    .live-time-display:hover {
        transform: scale(1.05);
    }
`;
document.head.appendChild(style);

// Initialize tooltips if using Bootstrap
if(typeof bootstrap !== 'undefined') {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
}


// Rating Functionality
document.addEventListener('DOMContentLoaded', function() {
    const ratingStars = document.querySelectorAll('.rating-stars i');
    let selectedRating = 0;

    ratingStars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.dataset.rating;
            highlightStars(rating);
        });

        star.addEventListener('mouseleave', function() {
            highlightStars(selectedRating);
        });

        star.addEventListener('click', function() {
            selectedRating = this.dataset.rating;
            highlightStars(selectedRating);
        });
    });

    function highlightStars(rating) {
        ratingStars.forEach(star => {
            const starRating = star.dataset.rating;
            if (starRating <= rating) {
                star.classList.remove('far');
                star.classList.add('fas');
            } else {
                star.classList.remove('fas');
                star.classList.add('far');
            }
        });
    }

    // Handle form submission
    const ratingForm = document.getElementById('rating-form');
    if (ratingForm) {
        ratingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (selectedRating === 0) {
                alert('Please select a rating');
                return;
            }
            
            const feedback = this.querySelector('textarea').value;
            // Here you would typically send the rating and feedback to your server
            console.log('Rating:', selectedRating, 'Feedback:', feedback);
            
            // Show success message
            alert('Thank you for your rating!');
            
            // Reset form
            selectedRating = 0;
            highlightStars(0);
            this.reset();
        });
    }
});
</script>


                    