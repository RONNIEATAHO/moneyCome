<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'suvan_ventures');
define('BASE_URL', 'http://localhost/moneyCome');

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour

// Payment Gateway Configuration (Placeholder for integration)
define('PAYMENT_GATEWAY', 'paypal'); // Can be 'paypal', 'stripe', etc.
define('PAYMENT_API_KEY', 'your_api_key_here');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

?>
