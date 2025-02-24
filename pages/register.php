<?php
// pages/register.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    <link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DuoMart - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #7c3aed;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite linear;
            z-index: 0;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(400px, -400px) rotate(360deg); }
        }

        .register-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container i {
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-container h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 1rem 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.8rem 1rem 0.8rem 3rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            transition: all 0.3s ease;
        }

        .form-control:focus + .form-icon {
            color: var(--primary);
        }

        .btn-register {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: var(--secondary);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .register-title {
            text-align: center;
            color: var(--dark);
            margin-bottom: 2rem;
            font-weight: 600;
        }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            body {
                padding: 0;
                background: var(--primary);
            }

            .floating-shape {
                display: none;
            }

            .register-container {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 1.5rem;
                border-radius: 20px 20px 0 0;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(10px);
                overflow-y: auto;
                max-height: 92vh;
                transform: translateY(0);
                transition: transform 0.3s ease;
            }

            .logo-container {
                margin-bottom: 1rem;
            }

            .logo-container i {
                font-size: 1.8rem;
            }

            .logo-container h1 {
                font-size: 1.5rem;
                margin: 0.5rem 0;
            }

            .logo-container p {
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }

            .form-group {
                margin-bottom: 0.8rem;
            }

            .form-control {
                height: 48px;
                padding: 0.7rem 1rem 0.7rem 2.5rem;
                font-size: 1rem;
            }

            .form-icon {
                font-size: 1.1rem;
                left: 0.8rem;
            }

            .password-toggle {
                padding: 12px;
                right: 0.5rem;
            }

            .btn-register {
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                margin-top: 1rem;
            }

            .login-link {
                margin-top: 1rem;
                font-size: 0.85rem;
                padding-bottom: env(safe-area-inset-bottom);
            }
        }
    </style>
</head>
<body>
    <!-- Floating background shapes -->
    <div class="floating-shape" style="width: 300px; height: 300px; top: 10%; left: -150px;"></div>
    <div class="floating-shape" style="width: 200px; height: 200px; bottom: 10%; right: -100px;"></div>

    <div class="register-container">
        <div class="logo-container">
            <i class="fas fa-shopping-bag"></i>
            <h1>DuoMart</h1>
            <p>Create your account and start shopping</p>
        </div>

        <form action="../actions/auth.php" method="POST" class="needs-validation" novalidate>
            <div class="form-group">
                <input type="text" class="form-control" name="nama" placeholder="Full Name" required>
                <i class="fas fa-user form-icon"></i>
                <div class="invalid-feedback">Please enter your name.</div>
            </div>

            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                <i class="fas fa-envelope form-icon"></i>
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
                <i class="fas fa-at form-icon"></i>
                <div class="invalid-feedback">Please choose a username.</div>
            </div>

            <div class="form-group">
                <input type="tel" class="form-control" name="telepon" placeholder="Phone Number" pattern="[0-9]{10,15}" required>
                <i class="fas fa-phone form-icon"></i>
                <div class="invalid-feedback">Please enter a valid phone number (10-15 digits).</div>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <i class="fas fa-lock form-icon"></i>
                <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                <div class="invalid-feedback">Please enter a password.</div>
            </div>

            <button type="submit" name="register" class="btn-register">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
            <p class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        function togglePassword(icon) {
            const input = icon.previousElementSibling.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>