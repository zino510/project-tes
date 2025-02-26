<?php
session_start();
include '../config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada ID produk
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$product_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Ambil data produk dan pastikan milik user yang login
$stmt = $conn->prepare("SELECT * FROM product WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Jika produk tidak ditemukan atau bukan milik user
if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Duo Mart</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Gunakan style yang sama dengan dashboard untuk konsistensi */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
        }
        .product-form {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .preview-image {
            max-width: 200px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-store me-2"></i>Duo Mart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_barang.php">
                            <i class="fas fa-plus-circle"></i> Jual Barang
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="product-form">
            <h2 class="mb-4">Edit Produk</h2>
            
            <form id="editProductForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                           value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="harga" class="form-label">Harga (Rp)</label>
                    <input type="number" class="form-control" id="harga" name="harga" 
                           value="<?php echo $product['harga']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-control" id="kategori" name="kategori" required>
                        <option value="Elektronik" <?php echo $product['kategori'] == 'Elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                        <option value="Fashion" <?php echo $product['kategori'] == 'Fashion' ? 'selected' : ''; ?>>Fashion</option>
                        <option value="Kesehatan" <?php echo $product['kategori'] == 'Kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                        <option value="Makanan" <?php echo $product['kategori'] == 'Makanan' ? 'selected' : ''; ?>>Makanan</option>
                        <option value="Lainnya" <?php echo $product['kategori'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="kondisi" class="form-label">Kondisi</label>
                    <select class="form-control" id="kondisi" name="kondisi" required>
                        <option value="Baru" <?php echo $product['kondisi'] == 'Baru' ? 'selected' : ''; ?>>Baru</option>
                        <option value="Bekas" <?php echo $product['kondisi'] == 'Bekas' ? 'selected' : ''; ?>>Bekas</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gambar Saat Ini</label>
                    <div>
                        <img src="../<?php echo $product['gambar']; ?>" alt="Current product image" class="preview-image">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="gambar" class="form-label">Upload Gambar Baru (Opsional)</label>
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('editProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                
                // Disable button and show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                
                const response = await fetch('../actions/update_product.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    // Redirect ke dashboard
                    window.location.href = 'dashboard.php';
                } else {
                    throw new Error(result.message || 'Gagal mengupdate produk');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Terjadi kesalahan saat mengupdate produk'
                });
            } finally {
                // Re-enable button and restore original text
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
            }
        });
    </script>
</body>
</html>