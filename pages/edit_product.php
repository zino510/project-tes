<?php
session_start();
include '../config/database.php';

// Di bagian awal file PHP
date_default_timezone_set('Asia/Jakarta');

// Fungsi helper untuk format waktu
function formatDateTimeWIB($date) {
    $dateTime = new DateTime($date);
    $dateTime->setTimezone(new DateTimeZone('Asia/Jakarta'));
    return $dateTime->format('l, d F Y H:i:s') . ' WIB';
}


// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil username dari database jika tidak ada di session
if (!isset($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT username FROM user WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
    } else {
        $_SESSION['username'] = 'User'; // Default fallback
    }
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
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark-bg) !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        /* Container & Form Styles */
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .product-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .product-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: var(--dark-bg);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            border-color: var(--primary-color);
        }

        /* Time Display */
        .current-time {
            background-color: var(--light-bg);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .current-time .fas {
            color: var(--primary-color);
        }

        .current-time .text-muted {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Stock Management */
        .stock-management {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .current-stock {
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        #currentStock {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .stock-input-group {
            gap: 0.5rem;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Image Preview */
        .preview-image {
            max-width: 200px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .preview-image:hover {
            transform: scale(1.05);
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--dark-bg);
            border: none;
        }

        .btn-secondary:hover {
            background: #2c3038;
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes blinkColon {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        .product-form {
            animation: fadeIn 0.6s ease-out;
        }

        .time-colon {
            animation: blinkColon 1s infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .product-form {
                padding: 1.5rem;
            }

            .stock-management {
                padding: 1rem;
            }

            .stock-input-group {
                flex-direction: column;
                align-items: stretch;
            }

            #stockAdjustment {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
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
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_barang.php">
                            <i class="fas fa-plus-circle me-1"></i> Jual Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-1"></i> 
                            <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="product-form">
            <!-- Header dengan Waktu Real-time -->
            <div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="mb-0">
        <i class="fas fa-edit me-2"></i>Edit Produk
    </h2>
    <div class="current-time">
        <span class="text-muted" id="realTimeClock">
            <i class="fas fa-clock me-1"></i>
            <?php echo formatDateTimeWIB(date('Y-m-d H:i:s')); ?>
        </span>
    </div>
</div>
            
            <form id="editProductForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <!-- Nama Produk dan Harga -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_produk" class="form-label">
                            <i class="fas fa-tag me-1"></i> Nama Produk
                        </label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="harga" class="form-label">
                            <i class="fas fa-money-bill me-1"></i> Harga (Rp)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="harga" name="harga" 
                                   value="<?php echo $product['harga']; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">
                        <i class="fas fa-align-left me-1"></i> Deskripsi
                    </label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                </div>

                <!-- Kategori dan Kondisi -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kategori" class="form-label">
                            <i class="fas fa-folder me-1"></i> Kategori
                        </label>
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="Elektronik" <?php echo $product['kategori'] == 'Elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                            <option value="Fashion" <?php echo $product['kategori'] == 'Fashion' ? 'selected' : ''; ?>>Fashion</option>
                            <option value="Kesehatan" <?php echo $product['kategori'] == 'Kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                            <option value="Makanan" <?php echo $product['kategori'] == 'Makanan' ? 'selected' : ''; ?>>Makanan</option>
                            <option value="Lainnya" <?php echo $product['kategori'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="kondisi" class="form-label">
                            <i class="fas fa-star me-1"></i> Kondisi
                        </label>
                        <select class="form-select" id="kondisi" name="kondisi" required>
                            <option value="Baru" <?php echo $product['kondisi'] == 'Baru' ? 'selected' : ''; ?>>Baru</option>
                            <option value="Bekas" <?php echo $product['kondisi'] == 'Bekas' ? 'selected' : ''; ?>>Bekas</option>
                        </select>
                    </div>
                </div>

                <!-- Manajemen Stok -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-box me-1"></i> Manajemen Stok
                    </label>
                    <div class="stock-management">
                        <div class="current-stock">
                            <h5 class="mb-2">Stok Saat Ini</h5>
                            <div id="currentStock"><?php echo number_format((int)$product['stock'], 0, ',', '.'); ?></div>
                            <input type="hidden" id="originalStock" value="<?php echo (int)$product['stock']; ?>">
                        </div>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-center align-items-center gap-3">
                                <div class="input-group stock-input-group">
                                    <button type="button" class="btn btn-outline-primary" id="decreaseStock">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center" id="stockAdjustment" 
                                           name="stock_adjustment" value="0" min="0" max="999999">
                                    <button type="button" class="btn btn-outline-primary" id="increaseStock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <select class="form-select" id="adjustmentType" name="adjustment_type" style="width: auto;">
                                    <option value="add">Tambah Stok</option>
                                    <option value="subtract">Kurangi Stok</option>
                                    <option value="set">Set Stok Baru</option>
                                </select>
                            </div>
                            
                            <div id="stockWarning" class="stock-warning" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span></span>
                            </div>
                            
                            <div class="stock-history">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-history text-primary"></i>
                                    <span id="stockPreview" class="text-muted"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Preview & Upload -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-image me-1"></i> Gambar Saat Ini
                        </label>
                        <div class="position-relative">
                            <img src="../<?php echo $product['gambar']; ?>" alt="Current product image" 
                                 class="preview-image img-fluid">
                            <div class="position-absolute top-0 end-0 p-2">
                                <span class="badge bg-primary">
                                    <i class="fas fa-camera me-1"></i> Current
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="gambar" class="form-label">
                            <i class="fas fa-upload me-1"></i> Upload Gambar Baru (Opsional)
                        </label>
                        <div class="upload-area">
                            <input type="file" class="form-control" id="gambar" name="gambar" 
                                   accept="image/*">
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Format yang didukung: JPG, JPEG, PNG, GIF (Max. 5MB)
                            </small>
                        </div>
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <img src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="dashboard.php" class="btn btn-secondary w-100">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Last Update Info -->
                <div class="text-center mt-3">
                    <div class="last-update-info">
                        <i class="fas fa-user-edit me-1"></i>
                        <span class="text-muted">
                            Terakhir diupdate: <?php echo formatDateTimeWIB($product['updated_at']); ?>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
// Definisi variabel global untuk elemen-elemen yang sering digunakan 
let currentStockElement, stockAdjustment, adjustmentType, stockPreview, stockWarning, originalStock;

// Fungsi untuk memformat angka dengan pemisah ribuan
function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Fungsi untuk memvalidasi input stok
function validateStockInput(value) {
    let number = parseInt(value.toString().replace(/\D/g, '')) || 0;
    return Math.min(Math.max(number, 0), 999999); // Batasi antara 0 dan 999999
}

// Fungsi untuk mengupdate waktu realtime
function updateRealtimeClock() {
    const clockElement = document.getElementById('realTimeClock');
    if (!clockElement) return;
    
    function update() {
        const now = new Date();
        const options = {
            weekday: 'long',
            year: 'numeric', 
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'Asia/Jakarta'
        };

        const formatter = new Intl.DateTimeFormat('id-ID', options);
        const formattedDate = formatter.format(now);
        
        clockElement.innerHTML = `
            <i class="fas fa-clock me-1"></i>
            ${formattedDate} WIB
        `;
    }

    update();
    setInterval(update, 1000);
}

// Fungsi untuk mengupdate preview perubahan stok
function updateStockPreview() {
    if (!currentStockElement || !stockAdjustment || !stockPreview) return;

    const adjustment = validateStockInput(stockAdjustment.value);
    const type = adjustmentType.value;
    let newStock = originalStock;
    let previewText = '';
    
    switch(type) {
        case 'add':
            newStock = originalStock + adjustment;
            previewText = adjustment > 0 ? 
                `Menambah ${formatNumber(adjustment)} unit ke stok (${formatNumber(originalStock)} + ${formatNumber(adjustment)} = ${formatNumber(newStock)})` :
                'Tidak ada perubahan stok';
            break;
            
        case 'subtract':
            if (adjustment > originalStock) {
                showStockWarning('Pengurangan melebihi stok yang tersedia!');
                stockAdjustment.value = originalStock;
                newStock = 0;
            } else {
                newStock = originalStock - adjustment;
            }
            previewText = adjustment > 0 ?
                `Mengurangi ${formatNumber(adjustment)} unit dari stok (${formatNumber(originalStock)} - ${formatNumber(adjustment)} = ${formatNumber(newStock)})` :
                'Tidak ada perubahan stok';
            break;
            
        case 'set':
            newStock = adjustment;
            previewText = `Mengatur ulang stok menjadi ${formatNumber(newStock)} unit`;
            break;
    }
    
    currentStockElement.textContent = formatNumber(newStock);
    stockPreview.textContent = previewText;
    
    currentStockElement.classList.add('text-primary');
    setTimeout(() => {
        currentStockElement.classList.remove('text-primary');
    }, 300);
}

// Fungsi untuk menampilkan peringatan stok
function showStockWarning(message) {
    if (!stockWarning) return;
    const warningSpan = stockWarning.querySelector('span');
    if (!warningSpan) return;

    warningSpan.textContent = message;
    stockWarning.style.display = 'block';
    setTimeout(() => {
        stockWarning.style.display = 'none';
    }, 3000);
}

// Fungsi debounce untuk input stok
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Inisialisasi dan event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi variabel global
    currentStockElement = document.getElementById('currentStock');
    stockAdjustment = document.getElementById('stockAdjustment');
    adjustmentType = document.getElementById('adjustmentType');
    stockPreview = document.getElementById('stockPreview');
    stockWarning = document.getElementById('stockWarning');
    originalStock = parseInt(document.getElementById('originalStock')?.value || 0);

    // Pastikan semua elemen yang diperlukan ada
    if (!currentStockElement || !stockAdjustment || !adjustmentType || !stockPreview || !stockWarning) {
        console.error('Beberapa elemen yang diperlukan tidak ditemukan');
        return;
    }

    // Event listener untuk tombol tambah stok
    const increaseButton = document.getElementById('increaseStock');
    if (increaseButton) {
        increaseButton.addEventListener('click', () => {
            const currentValue = validateStockInput(stockAdjustment.value);
            if (currentValue < 999999) {
                stockAdjustment.value = currentValue + 1;
                updateStockPreview();
            }
        });
    }

    // Event listener untuk tombol kurang stok
    const decreaseButton = document.getElementById('decreaseStock');
    if (decreaseButton) {
        decreaseButton.addEventListener('click', () => {
            const currentValue = validateStockInput(stockAdjustment.value);
            if (currentValue > 0) {
                stockAdjustment.value = currentValue - 1;
                updateStockPreview();
            }
        });
    }

    // Event listener untuk input stok manual dengan debouncing
    if (stockAdjustment) {
        stockAdjustment.addEventListener('input', debounce(function() {
            this.value = validateStockInput(this.value);
            updateStockPreview();
        }, 300));
    }

    // Event listener untuk perubahan tipe penyesuaian
    if (adjustmentType) {
        adjustmentType.addEventListener('change', updateStockPreview);
    }

    // Event listener untuk form submission
    const form = document.getElementById('editProductForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                if (!submitButton) throw new Error('Submit button not found');
                
                const finalStock = parseInt(currentStockElement.textContent.replace(/\./g, ''));
                if (finalStock < 0) {
                    throw new Error('Stok tidak boleh negatif');
                }
                
                formData.append('final_stock', finalStock);
                formData.append('original_stock', originalStock);
                formData.append('stock_adjustment', stockAdjustment.value);
                formData.append('adjustment_type', adjustmentType.value);
                
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
                
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
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Perubahan';
                }
            }
        });
    }

    // Preview gambar yang diupload
    const imageInput = document.getElementById('gambar');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (!preview || !file) return;

            if (file.size > 5 * 1024 * 1024) { // 5MB
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ukuran file tidak boleh lebih dari 5MB'
                });
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.style.display = 'block';
                preview.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Inisialisasi komponen
    updateRealtimeClock();
    updateStockPreview();
});
</script>
</body>
</html>