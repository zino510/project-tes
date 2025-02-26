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
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .product-img {
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }

        .btn-custom {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background-color: var(--primary);
            color: white;
        }

        .btn-delete {
            background-color: var(--accent);
            color: white;
        }

        .btn-back {
            background-color: var(--dark);
            color: white;
        }

        .product-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
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
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box me-2"></i>Produk Saya</h2>
        <div>
            <a href="post_barang.php" class="btn btn-custom btn-edit me-2">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </a>
            <a href="dashboard.php" class="btn btn-custom btn-back">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
    <img src="../<?php echo htmlspecialchars($row['gambar']); ?>" 
         class="product-img" 
         alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
    <div class="product-status <?php echo isset($row['status']) && $row['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
        <?php echo isset($row['status']) ? ucfirst($row['status']) : 'Inactive'; ?>
    </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                            <p class="card-text text-muted">
                                <small><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($row['kategori']); ?></small>
                            </p>
                            <p class="card-text">
                                <strong>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></strong>
                            </p>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($row['deskripsi'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="d-flex gap-2">
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-custom btn-edit flex-grow-1">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <button onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>')" 
                                        class="btn btn-custom btn-delete flex-grow-1">
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
            <p class="text-muted">Anda belum memiliki produk yang dijual. Mulai jual produk Anda sekarang!</p>
            <a href="post_barang.php" class="btn btn-custom btn-edit mt-3">
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
        cancelButtonText: 'Batal'
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
            });
        }
    });
}
</script>

</body>
</html>