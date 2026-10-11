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

if ($method === 'POST' && $action === 'add_to_cart') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : null;
    $service_id = isset($data['service_id']) ? (int)$data['service_id'] : null;
    $quantity = (int)$data['quantity'];
    $price = (float)$data['price'];
    $type = $data['type']; // 'product' or 'service'
    
    $sql = "INSERT INTO cart (user_id, product_id, service_id, quantity, price, type) 
            VALUES ($user_id, $product_id, $service_id, $quantity, $price, '$type')";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['cart_id' => $conn->insert_id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to add to cart'));
    }
}

elseif ($method === 'GET' && $action === 'get_cart') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT c.*, p.name as product_name, s.name as service_name 
            FROM cart c 
            LEFT JOIN products p ON c.product_id = p.id 
            LEFT JOIN services s ON c.service_id = s.id 
            WHERE c.user_id = $user_id 
            ORDER BY c.added_at DESC";
    
    $result = $conn->query($sql);
    $cart = [];
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $cart[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
    sendJsonResponse(getSuccessResponse(['items' => $cart, 'total' => $total]));
}

elseif ($method === 'POST' && $action === 'remove_from_cart') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $cart_id = (int)$data['cart_id'];
    
    $sql = "DELETE FROM cart WHERE id=$cart_id AND user_id=$user_id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to remove from cart'));
    }
}

elseif ($method === 'POST' && $action === 'update_cart_quantity') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $cart_id = (int)$data['cart_id'];
    $quantity = (int)$data['quantity'];
    
    $sql = "UPDATE cart SET quantity=$quantity WHERE id=$cart_id AND user_id=$user_id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to update cart'));
    }
}

elseif ($method === 'POST' && $action === 'clear_cart') {
    requireLogin();
    
    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM cart WHERE user_id=$user_id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to clear cart'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
