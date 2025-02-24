<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    <link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DuoMart - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #7c3aed;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray:rgb(2, 5, 8);
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

        .login-container {
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
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

        .social-login {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e2e8f0;
            color: var(--gray);
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-3px);
        }

        .forgot-password {
            text-align: right;
            margin-top: 0.5rem;
        }

        .forgot-password a {
            color: var(--gray);
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            color: var(--primary);
        }

        /* Base styles */
        html {
            height: -webkit-fill-available;
            overflow: hidden;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            min-height: -webkit-fill-available;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            width: 100%;
            overflow: hidden;
        }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            body {
                padding: 0;
                background: var(--primary);
            }

            .floating-shape {
                display: none; /* Hide floating shapes on mobile */
            }

            .login-container {
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
                max-height: 90vh;
                transform: translateY(0);
                transition: transform 0.3s ease;
            }

            .logo-container {
                margin-bottom: 1.5rem;
            }

            .logo-container i {
                font-size: 2rem;
            }

            .logo-container h1 {
                font-size: 1.75rem;
                margin: 0.5rem 0;
            }

            .logo-container p {
                font-size: 0.9rem;
                margin-bottom: 1rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-control {
                padding: 0.75rem 1rem 0.75rem 2.5rem;
                font-size: 1rem;
                height: 48px; /* Optimal touch target size */
            }

            .form-icon {
                font-size: 1.1rem;
                left: 0.8rem;
            }

            .password-toggle {
                padding: 12px;
                right: 0.5rem;
            }

            .btn-login {
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                margin-top: 1.5rem;
            }

            .forgot-password {
                margin: 0.5rem 0;
            }

            .forgot-password a {
                font-size: 0.85rem;
                padding: 0.5rem 0;
                display: inline-block;
            }

            .register-link {
                margin-top: 1rem;
                font-size: 0.9rem;
                padding-bottom: 1rem;
            }
        }

        /* Additional mobile improvements */
        @media (max-width: 480px) {
            .login-container {
                padding: 1.25rem;
            }
        }

        /* Prevent pull-to-refresh and bounce effects */
        .login-container {
            overscroll-behavior-y: contain;
            -webkit-overflow-scrolling: touch;
        }

        /* Better touch interactions */
        input, button, a {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        /* Remove autofill background */
        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }

        /* Smooth scrolling */
        .login-container {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .login-container::-webkit-scrollbar {
            display: none;
        }

        /* Focus styles for better visibility */
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Active state for buttons */
        .btn-login:active {
            transform: scale(0.98);
        }

        .google-login-btn {
            width: 100%;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .google-login-btn:hover {
            background: #f8fafc;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .google-login-btn img {
            width: 20px;
            height: 20px;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: var(--gray);
            opacity: 0.2;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .divider span {
            background: rgba(255, 255, 255, 0.9);
            padding: 0 1rem;
            color: var(--gray);
            font-size: 0.9rem;
        }
    </style>
    <!-- Add Google Sign-In API -->
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script>
        // Initialize Google Sign-In
        function initGoogle() {
            gapi.load('auth2', function() {
                gapi.auth2.init({
                    client_id: '636392080842-74c7qbrcelo13o2v9sqsksatsmr7av9q.apps.googleusercontent.com'
                });
            });
        }

        // Handle Google Sign-In
        async function handleGoogleSignIn() {
            const button = document.getElementById('googleSignInBtn');
            const buttonText = document.getElementById('googleBtnText');
            const spinner = document.getElementById('loadingSpinner');

            try {
                // Show loading state
                button.disabled = true;
                buttonText.textContent = 'Signing in...';
                spinner.style.display = 'block';

                const auth2 = await gapi.auth2.getAuthInstance();
                const googleUser = await auth2.signIn();
                const idToken = googleUser.getAuthResponse().id_token;

                // Send token to server
                const response = await fetch('../actions/google_auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id_token: idToken })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    throw new Error(data.message || 'Authentication failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Sign in failed: ' + (error.message || 'Please try again'));
                
                // Reset button state
                button.disabled = false;
                buttonText.textContent = 'Sign in with Google';
                spinner.style.display = 'none';
            }
        }

        // Initialize Google Sign-In on page load
        window.onload = initGoogle;

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
</head>
<body>
    <!-- Floating background shapes -->
    <div class="floating-shape" style="width: 300px; height: 300px; top: 10%; left: -150px;"></div>
    <div class="floating-shape" style="width: 200px; height: 200px; bottom: 10%; right: -100px;"></div>

    <div class="login-container">
        <div class="logo-container">
            <i class="fas fa-shopping-bag"></i>
            <h1>DuoMart</h1>
            <p>Welcome back! Please login to your account</p>
        </div>

        <!-- Google Sign-In Button -->
        <button type="button" class="google-login-btn" id="googleSignInBtn" onclick="handleGoogleSignIn()">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google logo">
            <span id="googleBtnText">Sign in with Google</span>
        </button>

        <div id="loadingSpinner" style="display: none; text-align: center; margin: 10px 0;">
            <i class="fas fa-spinner fa-spin"></i> Signing in...
        </div>

        <div class="divider">
            <span>OR</span>
        </div>

        <!-- Regular Login Form -->
        <form action="../actions/auth.php" method="POST" class="needs-validation" novalidate>
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
                <i class="fas fa-user form-icon"></i>
                <div class="invalid-feedback">Please enter your username.</div>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <i class="fas fa-lock form-icon"></i>
                <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                <div class="invalid-feedback">Please enter your password.</div>
            </div>

            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>

            <p class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>