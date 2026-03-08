<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apmc_trading');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: Check if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: Check role
function checkRole($role) {
    if (!isLoggedIn() || $_SESSION['role'] !== $role) {
        redirect('../index.php');
    }
}

// Helper: Sanitize input
function sanitize($conn, $data) {
    return $conn->real_escape_string(trim(htmlspecialchars($data)));
}
?>
