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

if ($method === 'POST' && $action === 'process_payment') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $order_id = (int)$data['order_id'];
    $payment_method = $data['payment_method'] ?? '';
    $transaction_id = sanitizeInput($conn, $data['transaction_id'] ?? '');

    $allowed_methods = ['mtn_mobile_money', 'airtel_money', 'paypal'];
    if (!in_array($payment_method, $allowed_methods, true)) {
        sendJsonResponse(getErrorResponse('Select a valid payment method'));
    }
    
    $verify_sql = "SELECT total_amount, payment_status FROM orders WHERE id = $order_id AND user_id = $user_id";
    $order_result = $conn->query($verify_sql);
    if (!$order_result || $order_result->num_rows === 0) {
        sendJsonResponse(getErrorResponse('Order not found'));
    }
    $order = $order_result->fetch_assoc();
    if ($order['payment_status'] === 'completed') {
        sendJsonResponse(getErrorResponse('This order has already been paid'));
    }
    $amount = (float)$order['total_amount'];
    
    $payment_statement = $conn->prepare(
        "INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, 'completed')"
    );
    if (!$payment_statement) {
        sendJsonResponse(getErrorResponse('Unable to prepare payment: ' . $conn->error));
    }
    $payment_statement->bind_param('iidss', $order_id, $user_id, $amount, $payment_method, $transaction_id);
    if (!$payment_statement->execute()) {
        sendJsonResponse(getErrorResponse('Payment processing failed: ' . $payment_statement->error));
    }

    $update_statement = $conn->prepare(
        "UPDATE orders SET payment_status = 'completed', payment_method = ? WHERE id = ? AND user_id = ?"
    );
    if (!$update_statement) {
        sendJsonResponse(getErrorResponse('Payment was recorded but the order could not be updated: ' . $conn->error));
    }
    $update_statement->bind_param('sii', $payment_method, $order_id, $user_id);
    if (!$update_statement->execute()) {
        sendJsonResponse(getErrorResponse('Payment was recorded but the order could not be updated: ' . $update_statement->error));
    }

    $conn->query("DELETE FROM cart WHERE user_id = $user_id");
    sendJsonResponse(getSuccessResponse([
        'payment_id' => $conn->insert_id,
        'transaction_id' => $transaction_id
    ]));
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
