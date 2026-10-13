<?php
header('Content-Type: application/json');
include '../config/database.php';
include '../includes/auth.php';
include '../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$data = [];

if ($method === 'POST') {
    if (!empty($_POST)) {
        $data = $_POST;
    } else {
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
    }
    $action = $data['action'] ?? $action;
}

function saveServiceImageUpload(): array {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    $file = $_FILES['image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Image upload failed'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['path' => null, 'error' => 'Image must be 5 MB or smaller'];
    }

    $mimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($fileInfo === false) {
        return ['path' => null, 'error' => 'Unable to validate the uploaded image'];
    }
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!isset($mimeTypes[$mimeType])) {
        return ['path' => null, 'error' => 'Choose a JPG, PNG, GIF, or WebP image'];
    }

    $uploadDirectory = __DIR__ . '/../../public/images/services';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
        return ['path' => null, 'error' => 'Unable to create the service image directory'];
    }

    try {
        $fileName = 'service_' . bin2hex(random_bytes(12)) . '.' . $mimeTypes[$mimeType];
    } catch (Throwable $error) {
        return ['path' => null, 'error' => 'Unable to prepare the image upload'];
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . DIRECTORY_SEPARATOR . $fileName)) {
        return ['path' => null, 'error' => 'Failed to save the uploaded image'];
    }

    return ['path' => 'images/services/' . $fileName, 'error' => null];
}

function requireServiceAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403);
        sendJsonResponse(getErrorResponse('Admin privileges are required to manage services. Please sign in with an admin account.'));
    }
}

if ($method === 'GET' && ($action === 'get_all' || $action === 'get_all_admin')) {
    if ($action === 'get_all_admin') {
        requireServiceAdmin();
        $sql = "SELECT * FROM services ORDER BY created_at DESC";
    } else {
        $sql = "SELECT * FROM services WHERE status = 'active' ORDER BY created_at DESC";
    }
    $result = $conn->query($sql);
    if (!$result) {
        sendJsonResponse(getErrorResponse('Unable to load services: ' . $conn->error));
    }

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
    requireServiceAdmin();

    $upload = saveServiceImageUpload();
    if ($upload['error'] !== null) {
        sendJsonResponse(getErrorResponse($upload['error']));
        exit;
    }
    $name = sanitizeInput($conn, $data['name'] ?? '');
    $description = sanitizeInput($conn, $data['description'] ?? '');
    $price = (float)($data['price'] ?? 0);
    $category = sanitizeInput($conn, $data['category'] ?? '');
    $image_url = sanitizeInput($conn, $upload['path'] ?? ($data['image_url'] ?? ''));

    if ($name === '' || $description === '' || $category === '' || !is_numeric($data['price'] ?? null) || $price < 0) {
        sendJsonResponse(getErrorResponse('Enter a service name, description, non-negative price, and category.'));
    }
    
    $sql = "INSERT INTO services (name, description, price, category, image_url, status)
            VALUES ('$name', '$description', $price, '$category', '$image_url', 'active')";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['id' => $conn->insert_id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to add service: ' . $conn->error));
    }
}

elseif ($method === 'POST' && $action === 'update_service') {
    requireServiceAdmin();

    $upload = saveServiceImageUpload();
    if ($upload['error'] !== null) {
        sendJsonResponse(getErrorResponse($upload['error']));
        exit;
    }
    $id = (int)($data['id'] ?? 0);
    $name = sanitizeInput($conn, $data['name'] ?? '');
    $description = sanitizeInput($conn, $data['description'] ?? '');
    $price = (float)($data['price'] ?? 0);
    $category = sanitizeInput($conn, $data['category'] ?? '');
    $updates = [
        "name='$name'",
        "description='$description'",
        "price=$price",
        "category='$category'"
    ];
    if ($id <= 0 || $name === '' || $description === '' || $category === '' || !is_numeric($data['price'] ?? null) || $price < 0) {
        sendJsonResponse(getErrorResponse('Enter a valid service ID, name, description, non-negative price, and category.'));
    }
    if ($upload['path'] !== null) {
        $image_url = sanitizeInput($conn, $upload['path']);
        $updates[] = "image_url='$image_url'";
    } elseif (array_key_exists('image_url', $data)) {
        $image_url = sanitizeInput($conn, $data['image_url']);
        $updates[] = "image_url='$image_url'";
    }

    $sql = "UPDATE services SET " . implode(', ', $updates) . " WHERE id=$id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['id' => $id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to update service: ' . $conn->error));
    }
}

elseif ($method === 'POST' && $action === 'delete_service') {
    requireServiceAdmin();
    
    $id = (int)$data['id'];
    $sql = "UPDATE services SET status='inactive' WHERE id=$id";
    
    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse());
    } else {
        sendJsonResponse(getErrorResponse('Failed to delete service'));
    }
}

elseif ($method === 'POST' && $action === 'activate_service') {
    requireServiceAdmin();

    $id = (int)($data['id'] ?? 0);
    $sql = "UPDATE services SET status='active' WHERE id=$id";

    if ($conn->query($sql)) {
        sendJsonResponse(getSuccessResponse(['id' => $id]));
    } else {
        sendJsonResponse(getErrorResponse('Failed to activate service'));
    }
}

else {
    sendJsonResponse(getErrorResponse('Invalid action'));
}

?>
