<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'] ?? '';

// Get all products from the current user
$stmt = $conn->prepare("SELECT * FROM product WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Saya - Duo Mart</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
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
        }

        body {
            background-color: var(--light);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            padding: 1rem;
        }

        .page-header {
            background-color: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .header-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        @media (max-width: 768px) {
            .header-actions {
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
            }

            .header-actions .btn {
                width: 100%;
            }

            .page-header {
                padding: 1rem;
            }

            .header-title {
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .product-img-wrapper {
            position: relative;
            padding-top: 75%; /* 4:3 Aspect Ratio */
            overflow: hidden;
            border-radius: 15px 15px 0 0;
        }

        .product-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 1.25rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8rem;
        }

        .product-category {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .product-description {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .btn-custom {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .btn-edit {
            background-color: var(--primary);
            color: white;
        }

        .btn-edit:hover {
            background-color: #2573a7;
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete {
            background-color: var(--accent);
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
            color: white;
            transform: translateY(-2px);
        }

        .btn-back {
            background-color: var(--dark);
            color: white;
        }

        .btn-back:hover {
            background-color: #243444;
            color: white;
        }

        .product-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.35rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .status-active {
            background-color: var(--success);
            color: white;
        }

        .status-inactive {
            background-color: var(--accent);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--secondary);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        @media (max-width: 576px) {
            .container {
                padding: 0.5rem;
            }

            .card {
                margin-bottom: 15px;
            }

            .product-title {
                font-size: 1rem;
            }

            .product-price {
                font-size: 1.1rem;
            }

            .btn-custom {
                padding: 0.4rem 1rem;
                font-size: 0.8rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* Animation for cards */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.5s ease-out;
            animation-fill-mode: both;
        }

        .row > div:nth-child(1) .card { animation-delay: 0.1s; }
        .row > div:nth-child(2) .card { animation-delay: 0.2s; }
        .row > div:nth-child(3) .card { animation-delay: 0.3s; }
        .row > div:nth-child(4) .card { animation-delay: 0.4s; }
        .row > div:nth-child(5) .card { animation-delay: 0.5s; }
        .row > div:nth-child(6) .card { animation-delay: 0.6s; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h2 class="header-title"><i class="fas fa-box me-2"></i>Produk Saya</h2>
        <div class="header-actions d-flex gap-2">
            <a href="post_barang.php" class="btn btn-custom btn-edit">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </a>
            <a href="dashboard.php" class="btn btn-custom btn-back">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="product-img-wrapper">
                            <img src="../<?php echo htmlspecialchars($row['gambar']); ?>" 
                                 class="product-img" 
                                 alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                            <div class="product-status <?php echo isset($row['status']) && $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo isset($row['status']) ? ucfirst($row['status']) : 'Inactive'; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                            <p class="product-category">
                                <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($row['kategori']); ?>
                            </p>
                            <p class="product-price">
                                Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                            </p>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($row['deskripsi'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="action-buttons">
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-custom btn-edit">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <button onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>')" 
                                        class="btn btn-custom btn-delete">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>Belum Ada Produk</h3>
            <p>Anda belum memiliki produk yang dijual. Mulai jual produk Anda sekarang!</p>
            <a href="post_barang.php" class="btn btn-custom btn-edit">
                <i class="fas fa-plus me-1"></i> Tambah Produk Pertama
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function deleteProduct(productId, productName) {
    Swal.fire({
        title: 'Hapus Produk?',
        text: `Apakah Anda yakin ingin menghapus "${productName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Send delete request
            fetch('../actions/delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Produk berhasil dihapus',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Gagal menghapus produk');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message
                });
        }
    });
}

// Add loading animation for images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.product-img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.classList.add('loaded');
        });
    });

    // Add timestamp to user session
    const currentTime = '2025-03-04 04:13:00';
    const userLogin = 'indag';
    
    // Optional: Add last activity tracking
    const trackActivity = () => {
        localStorage.setItem('lastActivity', currentTime);
        localStorage.setItem('userLogin', userLogin);
    };
    trackActivity();

    // Add responsive card height adjustment
    const adjustCardHeights = () => {
        const cards = document.querySelectorAll('.card');
        const cardBodies = document.querySelectorAll('.card-body');
        
        // Reset heights
        cardBodies.forEach(body => body.style.height = 'auto');
        
        // Only equalize heights on larger screens
        if (window.innerWidth >= 768) {
            const rows = {};
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                const row = Math.floor(rect.top);
                if (!rows[row]) rows[row] = [];
                rows[row].push(card.querySelector('.card-body'));
            });

            // Set equal heights for cards in the same row
            Object.values(rows).forEach(rowCards => {
                const maxHeight = Math.max(...rowCards.map(card => card.offsetHeight));
                rowCards.forEach(card => card.style.height = `${maxHeight}px`);
            });
        }
    };

    // Call on load and resize
    window.addEventListener('resize', adjustCardHeights);
    adjustCardHeights();

    // Add smooth scroll to top button
    const scrollButton = document.createElement('button');
    scrollButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollButton.className = 'scroll-top-btn';
    document.body.appendChild(scrollButton);

    // Add styles for scroll button
    const style = document.createElement('style');
    style.textContent = 
        .scroll-top-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            transform: translateY(100px);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .scroll-top-btn.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .scroll-top-btn:hover {
            background-color: #2573a7;
            transform: translateY(-2px);
        }
        .product-img.loaded {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        /* Add loading skeleton animation */
        .card-loading {
            position: relative;
            overflow: hidden;
        }
        .card-loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        /* Add responsive font sizes */
        @media (max-width: 576px) {
            .product-price {
                font-size: 1rem;
            }
            .product-category {
                font-size: 0.75rem;
            }
            .scroll-top-btn {
                width: 35px;
                height: 35px;
                bottom: 15px;
                right: 15px;
            }
        };
    document.head.appendChild(style);

    // Show/hide scroll button based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollButton.classList.add('visible');
        } else {
            scrollButton.classList.remove('visible');
        }
    });

    // Smooth scroll to top
    scrollButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Add touch support for mobile devices
    let touchStartY = 0;
    let touchEndY = 0;

    document.addEventListener('touchstart', e => {
        touchStartY = e.changedTouches[0].screenY;
    }, false);

    document.addEventListener('touchend', e => {
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
    }, false);

    function handleSwipe() {
        const swipeDistance = touchStartY - touchEndY;
        if (swipeDistance > 100) { // Swipe up
            scrollButton.classList.add('visible');
        } else if (swipeDistance < -100) { // Swipe down
            scrollButton.classList.remove('visible');
        }
    }

    // Add error handling for failed image loads
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = '../assets/images/placeholder.jpg'; // Make sure to create this placeholder image
            this.classList.add('image-load-error');
        });
    });

    // Optional: Add infinite scroll simulation for large product lists
    let isLoading = false;
    window.addEventListener('scroll', () => {
        if (isLoading) return;
        
        const scrollPos = window.innerHeight + window.pageYOffset;
        const pageHeight = document.documentElement.scrollHeight;
        
        if (scrollPos >= pageHeight - 500) {
            // Here you could implement lazy loading of more products
            // For now, we'll just show a loading indicator
            isLoading = true;
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'text-center p-4';
            loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            document.querySelector('.row').appendChild(loadingIndicator);
            
            // Simulate loading delay
            setTimeout(() => {
                loadingIndicator.remove();
                isLoading = false;
            }, 2000);
        }
    });
});
</script>

</body>
</html>