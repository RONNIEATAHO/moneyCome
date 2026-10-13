<?php
header('Content-Type: application/json');
include '../config/database.php';
include '../includes/auth.php';
include '../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $action = $data['action'] ?? '';
}

// All admin operations require authorization
requireAdmin();

if ($method === 'GET' && $action === 'get_all_users') {
    $sql = "SELECT id, email, first_name, last_name, phone, role, status, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    if (!$result) {
        sendJsonResponse(getErrorResponse('Failed to load users'));
    }
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    sendJsonResponse(getSuccessResponse($users));
}

elseif ($method === 'GET' && $action === 'get_user' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $statement = $conn->prepare(
        "SELECT id, email, first_name, last_name, phone, address, role, status, created_at FROM users WHERE id = ?"
    );
    if (!$statement) {
        sendJsonResponse(getErrorResponse('Failed to load user'));
    }
    $statement->bind_param('i', $id);
    if (!$statement->execute()) {
        sendJsonResponse(getErrorResponse('Failed to load user'));
    }
    $result = $statement->get_result();
    if ($result && $result->num_rows > 0) {
        sendJsonResponse(getSuccessResponse($result->fetch_assoc()));
    } else {
        sendJsonResponse(getErrorResponse('User not found'));
    }
}

elseif ($method === 'POST' && $action === 'update_user_status') {
    requireSystemAdmin();

    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    $status = $data['status'] ?? '';
    if (!$id || !in_array($status, ['active', 'inactive'], true)) {
        sendJsonResponse(getErrorResponse('Invalid account status update'));
    }
    if ((int)$id === (int)$_SESSION['user_id']) {
        sendJsonResponse(getErrorResponse('You cannot change your own account status'));
    }

    $statement = $conn->prepare(
        "UPDATE users SET status = ? WHERE id = ? AND role IN ('customer', 'admin')"
    );
    if (!$statement) {
        sendJsonResponse(getErrorResponse('Failed to prepare account status update'));
    }
    $statement->bind_param('si', $status, $id);
    if (!$statement->execute()) {
        sendJsonResponse(getErrorResponse('Failed to update account status'));
    }
    if ($statement->affected_rows === 1) {
        sendJsonResponse(getSuccessResponse());
    }
    sendJsonResponse(getErrorResponse('Account not found or status was not changed'));
}

elseif ($method === 'POST' && $action === 'create_admin') {
    requireSystemAdmin();

    $email = strtolower(trim(is_string($data['email'] ?? null) ? $data['email'] : ''));
    $first_name = trim(is_string($data['first_name'] ?? null) ? $data['first_name'] : '');
    $last_name = trim(is_string($data['last_name'] ?? null) ? $data['last_name'] : '');
    $phone = trim(is_string($data['phone'] ?? null) ? $data['phone'] : '');
    $password = is_string($data['password'] ?? null) ? $data['password'] : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $first_name === '' || $last_name === '') {
        sendJsonResponse(getErrorResponse("Enter a valid email address and the admin's first and last name"));
    }
    if (strlen($password) < 8) {
        sendJsonResponse(getErrorResponse('The admin password must be at least 8 characters'));
    }

    $password_hash = hash('sha256', $password);
    $statement = $conn->prepare(
        "INSERT INTO users (email, password, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, 'admin')"
    );
    if (!$statement) {
        sendJsonResponse(getErrorResponse('Failed to prepare admin account creation'));
    }
    $statement->bind_param('sssss', $email, $password_hash, $first_name, $last_name, $phone);
    if ($statement->execute()) {
        sendJsonResponse(getSuccessResponse(['id' => $statement->insert_id]));
    }
    if ($statement->errno === 1062) {
        sendJsonResponse(getErrorResponse('An account with that email address already exists'));
    }
    sendJsonResponse(getErrorResponse('Failed to create admin account'));
}

elseif ($method === 'POST' && $action === 'update_user_role') {
    requireSystemAdmin(); // Only system admins can change roles

    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    $role = is_string($data['role'] ?? null) ? $data['role'] : '';
    if (!$id || !in_array($role, ['customer', 'admin'], true)) {
        sendJsonResponse(getErrorResponse('Invalid role'));
    }

    if ((int)$id === (int)$_SESSION['user_id']) {
        sendJsonResponse(getErrorResponse('You cannot change your own role'));
    }

    $statement = $conn->prepare("UPDATE users SET role = ? WHERE id = ? AND role <> 'system_admin'");
    if (!$statement) {
        sendJsonResponse(getErrorResponse('Failed to prepare role update'));
    }
    $statement->bind_param('si', $role, $id);
    if (!$statement->execute()) {
        sendJsonResponse(getErrorResponse('Failed to update user role'));
    }
    if ($statement->affected_rows === 1) {
        sendJsonResponse(getSuccessResponse());
    }
    sendJsonResponse(getErrorResponse('User not found or role was not changed'));
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
