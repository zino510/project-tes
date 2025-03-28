<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_GET['id']);

$query = $conn->prepare("SELECT * FROM product WHERE id = ?");
$query->bind_param("i", $product_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo "Produk tidak ditemukan.";
    exit();
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($product['nama_produk']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --text-color: #34495e;
            --light-bg: #f8f9fa;
            --border-radius: 15px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e0e6ed 100%);
            color: var(--text-color);
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .checkout-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .product-image {
            width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .price-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.8rem;
            border: 2px solid #e0e6ed;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .payment-method-card {
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method-card:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .payment-method-card.selected {
            border-color: var(--success-color);
            background-color: rgba(46, 204, 113, 0.1);
        }

        .btn-checkout {
            background: var(--success-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1.5rem;
            border: none;
        }

        .btn-checkout:hover {
            background: #27ae60;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        }

        .product-details {
            padding: 1rem;
        }

        .product-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .product-description {
            color: var(--text-color);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .total-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            border-top: 2px solid #e0e6ed;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        /* Animasi */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .checkout-container {
            animation: fadeIn 0.6s ease-out;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding-top: 1rem;
            }
            
            .checkout-container {
                padding: 1rem;
            }
        }

        /* Style untuk quantity selector */
.quantity-selector {
    margin-top: 1rem;
}

.quantity-selector .input-group {
    width: 150px;
}

.quantity-selector .form-control {
    text-align: center;
    border-left: none;
    border-right: none;
}

.quantity-selector .btn {
    border-color: #ced4da;
    background-color: #f8f9fa;
    color: #495057;
}

.quantity-selector .btn:hover {
    background-color: #e9ecef;
}

.stock-info {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 10px;
    margin-top: 1rem;
}

.stock-info p {
    margin-bottom: 0;
    color: var(--text-color);
}

.stock-info .fw-bold {
    color: var(--primary-color);
}

/* Animation untuk perubahan quantity */
.quantity-selector .form-control {
    transition: all 0.3s ease;
}

.quantity-selector .form-control:focus {
    transform: scale(1.05);
}

/* Disable browser default spinners untuk input number */
.quantity-selector input[type="number"]::-webkit-inner-spin-button,
.quantity-selector input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-selector input[type="number"] {
    -moz-appearance: textfield;
}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <span class="navbar-brand">
            <i class="fas fa-shopping-cart me-2"></i>
            Checkout
        </span>
        <a href="dashboard.php" class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-2"></i>
            Kembali
        </a>
    </div>
</nav>

<div class="container">
    <div class="row">
       <!-- Detail Produk -->
<div class="col-md-5">
    <div class="checkout-container">
        <h3 class="section-title">Detail Produk</h3>
        <div class="product-image-container">
            <img src="../<?php echo htmlspecialchars($product['gambar']); ?>" 
                 alt="<?php echo htmlspecialchars($product['nama_produk']); ?>" 
                 class="product-image">
            <div class="price-tag">
                <i class="fas fa-tag me-2"></i>
                Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
            </div>
        </div>
        <div class="product-details">
            <h4 class="product-title"><?php echo htmlspecialchars($product['nama_produk']); ?></h4>
            <p class="product-description"><?php echo nl2br(htmlspecialchars($product['deskripsi'])); ?></p>
            
            <!-- Stock Info dan Quantity Selector -->
            <div class="stock-info">
                <p class="mb-2">
                    <i class="fas fa-box me-2"></i>
                    Stok Tersedia: <span class="fw-bold"><?php echo $product['stock']; ?></span>
                </p>
                <div class="quantity-selector">
                    <label class="form-label">Jumlah:</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity('decrease')">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" name="quantity" id="quantity" class="form-control text-center" 
                               value="1" min="1" max="<?php echo $product['stock']; ?>" required
                               onchange="updateTotal()">
                        <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity('increase')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Form Checkout -->
        <div class="col-md-7">
            <div class="checkout-container">
                <h3 class="section-title">Informasi Pengiriman</h3>
                <form action="process_checkout.php" method="POST" id="checkoutForm">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="quantity" id="quantityInput" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user me-2"></i>
                            Nama Penerima
                        </label>
                        <input type="text" name="nama_penerima" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Alamat Lengkap
                        </label>
                        <textarea name="alamat" class="form-control" rows="3" required></textarea>
                    </div>

                    <h3 class="section-title">Metode Pembayaran</h3>
                    <div class="payment-methods">
                        <div class="payment-method-card" onclick="selectPayment('bank')">
                            <input type="radio" name="metode_pembayaran" value="Transfer Bank" required>
                            <i class="fas fa-university me-2"></i>
                            Transfer Bank
                        </div>
                        <div class="payment-method-card" onclick="selectPayment('cod')">
                            <input type="radio" name="metode_pembayaran" value="COD">
                            <i class="fas fa-truck me-2"></i>
                            Cash on Delivery (COD)
                        </div>
                        <div class="payment-method-card" onclick="selectPayment('ewallet')">
                            <input type="radio" name="metode_pembayaran" value="E-Wallet">
                            <i class="fas fa-wallet me-2"></i>
                            E-Wallet
                        </div>
                    </div>

                    <div class="order-summary">
    <h4 class="mb-3">Ringkasan Pesanan</h4>
    <!-- Menampilkan harga per item -->
    <div class="summary-item">
        <span>Harga Barang</span>
        <span id="hargaPerItem">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></span>
    </div>
    <!-- Menampilkan jumlah item -->
    <div class="summary-item">
        <span>Jumlah</span>
        <span id="jumlahItem">1</span>
    </div>
    <!-- Subtotal (harga x jumlah) -->
    <div class="summary-item">
        <span>Subtotal</span>
        <span id="subtotal">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></span>
    </div>
    <!-- Biaya ppn -->
    <div class="summary-item">
        <span>Biaya penanganan</span>
        <span id="ppn">Rp <?php echo number_format($product['harga'] * 0.15, 0, ',', '.'); ?></span>
    </div>
    <!-- Total keseluruhan -->
    <div class="summary-item total-amount">
    <span>Total</span>
        <span id="totalAmount">Rp <?php echo number_format($product['harga'] * 1.15, 0, ',', '.'); ?></span>
    </div>
</div>

                    <button type="submit" class="btn btn-checkout">
                        <i class="fas fa-lock me-2"></i>
                        Bayar Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Konstanta untuk timestamp dan user dari PHP
const CURRENT_TIMESTAMP = '<?php echo date("Y-m-d H:i:s"); ?>';
const CURRENT_USER = '<?php echo isset($_SESSION["user_login"]) ? $_SESSION["user_login"] : ""; ?>';

// Fungsi untuk memilih metode pembayaran
function selectPayment(type) {
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    event.currentTarget.querySelector('input[type="radio"]').checked = true;
}

// Fungsi untuk update jumlah barang
function updateQuantity(action) {
    const quantityInput = document.getElementById('quantity');
    const quantityHidden = document.getElementById('quantityInput');
    const currentValue = parseInt(quantityInput.value);
    const maxStock = parseInt(quantityInput.getAttribute('max'));
    
    if (action === 'increase' && currentValue < maxStock) {
        quantityInput.value = currentValue + 1;
        quantityHidden.value = currentValue + 1;
    } else if (action === 'decrease' && currentValue > 1) {
        quantityInput.value = currentValue - 1;
        quantityHidden.value = currentValue - 1;
    }
    
    updateTotal();
}

// Fungsi untuk memformat angka ke format rupiah
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Fungsi untuk update total harga
function updateTotal() {
    const quantity = parseInt(document.getElementById('quantity').value);
    const price = <?php echo $product['harga']; ?>;
    const subtotal = quantity * price;
    const ppn = subtotal * 0.15;
    const total = subtotal + ppn;
    
    document.getElementById('jumlahItem').textContent = quantity;
    document.getElementById('hargaPerItem').textContent = 'Rp ' + formatRupiah(price);
    document.getElementById('subtotal').textContent = 'Rp ' + formatRupiah(subtotal);
    document.getElementById('ppn').textContent = 'Rp ' + formatRupiah(ppn);
    document.getElementById('totalAmount').textContent = 'Rp ' + formatRupiah(total);
    
    // Update hidden input
    document.getElementById('quantityInput').value = quantity;
}

// Form submission handler
document.getElementById('checkoutForm').onsubmit = function(e) {
    e.preventDefault();

    const quantity = parseInt(document.getElementById('quantity').value);
    const maxStock = parseInt(document.getElementById('quantity').getAttribute('max'));
    const productId = <?php echo $product['id']; ?>;
    const nama = document.querySelector('input[name="nama_penerima"]').value;
    const alamat = document.querySelector('textarea[name="alamat"]').value;
    const pembayaran = document.querySelector('input[name="metode_pembayaran"]:checked');

    // Validasi form
    if (!nama || !alamat || !pembayaran) {
        alert('Mohon lengkapi semua informasi yang diperlukan');
        return false;
    }

    if (quantity <= 0 || quantity > maxStock) {
        alert('Jumlah pembelian tidak valid atau melebihi stok tersedia');
        return false;
    }

    // Ambil total dengan PPN
    const totalText = document.getElementById('totalAmount').textContent;
    const total = parseFloat(totalText.replace('Rp ', '').replace(/\./g, '').replace(',', '.'));

    // Konfirmasi pembelian
    if (!confirm(`Total pembayaran (termasuk PPN 15%): Rp ${formatRupiah(total)}\nApakah Anda yakin ingin melakukan pembelian ini?`)) {
        return false;
    }

    // Handle E-Wallet payment
    if (pembayaran.value === "E-Wallet") {
        const checkoutData = {
            quantity: quantity,
            nama_penerima: nama,
            alamat: alamat,
            metode_pembayaran: pembayaran.value,
            total: total,
            timestamp: CURRENT_TIMESTAMP,
            user: CURRENT_USER,
            product_id: productId
        };
        
        sessionStorage.setItem('checkoutData', JSON.stringify(checkoutData));
        window.location.href = "../pages/qris.php";
        return false;
    }

    // Untuk metode pembayaran lain, submit form secara normal
    this.submit();
    return false; // Prevent default form submission
};

// Event listener untuk input quantity
document.getElementById('quantity').addEventListener('input', function(e) {
    const value = parseInt(this.value) || 1;
    const max = parseInt(this.getAttribute('max'));
    
    if (value > max) {
        alert('Jumlah melebihi stok tersedia!');
        this.value = max;
    } else if (value < 1) {
        this.value = 1;
    }
    
    updateTotal();
});

// Event listener untuk tombol quantity
document.querySelectorAll('.quantity-selector button').forEach(button => {
    button.addEventListener('click', updateTotal);
});

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
    
    const orderSummary = document.querySelector('.order-summary');
    
    const timestampInfo = document.createElement('div');
    timestampInfo.classList.add('small', 'text-muted', 'mt-2');
    timestampInfo.textContent = `Waktu Transaksi: ${CURRENT_TIMESTAMP}`;
    orderSummary.appendChild(timestampInfo);

    const userInfo = document.createElement('div');
    userInfo.classList.add('small', 'text-muted');
    userInfo.textContent = `User: ${CURRENT_USER}`;
    orderSummary.appendChild(userInfo);
});
</script>
</body>
</html>