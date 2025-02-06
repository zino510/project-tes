<?php
// pages/profile.php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user berdasarkan user_id
$result = $conn->query("SELECT * FROM user WHERE id = $user_id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang='id'>
<head>
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

        <input type='file' name='foto' id='foto' class='form-control d-none' accept="image/*">

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
    <form action='change_password.php' method='POST'>
        <div class='mb-3'>
            <label for='current_password' class='form-label'>Password Saat Ini</label>
            <input type='password' name='current_password' id='current_password' class='form-control' required>
        </div>

        <div class='mb-3'>
            <label for='new_password' class='form-label'>Password Baru</label>
            <input type='password' name='new_password' id='new_password' class='form-control' required>
        </div>

        <div class='mb-3'>
            <label for='confirm_password' class='form-label'>Konfirmasi Password</label>
            <input type='password' name='confirm_password' id='confirm_password' class='form-control' required>
        </div>

        <button type='submit' class='btn btn-success'>
            <i class="fas fa-key me-2"></i>Ganti Password
        </button>
    </form>
</div>

<!-- Bootstrap JS Bundle CDN -->
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>

<!-- Custom JavaScript -->
<script>
document.getElementById('foto').onchange = function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImage').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
}
</script>

</body>
</html>