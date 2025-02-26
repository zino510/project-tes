<?php
session_start();
include '../config/database.php';

// Di bagian awal file dashboard.php, setelah session_start()
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Di bagian awal dashboard.php setelah session_start()
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
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

    /* Navbar & Dropdown Styles */
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

    .dropdown-menu {
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0,0,0,0.1);
        padding: 0.5rem;
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        color: var(--dark);
    }

    .dropdown-item:hover {
        background-color: var(--primary);
        color: var(--white);
    }

    .dropdown-toggle::after {
        margin-left: 0.5rem;
    }

    .btn-custom.dropdown-toggle {
        display: inline-flex;
        align-items: center;
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

    .mobile-nav {
        display: none;
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

    /* Button Styles */
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
                <div class="dropdown">
                    <button class="btn-custom btn-primary dropdown-toggle" type="button" id="sellerMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-box me-1"></i>
                        Menu Penjual
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="sellerMenuButton">
                        <li>
                            <a class="dropdown-item" href="../pages/my_product.php">
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
                <a href="transactions.php" class="btn-custom btn-outline">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Orders</span>
                </a>
                <a href="profile.php" class="btn-custom btn-outline">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
                <a href="../actions/logout.php?logout=true" class="btn-custom btn-outline">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
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

                        <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                        <!-- Tombol untuk pemilik produk -->
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" 
                           class="btn-custom btn-primary w-100 mb-2">
                            <i class="fas fa-edit"></i>
                            <span>Edit Produk</span>
                        </a>
                        <button class="btn-custom btn-outline btn-danger w-100 mb-2" 
                                onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>')">
                            <i class="fas fa-trash-alt"></i>
                            <span>Hapus Produk</span>
                        </button>
                        <?php else: ?>
                        <!-- Tombol untuk pembeli -->
                        <button class="btn-custom btn-outline w-100 mb-2" 
                                onclick="openRatingModal(<?php echo $row['id']; ?>)">
                            <i class="fas fa-star"></i>
                            <span>Beri Rating</span>
                        </button>
                        <?php endif; ?>

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

            // Prevent going back
            window.history.forward();
            function noBack() {
                window.history.forward();
            }
        });
    </script>
</body>
</html>