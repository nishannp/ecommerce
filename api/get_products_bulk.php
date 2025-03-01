<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ids']) || !is_array($input['ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Product IDs are required']);
    exit;
}

$ids = array_map('intval', $input['ids']);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';

$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN product_categories pc ON p.id = pc.product_id 
    LEFT JOIN categories c ON pc.category_id = c.id 
    WHERE p.id IN ($placeholders)
");

$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'category_name' => $row['category_name'],
        'image_url_1' => $row['image_url_1'],
    ];
}

echo json_encode($products);