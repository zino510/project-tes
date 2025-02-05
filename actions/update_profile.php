<?php
session_start();
include '../config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $foto = "uploads/" . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], "../" . $foto);

    $sql = "UPDATE user SET nama=?, email=?, foto=?" . ($password ? ", password=?" : "") . " WHERE id=?";
    $stmt = $conn->prepare($sql);
    if ($password) {
        $stmt->bind_param("ssssi", $nama, $email, $foto, $password, $_SESSION['user_id']);
    } else {
        $stmt->bind_param("sssi", $nama, $email, $foto, $_SESSION['user_id']);
    }
    if ($stmt->execute()) {
        header("Location: ../pages/profile.php");
        exit();
    }
}
?>