<?php
require_once 'config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    
    if (empty($cart)) {
        $_SESSION['error'] = "Your cart is empty!";
        header('Location: cart.php');
        exit;
    }
    
    // Validate form data
    $errors = validateOrderData($_POST);
    if (!empty($errors)) {
        $_SESSION['checkout_errors'] = $errors;
        $_SESSION['checkout_data'] = $_POST;
        header('Location: checkout.php');
        exit;
    }
    
    try {
        $conn->begin_transaction();
        
        // Create order data array
        $orderData = [
            'user_id' => null,
            'first_name' => sanitizeInput($_POST['first_name']),
            'last_name' => sanitizeInput($_POST['last_name']),
            'email' => sanitizeInput($_POST['email']),
            'phone' => sanitizeInput($_POST['phone']),
            'address' => sanitizeInput($_POST['address']),
            'city' => sanitizeInput($_POST['city']),
            'postal_code' => sanitizeInput($_POST['postal_code']),
            'total_amount' => calculateOrderTotal($conn, $cart),
            'payment_method' => 'cod'
        ];
        
        // Create order
        $orderId = createOrder($conn, $orderData);
        if (!$orderId) {
            throw new Exception("Failed to create order");
        }
        
        // Create order items
        createOrderItems($conn, $orderId, $cart);
        
        $conn->commit();
        
        // Clear cart from session storage
        echo "<script>sessionStorage.removeItem('cart');</script>";
        
        // Store order ID in session for success page
        $_SESSION['order_id'] = $orderId;
        header('Location: order_success.php');
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing order. Please try again.";
        header('Location: checkout.php');
        exit;
    }
}