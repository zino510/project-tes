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
    // Cek apakah password baru dan konfirmasi sama
    elseif ($new_password !== $confirm_password) {
        $error_msg = "Password baru dan konfirmasi password tidak cocok!";
    }
    // Cek panjang minimal password
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
            // Verifikasi password lama
            if (password_verify($current_password, $user['password'])) {
                // Hash password baru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
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

// Ambil data user berdasarkan user_id
$result = $conn->query("SELECT * FROM user WHERE id = $user_id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang='id'>
<head>
<link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
<link rel="shortcut icon" href="../favicon/favicon.ico" type="image/x-icon">
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <!-- Bootstrap CSS CDN -->
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            background: white;
            padding: 30px;
            max-width: 800px;
            margin: 20px auto;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .profile-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-picture-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
        }

        .profile-picture {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .photo-upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-upload-btn:hover {
            background: #224abe;
        }

        .photo-upload-btn i {
            color: white;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: var(--success-color);
            border: none;
        }

        .btn-success:hover {
            background: #15a979;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--secondary-color);
            border: none;
        }

        .btn-secondary:hover {
            background: #717484;
            transform: translateY(-2px);
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e3e6f0;
        }

        /* Animasi */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            animation: fadeIn 0.5s ease-out;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 10px;
            }

            .profile-picture-container {
                width: 150px;
                height: 150px;
            }

            .profile-picture {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>

<div class="page-header">
    <h2 class="text-center mb-0">Profil Pengguna</h2>
</div>

<!-- Form Update Profil -->
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

        <div class='col-md-6 mb-3'>
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

        <div class="d-flex gap-2">
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
            <small class="text-muted">Minimal 6 karakter</small>
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

<script>
// Function untuk toggle password visibility
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

// Validasi form sebelum submit
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
