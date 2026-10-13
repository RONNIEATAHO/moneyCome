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
    $quantity = (int)($data['quantity'] ?? 0);
    $type = $data['type'] ?? '';

    if ($quantity < 1 || !in_array($type, ['product', 'service'], true)) {
        sendJsonResponse(getErrorResponse('Invalid cart item or quantity'));
    }

    if ($type === 'product') {
        $product_id = (int)($data['product_id'] ?? 0);
        $product_result = $conn->query(
            "SELECT price, discount_price, stock_quantity FROM products WHERE id = $product_id AND status = 'active'"
        );
        if (!$product_id || !$product_result || $product_result->num_rows === 0) {
            sendJsonResponse(getErrorResponse('Product is unavailable'));
        }

        $product = $product_result->fetch_assoc();
        if ($quantity > (int)$product['stock_quantity']) {
            sendJsonResponse(getErrorResponse('Requested quantity exceeds available stock'));
        }

        $price = $product['discount_price'] !== null && (float)$product['discount_price'] > 0
            ? (float)$product['discount_price']
            : (float)$product['price'];
        $statement = $conn->prepare(
            "INSERT INTO cart (user_id, product_id, service_id, quantity, price, type) VALUES (?, ?, NULL, ?, ?, 'product')"
        );
        if (!$statement) {
            sendJsonResponse(getErrorResponse('Unable to prepare cart item: ' . $conn->error));
        }
        $statement->bind_param('iiid', $user_id, $product_id, $quantity, $price);
    } else {
        $service_id = (int)($data['service_id'] ?? 0);
        $service_result = $conn->query(
            "SELECT price FROM services WHERE id = $service_id AND status = 'active'"
        );
        if (!$service_id || !$service_result || $service_result->num_rows === 0) {
            sendJsonResponse(getErrorResponse('Service is unavailable'));
        }

        $service = $service_result->fetch_assoc();
        $price = (float)$service['price'];
        $statement = $conn->prepare(
            "INSERT INTO cart (user_id, product_id, service_id, quantity, price, type) VALUES (?, NULL, ?, ?, ?, 'service')"
        );
        if (!$statement) {
            sendJsonResponse(getErrorResponse('Unable to prepare cart item: ' . $conn->error));
        }
        $statement->bind_param('iiid', $user_id, $service_id, $quantity, $price);
    }

    if ($statement && $statement->execute()) {
        sendJsonResponse(getSuccessResponse(['cart_id' => $conn->insert_id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to add to cart: ' . $statement->error));
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
