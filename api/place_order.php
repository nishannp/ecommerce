<?php
// Include database configuration
include '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get request data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if data is valid
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Start a transaction
$conn->begin_transaction();

try {
    // Generate unique order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . mt_rand(1000, 9999);
    
    // Insert order into database
    $stmt = $conn->prepare("INSERT INTO orders (
            order_number, 
            first_name, 
            last_name, 
            email, 
            phone, 
            address, 
            city, 
            state, 
            zip_code, 
            notes, 
            payment_method, 
            order_total, 
            order_status, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $status = 'pending';
    
    $stmt->bind_param(
        'sssssssssssds',
        $orderNumber,
        $data['firstName'],
        $data['lastName'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['city'],
        $data['state'],
        $data['zipCode'],
        $data['notes'],
        $data['paymentMethod'],
        $data['orderTotal'],
        $status
    );
    
    $stmt->execute();
    $orderId = $conn->insert_id;
    
    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (
            order_id, 
            product_id, 
            quantity, 
            created_at
        ) VALUES (?, ?, ?, NOW())");
    
    foreach ($data['items'] as $item) {
        $stmt->bind_param('iii', $orderId, $item['id'], $item['quantity']);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'orderNumber' => $orderNumber,
        'orderId' => $orderId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Return error response
    echo json_encode([
        'success' => false, 
        'message' => 'Error processing order: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>