<?php
// config/database.php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "marketplace";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
