<?php
session_start();
include '../config/database.php';

// Periksa koneksi database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        if (!empty($_POST['nama']) && !empty($_POST['email']) && !empty($_POST['username']) && !empty($_POST['password'])) {
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO user (nama, email, username, password) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $nama, $email, $username, $password);
                if ($stmt->execute()) {
                    header("Location: ../pages/login.php");
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            echo "Please fill in all fields.";
        }
    }

    if (isset($_POST['login'])) {
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $conn->prepare("SELECT id, password FROM user WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    header("Location: ../pages/dashboard.php");
                    exit();
                } else {
                    echo "Invalid username or password";
                }
                $stmt->close();
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            echo "Please fill in all fields.";
        }
    }
}

$conn->close();
?>