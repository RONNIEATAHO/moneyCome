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

if ($method === 'POST' && $action === 'book_appointment') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $service_id = (int)$data['service_id'];
    $appointment_date = sanitizeInput($conn, $data['appointment_date']);
    $appointment_time = sanitizeInput($conn, $data['appointment_time']);
    $notes = sanitizeInput($conn, $data['notes'] ?? '');
    
    $sql = "INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time, notes) 
            VALUES ($user_id, $service_id, '$appointment_date', '$appointment_time', '$notes')";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['appointment_id' => $conn->insert_id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to book appointment'));
    }
}

elseif ($method === 'GET' && $action === 'get_appointments') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT a.*, s.name, s.category FROM appointments a 
            JOIN services s ON a.service_id = s.id 
            WHERE a.user_id = $user_id 
            ORDER BY a.appointment_date DESC";
    
    $result = $conn->query($sql);
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    sendJsonResponse(getSuccessResponse($appointments));
}

elseif ($method === 'POST' && $action === 'cancel_appointment') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $appointment_id = (int)$data['appointment_id'];
    
    $verify_sql = "SELECT id FROM appointments WHERE id = $appointment_id AND user_id = $user_id";
    if ($conn->query($verify_sql)->num_rows === 0) {
        sendJsonResponse(getErrorResponse('Appointment not found'));
    }
    
    $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = $appointment_id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to cancel appointment'));
    }
}

elseif ($method === 'GET' && $action === 'get_all_appointments') {
    requireAdmin();
    
    $sql = "SELECT a.*, s.name as service_name, u.email, u.phone FROM appointments a 
            JOIN services s ON a.service_id = s.id 
            JOIN users u ON a.user_id = u.id 
            ORDER BY a.appointment_date ASC";
    
    $result = $conn->query($sql);
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    sendJsonResponse(getSuccessResponse($appointments));
}

elseif ($method === 'POST' && $action === 'update_appointment_status') {
    requireAdmin();
    
    $appointment_id = (int)$data['appointment_id'];
    $status = sanitizeInput($conn, $data['status']);
    
    $sql = "UPDATE appointments SET status = '$status' WHERE id = $appointment_id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to update appointment'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
