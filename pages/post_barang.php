<?php
session_start();
include '../config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $current_user = $user_data['username'];
} else {
    // Jika user tidak ditemukan di database, redirect ke login
    session_destroy();
    header("Location: ../pages/login.php");
    exit();
}

// Timestamp untuk cache busting
$timestamp = "2025-03-10 16:17:00";

// Logging aktivitas user
$log_message = sprintf(
    "Access Time: %s | User ID: %d | Username: %s | IP: %s",
    $timestamp,
    $user_id,
    $current_user,
    $_SERVER['REMOTE_ADDR']
);
error_log($log_message);

// Fungsi untuk validasi input
function validateInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk validasi MIME type
function validateMimeType($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    return in_array($mimeType, $allowed_types);
}

// Fungsi untuk kompresi gambar
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
        imagejpeg($image, $destination, $quality);
    }
    elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
        imagepng($image, $destination, round(9 * $quality / 100));
    }
    imagedestroy($image);
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

// Set header cache-control
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set header untuk response JSON
    header('Content-Type: application/json');
    $response = array();
    
    try {
        // Validasi field yang wajib diisi
        $required_fields = ['nama_produk', 'deskripsi', 'harga', 'kategori', 'kondisi', 'stock'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Field $field wajib diisi");
            }
        }

        // Ambil dan validasi data dari form
        $nama_produk = validateInput($_POST['nama_produk']);
        $deskripsi = validateInput($_POST['deskripsi']);
        $harga = preg_replace('/[^0-9]/', '', $_POST['harga']);
        $kategori = validateInput($_POST['kategori']);
        $kondisi = validateInput($_POST['kondisi']);
        $stock = (int)$_POST['stock'];
        $user_id = $_SESSION['user_id'];

        // Validasi stok
        if (!is_numeric($stock) || $stock < 0) {
            throw new Exception('Stok harus berupa angka positif');
        }

        // Validasi harga
        if ((int)$harga < 100) {
            throw new Exception('Harga minimal Rp 100');
        }

        // Periksa dan buat direktori upload
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception('Gagal membuat direktori upload');
            }
        }

        // Validasi file upload
        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception('Gambar produk wajib diunggah');
        }

        $file = $_FILES['gambar'];
        
        // Validasi tipe file menggunakan MIME
        if (!validateMimeType($file)) {
            throw new Exception('Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WebP');
        }

        // Validasi ukuran file
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            throw new Exception('Ukuran file terlalu besar. Maksimum 5MB');
        }

        // Generate nama file unik
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $unique_filename = uniqid('product_', true) . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_filename;

        // Upload dan kompres file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Kompres gambar
            compressImage($upload_path, $upload_path, 75);
            $gambar = "uploads/" . $unique_filename;
            
            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO product (nama_produk, deskripsi, harga, kategori, kondisi, gambar, stock, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssdsssii", $nama_produk, $deskripsi, $harga, $kategori, $kondisi, $gambar, $stock, $user_id);

            if ($stmt->execute()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Produk berhasil ditambahkan',
                    'data' => [
                        'product_id' => $conn->insert_id,
                        'nama_produk' => $nama_produk
                    ]
                ];
            } else {
                throw new Exception($stmt->error);
            }
        } else {
            throw new Exception('Gagal mengunggah gambar');
        }

    } catch (Exception $e) {
        error_log("Error pada upload produk: " . $e->getMessage());
        $response = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'error_code' => $e->getCode()
        ];
    }

    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jual Barang - Duo Mart</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
    /* Root Variables */
    :root {
        /* Glass Effect Colors */
        --glass-bg: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.25);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        
        /* Gradient Colors */
        --gradient-start: #f8fafc;
        --gradient-end: #f1f5f9;
        
        /* Accent Colors */
        --accent-primary: #4f46e5;
        --accent-secondary: #6366f1;
        --accent-success: #10b981;
        --accent-warning: #f59e0b;
        --accent-error: #ef4444;
        
        /* Neutral Colors */
        --color-neutral-50: #fafafa;
        --color-neutral-100: #f5f5f5;
        --color-neutral-200: #e5e5e5;
        --color-neutral-300: #d4d4d4;
        --color-neutral-400: #a3a3a3;
        --color-neutral-500: #737373;
        --color-neutral-600: #525252;
        --color-neutral-700: #404040;
        --color-neutral-800: #262626;
        --color-neutral-900: #171717;

        /* Transitions */
        --transition-all: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-transform: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Base Styles */
    body {
        font-family: 'Inter', 'Poppins', sans-serif;
        background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
        min-height: 100vh;
        color: var(--color-neutral-800);
        line-height: 1.5;
    }

    /* Enhanced Glass Card Styles */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        box-shadow: var(--glass-shadow);
        border-radius: 1.25rem;
        transition: var(--transition-all);
        overflow: hidden;
        transform: translateY(0);
    }

    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px 0 rgba(31, 38, 135, 0.18);
    }

    /* Enhanced Modern Input Styles */
    .modern-input {
        width: 100%;
        padding: 0.875rem 1.25rem;
        background: rgba(255, 255, 255, 0.95);
        border: 2px solid var(--color-neutral-200);
        border-radius: 0.75rem;
        transition: var(--transition-all);
        font-size: 1rem;
        color: var(--color-neutral-800);
    }

    .modern-input:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    .modern-input::placeholder {
        color: var(--color-neutral-400);
    }

    /* Enhanced Form Group Styles */
    .form-group {
        margin-bottom: 1.75rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.625rem;
        font-weight: 500;
        color: var(--color-neutral-700);
        font-size: 0.9375rem;
    }

    /* Enhanced Button Styles */
    .btn-modern {
        padding: 0.875rem 1.75rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
        color: white;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: var(--transition-all);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        font-size: 1rem;
        position: relative;
        overflow: hidden;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.25);
    }

    .btn-modern:active {
        transform: translateY(0);
    }

    /* Enhanced File Upload Area */
    .file-upload-area {
        border: 2.5px dashed rgba(79, 70, 229, 0.3);
        border-radius: 1.25rem;
        padding: 2.5rem;
        text-align: center;
        cursor: pointer;
        transition: var(--transition-all);
        background: rgba(255, 255, 255, 0.6);
        position: relative;
    }

    .file-upload-area:hover {
        border-color: var(--accent-primary);
        background: rgba(255, 255, 255, 0.8);
    }

    .file-upload-area.dragover {
        border-color: var(--accent-success);
        background: rgba(16, 185, 129, 0.05);
        transform: scale(1.02);
    }

    /* Enhanced Progress Bar */
    .progress-bar {
        width: 100%;
        height: 6px;
        background: var(--color-neutral-200);
        border-radius: 3px;
        overflow: hidden;
        margin-top: 1rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .progress-bar.show {
        opacity: 1;
    }

    .progress-bar__fill {
        height: 100%;
        background: linear-gradient(to right, var(--accent-primary), var(--accent-secondary));
        transition: width 0.3s ease;
        border-radius: 3px;
    }

    /* Enhanced Notifications */
    .notification {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        background: white;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 0.875rem;
        z-index: 1000;
        transform: translateX(150%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        min-width: 300px;
        max-width: 450px;
    }

    .notification.show {
        transform: translateX(0);
    }

    /* Enhanced Loading Animation */
    .loading-spinner {
        width: 45px;
        height: 45px;
        border: 3px solid var(--color-neutral-200);
        border-top-color: var(--accent-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Enhanced Animations */
    .fade-in-up {
        animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        opacity: 0;
    }

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

    /* Enhanced Image Preview */
    .image-preview-container {
        position: relative;
        width: 100%;
        height: 240px;
        overflow: hidden;
        border-radius: 1rem;
        background: var(--color-neutral-100);
        transition: var(--transition-all);
    }

    .image-preview-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition-transform);
    }

    .image-preview-container:hover img {
        transform: scale(1.05);
    }

    .remove-image-btn {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-all);
        opacity: 0;
        transform: scale(0.8);
    }

    .image-preview-container:hover .remove-image-btn {
        opacity: 1;
        transform: scale(1);
    }

    .remove-image-btn:hover {
        background: var(--accent-error);
        transform: scale(1.1);
    }

    /* Form Validation Styles */
    .is-invalid {
        border-color: var(--accent-error) !important;
    }

    .error-message {
        color: var(--accent-error);
        font-size: 0.875rem;
        margin-top: 0.375rem;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    /* Accessibility Improvements */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .glass-card {
            padding: 1.25rem;
        }

        .btn-modern {
            width: 100%;
        }

        .file-upload-area {
            padding: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <div class="page-wrapper min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100">
        <!-- Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-grid-neutral-100/25"></div>
            <div id="particles-js" class="absolute inset-0"></div>
        </div>

        <!-- Main Content -->
        <div class="container py-8 relative z-10">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <!-- Form Container -->
                    <div class="glass-card p-8" data-aos="fade-up">
                        <!-- Header -->
                        <div class="text-center mb-8">
    <h2 class="text-3xl font-bold text-neutral-800 mb-2 flex items-center justify-center gap-3">
        <i class="fas fa-store text-accent-primary"></i>
        <span class="relative">
            Jual Barang
            <span class="absolute -bottom-2 left-0 w-full h-1 bg-gradient-to-r from-accent-primary to-accent-secondary rounded-full"></span>
        </span>
    </h2>
    <p class="text-neutral-500">Terakhir diperbarui: <?php echo $timestamp; ?></p>
    <p class="text-sm text-neutral-400">
        <i class="fas fa-user"></i> 
        <?php echo htmlspecialchars($current_user); ?>
    </p>
</div>

                        <!-- Form -->
                        <form id="productForm" action="post_barang.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <!-- Nama Produk -->
                            <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="100">
                                <div class="form-group">
                                    <label for="nama_produk" class="form-label flex items-center gap-2">
                                        <i class="fas fa-box-open text-accent-primary"></i>
                                        Nama Barang
                                        <span class="text-accent-error">*</span>
                                    </label>
                                    <input type="text" 
                                           id="nama_produk"
                                           name="nama_produk" 
                                           class="modern-input" 
                                           placeholder="Masukkan nama barang"
                                           aria-required="true"
                                           required>
                                    <small class="text-neutral-500 mt-1 block">
                                        Minimal 3 karakter
                                    </small>
                                </div>
                            </div>

                            <!-- Deskripsi Produk -->
                            <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="200">
                                <div class="form-group">
                                    <label for="deskripsi" class="form-label flex items-center gap-2">
                                        <i class="fas fa-align-left text-accent-primary"></i>
                                        Deskripsi
                                        <span class="text-accent-error">*</span>
                                    </label>
                                    <textarea id="deskripsi"
                                              name="deskripsi" 
                                              class="modern-input h-32 resize-none" 
                                              placeholder="Jelaskan detail barang"
                                              aria-required="true"
                                              required></textarea>
                                    <small class="text-neutral-500 mt-1 block">
                                        Deskripsikan produk Anda secara detail
                                    </small>
                                </div>
                            </div>

                            <!-- Harga dan Kategori -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Input Harga -->
<div class="glass-card p-6" data-aos="fade-up" data-aos-delay="300">
    <div class="form-group">
        <label for="harga" class="form-label flex items-center gap-2">
            <i class="fas fa-tag text-accent-primary"></i>
            Harga (Rp)
            <span class="text-accent-error">*</span>
        </label>
        <div class="relative">
            <div class="currency-prefix">Rp</div>
            <input type="text" 
                   id="harga"
                   name="harga" 
                   class="modern-input price-input" 
                   placeholder="0"
                   aria-required="true"
                   inputmode="numeric"
                   pattern="[0-9,\.]*"
                   required>
        </div>
        <small class="text-neutral-500 mt-1 block flex items-center gap-1">
            <i class="fas fa-info-circle"></i>
            Minimal Rp 100
        </small>
    </div>
</div>

                                <!-- Pilihan Kategori -->
                                <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="400">
                                    <div class="form-group">
                                        <label for="kategori" class="form-label flex items-center gap-2">
                                            <i class="fas fa-list text-accent-primary"></i>
                                            Kategori
                                            <span class="text-accent-error">*</span>
                                        </label>
                                        <select id="kategori"
                                                name="kategori" 
                                                class="modern-input" 
                                                required>
                                            <option value="">Pilih Kategori</option>
                                            <option value="Elektronik">Elektronik</option>
                                            <option value="Fashion">Fashion</option>
                                            <option value="Kesehatan">Kesehatan</option>
                                            <option value="Makanan">Makanan</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Kondisi dan Stok -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Pilihan Kondisi -->
                                <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="500">
                                    <div class="form-group">
                                        <label for="kondisi" class="form-label flex items-center gap-2">
                                            <i class="fas fa-star text-accent-primary"></i>
                                            Kondisi
                                            <span class="text-accent-error">*</span>
                                        </label>
                                        <select id="kondisi"
                                                name="kondisi" 
                                                class="modern-input" 
                                                required>
                                            <option value="">Pilih Kondisi</option>
                                            <option value="Baru">Baru</option>
                                            <option value="Bekas">Bekas</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Input Stok -->
                                <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="600">
                                    <div class="form-group">
                                        <label for="stock" class="form-label flex items-center gap-2">
                                            <i class="fas fa-cubes text-accent-primary"></i>
                                            Stok Barang
                                            <span class="text-accent-error">*</span>
                                        </label>
                                        <input type="number" 
                                               id="stock"
                                               name="stock" 
                                               class="modern-input" 
                                               placeholder="0"
                                               min="0"
                                               required>
                                        <small class="text-neutral-500 mt-1 block">
                                            Minimal 1 stok
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Gambar -->
                            <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="700">
                                <div class="form-group">
                                    <label class="form-label flex items-center gap-2">
                                        <i class="fas fa-image text-accent-primary"></i>
                                        Foto Produk
                                        <span class="text-accent-error">*</span>
                                    </label>
                                    <div class="file-upload-area" id="dropZone">
                                        <div id="imagePreview" class="mb-4">
                                            <i class="fas fa-cloud-upload-alt text-4xl text-accent-primary mb-3"></i>
                                            <p class="text-neutral-500">Drag & drop gambar atau klik untuk memilih</p>
                                            <p class="text-sm text-neutral-400 mt-2">Format: JPG, PNG, GIF, atau WebP (Max. 5MB)</p>
                                        </div>
                                        <input type="file" 
                                               name="gambar" 
                                               accept="image/*" 
                                               class="hidden" 
                                               id="imageInput"
                                               required>
                                        <div id="uploadProgress" class="progress-bar">
                                            <div class="progress-bar__fill"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex gap-4 mt-8">
                                <a href="dashboard.php" class="btn-modern bg-neutral-500 flex-grow-0">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Kembali</span>
                                </a>
                                <button type="submit" class="btn-modern flex-grow">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Jual Sekarang</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
    // UI Enhancement Class
    class UIEnhancement {
    constructor() {
        // Initialize timestamps and user info from PHP session
        this.timestamp = "<?php echo $timestamp; ?>";
        this.currentUser = "<?php echo htmlspecialchars($current_user); ?>";
            
            // Initialize form elements
            this.form = document.getElementById('productForm');
            this.imageInput = document.getElementById('imageInput');
            this.dropZone = document.getElementById('dropZone');
            this.imagePreview = document.getElementById('imagePreview');
            this.uploadProgress = document.getElementById('uploadProgress');
            
            // Initialize features
            this.initializeAOS();
            this.initializeParticles();
            this.initializeEventListeners();
            this.initializeAutosave();
            this.initializeValidation();
        }

        initializeAOS() {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true,
                offset: 50
            });
        }

        initializeParticles() {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 50, density: { enable: true, value_area: 800 } },
                    color: { value: '#6b7280' },
                    shape: { type: 'circle' },
                    opacity: { 
                        value: 0.2, 
                        random: true,
                        anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false }
                    },
                    size: { 
                        value: 3, 
                        random: true,
                        anim: { enable: true, speed: 2, size_min: 0.1, sync: false }
                    },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: '#6b7280',
                        opacity: 0.1,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 2,
                        direction: 'none',
                        random: true,
                        straight: false,
                        out_mode: 'out',
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: {
                        onhover: { 
                            enable: true, 
                            mode: 'grab',
                            parallax: { enable: true, force: 60, smooth: 10 }
                        },
                        onclick: { enable: true, mode: 'push' },
                        resize: true
                    },
                    modes: {
                        grab: { distance: 140, line_linked: { opacity: 0.4 } },
                        push: { particles_nb: 3 }
                    }
                },
                retina_detect: true
            });
        }

        initializeEventListeners() {
            // Form submission
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            
            // Image upload handlers
            this.imageInput.addEventListener('change', (e) => this.handleImageSelect(e));
            this.dropZone.addEventListener('dragover', (e) => this.handleDragOver(e));
            this.dropZone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
            this.dropZone.addEventListener('drop', (e) => this.handleDrop(e));
            this.dropZone.addEventListener('click', () => this.imageInput.click());

            // Real-time price formatting
            const priceInput = this.form.querySelector('input[name="harga"]');
            priceInput.addEventListener('input', (e) => this.formatPrice(e.target));

            // Form change tracking
            this.form.addEventListener('input', () => {
                window.formChanged = true;
                this.debouncedSave();
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));
        }

        // Debounce function for autosave
        debounce(func, wait) {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        debouncedSave = this.debounce(() => this.saveFormData(), 500);

        formatPrice(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = parseInt(value).toLocaleString('id-ID');
                input.value = value;
            }
        }

        handleImageSelect(e) {
            const file = e.target.files[0];
            if (this.validateImage(file)) {
                this.previewImage(file);
                this.simulateUploadProgress();
            }
        }

        validateImage(file) {
            if (!file) return false;

            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!validTypes.includes(file.type)) {
                this.showNotification('Gunakan format file JPG, PNG, GIF, atau WebP', 'error');
                return false;
            }

            if (file.size > maxSize) {
                this.showNotification('Ukuran file maksimal 5MB', 'error');
                return false;
            }

            return true;
        }

        previewImage(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview.innerHTML = `
                    <div class="relative w-full h-48 rounded-lg overflow-hidden">
                        <img src="${e.target.result}" 
                             class="w-full h-full object-cover transition-transform hover:scale-105"
                             alt="Preview">
                        <button type="button" 
                                class="remove-image-btn"
                                onclick="ui.removeImage()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }

        simulateUploadProgress() {
            this.uploadProgress.classList.add('show');
            const fill = this.uploadProgress.querySelector('.progress-bar__fill');
            let progress = 0;
            
            const interval = setInterval(() => {
                progress += 5;
                fill.style.width = `${progress}%`;
                
                if (progress >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        this.uploadProgress.classList.remove('show');
                    }, 500);
                }
            }, 50);
        }

        removeImage() {
            this.imageInput.value = '';
            this.imagePreview.innerHTML = `
                <i class="fas fa-cloud-upload-alt text-4xl text-accent-primary mb-3"></i>
                <p class="text-neutral-500">Drag & drop gambar atau klik untuk memilih</p>
                <p class="text-sm text-neutral-400 mt-2">Format: JPG, PNG, GIF, atau WebP (Max. 5MB)</p>
            `;
            this.uploadProgress.classList.remove('show');
        }

        async handleSubmit(e) {
            e.preventDefault();
            
            if (!this.validateForm()) {
                this.showNotification('Mohon lengkapi semua field yang wajib diisi', 'error');
                return;
            }

            const formData = new FormData(this.form);
            
            try {
                this.showLoadingOverlay();
                
                const response = await fetch(this.form.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.status === 'success') {
                    this.showNotification(result.message || 'Produk berhasil ditambahkan', 'success');
                    this.clearAutosave();
                    window.formChanged = false;
                    
                    setTimeout(() => {
                        window.location.href = '../pages/dashboard.php';
                    }, 1500);
                } else {
                    this.showNotification(result.message || 'Terjadi kesalahan', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('Terjadi kesalahan saat mengirim data', 'error');
            } finally {
                this.hideLoadingOverlay();
            }
        }

        validateForm() {
            let isValid = true;
            const required = this.form.querySelectorAll('[required]');
            
            required.forEach(field => {
                if (!field.value.trim()) {
                    this.showFieldError(field);
                    isValid = false;
                } else {
                    this.clearFieldError(field);
                }
            });

            // Validate minimum price
            const price = parseInt(this.form.querySelector('input[name="harga"]').value.replace(/\D/g, ''));
            if (price < 100) {
                this.showFieldError(this.form.querySelector('input[name="harga"]'));
                this.showNotification('Harga minimal Rp 100', 'error');
                isValid = false;
            }

            return isValid;
        }

        showFieldError(field) {
            field.classList.add('border-red-500');
            const errorMessage = document.createElement('p');
            errorMessage.className = 'error-message';
            errorMessage.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <span>Field ini wajib diisi</span>
            `;
            
            // Remove existing error message if any
            const existingError = field.parentElement.querySelector('.error-message');
            if (existingError) existingError.remove();
            
            field.parentElement.appendChild(errorMessage);
        }

        clearFieldError(field) {
            field.classList.remove('border-red-500');
            const errorMessage = field.parentElement.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }

        showLoadingOverlay() {
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-xl text-center">
                    <div class="loading-spinner mb-4"></div>
                    <p class="text-neutral-600">Memproses...</p>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        hideLoadingOverlay() {
            const overlay = document.querySelector('.fixed.inset-0');
            if (overlay) {
                overlay.remove();
            }
        }

        showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type === 'success' ? 'bg-green-50' : 'bg-red-50'}`;
            
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-red-500'}"></i>
                <p class="text-neutral-700">${message}</p>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => notification.classList.add('show'), 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        handleKeyboardShortcuts(e) {
            // Ctrl/Cmd + S to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.form.querySelector('button[type="submit"]').click();
            }
            
            // Esc to clear form
            if (e.key === 'Escape') {
                if (confirm('Apakah Anda yakin ingin mengosongkan formulir?')) {
                    this.form.reset();
                    this.clearAutosave();
                    this.removeImage();
                    this.showNotification('Formulir telah dikosongkan', 'success');
                }
            }
        }

        // Autosave functionality
        initializeAutosave() {
            this.loadFormData();
            this.initializeFormChangeTracking();
        }

        initializeFormChangeTracking() {
            window.formChanged = false;
            window.addEventListener('beforeunload', (e) => {
                if (window.formChanged) {
                    e.preventDefault();
                    e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                }
            });
        }

        saveFormData() {
            const formData = {};
            this.form.querySelectorAll('input, select, textarea').forEach(element => {
                if (element.type !== 'file' && element.name) {
                    formData[element.name] = element.value;
                }
            });
            localStorage.setItem('productFormData', JSON.stringify(formData));
            localStorage.setItem('productFormLastSaved', new Date().toISOString());
        }

        loadFormData() {
            const saved = localStorage.getItem('productFormData');
            const lastSaved = localStorage.getItem('productFormLastSaved');
            
            if (saved && lastSaved) {
                const formData = JSON.parse(saved);
                const lastSavedDate = new Date(lastSaved);
                const currentDate = new Date();
                
                // Only load if saved within the last 24 hours
                if ((currentDate - lastSavedDate) < (24 * 60 * 60 * 1000)) {
                    Object.keys(formData).forEach(key => {
                        const element = this.form.querySelector(`[name="${key}"]`);
                        if (element && element.type !== 'file') {
                            element.value = formData[key];
                        }
                    });
                    this.showNotification('Data formulir terakhir berhasil dimuat', 'success');
                } else {
                    this.clearAutosave();
                }
            }
        }

        clearAutosave() {
            localStorage.removeItem('productFormData');
            localStorage.removeItem('productFormLastSaved');
        }

        handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            this.dropZone.classList.add('dragover');
        }

        handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            this.dropZone.classList.remove('dragover');
        }

        handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            this.dropZone.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (this.validateImage(file)) {
                this.imageInput.files = e.dataTransfer.files;
                this.previewImage(file);
                this.simulateUploadProgress();
            }
        }
    }

    // Initialize UI enhancements
    const ui = new UIEnhancement();

    // Log system information
    console.log(`System Time: ${ui.timestamp}`);
    console.log(`Current User: ${ui.currentUser}`);
    </script>
</body>
</html>