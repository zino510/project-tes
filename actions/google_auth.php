<?php
session_start();
require_once '../config/database.php';

// Include Composer's autoload file
require_once '../vendor/autoload.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$id_token = $data['id_token'] ?? null;

if (!$id_token) {
    echo json_encode(['success' => false, 'message' => 'No token provided']);
    exit;
}

// Verify the token with Google
try {
    $client = new Google_Client(['client_id' => '636392080842-74c7qbrcelo13o2v9sqsksatsmr7av9q.apps.googleusercontent.com']);
    $payload = $client->verifyIdToken($id_token);

    if ($payload) {
        $google_id = $payload['sub'];
        $email = $payload['email'];
        $nama = $payload['name'];
        $picture = $payload['picture'] ?? '';

        // Check if user exists in database
        $stmt = $conn->prepare("SELECT * FROM user WHERE google_id = ?");
        $stmt->bind_param("s", $google_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, login
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            echo json_encode(['success' => true]);
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Email already registered']);
                exit;
            }

            // Create new user
            $username = generateUniqueUsername($email);
            // Generate a random password for Google users
            $random_password = bin2hex(random_bytes(8));
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO user (nama, email, username, password, foto, google_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nama, $email, $username, $hashed_password, $picture, $google_id);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['nama'] = $nama;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create user']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function generateUniqueUsername($email) {
    // Generate username from email
    $username = strtolower(explode('@', $email)[0]);
    $username = preg_replace('/[^a-z0-9]/', '', $username);

    global $conn;
    $original = $username;
    $counter = 1;

    // Check if username exists and append number if it does
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) break;
        $username = $original . $counter++;
    }

    return $username;
}
?>