<?php
session_start();
include '../config/database.php';

// Set timezone ke WIB
date_default_timezone_set('Asia/Jakarta');

// Waktu UTC dari input: 2025-03-04 14:43:08
$current_datetime_utc = '2025-03-04 14:43:08';
// Konversi ke WIB (UTC+7)
$current_datetime_wib = date('Y-m-d H:i:s', strtotime($current_datetime_utc . ' +7 hours'));

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user dari database
$user_query = "SELECT nama FROM user WHERE id = ?";
try {
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $current_user = $user['nama']; // Mengambil nama user dari database
    } else {
        header("Location: login.php");
        exit();
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    header("Location: login.php");
    exit();
}

// Ambil data pembelian (produk yang dibeli oleh user)
$pembelian_query = "
    SELECT t.id as transaction_id, t.total_harga, t.status, t.created_at, 
           td.product_id, td.quantity, td.harga as product_price, 
           p.nama_produk, p.deskripsi, p.gambar, 
           s.nama as seller_name, s.telepon as seller_phone, s.email as seller_email
    FROM transactions t
    JOIN transaction_details td ON t.id = td.transaction_id
    JOIN product p ON td.product_id = p.id
    JOIN user s ON p.user_id = s.id
    WHERE t.user_id = $user_id
    ORDER BY t.created_at DESC
";

$pembelian_result = $conn->query($pembelian_query);

// Count different status
$pending_count = 0;
$completed_count = 0;
$shipping_count = 0;

if ($pembelian_result) {
    while ($row = $pembelian_result->fetch_assoc()) {
        if (strtolower($row['status']) === 'pending') $pending_count++;
        if (strtolower($row['status']) === 'selesai') $completed_count++;
        if (strtolower($row['status']) === 'dikirim') $shipping_count++;
    }
    $pembelian_result->data_seek(0); // Reset pointer
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    <link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian - Duo Mart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .purchase-card {
            animation: fadeIn 0.3s ease-out forwards;
            opacity: 0;
        }
        
        .status-badge {
            transition: all 0.2s ease;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #666;
        }

        .scale-hover {
            transition: transform 0.2s ease;
        }

        .scale-hover:hover {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
   <!-- Top Navigation Bar -->
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="../pages/dashboard.php" class="flex items-center text-gray-800 hover:text-green-600 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <div class="h-4 w-px bg-gray-300"></div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-user-circle mr-2 text-green-600"></i>
                    <span class="font-medium"><?php echo htmlspecialchars($current_user); ?></span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    <i class="far fa-clock mr-2 text-green-600"></i>
                    <span id="currentDateTime">Loading...</span>
                    <span class="ml-2 text-sm font-medium">(WIB)</span>
                </div>
            </div>
        </div>
    </div>
</nav>

    <!-- Main Content Container -->
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section with Navigation -->
        <div class="mb-12">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-3">
                    <i class="fas fa-shopping-cart text-green-600 mr-3"></i>
                    Riwayat Pembelian
                </h1>
                <p class="text-gray-600 text-lg">
                    Kelola dan pantau status pembelian Anda
                </p>
            </div>
            
            <!-- Navigation Tabs -->
            <div class="flex justify-center space-x-4 mb-8">
                <a href="transactions.php" 
                   class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center">
                    <i class="fas fa-store mr-2"></i>
                    Pesanan Masuk
                </a>
                <a href="purchases.php" 
                   class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    Riwayat Pembelian
                </a>
            </div>
        </div>

        <!-- Purchase Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <!-- Total Purchases -->
            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Pembelian</p>
                        <h3 class="text-2xl font-bold text-gray-800 mt-1">
                            <?php echo $pembelian_result->num_rows; ?>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Keseluruhan pembelian</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Purchases -->
            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Menunggu Pembayaran</p>
                        <h3 class="text-2xl font-bold text-yellow-600 mt-1">
                            <?php echo $pending_count; ?>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Belum dibayar</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Shipping -->
            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Dalam Pengiriman</p>
                        <h3 class="text-2xl font-bold text-blue-600 mt-1">
                            <?php echo $shipping_count; ?>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Sedang dikirim</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shipping-fast text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Selesai</p>
                        <h3 class="text-2xl font-bold text-green-600 mt-1">
                            <?php echo $completed_count; ?>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Pembelian sukses</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase List -->
        <div class="space-y-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <i class="fas fa-list text-2xl text-green-600 mr-3"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Pembelian</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <select id="statusFilter" 
                            class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="all">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="dibayar">Dibayar</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            </div>

            <?php if ($pembelian_result->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php 
                    $delay = 0;
                    while ($row = $pembelian_result->fetch_assoc()): 
                    ?>
                    <div class="purchase-card bg-white rounded-xl shadow-sm hover:shadow-md p-6 scale-hover"
                         style="animation-delay: <?php echo $delay; ?>ms"
                         data-status="<?php echo strtolower($row['status']); ?>">
                        <!-- Purchase Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg text-gray-800">
                                        <?php echo htmlspecialchars($row['nama_produk']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        Order #<?php echo $row['transaction_id']; ?>
                                    </p>
                                </div>
                            </div>
                            <span class="status-badge px-4 py-1.5 rounded-full text-sm font-medium <?php
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'dibayar' => 'bg-blue-100 text-blue-700',
                                    'dikirim' => 'bg-purple-100 text-purple-700',
                                    'selesai' => 'bg-green-100 text-green-700',
                                    'dibatalkan' => 'bg-red-100 text-red-700'
                                ];
                                echo $status_colors[strtolower($row['status'])] ?? 'bg-gray-100 text-gray-700';
                            ?>">
                                <i class="fas fa-circle text-xs mr-1.5"></i>
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>

                        <!-- Purchase Details -->
                        <div class="grid grid-cols-2 gap-6 mb-4">
                            <div class="space-y-3">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-shopping-basket w-5 mr-2 text-gray-400"></i>
                                    <span>Qty: <?php echo $row['quantity']; ?> unit</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-tag w-5 mr-2 text-gray-400"></i>
                                    <span>Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-store w-5 mr-2 text-gray-400"></i>
                                    <span><?php echo htmlspecialchars($row['seller_name']); ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar w-5 mr-2 text-gray-400"></i>
                                    <span class="formatted-date" data-date="<?php echo $row['created_at']; ?>">
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                                               <!-- Seller Contact -->
                                               <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <h6 class="text-sm font-medium text-gray-700 mb-3">Informasi Penjual</h6>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone w-5 mr-2 text-gray-400"></i>
                                    <span><?php echo htmlspecialchars($row['seller_phone']); ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope w-5 mr-2 text-gray-400"></i>
                                    <span><?php echo htmlspecialchars($row['seller_email']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Total and Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-700 font-medium">Total:</span>
                                <span class="text-lg font-bold text-green-600">
                                    Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?>
                                </span>
                            </div>
                            <?php if ($row['status'] === 'dikirim'): ?>
                                <button type="button"
                                        onclick="confirmDelivery(<?php echo $row['transaction_id']; ?>)"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Konfirmasi Penerimaan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                    $delay += 100;
                    endwhile; 
                    ?>
                </div>
            <?php else: ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                    <i class="fas fa-info-circle text-green-500 text-xl mb-2"></i>
                    <p class="text-green-700">Belum ada pembelian saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script>

// Fungsi untuk update waktu real-time WIB
function updateDateTime() {
    // Waktu UTC server yang diberikan
    const serverTimeUTC = new Date('<?php echo $current_datetime_utc; ?>');
    const currentTime = new Date();
    
    // Hitung selisih waktu sejak halaman dimuat
    const timeDiff = currentTime - window.performance.timing.navigationStart;
    
    // Update waktu berdasarkan waktu server + selisih
    const updatedTime = new Date(serverTimeUTC.getTime() + timeDiff);
    
    // Format waktu
    const hours = String(updatedTime.getUTCHours() + 7).padStart(2, '0');
    const minutes = String(updatedTime.getUTCMinutes()).padStart(2, '0');
    const seconds = String(updatedTime.getUTCSeconds()).padStart(2, '0');
    const day = String(updatedTime.getUTCDate()).padStart(2, '0');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    const month = months[updatedTime.getUTCMonth()];
    const year = updatedTime.getUTCFullYear();

    // Format: "04 Mar 2025 21:43:08"
    const formattedDate = `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
    
    document.getElementById('currentDateTime').textContent = formattedDate;
}

// Update waktu setiap detik
const timeInterval = setInterval(updateDateTime, 1000);

// Update awal saat halaman dimuat
document.addEventListener('DOMContentLoaded', updateDateTime);

// Bersihkan interval saat halaman unload
window.addEventListener('unload', () => {
    clearInterval(timeInterval);
});

        // Confirm delivery function
        function confirmDelivery(transactionId) {
            if (confirm('Apakah Anda yakin telah menerima pesanan ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../pages/update_status.php';
                
                const transactionInput = document.createElement('input');
                transactionInput.type = 'hidden';
                transactionInput.name = 'transaction_id';
                transactionInput.value = transactionId;
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'new_status';
                statusInput.value = 'selesai';
                
                form.appendChild(transactionInput);
                form.appendChild(statusInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value.toLowerCase();
            const cards = document.querySelectorAll('.purchase-card');
            
            cards.forEach(card => {
                const status = card.getAttribute('data-status');
                if (selectedStatus === 'all' || status === selectedStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Add loading animation for cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.purchase-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Format dates
            const dates = document.querySelectorAll('.formatted-date');
            dates.forEach(date => {
                const rawDate = date.getAttribute('data-date');
                if (rawDate) {
                    const formattedDate = new Date(rawDate).toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    date.textContent = formattedDate;
                }
            });
        });

        // Show notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            } transform transition-transform duration-300 ease-in-out z-50`;
            notification.textContent = message;
            
            notification.style.transform = 'translateX(100%)';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Update current time
        function updateCurrentTime() {
            const timeDisplay = document.querySelector('.current-time');
            if (timeDisplay) {
                const now = new Date('<?php echo $current_datetime; ?>');
                timeDisplay.textContent = now.toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
            }
        }

        // Add hover effect for cards
        document.querySelectorAll('.purchase-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('transform', 'scale-102');
            });
            
            card.addEventListener('mouseleave', function() {
                this.classList.remove('transform', 'scale-102');
            });
        });

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
            tooltipTriggers.forEach(trigger => {
                trigger.addEventListener('mouseenter', e => {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'absolute bg-gray-800 text-white text-xs rounded px-2 py-1 mt-1';
                    tooltip.textContent = e.target.dataset.tooltip;
                    tooltip.style.zIndex = '50';
                    e.target.appendChild(tooltip);
                });

                trigger.addEventListener('mouseleave', e => {
                    const tooltip = e.target.querySelector('.bg-gray-800');
                    if (tooltip) tooltip.remove();
                });
            });
        });
    </script>
</body>
</html>