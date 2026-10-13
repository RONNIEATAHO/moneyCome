<?php
// Authentication functions
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function userHasRole($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['user_role'] === $required_role;
}

// Function to check if user is admin or system admin
function isAdmin() {
    return isset($_SESSION['user_role']) && 
           ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'system_admin');
}

// Function to check if user is system admin
function isSystemAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'system_admin';
}

// Function to login user
function loginUser($conn, $email, $password) {
    $email = $conn->real_escape_string($email);
    $password_hash = hash('sha256', $password);
    
    $sql = "SELECT id, email, first_name, role FROM users WHERE email = '$email' AND password = '$password_hash' AND status = 'active'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

// Function to logout user
function logoutUser() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie_params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $cookie_params['path'],
            $cookie_params['domain'],
            $cookie_params['secure'],
            $cookie_params['httponly']
        );
    }

    session_destroy();
}

// Function to register user
function registerUser($conn, $email, $password, $first_name, $last_name, $phone) {
    $email = $conn->real_escape_string($email);
    $password_hash = hash('sha256', $password);
    $first_name = $conn->real_escape_string($first_name);
    $last_name = $conn->real_escape_string($last_name);
    $phone = $conn->real_escape_string($phone);
    
    $sql = "INSERT INTO users (email, password, first_name, last_name, phone, role) 
            VALUES ('$email', '$password_hash', '$first_name', '$last_name', '$phone', 'customer')";
    
    if ($conn->query($sql)) {
        return true;
    }
    return false;
}

// Require login middleware
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/public/index.html?auth=login");
        exit;
    }
}

// Require admin middleware
function requireAdmin() {
    if (!isAdmin()) {
        header("HTTP/1.1 403 Forbidden");
        die("Access denied. Admin privileges required.");
    }
}

// Require system admin middleware
function requireSystemAdmin() {
    if (!isSystemAdmin()) {
        header("HTTP/1.1 403 Forbidden");
        die("Access denied. System admin privileges required.");
    }
}

?>
