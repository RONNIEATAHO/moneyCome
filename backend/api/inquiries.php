<?php
header('Content-Type: application/json');
include '../config/database.php';
include '../includes/auth.php';
include '../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';
}

if ($method === 'POST' && $action === 'submit_inquiry') {
    $name = sanitizeInput($conn, $data['name']);
    $email = sanitizeInput($conn, $data['email']);
    $phone = sanitizeInput($conn, $data['phone']);
    $subject = sanitizeInput($conn, $data['subject']);
    $message = sanitizeInput($conn, $data['message']);
    $service_type = sanitizeInput($conn, $data['service_type'] ?? '');
    
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    
    $sql = "INSERT INTO inquiries (user_id, name, email, phone, subject, message, service_type) 
            VALUES (" . ($user_id ? $user_id : 'NULL') . ", '$name', '$email', '$phone', '$subject', '$message', '$service_type')";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['inquiry_id' => $conn->insert_id, 'message' => 'Thank you for your inquiry. We will respond shortly.']));
    } else {
        sendJsonResponse(getErrorResponse('Failed to submit inquiry'));
    }
}

elseif ($method === 'GET' && $action === 'get_all_inquiries') {
    requireAdmin();
    
    $sql = "SELECT * FROM inquiries ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $inquiries = [];
    while ($row = $result->fetch_assoc()) {
        $inquiries[] = $row;
    }
    sendJsonResponse(getSuccessResponse($inquiries));
}

elseif ($method === 'GET' && $action === 'get_inquiry' && isset($_GET['id'])) {
    requireAdmin();
    
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM inquiries WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $inquiry = $result->fetch_assoc();
        // Mark as read
        $update_sql = "UPDATE inquiries SET status = 'read' WHERE id = $id";
        $conn->query($update_sql);
        
        sendJsonResponse(getSuccessResponse($inquiry));
    } else {
        sendJsonResponse(getErrorResponse('Inquiry not found'));
    }
}

elseif ($method === 'POST' && $action === 'update_inquiry_status') {
    requireAdmin();
    
    $id = (int)$data['id'];
    $status = sanitizeInput($conn, $data['status']);
    
    $sql = "UPDATE inquiries SET status = '$status' WHERE id = $id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to update inquiry'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
