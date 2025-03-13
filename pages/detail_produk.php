    <?php
    session_start();
    include '../config/database.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    if (!isset($_GET['id'])) {
        echo "Produk tidak ditemukan.";
        exit();
    }

    $id = intval($_GET['id']);
    // Modify the query to include rating information
    $query = $conn->prepare("
        SELECT p.*, 
            u.username as seller_username, 
            u.id as seller_id, 
            u.foto as seller_foto,
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(r.id) as total_ratings
        FROM product p 
        JOIN user u ON p.user_id = u.id 
        LEFT JOIN ratings r ON p.id = r.product_id
        WHERE p.id = ?
        GROUP BY p.id");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows == 0) {
        echo "Produk tidak ditemukan.";
        exit();
    }

    $row = $result->fetch_assoc();
    $isSeller = ($row['seller_id'] == $_SESSION['user_id']);

    // Get current user's info
    $userQuery = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $userQuery->bind_param("i", $_SESSION['user_id']);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    $userRow = $userResult->fetch_assoc();
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
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-color: #2c3e50;
                --accent-color: #3498db;
                --success-color: #2ecc71;
                --danger-color: #e74c3c;
                --text-color: #34495e;
                --light-gray: #f8f9fa;
                --border-radius: 15px;
                --card-shadow: 0 8px 30px rgba(0,0,0,0.08);
                --transition: all 0.3s ease;
            }

            body {
                font-family: 'Poppins', sans-serif;
                background-color: var(--light-gray);
                color: var(--text-color);
                line-height: 1.6;
            }

            .navbar {
                background-color: var(--primary-color);
                padding: 1rem 0;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .navbar-brand {
                color: white !important;
                font-weight: 600;
                font-size: 1.4rem;
                letter-spacing: 0.5px;
            }

            .back-btn {
                color: white;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                transition: var(--transition);
                padding: 0.5rem 1rem;
                border-radius: var(--border-radius);
                font-weight: 500;
            }

            .back-btn:hover {
                background: rgba(255,255,255,0.1);
                transform: translateX(-5px);
            }

            .product-container {
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--card-shadow);
                padding: 2rem;
                margin: 2rem auto;
                max-width: 1200px;
            }

            .product-image-container {
                position: relative;
                overflow: hidden;
                border-radius: var(--border-radius);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                aspect-ratio: 4/3;
                margin-bottom: 1.5rem;
            }

            .product-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s ease;
            }

            .product-image:hover {
                transform: scale(1.05);
            }

            .product-details {
                padding: 0 1rem;
            }

            .product-title {
                font-size: 2rem;
                font-weight: 700;
                color: var(--primary-color);
                margin-bottom: 1.5rem;
                line-height: 1.2;
            }

            .seller-info {
                background: var(--light-gray);
                padding: 1rem;
                border-radius: var(--border-radius);
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 1rem;
                transition: var(--transition);
            }

            .seller-info:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            }

            .seller-avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: var(--primary-color);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                overflow: hidden;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .seller-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .seller-avatar i {
                font-size: 1.5rem;
            }

            .seller-details {
                flex: 1;
            }

            .seller-name {
                font-weight: 600;
                color: var(--primary-color);
                font-size: 1.1rem;
            }

            .seller-badge {
                font-size: 0.85rem;
                color: var(--accent-color);
                display: flex;
                align-items: center;
                gap: 0.3rem;
            }

            .product-info {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
                margin-bottom: 1.5rem;
                background: var(--light-gray);
                padding: 1.5rem;
                border-radius: var(--border-radius);
            }

            .info-item {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .info-label {
                font-size: 0.9rem;
                color: var(--text-color);
                opacity: 0.8;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .info-value {
                font-weight: 600;
                color: var(--primary-color);
                font-size: 1.1rem;
            }

            .product-price {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1.5rem;
                flex-wrap: wrap;
            }

            .price-tag {
                background: var(--accent-color);
                color: white;
                padding: 0.8rem 1.5rem;
                border-radius: 50px;
                font-size: 1.3rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
            }

            .stock-info {
                padding: 0.8rem 1.5rem;
                border-radius: 50px;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            .stock-available {
                background: var(--success-color);
                color: white;
                box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
            }

            .stock-empty {
                background: var(--danger-color);
                color: white;
                box-shadow: 0 4px 15px rgba(231, 76, 60, 0.2);
            }

            .product-description {
                background: var(--light-gray);
                padding: 1.5rem;
                border-radius: var(--border-radius);
                margin-bottom: 2rem;
                font-size: 1.1rem;
                line-height: 1.8;
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
                transition: var(--transition);
                text-transform: uppercase;
                letter-spacing: 1px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                min-width: 200px;
                font-size: 1rem;
            }

            .btn-buy {
                background: var(--success-color);
                color: white;
                border: none;
                box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
            }

            .btn-buy:hover:not(:disabled) {
                background: #27ae60;
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
            }

            .btn-disabled {
                background: #bdc3c7 !important;
                cursor: not-allowed !important;
                box-shadow: none !important;
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
            }

            .alert {
                background: #fff3cd;
                color: #856404;
                border: 1px solid #ffeeba;
                padding: 1rem 1.5rem;
                border-radius: var(--border-radius);
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 500;
            }

            @media (max-width: 768px) {
                .container {
                    padding: 0.5rem;
                }

                .product-container {
                    padding: 1rem;
                    margin: 1rem 0;
                }

                .product-title {
                    font-size: 1.6rem;
                }

                .product-info {
                    grid-template-columns: 1fr;
                    padding: 1rem;
                }

                .action-buttons {
                    flex-direction: column;
                }

                .btn-custom {
                    width: 100%;
                    min-width: unset;
                }

                .product-price {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .price-tag, .stock-info {
                    width: 100%;
                    justify-content: center;
                }
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --light-gray: #2d2d2d;
                }

                body {
                    background-color: #1a1a1a;
                }

                .product-container {
                    background: #222;
                }

                .product-title,
                .seller-name,
                .info-value {
                    color: #fff;
                }

                .product-description {
                    color: #ddd;
                }

                .alert {
                    background: #2c2517;
                    color: #fff3cd;
                    border-color: #665c42;
                }
            }

            /* Rating Styles */
    .rating-section {
        margin-top: 1.5rem;
        background: var(--light-gray);
        padding: 1.5rem;
        border-radius: var(--border-radius);
    }

    .rating-stars {
        display: inline-flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .rating-stars .fa-star {
        color: #ddd;
        font-size: 1.5rem;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .rating-stars .fa-star.active {
        color: #f1c40f;
    }

    .rating-summary {
        font-size: 1.2rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .rating-form {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.1);
    }

    .star-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-start;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .star-input input[type="radio"] {
        display: none;
    }

    .star-input label {
        cursor: pointer;
        font-size: 2rem;
        color: #ddd;
        transition: color 0.3s ease;
    }

    .star-input:hover label {
        color: #f1c40f;
    }

    .star-input input[type="radio"]:checked ~ label {
        color: #f1c40f;
    }

    .star-input label:hover,
    .star-input label:hover ~ label {
        color: #f1c40f;
    }

    @media (prefers-color-scheme: dark) {
        .rating-section {
            background: #2d2d2d;
        }
        
        .rating-summary {
            color: #fff;
        }

        .rating-form {
            border-color: rgba(255,255,255,0.1);
        }
    }
        </style>
    </head>
    <body>

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
            <?php if ($isSeller): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Anda tidak dapat membeli produk yang Anda pasarkan sendiri.
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="product-image-container">
                        <img src="../<?php echo htmlspecialchars($row['gambar']); ?>" 
                            alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                            class="product-image">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="product-details">
                        <h1 class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h1>
                        
                        <div class="seller-info">
                            <div class="seller-avatar">
                                <?php if ($row['seller_foto'] && file_exists("../" . $row['seller_foto'])): ?>
                                    <img src="../<?php echo htmlspecialchars($row['seller_foto']); ?>" 
                                        alt="<?php echo htmlspecialchars($row['seller_username']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="seller-details">
                                <div class="seller-name">
                                    <?php echo htmlspecialchars($row['seller_username']); ?>
                                </div>
                                <div class="seller-badge">
                                    <i class="fas fa-store"></i> Sales Partner
                                </div>
                            </div>
                        </div>

                        <div class="product-info">
                            <div class="info-item">
                                <span class="info-label">Kategori</span>
                                <span class="info-value"><?php echo htmlspecialchars($row['kategori']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Kondisi</span>
                                <span class="info-value"><?php echo htmlspecialchars($row['kondisi']); ?></span>
                            </div>
                        </div>
                        
                        <div class="product-price">
                            <span class="price-tag">
                                <i class="fas fa-tag"></i>
                                Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                            </span>
                            <span class="stock-info <?php echo $row['stock'] > 0 ? 'stock-available' : 'stock-empty'; ?>">
                                <i class="fas <?php echo $row['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                <?php echo $row['stock'] > 0 ? 'Stock: ' . $row['stock'] : 'Stock Habis'; ?>
                            </span>
                        </div>

                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?>
                        </div>

                        <div class="rating-section">
        <?php
        // Get user's existing rating if any
        $stmt_user_rating = $conn->prepare("
            SELECT rating, review 
            FROM ratings 
            WHERE product_id = ? AND user_id = ?
        ");
        $stmt_user_rating->bind_param("ii", $id, $_SESSION['user_id']);
        $stmt_user_rating->execute();
        $user_rating = $stmt_user_rating->get_result()->fetch_assoc();
        $stmt_user_rating->close();
        ?>

        <h3>Rating & Ulasan</h3>
        <div class="rating-summary">
            <div class="rating-stars">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?php echo $i <= $row['avg_rating'] ? 'active' : ''; ?>"></i>
                <?php endfor; ?>
            </div>
            <span>
                <?php echo number_format($row['avg_rating'], 1); ?> dari 5
                <small>(<?php echo $row['total_ratings']; ?> ulasan)</small>
            </span>
        </div>

        <?php if (!$isSeller): ?>
            <div class="rating-form">
                <h4><?php echo $user_rating ? 'Edit Rating Anda' : 'Beri Rating'; ?></h4>
                <form id="ratingForm" data-product-id="<?php echo $id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Rating Anda</label>
                        <div class="star-input">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" 
                                    name="rating" 
                                    id="star<?php echo $i; ?>" 
                                    value="<?php echo $i; ?>"
                                    <?php echo ($user_rating && $user_rating['rating'] == $i) ? 'checked' : ''; ?>>
                                <label for="star<?php echo $i; ?>">
                                    <i class="fas fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ulasan (Opsional)</label>
                        <textarea class="form-control" 
                                name="review" 
                                rows="3"
                                placeholder="Tulis ulasan Anda di sini..."><?php echo $user_rating['review'] ?? ''; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom btn-dashboard">
                        <i class="fas fa-save"></i>
                        <?php echo $user_rating ? 'Update Rating' : 'Kirim Rating'; ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

                        <div class="action-buttons">
                            <?php
                            $canBuy = $row['stock'] > 0 && !$isSeller;
                            $buyButtonText = $isSeller ? 'Tidak Dapat Membeli Produk Sendiri' : 
                                        ($row['stock'] > 0 ? 'Beli Sekarang' : 'Stock Habis');
                            ?>
                            <a href="<?php echo $canBuy ? 'checkout.php?id=' . $row['id'] : 'javascript:void(0)'; ?>" 
                            class="btn btn-custom btn-buy <?php echo !$canBuy ? 'btn-disabled' : ''; ?>"
                            <?php echo !$canBuy ? 'disabled' : ''; ?>>
                                <i class="fas <?php echo $canBuy ? 'fa-shopping-cart' : 'fa-ban'; ?>"></i>
                                <?php echo $buyButtonText; ?>
                            </a>
                            <a href="../pages/dashboard.php" class="btn btn-custom btn-dashboard">
                                <i class="fas fa-home"></i>
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingForm = document.getElementById('ratingForm');
    if (ratingForm) {
        ratingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Tampilkan loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            fetch('../actions/submit_rating.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
    // Update rating di detail produk
    const stars = document.querySelectorAll('.rating-summary .rating-stars .fa-star');
    const ratingText = document.querySelector('.rating-summary span');
    
    stars.forEach((star, index) => {
        if (index < Math.floor(data.avgRating)) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
    
    ratingText.innerHTML = `${data.avgRating} dari 5 <small>(${data.totalRatings} ulasan)</small>`;

    // Update rating di halaman dashboard/index jika ada
    if (typeof updateProductRating === 'function') {
        updateProductRating(formData.get('product_id'), data.avgRating, data.totalRatings);
    }

    // Tampilkan pesan sukses
    alert('Rating berhasil disimpan!');
} else {
    throw new Error(data.message || 'Gagal menyimpan rating');
}
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Terjadi kesalahan saat menyimpan rating');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
    </script>
    </body>
    </html>