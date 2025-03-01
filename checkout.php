<?php include 'includes/header.php'; ?>
<?php include 'config.php'; ?>

<section class="checkout-section py-5">
    <div class="container">
        <h1 class="mb-4">Checkout</h1>
        
        <div class="row">
            <div class="col-lg-8">
            
                <div class="checkout-form card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Shipping Information</h5>
                        <form id="checkoutForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="state" class="form-label">State/Province *</label>
                                    <input type="text" class="form-control" id="state" name="state" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="zipCode" class="form-label">ZIP Code *</label>
                                    <input type="text" class="form-control" id="zipCode" name="zipCode" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="notes" class="form-label">Order Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="payment-methods card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Payment Method</h5>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="codPayment" value="cod" checked>
                            <label class="form-check-label" for="codPayment">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Cash on Delivery
                            </label>
                            <p class="text-muted small mt-1">Pay with cash when your order is delivered.</p>
                        </div>
                        
                        <div class="form-check mb-3 disabled">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="paypalPayment" value="paypal" disabled>
                            <label class="form-check-label text-muted" for="paypalPayment">
                                <i class="fab fa-paypal me-2"></i>
                                PayPal (Currently Unavailable)
                            </label>
                        </div>
                        
                        <div class="form-check mb-3 disabled">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="cardPayment" value="card" disabled>
                            <label class="form-check-label text-muted" for="cardPayment">
                                <i class="far fa-credit-card me-2"></i>
                                Credit/Debit Card (Currently Unavailable)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
              
                <div class="order-summary card sticky-top" style="top: 20px">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Summary</h5>
                        
                        <div id="orderItems" class="mb-3">
                           <!-- Order Items -->
                        </div>
                        
                        <hr>
                        
                        <div class="summary-item d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="summary-item d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span id="shipping">$0.00</span>
                        </div>
                        <hr>
                        <div class="summary-item d-flex justify-content-between mb-4">
                            <strong>Total</strong>
                            <strong id="total">$0.00</strong>
                        </div>
                        
                        <button type="button" id="placeOrderBtn" class="btn btn-primary w-100">Place Order</button>
                        <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="window.location.href='cart.php'">Back to Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Order Confirmed!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Thank You For Your Order</h4>
                <p>Your order has been placed successfully.</p>
                <p class="mb-0">Order Number: <span id="orderNumber"></span></p>
                <p>We'll send a confirmation email to <span id="confirmationEmail"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="window.location.href='products.php'">Continue Shopping</button>
            </div>
        </div>
    </div>
</div>


<template id="orderItemTemplate">
    <div class="order-item d-flex align-items-center mb-2">
        <div class="me-3" style="width: 40px; height: 40px;">
            <img src="" alt="" class="img-fluid rounded product-img">
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
                <h6 class="product-name mb-0"></h6>
                <span class="product-price"></span>
            </div>
            <small class="text-muted">Qty: <span class="product-quantity"></span></small>
        </div>
    </div>
</template>

<script>

let cartProducts = new Map();
let orderTotal = 0;


document.addEventListener('DOMContentLoaded', function() {
    loadCartForCheckout();
    initializeFormValidation();
    document.getElementById('placeOrderBtn').addEventListener('click', validateAndSubmitOrder);
});


async function loadCartForCheckout() {
    const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    
    if (cart.length === 0) {
        redirectToCart('Your cart is empty');
        return;
    }

    try {
        await loadProductDetails(cart);
        displayOrderSummary(cart);
    } catch (error) {
        console.error('Error loading cart for checkout:', error);
        showToast('Error loading cart. Please try again.', 'error');
    }
}

async function loadProductDetails(cart) {
    const productIds = cart.map(item => item.id);
    
    try {
        const response = await fetch('api/get_products_bulk.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: productIds })
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const products = await response.json();
        products.forEach(product => cartProducts.set(product.id, product));
    } catch (error) {
        console.error('Error fetching product details:', error);
        throw error;
    }
}

function displayOrderSummary(cart) {
    const orderItemsContainer = document.getElementById('orderItems');
    orderItemsContainer.innerHTML = '';
    
    let subtotal = 0;
    
    cart.forEach(item => {
        const product = cartProducts.get(item.id);
        if (product) {
            renderOrderItem(orderItemsContainer, product, item.quantity);
            subtotal += product.price * item.quantity;
        }
    });
    
    const shipping = calculateShipping(subtotal);
    orderTotal = subtotal + shipping;
    
    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('shipping').textContent = shipping > 0 ? `$${shipping.toFixed(2)}` : 'Free';
    document.getElementById('total').textContent = `$${orderTotal.toFixed(2)}`;
}

function renderOrderItem(container, product, quantity) {
    const template = document.getElementById('orderItemTemplate');
    const clone = template.content.cloneNode(true);
    
    const img = clone.querySelector('.product-img');
    img.src = product.image_url_1;
    img.alt = product.name;
    
    clone.querySelector('.product-name').textContent = product.name;
    clone.querySelector('.product-quantity').textContent = quantity;
    clone.querySelector('.product-price').textContent = `$${(product.price * quantity).toFixed(2)}`;
    
    container.appendChild(clone);
}

function calculateShipping(subtotal) {
    if (subtotal === 0) return 0;
    return subtotal >= 100 ? 0 : 10;
}


function initializeFormValidation() {
    const form = document.getElementById('checkoutForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
    });
}

function validateAndSubmitOrder() {
    const form = document.getElementById('checkoutForm');
    
    if (!form.checkValidity()) {
       
        form.reportValidity();
        return;
    }
    
   
    const formData = new FormData(form);
    const orderData = {
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        city: formData.get('city'),
        state: formData.get('state'),
        zipCode: formData.get('zipCode'),
        notes: formData.get('notes'),
        paymentMethod: document.querySelector('input[name="paymentMethod"]:checked').value,
        orderTotal: orderTotal,
        items: Array.from(JSON.parse(sessionStorage.getItem('cart')) || [])
    };
    
   
    submitOrder(orderData);
}

async function submitOrder(orderData) {
    try {
       
        const orderButton = document.getElementById('placeOrderBtn');
        orderButton.disabled = true;
        orderButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        
       
        const response = await fetch('api/place_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        
        if (!response.ok) {
            throw new Error('Failed to place order');
        }
        
        const result = await response.json();
        
        if (result.success) {
          
            document.getElementById('orderNumber').textContent = result.orderNumber;
            document.getElementById('confirmationEmail').textContent = orderData.email;
            
            
            sessionStorage.removeItem('cart');
            updateCartCount();
            
            
            const orderModal = new bootstrap.Modal(document.getElementById('orderConfirmationModal'));
            orderModal.show();
        } else {
            throw new Error(result.message || 'Failed to place order');
        }
    } catch (error) {
        console.error('Error submitting order:', error);
        showToast('Failed to place your order. Please try again.', 'error');
    } finally {
        
        const orderButton = document.getElementById('placeOrderBtn');
        orderButton.disabled = false;
        orderButton.textContent = 'Place Order';
    }
}


function redirectToCart(message) {
    showToast(message, 'error');
    setTimeout(() => {
        window.location.href = 'cart.php';
    }, 1000);
}

function updateCartCount() {
    const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    let totalItems = cart.reduce((sum, item) => sum + parseInt(item.quantity), 0);
    
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        cartBadge.style.display = totalItems > 0 ? 'inline-block' : 'none';
        cartBadge.textContent = totalItems;
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }, 100);
}
</script>

<style>
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #333;
    color: white;
    padding: 12px 24px;
    border-radius: 4px;
    z-index: 9999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.toast-notification.show {
    opacity: 1;
    transform: translateY(0);
}

.toast-success {
    background-color: #28a745;
}

.toast-error {
    background-color: #dc3545;
}

.toast-info {
    background-color: #17a2b8;
}

.payment-methods .disabled {
    opacity: 0.6;
}


@keyframes checkmark {
    0% {
        transform: scale(0.8);
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

#orderConfirmationModal .fa-check-circle {
    animation: checkmark 0.5s ease-in-out forwards;
}
</style>
<?php include 'includes/footer.php'; ?>