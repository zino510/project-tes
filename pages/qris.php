<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRIS Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #60a5fa;
            --background-color: #f0f9ff;
            --text-color: #1e293b;
            --border-radius: 20px;
            --box-shadow: 0 10px 30px rgba(37, 99, 235, 0.1);
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--background-color) 0%, #ffffff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .qris-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .qris-header {
            margin-bottom: 2rem;
            position: relative;
        }

        .qris-header h3 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .qris-image-wrapper {
            background: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            margin: 2rem 0;
            transition: transform 0.3s ease;
        }

        .qris-image-wrapper:hover {
            transform: scale(1.02);
        }

        .qris-image {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 10px;
            border: 2px solid var(--accent-color);
        }

        .scan-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
            animation: scanning 2s linear infinite;
        }

        @keyframes scanning {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(300px);
            }
            100% {
                transform: translateY(0);
            }
        }

        .instruction-text {
            color: var(--text-color);
            font-size: 1.1rem;
            margin: 1.5rem 0;
            padding: 1rem;
            background: rgba(96, 165, 250, 0.1);
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }

        .btn-back {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            margin-top: 1rem;
        }

        .btn-back:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
            color: white;
        }

        .timer-container {
            margin: 1.5rem 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .qris-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .qris-header h3 {
                font-size: 1.5rem;
            }
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="qris-container">
    <div class="qris-header">
        <h3>
            <i class="fas fa-qrcode me-2"></i>
            Scan QRIS untuk Pembayaran
        </h3>
        <div class="status-badge">
            <i class="fas fa-clock"></i>
            Menunggu Pembayaran
        </div>
    </div>

    <div class="qris-image-wrapper">
        <div class="scan-animation"></div>
        <img src="../uploads/WhatsApp Image 2025-02-25 at 11.34.39.jpeg" alt="QRIS Pembayaran" class="qris-image">
    </div>

    <div class="timer-container">
        <i class="fas fa-stopwatch me-2"></i>
        <span id="timer">15:00</span>
    </div>

    <div class="instruction-text">
        <i class="fas fa-info-circle me-2"></i>
        Silakan scan kode QR menggunakan aplikasi e-wallet atau mobile banking Anda untuk menyelesaikan pembayaran
    </div>

    <a href="dashboard.php" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Kembali ke Dashboard
    </a>
</div>

<script>
// Timer countdown implementation
function startTimer(duration, display) {
    var timer = duration, minutes, seconds;
    var countdown = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(countdown);
            display.textContent = "Waktu Habis!";
            if(confirm("Waktu pembayaran telah habis. Kembali ke dashboard?")) {
                window.location.href = "dashboard.php";
            }
        }
    }, 1000);
}

window.onload = function () {
    var fifteenMinutes = 60 * 15,
        display = document.querySelector('#timer');
    startTimer(fifteenMinutes, display);
};
</script>

</body>
</html>