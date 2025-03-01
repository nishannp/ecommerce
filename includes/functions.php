<?php
require_once('../config.php');
// Database helper functions
function getCategories($conn) {
    $sql = "SELECT * FROM categories WHERE status = 1 ORDER BY name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductBySlug($conn, $slug) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE slug = ? AND status = 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getProducts($conn, $limit = null, $featured = false) {
    $sql = "SELECT * FROM products WHERE status = 1";
    if ($featured) {
        $sql .= " AND featured = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductsByCategory($conn, $categoryId, $limit = null) {
    $sql = "SELECT p.* FROM products p 
            JOIN product_categories pc ON p.id = pc.product_id 
            WHERE pc.category_id = ? AND p.status = 1 
            ORDER BY p.created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductCategories($conn, $productId) {
    $sql = "SELECT c.* FROM categories c 
            JOIN product_categories pc ON c.id = pc.category_id 
            WHERE pc.product_id = ? AND c.status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Image handling functions
function getCategoryImage($category) {
    return !empty($category['image_url']) 
        ? $category['image_url'] 
        : 'assets/img/default-category.jpg';
}

function getProductImage($product) {
    return !empty($product['image_url_1']) 
        ? $product['image_url_1'] 
        : 'assets/img/default-product.jpg';
}

// Format functions
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// URL and Slug functions
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

// Cart functions
function calculateCartTotal($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Error handling
function displayError($message) {
    return "<div class='alert alert-danger'>{$message}</div>";
}

function displaySuccess($message) {
    return "<div class='alert alert-success'>{$message}</div>";
}

// ... existing functions ...

// Order Processing Functions
function createOrder($conn, $orderData) {
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id, first_name, last_name, email, phone, 
            address, city, postal_code, total_amount, payment_method
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "isssssssds",
        $orderData['user_id'],
        $orderData['first_name'],
        $orderData['last_name'],
        $orderData['email'],
        $orderData['phone'],
        $orderData['address'],
        $orderData['city'],
        $orderData['postal_code'],
        $orderData['total_amount'],
        $orderData['payment_method']
    );
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function createOrderItems($conn, $orderId, $items) {
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($items as $item) {
        $product = getProductById($conn, $item['id']);
        if ($product) {
            $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $product['price']);
            $stmt->execute();
        }
    }
}

function getProductById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function calculateOrderTotal($conn, $cart) {
    $total = 0;
    foreach ($cart as $item) {
        $product = getProductById($conn, $item['id']);
        if ($product) {
            $total += $product['price'] * $item['quantity'];
        }
    }
    
    // Add shipping if total is less than $100
    if ($total < 100) {
        $total += 10;
    }
    
    return $total;
}

function getOrderById($conn, $orderId) {
    $stmt = $conn->prepare("
        SELECT o.*, oi.product_id, oi.quantity, oi.price, p.name as product_name
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.id = ?
    ");
    
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $order = null;
    $items = [];
    
    while ($row = $result->fetch_assoc()) {
        if (!$order) {
            $order = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'city' => $row['city'],
                'postal_code' => $row['postal_code'],
                'total_amount' => $row['total_amount'],
                'payment_method' => $row['payment_method'],
                'order_status' => $row['order_status'],
                'created_at' => $row['created_at'],
                'items' => []
            ];
        }
        
        if ($row['product_id']) {
            $order['items'][] = [
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'price' => $row['price']
            ];
        }
    }
    
    return $order;
}

function updateOrderStatus($conn, $orderId, $status) {
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    return $stmt->execute();
}

function validateOrderData($data) {
    $errors = [];
    
    if (empty($data['first_name'])) $errors[] = "First name is required";
    if (empty($data['last_name'])) $errors[] = "Last name is required";
    if (empty($data['email']) || !validateEmail($data['email'])) $errors[] = "Valid email is required";
    if (empty($data['phone'])) $errors[] = "Phone number is required";
    if (empty($data['address'])) $errors[] = "Address is required";
    if (empty($data['city'])) $errors[] = "City is required";
    if (empty($data['postal_code'])) $errors[] = "Postal code is required";
    if (empty($data['payment_method'])) $errors[] = "Payment method is required";
    
    return $errors;
}

function formatOrderNumber($orderId) {
    return 'ORD-' . str_pad($orderId, 8, '0', STR_PAD_LEFT);
}

function getOrderStatus($status) {
    $statuses = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    
    return $statuses[$status] ?? 'Unknown';
}
