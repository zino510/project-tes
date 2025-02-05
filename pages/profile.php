<?php
// pages/profile.php
session_start();
include '../config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM user WHERE id = $user_id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profil</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <h2>Profil</h2>
    <form action="update_profile.php" method="POST">
        <input type="text" name="nama" value="<?php echo $user['nama']; ?>" required>
        <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>