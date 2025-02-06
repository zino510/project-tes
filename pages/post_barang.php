<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $user_id = $_SESSION['user_id'];

    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $gambar_path = $upload_dir . basename($_FILES['gambar']['name']);
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $gambar_path)) {
        $gambar = "uploads/" . basename($_FILES['gambar']['name']);
        $stmt = $conn->prepare("INSERT INTO product (nama_produk, deskripsi, harga, kategori, gambar, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdssi", $nama_produk, $deskripsi, $harga, $kategori, $gambar, $user_id);

        if ($stmt->execute()) {
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Gagal mengunggah gambar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jual Barang - Duo Mart</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #00bcd4;
            --secondary-color: #3f51b5;
            --accent-color: #7c4dff;
            --dark-color: #1a237e;
            --light-color: #e8eaf6;
            --success-color: #00e676;
            --white: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a237e 0%, #0097a7 100%);
            min-height: 100vh;
            color: var(--white);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1" fill="rgba(255,255,255,0.05)"/></svg>') repeat;
            pointer-events: none;
            z-index: -1;
        }

        .container {
            position: relative;
            z-index: 1;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 700;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            padding-bottom: 1rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        .form-label {
            color: var(--white);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: var(--white);
            padding: 0.8rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(124, 77, 255, 0.25);
            color: var(--dark-color);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .image-preview {
            width: 100%;
            height: 200px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1rem;
            position: relative;
            background: rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .image-preview .placeholder {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
        }

        .btn-custom {
            background: var(--accent-color);
            color: var(--white);
            border: none;
            border-radius: 10px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(124, 77, 255, 0.4);
        }

        .btn-back {
            background: transparent;
            border: 2px solid var(--white);
            color: var(--white);
        }

        .btn-back:hover {
            background: var(--white);
            color: var(--dark-color);
        }

        .floating-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
            animation: float 8s infinite linear;
        }

        @keyframes float {
            0% { transform: translate(0, 0); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translate(100px, -100px); opacity: 0; }
        }

        /* Custom File Input */
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-label {
            background: rgba(255, 255, 255, 0.1);
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.15);
        }

        .file-upload input[type="file"] {
            display: none;
        }

        /* Animation */
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

        .form-container {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div id="particles"></div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <h2 class="page-title">
                        <i class="fas fa-store me-2"></i>
                        Jual Barang Baru
                    </h2>

                    <form action="post_barang.php" method="POST" enctype="multipart/form-data" id="productForm">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-box me-2"></i>
                                Nama Barang
                            </label>
                            <input type="text" name="nama_produk" class="form-control" 
                                   placeholder="Masukkan nama barang" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-align-left me-2"></i>
                                Deskripsi
                            </label>
                            <textarea name="deskripsi" class="form-control" rows="4" 
                                    placeholder="Jelaskan detail barang" required></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-tag me-2"></i>
                                    Harga (Rp)
                                </label>
                                <input type="number" name="harga" class="form-control" 
                                       placeholder="Masukkan harga" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-list me-2"></i>
                                    Kategori
                                </label>
                                <select name="kategori" class="form-control" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Fashion">Fashion</option>
                                    <option value="Kesehatan">Kesehatan</option>
                                    <option value="Makanan">Makanan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="image-preview" id="imagePreview">
                                <div class="placeholder">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p>Preview Gambar</p>
                                </div>
                            </div>
                            <div class="file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                    <p class="mb-0">Klik untuk memilih</p>
                                    <input type="file" name="gambar" accept="image/*" required 
                                           onchange="previewImage(this);">
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <a href="dashboard.php" class="btn btn-custom btn-back">
                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali
                            </a>
                            <button type="submit" class="btn btn-custom w-100">
                                <i class="fas fa-paper-plane me-2"></i>
                                Jual Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview gambar
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = `
                    <div class="placeholder">
                        <i class="fas fa-image fa-3x mb-2"></i>
                        <p>Preview Gambar</p>
                    </div>`;
            }
        }

        // Animated particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'floating-particle';
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                particle.style.animationDelay = `${Math.random() * 8}s`;
                particlesContainer.appendChild(particle);
            }
        }

        // Form validation
        document.getElementById('productForm').onsubmit = function(e) {
            const harga = document.querySelector('input[name="harga"]').value;
            if (harga <= 0) {
                e.preventDefault();
                alert('Harga harus lebih besar dari 0');
                return false;
            }
            return true;
        };

        // Initialize
        createParticles();
    </script>
</body>
</html>