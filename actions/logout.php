<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Current timestamp for logging
$logout_time = date('Y-m-d H:i:s');

try {
    // Check if user is actually logged in
    if (isset($_SESSION['user_login'])) {
        // Store username before destroying session for logging
        $username = $_SESSION['user_login'];
        
        // Clear all session variables
        $_SESSION = array();

        // Destroy session cookie if exists
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();

        // Optional: Log the logout action
        $log_message = sprintf(
            "[%s] User '%s' logged out successfully\n",
            $logout_time,
            $username
        );
        error_log($log_message, 3, "../logs/user_activity.log");

        // Set success message in temporary cookie
        setcookie('logout_message', 'Anda berhasil keluar dari sistem', time() + 3, '/');
        
        // Redirect with success parameter
        header("Location: ../index.php?status=logged_out");
        exit();
    } else {
        // If no active session, simply redirect
        header("Location: ../index.php");
        exit();
    }
} catch (Exception $e) {
    // Log error
    error_log(sprintf(
        "[%s] Logout error for user: %s. Error: %s\n",
        $logout_time,
        $username ?? 'unknown',
        $e->getMessage()
    ), 3, "../logs/error.log");

    // Redirect with error
    header("Location: ../index.php?error=logout_failed");
    exit();
}
?>