<?php
// pages/profile.php
session_start();
include '../config/database.php';

// Pastikan pengguna sudah login
if ( !isset( $_SESSION[ 'user_id' ] ) ) {
    header( 'Location: login.php' );
    exit();
}

$user_id = $_SESSION[ 'user_id' ];

// Ambil data user berdasarkan user_id
$result = $conn->query( "SELECT * FROM user WHERE id = $user_id" );
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang = 'id'>
<head>
<meta charset = 'UTF-8'>
<title>Profil</title>
<!-- Bootstrap CSS CDN -->
<link rel = 'stylesheet' href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>

<!-- Custom CSS ( style.css ) -->
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}
/* Container for the forms */
.form-container {
    background: white;
    padding: 20px;
    max-width: 500px;
    margin: 40px auto;
    border-radius: 10px;
}
/* Global form styling */
input, textarea, button {
    margin-bottom: 15px;
}
textarea {
    resize: vertical;
}
button {
    background: #5cb85c;
    color: white;
    border: none;
}
button:hover {
    background: #4cae4c;
}
/* Center heading */
h2 {
    text-align: center;
    margin-top: 30px;
}
</style>
</head>
<body>

<h2>Profil</h2>

<!-- Form Update Profil -->
<div class = 'form-container'>
<form action = '../actions/update_profile.php' method = 'POST' enctype = 'multipart/form-data' class = 'mb-4'>
<div class = 'mb-3'>
<label for = 'nama' class = 'form-label'>Nama:</label>
<input type = 'text' name = 'nama' id = 'nama' class = 'form-control' value = "<?php echo htmlspecialchars($user['nama']); ?>" required>
</div>

<div class = 'mb-3'>
<label for = 'username' class = 'form-label'>Username:</label>
<input type = 'text' name = 'username' id = 'username' class = 'form-control' value = "<?php echo htmlspecialchars($user['username']); ?>" required>
</div>

<div class = 'mb-3'>
<label for = 'telepon' class = 'form-label'>Telepon:</label>
<input type = 'text' name = 'telepon' id = 'telepon' class = 'form-control' value = "<?php echo htmlspecialchars($user['telepon']); ?>">
</div>

<div class = 'mb-3'>
<label for = 'foto' class = 'form-label'>Foto Profil:</label><br>
<?php if ( !empty( $user[ 'foto' ] ) ): ?>
<img src = "../uploads/<?php echo htmlspecialchars($user['foto']); ?>" alt = 'Foto Profil' width = '100' class = 'mb-2'>
<?php endif;
?>
<input type = 'file' name = 'foto' id = 'foto' class = 'form-control'>
</div>

<div class = 'mb-3'>
<label for = 'bio' class = 'form-label'>Bio:</label>
<textarea name = 'bio' id = 'bio' class = 'form-control' rows = '5'><?php echo htmlspecialchars( $user[ 'bio' ] );
?></textarea>
</div>

<div class = 'mb-3'>
<label for = 'email' class = 'form-label'>Email ( tidak diubah ):</label>
<input type = 'email' name = 'email' id = 'email' class = 'form-control' value = "<?php echo htmlspecialchars($user['email']); ?>" readonly>
</div>

<button type = 'submit' class = 'btn btn-success'>Update Profil</button>
</form>
</div>

<!-- Form Ganti Password -->
<div class = 'form-container'>
<form action = 'change_password.php' method = 'POST'>
<div class = 'mb-3'>
<label for = 'current_password' class = 'form-label'>Current Password:</label>
<input type = 'password' name = 'current_password' id = 'current_password' class = 'form-control' required>
</div>

<div class = 'mb-3'>
<label for = 'new_password' class = 'form-label'>New Password:</label>
<input type = 'password' name = 'new_password' id = 'new_password' class = 'form-control' required>
</div>

<div class = 'mb-3'>
<label for = 'confirm_password' class = 'form-label'>Confirm Password:</label>
<input type = 'password' name = 'confirm_password' id = 'confirm_password' class = 'form-control' required>
</div>

<button type = 'submit' class = 'btn btn-success'>Change Password</button>
</form>
</div>

<!-- Bootstrap JS Bundle CDN -->
<script src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>