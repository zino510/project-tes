<!DOCTYPE html>
<html lang="id">
<head>
<link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - DuoMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #7c3aed;
            --success: #10b981;
            --light: #f8fafc;
            --dark: #1e293b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem;
            color: var(--light);
        }

        .success-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            font-size: 3rem;
            color: white;
        }

        .success-title {
            color: var(--dark);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            animation: fadeInUp 0.5s ease-out 0.3s both;
        }

        .success-message {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            animation: fadeInUp 0.5s ease-out 0.5s both;
        }

        .btn-back {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease-out 0.7s both;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            color: white;
        }

        .floating-shapes div {
            position: absolute;
            border-radius: 50%;
            animation: float 20s infinite linear;
            opacity: 0.1;
        }

        .shape-1 {
            width: 60px;
            height: 60px;
            background: var(--primary);
            top: 10%;
            left: 10%;
        }

        .shape-2 {
            width: 80px;
            height: 80px;
            background: var(--secondary);
            bottom: 10%;
            right: 10%;
            animation-delay: -5s;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
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

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(100px, -100px) rotate(360deg); }
        }

        .order-details {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            color: var(--dark);
            animation: fadeInUp 0.5s ease-out 0.6s both;
        }

        .order-details h3 {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .timestamp {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 2rem;
            animation: fadeInUp 0.5s ease-out 0.8s both;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <!-- Floating shapes for background -->
        <div class="floating-shapes">
            <div class="shape-1"></div>
            <div class="shape-2"></div>
        </div>

        <!-- Success icon -->
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <!-- Success content -->
        <h1 class="success-title">Pesanan Berhasil!</h1>
        <p class="success-message">Terima kasih telah berbelanja di DuoMart. Pesanan Anda sedang diproses dan akan segera dikirim.</p>

        <!-- Order details -->
        <div class="order-details">
            <h3><i class="fas fa-info-circle me-2"></i>Informasi Pesanan</h3>
            <p><strong>Order ID:</strong> #<?php echo rand(100000, 999999); ?></p>
            <p><strong>Status:</strong> <span class="badge bg-success">Berhasil</span></p>
        </div>

    <!-- Timestamp -->
<p class="timestamp">
    <i class="far fa-clock me-1"></i>
    <?php 
        date_default_timezone_set('Asia/Jakarta'); // Set timezone to GMT+7
        echo date('d M Y, H:i'); 
    ?> WIB
</p>
        <!-- Back button -->
        <a href="dashboard.php" class="btn-back">
            <i class="fas fa-home"></i>
            Kembali ke Dashboard
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>