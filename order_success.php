<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['order_id'])) {
    header('Location: index.php');
    exit;
}

$order = getOrderById($conn, $_SESSION['order_id']);
unset($_SESSION['order_id']); // Clear the order ID from session
?>

<?php include 'includes/header.php'; ?>

<div class="order-success-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card success-card">
                    <div class="card-body text-center">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1 class="mb-4">Thank You for Your Order!</h1>
                        <p class="lead mb-4">Your order has been successfully placed.</p>
                        <div class="order-details text-start p-4 mb-4">
                            <h5 class="mb-3">Order Details</h5>
                            <p><strong>Order Number:</strong> <?php echo formatOrderNumber($order['id']); ?></p>
                            <p><strong>Total Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                            <p><strong>Payment Method:</strong> Cash on Delivery</p>
                            
                            <h6 class="mt-4 mb-3">Shipping Details</h6>
                            <p class="mb-1"><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                            <p class="mb-1"><?php echo $order['address']; ?></p>
                            <p class="mb-1"><?php echo $order['city'] . ' ' . $order['postal_code']; ?></p>
                            <p class="mb-1"><?php echo $order['phone']; ?></p>
                            <p><?php echo $order['email']; ?></p>
                        </div>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary btn-lg">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-success-section {
    background-color: #f8f9fa;
    min-height: 80vh;
    display: flex;
    align-items: center;
}

.success-card {
    border: none;
    box-shadow: 0 0 30px rgba(0,0,0,0.1);
    border-radius: 15px;
}

.success-icon {
    font-size: 5rem;
    color: #28a745;
}

.success-icon i {
    animation: scaleUp 0.5s ease-in-out;
}

.order-details {
    background-color: #f8f9fa;
    border-radius: 10px;
}

@keyframes scaleUp {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.btn-primary {
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 30px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
}

@media (max-width: 768px) {
    .order-success-section {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>