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

if ($method === 'POST' && $action === 'checkout') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $cart_sql = "SELECT * FROM cart WHERE user_id = $user_id ORDER BY added_at";
    $cart_result = $conn->query($cart_sql);
    
    if (!$cart_result) {
        sendJsonResponse(getErrorResponse('Unable to read cart: ' . $conn->error));
    }
    if ($cart_result->num_rows === 0) {
        sendJsonResponse(getErrorResponse('Cart is empty'));
    }
    
    $subtotal = 0;
    $items = [];
    while ($item = $cart_result->fetch_assoc()) {
        $items[] = $item;
        $subtotal += (float)$item['price'] * (int)$item['quantity'];
    }
    $total = round($subtotal * 1.05, 2);
    
    $order_number = generateOrderNumber();
    if (!$conn->begin_transaction()) {
        sendJsonResponse(getErrorResponse('Unable to start checkout: ' . $conn->error));
    }

    $order_statement = $conn->prepare(
        'INSERT INTO orders (user_id, order_number, total_amount) VALUES (?, ?, ?)'
    );
    if (!$order_statement) {
        $conn->rollback();
        sendJsonResponse(getErrorResponse('Unable to prepare order: ' . $conn->error));
    }
    $order_statement->bind_param('isd', $user_id, $order_number, $total);
    if (!$order_statement->execute()) {
        $error = $order_statement->error;
        $conn->rollback();
        sendJsonResponse(getErrorResponse('Failed to create order: ' . $error));
    }

    $order_id = $conn->insert_id;
    $item_statement = $conn->prepare(
        'INSERT INTO order_items (order_id, product_id, service_id, quantity, unit_price, type) VALUES (?, ?, ?, ?, ?, ?)'
    );
    if (!$item_statement) {
        $conn->rollback();
        sendJsonResponse(getErrorResponse('Unable to prepare order items: ' . $conn->error));
    }

    foreach ($items as $item) {
        $product_id = $item['product_id'] !== null ? (int)$item['product_id'] : null;
        $service_id = $item['service_id'] !== null ? (int)$item['service_id'] : null;
        $quantity = (int)$item['quantity'];
        $unit_price = (float)$item['price'];
        $type = $item['type'];
        $item_statement->bind_param('iiiids', $order_id, $product_id, $service_id, $quantity, $unit_price, $type);
        if (!$item_statement->execute()) {
            $error = $item_statement->error;
            $conn->rollback();
            sendJsonResponse(getErrorResponse('Failed to save order items: ' . $error));
        }
    }

    if (!$conn->commit()) {
        $error = $conn->error;
        $conn->rollback();
        sendJsonResponse(getErrorResponse('Unable to complete checkout: ' . $error));
    }

    sendJsonResponse(getSuccessResponse([
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total_amount' => $total,
        'items' => $items
    ]));
}

elseif ($method === 'GET' && $action === 'get_orders') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    sendJsonResponse(getSuccessResponse($orders));
}

elseif ($method === 'GET' && $action === 'get_order_details' && isset($_GET['order_id'])) {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $order_id = (int)$_GET['order_id'];
    
    $sql = "SELECT o.*, u.email, u.phone FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = $order_id AND o.user_id = $user_id";
    $order_result = $conn->query($sql);
    
    if ($order_result->num_rows === 0) {
        sendJsonResponse(getErrorResponse('Order not found'));
    }
    
    $order = $order_result->fetch_assoc();
    
    // Get order items
    $items_sql = "SELECT oi.*, p.name as product_name, s.name as service_name 
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  LEFT JOIN services s ON oi.service_id = s.id 
                  WHERE oi.order_id = $order_id";
    $items_result = $conn->query($items_sql);
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    sendJsonResponse(getSuccessResponse(['order' => $order, 'items' => $items]));
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
