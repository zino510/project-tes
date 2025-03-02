<?php
// pages/profile.php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Inisialisasi variable pesan
$error_msg = "";
$success_msg = "";

// Proses form ganti password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    // Validasi input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "Semua field harus diisi!";
    } 
    elseif ($new_password !== $confirm_password) {
        $error_msg = "Password baru dan konfirmasi password tidak cocok!";
    }
    elseif (strlen($new_password) < 6) {
        $error_msg = "Password baru minimal 6 karakter!";
    } 
    else {
        // Cek password lama
        $stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $success_msg = "Password berhasil diubah!";
                } else {
                    $error_msg = "Gagal mengubah password!";
                }
                $update_stmt->close();
            } else {
                $error_msg = "Password saat ini tidak sesuai!";
            }
        }
        $stmt->close();
    }
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM user WHERE id = $user_id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    <link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1e293b;
            --light-color: #f1f5f9;
            --border-radius: 12px;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f6f7ff 0%, #e8eeff 100%);
            min-height: 100vh;
            padding: 30px 20px;
            color: var(--dark-color);
        }

        .page-header {
            background: linear-gradient(120deg, #2563eb, #1e40af);
            padding: 40px 20px;
            border-radius: var(--border-radius);
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            color: white;
            box-shadow: var(--card-shadow);
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5z' fill='rgba(255,255,255,0.1)' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.1;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--card-shadow);
            border-radius: var(--border-radius);
            padding: 40px;
            margin-bottom: 30px;
            animation: slideIn 0.5s ease-out forwards;
        }

        .profile-picture-container {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto 30px;
        }

        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .photo-upload-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary-color);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            border: none;
            color: white;
        }

        .photo-upload-btn:hover {
            transform: scale(1.1);
            background: #1d4ed8;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .btn {
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-success {
            background: var(--success-color);
            border: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary {
            background: var(--secondary-color);
            border: none;
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            background: #475569;
        }

        .alert {
            border: none;
            border-radius: var(--border-radius);
            padding: 16px;
            margin-bottom: 24px;
            position: relative;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success-color);
            color: #065f46;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger-color);
            color: #991b1b;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dark-mode {
            background: linear-gradient(135deg, #1a1c2e 0%, #2d3748 100%);
            color: #e2e8f0;
        }

        .dark-mode .form-container {
            background: rgba(30, 41, 59, 0.95);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .dark-mode .form-control {
            background: #2d3748;
            border-color: #4a5568;
            color: #e2e8f0;
        }

        .dark-mode .section-title {
            color: #e2e8f0;
            border-bottom-color: #4a5568;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 10px;
            }

            .profile-picture-container {
                width: 150px;
                height: 150px;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="theme-toggle">
        <button id="themeToggle" class="btn btn-outline-light">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="page-header">
        <h2 class="text-center mb-0">Profil Pengguna</h2>
    </div>

    <div class='form-container'>
        <div class="profile-section">
            <div class="profile-picture-container">
                <?php if (!empty($user['foto'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto Profil" class="profile-picture" id="profileImage">
                <?php else: ?>
                    <img src="../assets/default-avatar.png" alt="Default Foto Profil" class="profile-picture" id="profileImage">
                <?php endif; ?>
                <label for="foto" class="photo-upload-btn">
                    <i class="fas fa-camera"></i>
                </label>
            </div>
            <h4 class="mt-3"><?php echo htmlspecialchars($user['nama']); ?></h4>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <h3 class="section-title">Informasi Pribadi</h3>
        <form action='../actions/update_profile.php' method='POST' enctype='multipart/form-data' class='mb-4'>
            <div class='row'>
                <div class='col-md-6 mb-3'>
                    <label for='nama' class='form-label'>Nama Lengkap</label>
                    <input type='text' name='nama' id='nama' class='form-control' value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                </div>

                <div class='col-md-6 mb-3'>
                    <label for='username' class='form-label'>Username</label>
                    <input type='text' name='username' id='username' class='form-control' value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
            </div>

            <div class='row'>
                <div class='col-md-6 mb-3'>
                <label for='telepon' class='form-label'>Nomor Telepon</label>
                    <input type='text' name='telepon' id='telepon' class='form-control' value="<?php echo htmlspecialchars($user['telepon']); ?>">
                </div>

                <div class='col-md-6 mb-3'>
                    <label for='email' class='form-label'>Email</label>
                    <input type='email' name='email' id='email' class='form-control' value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>
            </div>

            <div class='mb-3'>
                <label for='bio' class='form-label'>Biografi</label>
                <textarea name='bio' id='bio' class='form-control' rows='4' placeholder="Ceritakan sedikit tentang diri Anda..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>

            <input type='file' name='foto' id='foto' accept="image/*" style="display: none;">

            <div class="d-flex gap-2 flex-wrap">
                <a href="../pages/dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type='submit' class='btn btn-success'>
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Form Ganti Password -->
    <div class='form-container'>
        <h3 class="section-title">Ganti Password</h3>
        
        <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form method="POST" id="changePasswordForm" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class='mb-3'>
                <label for='current_password' class='form-label'>Password Saat Ini</label>
                <div class="input-group">
                    <input type='password' name='current_password' id='current_password' class='form-control' required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class='mb-3'>
                <label for='new_password' class='form-label'>Password Baru</label>
                <div class="input-group">
                    <input type='password' name='new_password' id='new_password' class='form-control' required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="passwordStrengthFeedback" class="form-text mt-2"></div>
            </div>

            <div class='mb-3'>
                <label for='confirm_password' class='form-label'>Konfirmasi Password</label>
                <div class="input-group">
                    <input type='password' name='confirm_password' id='confirm_password' class='form-control' required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type='submit' class='btn btn-success'>
                <i class="fas fa-key me-2"></i>Ganti Password
            </button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = document.getElementById(this.getAttribute('data-target'));
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password strength checker
        const passwordStrength = {
            0: ["Sangat Lemah", "#ff4444"],
            1: ["Lemah", "#ffa700"],
            2: ["Sedang", "#ffdd00"],
            3: ["Kuat", "#00c851"],
            4: ["Sangat Kuat", "#007E33"]
        };

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;
            return strength;
        }

        document.getElementById('new_password').addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            const feedback = document.getElementById('passwordStrengthFeedback');
            feedback.textContent = `Kekuatan Password: ${passwordStrength[strength][0]}`;
            feedback.style.color = passwordStrength[strength][1];
        });

        // Preview foto sebelum upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImage').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Dark mode toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const icon = themeToggle.querySelector('i');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');
            
            localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
        });

        // Check saved theme
        if (localStorage.getItem('darkMode') === 'true') {
            body.classList.add('dark-mode');
            themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
        }

        // Form validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            
            if (newPass.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
                return false;
            }
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return false;
            }
        });
    </script>
</body>
</html>