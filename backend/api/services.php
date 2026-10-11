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

if ($method === 'GET' && $action === 'get_all') {
    $sql = "SELECT * FROM services WHERE status = 'active' ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    sendJsonResponse(getSuccessResponse($services));
}

elseif ($method === 'GET' && $action === 'get_single' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM services WHERE id = $id AND status = 'active'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $service = $result->fetch_assoc();
        sendJsonResponse(getSuccessResponse($service));
    } else {
        sendJsonResponse(getErrorResponse('Service not found'));
    }
}

elseif ($method === 'POST' && $action === 'add_service') {
    requireAdmin();
    
    $name = sanitizeInput($conn, $data['name']);
    $description = sanitizeInput($conn, $data['description']);
    $price = (float)$data['price'];
    $category = sanitizeInput($conn, $data['category']);
    $image_url = sanitizeInput($conn, $data['image_url'] ?? '');
    
    $sql = "INSERT INTO services (name, description, price, category, image_url) 
            VALUES ('$name', '$description', $price, '$category', '$image_url')";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['id' => $conn->insert_id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to add service'));
    }
}

elseif ($method === 'POST' && $action === 'update_service') {
    requireAdmin();
    
    $id = (int)$data['id'];
    $name = sanitizeInput($conn, $data['name']);
    $description = sanitizeInput($conn, $data['description']);
    $price = (float)$data['price'];
    $category = sanitizeInput($conn, $data['category']);
    $image_url = sanitizeInput($conn, $data['image_url'] ?? '');
    
    $sql = "UPDATE services SET name='$name', description='$description', price=$price, 
            category='$category', image_url='$image_url' WHERE id=$id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['id' => $id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to update service'));
    }
}

elseif ($method === 'POST' && $action === 'delete_service') {
    requireAdmin();
    
    $id = (int)$data['id'];
    $sql = "UPDATE services SET status='inactive' WHERE id=$id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to delete service'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
