<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    
    // Get product details
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN product_categories pc ON p.id = pc.product_id 
        LEFT JOIN categories c ON pc.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Format the response
        $response = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'category_name' => $row['category_name'],
            'image_url_1' => $row['image_url_1'],
            // Add other fields as needed
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
}