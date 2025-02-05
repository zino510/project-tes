<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../pages/login.php");
    exit();
}
?>