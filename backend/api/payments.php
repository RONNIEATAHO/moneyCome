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

if ($method === 'POST' && $action === 'process_payment') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $order_id = (int)$data['order_id'];
    $amount = (float)$data['amount'];
    $payment_method = sanitizeInput($conn, $data['payment_method']);
    $transaction_id = sanitizeInput($conn, $data['transaction_id'] ?? '');
    
    // Verify order belongs to user
    $verify_sql = "SELECT id FROM orders WHERE id = $order_id AND user_id = $user_id";
    if ($conn->query($verify_sql)->num_rows === 0) {
        sendJsonResponse(getErrorResponse('Order not found'));
    }
    
    // Create payment record
    $payment_sql = "INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status) 
                    VALUES ($order_id, $user_id, $amount, '$payment_method', '$transaction_id', 'completed')";
    
    if ($conn->query($payment_sql)) {
        // Update order payment status
        $update_order = "UPDATE orders SET payment_status = 'completed' WHERE id = $order_id";
        $conn->query($update_order);
        
        sendJsonResponse(getSuccessResponse([
            'payment_id' => $conn->insert_id,
            'transaction_id' => $transaction_id
        ]));
    } else {
        sendJsonResponse(getErrorResponse('Payment processing failed'));
    }
}

elseif ($method === 'GET' && $action === 'get_payment_status' && isset($_GET['order_id'])) {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $order_id = (int)$_GET['order_id'];
    
    $sql = "SELECT payment_status FROM orders WHERE id = $order_id AND user_id = $user_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        sendJsonResponse(getSuccessResponse($order));
    } else {
        sendJsonResponse(getErrorResponse('Order not found'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
