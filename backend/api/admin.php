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

// All admin operations require authorization
requireAdmin();

if ($method === 'GET' && $action === 'get_all_users') {
    $sql = "SELECT id, email, first_name, last_name, phone, role, status, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    sendJsonResponse(getSuccessResponse($users));
}

elseif ($method === 'GET' && $action === 'get_user' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        sendJsonResponse(getSuccessResponse($result->fetch_assoc()));
    } else {
        sendJsonResponse(getErrorResponse('User not found'));
    }
}

elseif ($method === 'POST' && $action === 'update_user_status') {
    $id = (int)$data['id'];
    $status = sanitizeInput($conn, $data['status']);
    
    $sql = "UPDATE users SET status = '$status' WHERE id = $id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to update user status'));
    }
}

elseif ($method === 'POST' && $action === 'update_user_role') {
    requireSystemAdmin(); // Only system admins can change roles
    
    $id = (int)$data['id'];
    $role = sanitizeInput($conn, $data['role']);
    
    // Validate role
    $valid_roles = ['customer', 'admin', 'system_admin'];
    if (!in_array($role, $valid_roles)) {
        sendJsonResponse(getErrorResponse('Invalid role'));
    }
    
    $sql = "UPDATE users SET role = '$role' WHERE id = $id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to update user role'));
    }
}

elseif ($method === 'GET' && $action === 'get_dashboard_stats') {
    $stats = [];
    
    // Total products
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
    $stats['total_products'] = $result->fetch_assoc()['count'];
    
    // Total services
    $result = $conn->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
    $stats['total_services'] = $result->fetch_assoc()['count'];
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $stats['total_customers'] = $result->fetch_assoc()['count'];
    
    // Total orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['count'];
    
    // Pending inquiries
    $result = $conn->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'");
    $stats['pending_inquiries'] = $result->fetch_assoc()['count'];
    
    // Total revenue
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'");
    $revenue = $result->fetch_assoc()['total'];
    $stats['total_revenue'] = $revenue ?? 0;
    
    sendJsonResponse(getSuccessResponse($stats));
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
