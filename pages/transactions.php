    <?php
    session_start();
    include '../config/database.php';

// Set timezone ke WIB
date_default_timezone_set('Asia/Jakarta');

// Waktu UTC dari input: 2025-03-04 05:45:58
$current_datetime_utc = '2025-03-04 13:00:00';
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

// Debug mode
$debug = true;
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function untuk logging
function debug_log($message) {
    global $debug;
    if ($debug) {
        error_log("[Transaction Debug] " . $message);
    }
}

    // Update status transaksi jika ada permintaan
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : 0;
        $new_status = isset($_POST['new_status']) ? strtolower(trim($_POST['new_status'])) : '';

        debug_log("Update attempt - Transaction ID: $transaction_id, New Status: $new_status");

        // Validasi input
        if ($transaction_id <= 0 || empty($new_status)) {
            debug_log("Invalid input data");
            header("Location: transactions.php?status=error&message=Data tidak valid");
            exit();
        }

        // Validasi status
        $allowed_statuses = ['pending', 'dibayar', 'dikirim', 'selesai', 'dibatalkan'];
        if (!in_array($new_status, $allowed_statuses)) {
            debug_log("Invalid status: $new_status");
            header("Location: transactions.php?status=error&message=Status tidak valid");
            exit();
        }

        try {
            // Begin transaction
            $conn->begin_transaction();

            // Persiapkan query untuk update
            $update_query = "
                UPDATE transactions t 
                INNER JOIN transaction_details td ON t.id = td.transaction_id 
                INNER JOIN product p ON td.product_id = p.id 
                SET t.status = ?
                WHERE t.id = ? AND p.user_id = ?
            ";

            $stmt = $conn->prepare($update_query);
            
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("sii", $new_status, $transaction_id, $user_id);
            debug_log("Executing update query with params: status=$new_status, transaction_id=$transaction_id, user_id=$user_id");

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $conn->commit();
                    debug_log("Update successful");
                    header("Location: transactions.php?status=success&message=Status berhasil diperbarui");
                    exit();
                } else {
                    throw new Exception("No rows affected");
                }
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            debug_log("Error: " . $e->getMessage());
            header("Location: transactions.php?status=error&message=Terjadi kesalahan sistem");
            exit();
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    // Ambil data pesanan
    $pesanan_query = "
        SELECT 
            t.id as transaction_id, 
            t.user_id as buyer_id, 
            t.total_harga, 
            t.status, 
            t.created_at, 
            u.nama as buyer_name, 
            u.email as buyer_email, 
            td.product_id, 
            td.quantity, 
            td.harga as product_price, 
            p.nama_produk, 
            p.deskripsi, 
            p.gambar 
        FROM transactions t
        JOIN user u ON t.user_id = u.id
        JOIN transaction_details td ON t.id = td.transaction_id
        JOIN product p ON td.product_id = p.id
        WHERE p.user_id = ?
        ORDER BY t.created_at DESC
    ";

    try {
        $stmt = $conn->prepare($pesanan_query);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $pesanan_result = $stmt->get_result();

        // Count different status
        $pending_count = 0;
        $completed_count = 0;

        while ($row = $pesanan_result->fetch_assoc()) {
            if (strtolower($row['status']) === 'pending') $pending_count++;
            if (strtolower($row['status']) === 'selesai') $completed_count++;
        }

        // Reset pointer
        $pesanan_result->data_seek(0);

    } catch (Exception $e) {
        debug_log("Error fetching orders: " . $e->getMessage());
        die("Terjadi kesalahan dalam mengambil data pesanan");
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
        <link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pesanan Masuk - Duo Mart</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        
        <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .transaction-card {
                animation: fadeIn 0.3s ease-out forwards;
                opacity: 0;
            }
            
            .status-badge {
                transition: all 0.2s ease;
            }
            
            .modal-overlay {
                backdrop-filter: blur(4px);
                transition: all 0.3s ease;
            }
            
            .modal-content {
                transform: scale(0.95);
                opacity: 0;
                transition: all 0.3s ease-in-out;
            }
            
            .modal-content.scale-100 {
                transform: scale(1);
                opacity: 1;
            }

            body.modal-open {
                overflow: hidden;
                padding-right: 15px;
            }

            .notification {
                transition: all 0.3s ease-in-out;
                transform: translateX(100%);
                opacity: 0;
            }

            .notification.show {
                transform: translateX(0);
                opacity: 1;
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

            /* Loading spinner */
            .spinner {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body class="bg-gray-50 min-h-screen">
      <!-- Top Navigation Bar -->
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="../pages/dashboard.php" class="flex items-center text-gray-800 hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </div>
            
            <div class="flex items-center space-x-6">
                <!-- User Info -->
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                    <span class="font-medium text-gray-700"><?php echo htmlspecialchars($current_user); ?></span>
                </div>
                
                <!-- DateTime Display -->
                <div class="flex items-center text-gray-600">
                    <i class="far fa-clock mr-2 text-blue-600"></i>
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
                        <i class="fas fa-shopping-cart text-blue-600 mr-3"></i>
                        Manajemen Transaksi
                    </h1>
                    <p class="text-gray-600 text-lg">
                        Kelola semua transaksi jual beli Anda dalam satu tempat
                    </p>
                </div>
                
                <!-- Navigation Tabs -->
                <div class="flex justify-center space-x-4 mb-8">
                    <a href="transactions.php" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                        <i class="fas fa-store mr-2"></i>
                        Pesanan Masuk
                    </a>
                    <a href="purchases.php" 
                    class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Riwayat Pembelian
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <!-- Total Orders -->
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Pesanan</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1">
                                <?php echo $pesanan_result->num_rows; ?>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Keseluruhan pesanan</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders -->
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pesanan Pending</p>
                            <h3 class="text-2xl font-bold text-yellow-600 mt-1">
                                <?php echo $pending_count; ?>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Menunggu tindakan</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Completed Orders -->
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pesanan Selesai</p>
                            <h3 class="text-2xl font-bold text-green-600 mt-1">
                                <?php echo $completed_count; ?>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Transaksi sukses</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-store text-2xl text-blue-600 mr-3"></i>
                        <h2 class="text-2xl font-bold text-gray-800">Pesanan Masuk</h2>
                    </div>
                    <select id="statusFilter" 
                            class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="dibayar">Dibayar</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                <?php if ($pesanan_result && $pesanan_result->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php 
                        $delay = 0;
                        while ($row = $pesanan_result->fetch_assoc()): 
                        ?>
                        <div class="transaction-card bg-white rounded-xl shadow-sm hover:shadow-md p-6 scale-hover"
                            style="animation-delay: <?php echo $delay; ?>ms"
                            data-status="<?php echo strtolower($row['status']); ?>">
                            <!-- Order Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-blue-600 text-xl"></i>
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

                            <!-- Order Details -->
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
                                        <i class="fas fa-user w-5 mr-2 text-gray-400"></i>
                                        <span><?php echo htmlspecialchars($row['buyer_name']); ?></span>
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-calendar w-5 mr-2 text-gray-400"></i>
                                        <span><?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-700 font-medium">Total:</span>
                                    <span class="text-lg font-bold text-blue-600">
                                        Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                                <div class="flex space-x-3">
                                    <button type="button"
                                            onclick="openModal('pesananDetailModal<?php echo $row['transaction_id']; ?>')"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Detail Transaksi
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php 
                        $delay += 100;
                        endwhile; 
                        ?>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
                        <i class="fas fa-info-circle text-blue-500 text-xl mb-2"></i>
                        <p class="text-blue-700">Tidak ada pesanan saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
            <!-- Modals Container -->
            <div id="modalsContainer">
            <?php 
            $pesanan_result->data_seek(0);
            while ($row = $pesanan_result->fetch_assoc()): 
            ?>
            <div id="pesananDetailModal<?php echo $row['transaction_id']; ?>" 
                class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="modal-content bg-white w-full max-w-2xl rounded-xl shadow-2xl">
                        <!-- Modal Header -->
                        <div class="border-b border-gray-200 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h5 class="text-xl font-bold text-gray-800">Detail Transaksi</h5>
                                <button onclick="closeModal('pesananDetailModal<?php echo $row['transaction_id']; ?>')"
                                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-4">
                            <div class="space-y-4">
                                <!-- Transaction Information -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h6 class="font-semibold text-gray-800 mb-3">Informasi Transaksi</h6>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-3">
                                            <p class="flex items-center text-gray-600">
                                                <i class="fas fa-hashtag w-6 text-gray-400"></i>
                                                <span class="ml-2">ID: #<?php echo $row['transaction_id']; ?></span>
                                            </p>
                                            <p class="flex items-center text-gray-600">
                                                <i class="fas fa-calendar w-6 text-gray-400"></i>
                                                <span class="ml-2"><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></span>
                                            </p>
                                        </div>
                                        <div class="space-y-3">
                                            <p class="flex items-center text-gray-600">
                                                <i class="fas fa-box w-6 text-gray-400"></i>
                                                <span class="ml-2"><?php echo htmlspecialchars($row['nama_produk']); ?></span>
                                            </p>
                                            <p class="flex items-center text-gray-600">
                                                <i class="fas fa-tag w-6 text-gray-400"></i>
                                                <span class="ml-2">Rp <?php echo number_format($row['product_price'], 0, ',', '.'); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buyer Information -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h6 class="font-semibold text-gray-800 mb-3">Informasi Pembeli</h6>
                                    <div class="space-y-3">
                                        <p class="flex items-center text-gray-600">
                                            <i class="fas fa-user w-6 text-gray-400"></i>
                                            <span class="ml-2"><?php echo htmlspecialchars($row['buyer_name']); ?></span>
                                        </p>
                                        <p class="flex items-center text-gray-600">
                                            <i class="fas fa-envelope w-6 text-gray-400"></i>
                                            <span class="ml-2"><?php echo htmlspecialchars($row['buyer_email']); ?></span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Status Update Form -->
                                <form method="post" action="" class="mt-6">
                                    <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                                    <div class="space-y-3">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Update Status Transaksi
                                        </label>
                                        <select name="new_status" 
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                data-current="<?php echo htmlspecialchars($row['status']); ?>">
                                            <?php
                                            $statuses = ['pending', 'dibayar', 'dikirim', 'selesai', 'dibatalkan'];
                                            foreach ($statuses as $status) {
                                                $selected = (strtolower($row['status']) === $status) ? 'selected' : '';
                                                $status_label = ucfirst($status);
                                                echo "<option value=\"$status\" $selected>$status_label</option>";
                                            }
                                            ?>
                                        </select>
                                        <button type="submit" 
                                                name="update_status"
                                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                            <span class="inline-flex items-center">
                                                <i class="fas fa-sync-alt mr-2"></i>
                                                <span>Update Status</span>
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Scripts -->
        <script>
// Fungsi untuk update waktu real-time WIB
function updateDateTime() {
    // Dapatkan waktu saat ini
    const now = new Date();
    
    // Tambahkan 7 jam untuk WIB (GMT+7)
    const wibTime = new Date(now.getTime() + (7 * 60 * 60 * 1000));
    
    // Format waktu ke WIB
    const options = {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta'
    };

    try {
        const formatter = new Intl.DateTimeFormat('id-ID', options);
        let formattedDate = formatter.format(wibTime)
            .replace(/\./g, ':')
            .replace(',', '');
        
        document.getElementById('currentDateTime').textContent = formattedDate;
    } catch (error) {
        console.error('Error formatting date:', error);
    }
}

// Update waktu setiap detik
const timeInterval = setInterval(updateDateTime, 1000);

// Update awal saat halaman dimuat
document.addEventListener('DOMContentLoaded', updateDateTime);

// Bersihkan interval saat halaman unload
window.addEventListener('unload', () => {
    clearInterval(timeInterval);
})
// Update awal
document.addEventListener('DOMContentLoaded', updateDateTime);

            // Check for URL parameters and show notification
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const status = urlParams.get('status');
                const message = urlParams.get('message');
                
                if (status && message) {
                    showNotification(decodeURIComponent(message), status);
                    
                    // Clean up URL after showing notification
                    window.history.replaceState({}, document.title, window.location.pathname);
                }

                // Initialize card animations
                const cards = document.querySelectorAll('.transaction-card');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            });

            // Modal handling
            let activeModal = null;

            function openModal(modalId) {
                if (activeModal) {
                    closeModal(activeModal);
                }

                const modal = document.getElementById(modalId);
                if (!modal) return;

                activeModal = modalId;
                modal.classList.remove('hidden');
                
                const modalContent = modal.querySelector('.modal-content');
                setTimeout(() => {
                    modalContent.classList.add('scale-100', 'opacity-100');
                    modalContent.classList.remove('scale-95', 'opacity-0');
                }, 10);

                document.body.classList.add('modal-open');
                modal.style.zIndex = '1000';
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;

                const modalContent = modal.querySelector('.modal-content');
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');

                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                    
                    if (activeModal === modalId) {
                        activeModal = null;
                    }
                }, 300);
            }

            // Event Listeners
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && activeModal) {
                    closeModal(activeModal);
                }
            });

            document.addEventListener('click', function(e) {
                if (activeModal && e.target.classList.contains('modal-overlay')) {
                    closeModal(activeModal);
                }
            });

            // Status filter functionality
            document.getElementById('statusFilter').addEventListener('change', function() {
                const selectedStatus = this.value.toLowerCase();
                const cards = document.querySelectorAll('.transaction-card');
                
                cards.forEach(card => {
                    const cardStatus = card.getAttribute('data-status');
                    if (selectedStatus === 'all' || cardStatus === selectedStatus) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Notification system
            function showNotification(message, type = 'success') {
                const existingNotif = document.querySelector('.notification');
                if (existingNotif) {
                    existingNotif.remove();
                }

                const notification = document.createElement('div');
                notification.className = `notification fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${
                    type === 'success' ? 'bg-green-600' : 'bg-red-600'
                }`;
                notification.style.zIndex = '9999';
                notification.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                }, 10);

                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
        </script>
    </body>
    </html>