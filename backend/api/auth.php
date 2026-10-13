<?php
header('Content-Type: application/json');
include '../config/database.php';
include '../includes/auth.php';
include '../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $action = $data['action'] ?? $action;
}

if ($method === 'POST' && $action === 'login') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (loginUser($conn, $email, $password)) {
        sendJsonResponse(getSuccessResponse([
            'user_id' => $_SESSION['user_id'],
            'user_email' => $_SESSION['user_email'],
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role']
        ]));
    } else {
        sendJsonResponse(getErrorResponse('Invalid email or password'));
    }
}

elseif ($method === 'POST' && $action === 'register') {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $first_name = $data['first_name'] ?? '';
    $last_name = $data['last_name'] ?? '';
    $phone = $data['phone'] ?? '';
    
    // Check if email already exists
    $email_check = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($email) . "'");
    if ($email_check->num_rows > 0) {
        sendJsonResponse(getErrorResponse('Email already registered'));
    }
    
    if (registerUser($conn, $email, $password, $first_name, $last_name, $phone)) {
        sendJsonResponse(getSuccessResponse(['message' => 'Registration successful. Please login.']));
    } else {
        sendJsonResponse(getErrorResponse('Registration failed'));
    }
}

elseif ($method === 'POST' && $action === 'logout') {
    logoutUser();
    sendJsonResponse(getSuccessResponse(['logged_out' => true]));
}

elseif ($method === 'GET' && $action === 'check_auth') {
    if (isLoggedIn()) {
        sendJsonResponse(getSuccessResponse([
            'logged_in' => true,
            'user_id' => $_SESSION['user_id'],
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role']
        ]));
    } else {
        sendJsonResponse(getSuccessResponse(['logged_in' => false]));
    }
}

elseif ($method === 'GET' && $action === 'get_user_profile') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id, email, first_name, last_name, phone, address, role, created_at FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        sendJsonResponse(getSuccessResponse($result->fetch_assoc()));
    } else {
        sendJsonResponse(getErrorResponse('User not found'));
    }
}

elseif ($method === 'POST' && $action === 'update_profile') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $first_name = sanitizeInput($conn, $data['first_name']);
    $last_name = sanitizeInput($conn, $data['last_name']);
    $phone = sanitizeInput($conn, $data['phone']);
    $address = sanitizeInput($conn, $data['address']);
    
    $sql = "UPDATE users SET first_name='$first_name', last_name='$last_name', phone='$phone', address='$address' WHERE id=$user_id";
    
    if ($conn->query($sql)) {
        $_SESSION['user_name'] = $first_name;
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to update profile'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
