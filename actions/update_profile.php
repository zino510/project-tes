<?php
// update_profile.php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil user_id dari sesi
$user_id = $_SESSION['user_id'];

// Ambil data dari form
$nama     = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$telepon  = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
$bio      = isset($_POST['bio']) ? trim($_POST['bio']) : '';

// Siapkan variabel untuk pesan error dan success
$error_message = '';
$success_message = '';

// Pastikan folder uploads ada dan bisa ditulis
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Ambil data user yang ada
$stmt = $conn->prepare("SELECT foto FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$old_foto = $user['foto'];

// Handle upload foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto'];
    
    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error_msg'] = "Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        header("Location: ../pages/profile.php");
        exit();
    }

    // Validasi ukuran file (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['error_msg'] = "Ukuran file terlalu besar. Maksimal 5MB.";
        header("Location: ../pages/profile.php");
        exit();
    }

    // Generate nama file yang unik
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $destination = $uploadDir . $newFileName;

    // Pindahkan file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Hapus foto lama jika ada
        if ($old_foto && file_exists($uploadDir . $old_foto)) {
            unlink($uploadDir . $old_foto);
        }
        
        // Update database dengan foto baru
        $sql = "UPDATE user SET nama = ?, username = ?, telepon = ?, bio = ?, foto = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nama, $username, $telepon, $bio, $newFileName, $user_id);
    } else {
        $_SESSION['error_msg'] = "Gagal mengupload file. Silakan coba lagi.";
        header("Location: ../pages/profile.php");
        exit();
    }
} else {
    // Update tanpa foto
    $sql = "UPDATE user SET nama = ?, username = ?, telepon = ?, bio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nama, $username, $telepon, $bio, $user_id);
}

// Eksekusi query
if ($stmt->execute()) {
    $_SESSION['success_msg'] = "Profil berhasil diperbarui!";
    header("Location: ../pages/profile.php");
    exit();
} else {
    $_SESSION['error_msg'] = "Gagal memperbarui profil: " . $conn->error;
    header("Location: ../pages/profile.php");
    exit();
}

$stmt->close();
$conn->close();
?>