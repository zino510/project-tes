<?php
session_start();
include '../config/database.php';

// Di bagian awal file dashboard.php, setelah session_start()
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'] ?? '';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: login.php");
    exit();
}

// Cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Get products sorted by newest first
$result = $conn->query("SELECT * FROM product ORDER BY created_at DESC LIMIT 12");
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

    /* Layout */
    .container {
        max-width: 1200px;
        padding: 0 1rem;
    }

    /* Navbar */
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

    /* Product Card */
    .product-card {
        background: var(--white);
        border-radius: var(--radius-md);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: var(--shadow-sm);
        height: 100%;
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
    }

    .product-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }

    .product-category {
        font-size: 0.85rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
        display: inline-block;
    }

    .product-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--accent);
        margin-bottom: 0.5rem;
    }

    .product-description {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Badges */
    .badges-container {
        position: absolute;
        top: 10px;
        left: 10px;
        right: 10px;
        display: flex;
        justify-content: space-between;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .condition-badge {
        background: var(--success);
        color: var(--white);
    }

    .condition-badge.bekas {
        background: var(--secondary);
    }

    .rating-badge {
        background: rgba(0,0,0,0.7);
        color: var(--warning);
    }

    /* Filters */
    .filters-section {
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(10px);
        padding: 1rem 0;
        position: sticky;
        top: 72px;
        z-index: 90;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .filter-pills {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding: 0.5rem 0;
        scrollbar-width: none;
    }

    .filter-pills::-webkit-scrollbar {
        display: none;
    }

    .filter-pill {
        padding: 0.5rem 1rem;
        background: var(--white);
        border-radius: var(--radius-full);
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--dark);
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        border: 1px solid rgba(0,0,0,0.1);
        user-select: none;
    }

    .filter-pill:hover,
    .filter-pill.active {
        background: var(--primary);
        color: var(--white);
    }

    /* Buttons */
    .btn-custom {
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-full);
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
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

    /* Search Styles */
    .search-container {
        max-width: 500px;
        width: 100%;
    }

    .search-input {
        padding: 0.5rem 2.5rem 0.5rem 1rem;
        border-radius: var(--radius-full);
        border: 1px solid rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        box-shadow: 0 0 0 2px var(--primary);
        border-color: var(--primary);
    }

    /* No Results Message */
    .no-results {
        text-align: center;
        padding: 2rem;
        color: var(--dark);
        background: var(--white);
        border-radius: var(--radius-md);
        margin: 2rem auto;
        box-shadow: var(--shadow-sm);
        max-width: 400px;
    }

    /* Rating Modal Styles */
    .stars {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 0.25rem;
    }

    .stars input {
        display: none;
    }

    .stars label {
        font-size: 30px;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .stars label:hover,
    .stars label:hover ~ label,
    .stars input:checked ~ label {
        color: #ffd700;
    }

    /* Rating Badge on Product Card */
    .product-rating {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: #ffd700;
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    @media (max-width: 768px) {
        .mobile-nav {
            display: flex;
            justify-content: space-around;
        }

        body {
            padding-bottom: 70px;
        }

        .product-title {
            font-size: 0.9rem;
        }

        .product-price {
            font-size: 1.1rem;
        }

        .filters-section {
            top: 56px;
        }
    }
    </style>
</head>
<body>
    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a href="index.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
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
                <a href="post_barang.php" class="btn-custom btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    <span>Jual</span>
                </a>
                <a href="profile.php" class="btn-custom btn-outline">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
                <a href="transactions.php" class="btn-custom btn-outline">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Orders</span>
                </a>
                <a href="../actions/logout.php?logout=true" class="btn-custom btn-outline">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Filters -->
    <div class="filters-section">
        <div class="container">
            <!-- Category Filters -->
            <div class="filter-pills">
                <div class="filter-pill active" data-filter="all">Semua</div>
                <div class="filter-pill" data-filter="Elektronik">Elektronik</div>
                <div class="filter-pill" data-filter="Fashion">Fashion</div>
                <div class="filter-pill" data-filter="Kesehatan">Kesehatan</div>
                <div class="filter-pill" data-filter="Makanan">Makanan</div>
                <div class="filter-pill" data-filter="Lainnya">Lainnya</div>
            </div>
            <!-- Condition Filters -->
            <div class="filter-pills mt-2">
                <div class="filter-pill active" data-condition="all">Semua Kondisi</div>
                <div class="filter-pill" data-condition="Baru">
                    <i class="fas fa-box-open"></i>
                    <span>Baru</span>
                </div>
                <div class="filter-pill" data-condition="Bekas">
                    <i class="fas fa-box"></i>
                    <span>Bekas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="container py-4">
        <h2 class="h4 mb-4">Produk Terbaru</h2>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-6 col-md-3">
                <div class="product-card" 
                     data-category="<?php echo htmlspecialchars($row['kategori']); ?>" 
                     data-condition="<?php echo htmlspecialchars($row['kondisi']); ?>">
                    <div class="product-image-container">
                        <img src="../<?php echo $row['gambar']; ?>" 
                             alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                             class="product-image">
                        <div class="badges-container">
                            <div class="badge condition-badge <?php echo strtolower($row['kondisi']); ?>">
                                <i class="fas <?php echo $row['kondisi'] === 'Baru' ? 'fa-box-open' : 'fa-box'; ?>"></i>
                                <span><?php echo $row['kondisi']; ?></span>
                            </div>
                            <?php if($row['rating'] > 0): ?>
                            <div class="badge rating-badge">
                                <i class="fas fa-star"></i>
                                <span><?php echo number_format($row['rating'], 1); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <span class="product-category">
                            <i class="fas fa-tag me-1"></i>
                            <?php echo $row['kategori']; ?>
                        </span>
                        <div class="product-price">
                            Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                        </div>
                        <p class="product-description">
                            <?php echo htmlspecialchars(substr($row['deskripsi'], 0, 100)) . '...'; ?>
                        </p>

                        <button class="btn-custom btn-outline w-100 mb-2" 
                                onclick="openRatingModal(<?php echo $row['id']; ?>)">
                            <i class="fas fa-star"></i>
                            <span>Beri Rating</span>
                        </button>

                        <a href="detail_produk.php?id=<?php echo $row['id']; ?>" 
                           class="btn-custom btn-outline w-100">
                            <i class="fas fa-eye"></i>
                            <span>Lihat Detail</span>
                        </a>
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
            // Filter functionality
            const categoryPills = document.querySelectorAll('[data-filter]');
            const conditionPills = document.querySelectorAll('[data-condition]');
            const productCards = document.querySelectorAll('.product-card');
            const searchInputs = document.querySelectorAll('.search-input');
            
            let activeCategory = 'all';
            let activeCondition = 'all';
            let searchTerm = '';

            // Function to filter products
            function filterProducts() {
                let hasVisibleProducts = false;

                productCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    const cardCondition = card.getAttribute('data-condition');
                    const title = card.querySelector('.product-title').textContent.toLowerCase();
                    const description = card.querySelector('.product-description').textContent.toLowerCase();
                    const category = cardCategory.toLowerCase();
                    const price = card.querySelector('.product-price').textContent.toLowerCase();
                    
                    const categoryMatch = activeCategory === 'all' || cardCategory === activeCategory;
                    const conditionMatch = activeCondition === 'all' || cardCondition === activeCondition;
                    const searchMatch = searchTerm === '' || 
                        title.includes(searchTerm) || 
                        description.includes(searchTerm) || 
                        category.includes(searchTerm) ||
                        price.includes(searchTerm);
                    
                    const cardParent = card.closest('.col-6');
                    if (categoryMatch && conditionMatch && searchMatch) {
                        cardParent.style.display = 'block';
                        hasVisibleProducts = true;
                    } else {
                        cardParent.style.display = 'none';
                    }
                });

                // Show/hide no results message
                const existingMessage = document.querySelector('.no-results');
                if (!hasVisibleProducts) {
                    if (!existingMessage) {
                        const noResultsMessage = document.createElement('div');
                        noResultsMessage.className = 'no-results';
                        noResultsMessage.innerHTML = `
                            <i class="fas fa-filter fa-2x mb-3 text-muted"></i>
                            <h3 class="h5">Tidak ada produk</h3>
                            <p class="text-muted">Tidak ada produk yang sesuai dengan filter yang dipilih</p>
                        `;
                        document.querySelector('.row.g-4').appendChild(noResultsMessage);
                    }
                } else if (existingMessage) {
                    existingMessage.remove();
                }
            }

            // Rating form handling
const ratingForm = document.getElementById('ratingForm');
if (ratingForm) {
    ratingForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate rating
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
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Try to parse JSON response
            let result;
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server tidak mengembalikan response JSON yang valid');
            }

            if (result.success) {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
                modal.hide();
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Reload page
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

            // Category filter event listeners
            categoryPills.forEach(pill => {
                pill.addEventListener('click', function() {
                    categoryPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    activeCategory = this.getAttribute('data-filter');
                    filterProducts();
                });
            });

            // Condition filter event listeners
            conditionPills.forEach(pill => {
                pill.addEventListener('click', function() {
                    conditionPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    activeCondition = this.getAttribute('data-condition');
                    filterProducts();
                });
            });

            // Search functionality with debounce
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func.apply(this, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Search input event listeners
            searchInputs.forEach(input => {
                input.addEventListener('input', debounce(function() {
                    searchTerm = this.value.toLowerCase().trim();
                    filterProducts();
                }, 300));
            });

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

            // Clear search when filters are clicked
            categoryPills.forEach(pill => {
                pill.addEventListener('click', function() {
                    searchInputs.forEach(input => {
                        input.value = '';
                    });
                    searchTerm = '';
                });
            });

            conditionPills.forEach(pill => {
                pill.addEventListener('click', function() {
                    searchInputs.forEach(input => {
                        input.value = '';
                    });
                    searchTerm = '';
                });
            });

            // Mobile navigation active state
            const currentPath = window.location.pathname;
            document.querySelectorAll('.mobile-nav .nav-item').forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                }
            });

            // Initialize filters
            filterProducts();

            // Prevent going back
            window.history.forward();
            function noBack() {
                window.history.forward();
            }
        });

        function openRatingModal(productId) {
            // Reset form
            document.getElementById('ratingForm').reset();
            
            // Set product ID
            document.getElementById('product_id').value = productId;
            
            // Reset stars
            document.querySelectorAll('.stars label').forEach(star => {
                star.style.color = '#ddd';
            });
            
            // Show modal
            const ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
            ratingModal.show();
        }
    </script>
</body>
</html>