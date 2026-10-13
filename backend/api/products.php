<?php
header('Content-Type: application/json');
include '../config/database.php';
include '../includes/auth.php';
include '../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle both JSON and form-data
if ($method === 'POST' && !isset($action)) {
    // For FormData with file upload
    $action = $_POST['action'] ?? '';
} elseif ($method === 'POST') {
    // For JSON requests
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';
}

if ($method === 'GET' && $action === 'get_all_admin') {
    requireAdmin();
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    sendJsonResponse(getSuccessResponse($products));
}

elseif ($method === 'GET' && $action === 'get_all') {
    $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    sendJsonResponse(getSuccessResponse($products));
}

elseif ($method === 'GET' && $action === 'get_single' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM products WHERE id = $id AND status = 'active'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        sendJsonResponse(getSuccessResponse($product));
    } else {
        sendJsonResponse(getErrorResponse('Product not found'));
    }
}

elseif (($method === 'POST' && $action === 'add_product') || ($method === 'POST' && !isset($_GET['action']))) {
    requireAdmin();
    
    // Get data from either JSON or FormData
    if (!empty($_POST)) {
        $name = sanitizeInput($conn, $_POST['name'] ?? '');
        $description = sanitizeInput($conn, $_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $discount_price = (!empty($_POST['discount_price']) && $_POST['discount_price'] !== 'null') ? (float)$_POST['discount_price'] : NULL;
        $category = sanitizeInput($conn, $_POST['category'] ?? '');
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $product_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    } else {
        $data = json_decode(file_get_contents("php://input"), true);
        $name = sanitizeInput($conn, $data['name'] ?? '');
        $description = sanitizeInput($conn, $data['description'] ?? '');
        $price = (float)($data['price'] ?? 0);
        $discount_price = (!empty($data['discount_price']) && $data['discount_price'] !== 'null') ? (float)$data['discount_price'] : NULL;
        $category = sanitizeInput($conn, $data['category'] ?? '');
        $stock_quantity = (int)($data['stock_quantity'] ?? 0);
        $product_id = isset($data['id']) ? (int)$data['id'] : null;
    }
    
    $image_url = '';
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../public/images/products/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid('product_') . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'images/products/' . $new_file_name;
            } else {
                sendJsonResponse(getErrorResponse('Failed to upload image'));
                exit;
            }
        } else {
            sendJsonResponse(getErrorResponse('Invalid image format'));
            exit;
        }
    }
    
    if ($product_id) {
        // Update product
        $update_parts = [];
        
        if (!empty($name)) $update_parts[] = "name='$name'";
        if (!empty($description)) $update_parts[] = "description='$description'";
        if ($price > 0) $update_parts[] = "price=$price";
        if ($discount_price !== NULL) {
            $update_parts[] = "discount_price=$discount_price";
        } else {
            $update_parts[] = "discount_price=NULL";
        }
        if (!empty($category)) $update_parts[] = "category='$category'";
        if ($stock_quantity >= 0) $update_parts[] = "stock_quantity=$stock_quantity";
        if (!empty($image_url)) $update_parts[] = "image_url='$image_url'";
        
        if (!empty($update_parts)) {
            $sql = "UPDATE products SET " . implode(', ', $update_parts) . " WHERE id=$product_id";
            
            if ($conn->query($sql)) {
                sendJsonResponse(getSuccessResponse(['id' => $product_id]));
            } else {
                sendJsonResponse(getErrorResponse('Failed to update product: ' . $conn->error));
            }
        } else {
            sendJsonResponse(getErrorResponse('No valid fields to update'));
        }
    } else {
        // Add new product
        if (empty($name) || empty($description) || $price <= 0) {
            sendJsonResponse(getErrorResponse('Missing required fields'));
            exit;
        }
        
        $discount_price_sql = $discount_price !== NULL ? $discount_price : 'NULL';
        
        $sql = "INSERT INTO products (name, description, price, discount_price, category, stock_quantity, image_url) 
                VALUES ('$name', '$description', $price, $discount_price_sql, '$category', $stock_quantity, '$image_url')";
        
        if ($conn->query($sql)) {
            sendJsonResponse(getSuccessResponse(['id' => $conn->insert_id]));
        } else {
            sendJsonResponse(getErrorResponse('Failed to add product: ' . $conn->error));
        }
    }
}

elseif ($method === 'POST' && $action === 'delete_product') {
    requireAdmin();
    
    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)$data['id'];
    $sql = "UPDATE products SET status='inactive' WHERE id=$id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to delete product'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
