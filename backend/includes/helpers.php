<?php
// Helper functions for database operations

function sanitizeInput($conn, $input) {
    return $conn->real_escape_string(strip_tags(trim($input)));
}

function generateOrderNumber() {
    return 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
}

function getSuccessResponse($data = null) {
    return [
        'success' => true,
        'message' => 'Operation successful',
        'data' => $data
    ];
}

function getErrorResponse($message = 'An error occurred') {
    return [
        'success' => false,
        'message' => $message,
        'data' => null
    ];
}

function sendJsonResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

?>
