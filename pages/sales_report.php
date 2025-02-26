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

// Get date range from query parameters with default values
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Initialize default values for statistics
$stats = [
    'total_orders' => 0,
    'total_revenue' => 0,
    'average_order_value' => 0
];

// Initialize default values for status data
$status_data = [
    'pending' => 0,
    'dibayar' => 0,
    'dikirim' => 0,
    'selesai' => 0,
    'dibatalkan' => 0
];

try {
    // Get sales statistics
    $stats_query = "SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_harga), 0) as total_revenue,
        COALESCE(AVG(total_harga), 0) as average_order_value
    FROM transactions 
    WHERE user_id = ? 
    AND created_at BETWEEN ? AND ?";

    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    
    if ($stats_result && $stats_result->num_rows > 0) {
        $stats = $stats_result->fetch_assoc();
    }

    // Get status distribution
    $status_query = "SELECT 
        status,
        COUNT(*) as count
    FROM transactions
    WHERE user_id = ?
    AND created_at BETWEEN ? AND ?
    GROUP BY status";

    $status_stmt = $conn->prepare($status_query);
    $status_stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    while ($row = $status_result->fetch_assoc()) {
        $status_data[$row['status']] = $row['count'];
    }

    // Get products data
    $products_query = "SELECT 
        p.id,
        p.nama_produk,
        p.harga,
        p.gambar,
        COALESCE(COUNT(t.id), 0) as order_count,
        COALESCE(SUM(t.total_harga), 0) as revenue
    FROM product p
    LEFT JOIN transactions t ON p.user_id = t.user_id 
        AND t.created_at BETWEEN ? AND ?
    WHERE p.user_id = ?
    GROUP BY p.id, p.nama_produk, p.harga, p.gambar
    ORDER BY revenue DESC";

    $products_stmt = $conn->prepare($products_query);
    $products_stmt->bind_param("ssi", $start_date, $end_date, $user_id);
    $products_stmt->execute();
    $products_result = $products_stmt->get_result();

    // Get daily sales data for chart
    $daily_sales_query = "SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        COALESCE(SUM(total_harga), 0) as revenue
    FROM transactions
    WHERE user_id = ?
    AND created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date";

    $daily_stmt = $conn->prepare($daily_sales_query);
    $daily_stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $daily_stmt->execute();
    $daily_result = $daily_stmt->get_result();

    $dates = [];
    $revenues = [];
    $orders = [];

    while ($row = $daily_result->fetch_assoc()) {
        $dates[] = $row['date'];
        $revenues[] = (float)$row['revenue'];
        $orders[] = (int)$row['orders'];
    }

} catch (Exception $e) {
    error_log("Error in sales report: " . $e->getMessage());
    // Initialize empty arrays if query fails
    $dates = [];
    $revenues = [];
    $orders = [];
}

// Format numbers for display
$formatted_stats = [
    'total_orders' => number_format($stats['total_orders']),
    'total_revenue' => number_format($stats['total_revenue'], 0, ',', '.'),
    'average_order_value' => number_format($stats['average_order_value'], 0, ',', '.')
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Duo Mart</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #2980b9;
            --secondary: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gradient-1: linear-gradient(135deg, #2980b9, #3498db);
            --gradient-2: linear-gradient(135deg, #2ecc71, #27ae60);
            --gradient-3: linear-gradient(135deg, #e74c3c, #c0392b);
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: var(--dark);
        }

        .page-header {
            background: var(--gradient-1);
            padding: 2rem 0;
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-section h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .card {
            border: none;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .status-card {
            text-align: center;
            padding: 1.5rem;
        }

        .status-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .status-card.pending { background: linear-gradient(135deg, #f6d365, #fda085); }
        .status-card.paid { background: linear-gradient(135deg, #84fab0, #8fd3f4); }
        .status-card.shipped { background: linear-gradient(135deg, #a1c4fd, #c2e9fb); }
        .status-card.completed { background: linear-gradient(135deg, #88d3ce, #6e45e2); }
        .status-card.cancelled { background: linear-gradient(135deg, #ff9a9e, #fad0c4); }

        .stat-card {
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: skewX(-15deg);
        }

        .stat-icon {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 3rem;
            opacity: 0.2;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .chart-card {
            padding: 1.5rem;
            background: white;
            margin-bottom: 2rem;
        }

        .date-filter {
            background: white;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .date-filter input {
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
        }

        .product-table {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-md);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background: rgba(0,0,0,0.1);
        }

        .progress-bar {
            background: var(--gradient-1);
        }

        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 1rem;
            }
        }

        .navigation-buttons {
        background: white;
        padding: 1rem;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
    }

    .btn-nav {
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-nav:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .user-info {
        background: rgba(255, 255, 255, 0.9);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-sm);
    }

    .user-info p {
        color: var(--dark);
        font-weight: 500;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .navigation-buttons .d-flex {
            flex-direction: column;
            gap: 0.5rem;
        }

        .navigation-buttons .d-flex .btn-nav {
            width: 100%;
            text-align: left;
        }

        .user-info {
            margin-top: 1rem;
            text-align: center;
        }
    }

    /* Hover effects for buttons */
    .btn-primary {
        background: var(--gradient-1);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2573a7, #2980b9);
    }

    .btn-outline-primary {
        border: 2px solid var(--primary);
        color: var(--primary);
        background: transparent;
    }

    .btn-outline-primary:hover {
        background: var(--primary);
        color: white;
    }

    /* Animation for user info */
    .user-info {
        animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="welcome-section">
                <h1>Dashboard Penjualan</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($user_login); ?></p>
                <p class="text-small">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo date('l, d F Y H:i'); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="container">


    <div class="container">
    <!-- Navigation Buttons -->
    <div class="navigation-buttons mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-primary btn-nav">
                    <i class="fas fa-arrow-left me-2"></i>
                    Kembali ke Dashboard
                </a>
                <a href="my_products.php" class="btn btn-outline-primary btn-nav">
                    <i class="fas fa-box me-2"></i>
                    Produk Saya
                </a>
                <a href="post_barang.php" class="btn btn-outline-primary btn-nav">
                    <i class="fas fa-plus-circle me-2"></i>
                    Tambah Produk
                </a>
            </div>
            <div class="user-info text-end">
                <p class="mb-0"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user_login); ?></p>
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo date('Y-m-d H:i:s'); ?>
                </small>
            </div>
        </div>
    </div>

        <!-- Date Filter -->
        <div class="date-filter shadow-sm">
            <i class="fas fa-calendar"></i>
            <input type="text" id="daterange" class="form-control" 
                   value="<?php echo date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)); ?>">
        </div>

        
        <!-- Status Cards -->
        <div class="row g-4 mb-4">
            <div class="col">
                <div class="card status-card pending text-white">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo $status_data['pending']; ?></h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
            <div class="col">
                <div class="card status-card paid text-white">
                    <i class="fas fa-check-circle"></i>
                    <h3><?php echo $status_data['dibayar']; ?></h3>
                    <p class="mb-0">Dibayar</p>
                </div>
            </div>
            <div class="col">
                <div class="card status-card shipped text-white">
                    <i class="fas fa-shipping-fast"></i>
                    <h3><?php echo $status_data['dikirim']; ?></h3>
                    <p class="mb-0">Dikirim</p>
                </div>
            </div>
            <div class="col">
                <div class="card status-card completed text-white">
                    <i class="fas fa-flag-checkered"></i>
                    <h3><?php echo $status_data['selesai']; ?></h3>
                    <p class="mb-0">Selesai</p>
                </div>
            </div>
            <div class="col">
                <div class="card status-card cancelled text-white">
                    <i class="fas fa-ban"></i>
                    <h3><?php echo $status_data['dibatalkan']; ?></h3>
                    <p class="mb-0">Dibatalkan</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card" style="background: linear-gradient(135deg, #6b8dd6, #8E37D7);">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h6>Total Pesanan</h6>
                    <h3><?php echo number_format($stats['total_orders']); ?></h3>
                    <p class="mb-0">Periode ini</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card" style="background: linear-gradient(135deg, #5B247A, #1BCEDF);">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h6>Total Pendapatan</h6>
                    <h3>Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></h3>
                    <p class="mb-0">Periode ini</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card" style="background: linear-gradient(135deg, #184e68, #57ca85);">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6>Rata-rata Pesanan</h6>
                    <h3>Rp <?php echo number_format($stats['average_order_value'], 0, ',', '.'); ?></h3>
                    <p class="mb-0">Periode ini</p>
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="card chart-card mb-4">
            <h5 class="card-title mb-4">Grafik Penjualan</h5>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Products Table -->
        <div class="card product-table">
            <h5 class="card-title mb-4">Performa Produk</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Jumlah Pesanan</th>
                            <th>Pendapatan</th>
                            <th>Performa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $max_revenue = 0;
                        $products_data = [];
                        while ($row = $products_result->fetch_assoc()) {
                            $products_data[] = $row;
                            if ($row['revenue'] > $max_revenue) {
                                $max_revenue = $row['revenue'];
                            }
                        }
                        
                        foreach ($products_data as $product): 
                            $percentage = $max_revenue > 0 ? ($product['revenue'] / $max_revenue) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../<?php echo htmlspecialchars($product['gambar']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                                         class="me-2"
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <span><?php echo htmlspecialchars($product['nama_produk']); ?></span>
                                </div>
                            </td>
                            <td><?php echo number_format($product['order_count']); ?></td>
                            <td>Rp <?php echo number_format($product['revenue'], 0, ',', '.'); ?></td>
                            <td style="width: 30%;">
                                <div class="progress">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%" 
                                         aria-valuenow="<?php echo $percentage; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Required JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
    // Date Range Picker Initialization
    $(function() {
        $('#daterange').daterangepicker({
            startDate: moment('<?php echo $start_date; ?>'),
            endDate: moment('<?php echo $end_date; ?>'),
            locale: {
                format: 'DD/MM/YYYY'
            }
        }, function(start, end) {
            window.location.href = `sales_report.php?start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`;
        });
    });

    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [
                {
                    label: 'Pendapatan',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: '#2980b9',
                    backgroundColor: 'rgba(41, 128, 185, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Pesanan',
                    data: <?php echo json_encode($orders); ?>,
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#2c3e50',
                    bodyColor: '#2c3e50',
                    borderColor: '#e1e1e1',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 8,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 0) {
                                    label += new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(context.parsed.y);
                                } else {
                                    label += context.parsed.y + ' pesanan';
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Pendapatan (Rp)',
                        color: '#2980b9',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        borderDash: [5, 5]
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Jumlah Pesanan',
                        color: '#27ae60',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    // Animate numbers
    function animateNumber(element, start, end, duration) {
        let current = start;
        const range = end - start;
        const increment = end > start ? 1 : -1;
        const stepTime = Math.abs(Math.floor(duration / range));
        const timer = setInterval(() => {
            current += increment;
            element.textContent = new Intl.NumberFormat('id-ID').format(current);
            if (current == end) {
                clearInterval(timer);
            }
        }, stepTime);
    }

    // Animate all number elements when page loads
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.stat-card h3').forEach(el => {
            const value = parseInt(el.textContent.replace(/[^0-9]/g, ''));
            el.textContent = '0';
            animateNumber(el, 0, value, 1000);
        });

        // Add hover effect to status cards
        document.querySelectorAll('.status-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 16px rgba(0,0,0,0.2)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            });
        });
    });

    // Back to top button
    const backToTop = document.createElement('button');
    backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTop.className = 'back-to-top';
    document.body.appendChild(backToTop);

    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 100) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    });
    </script>

    <style>
    /* Additional styles for back to top button */
    .back-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        cursor: pointer;
        display: none;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .back-to-top:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    /* Additional animation for cards */
    .card {
        animation: fadeInUp 0.5s ease-out;
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

    /* Loading animation */
    .loading {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading::after {
        content: '';
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    </style>

</body>
</html>