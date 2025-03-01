<?php include 'includes/header.php'; ?>

<section class="cart-section py-5">
    <div class="container">
        <h1 class="mb-4">Shopping Cart</h1>
        
        <div class="row">
            <div class="col-lg-8">
               
                <div class="cart-items mb-4">
                    <div id="cartContent">
                       
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
               
                <div class="cart-summary card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Summary</h5>
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
                        <button class="btn btn-primary w-100" id="checkoutBtn">Proceed to Checkout</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<template id="cartItemTemplate">
    <div class="cart-item card mb-3" data-product-id="">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="" alt="" class="img-fluid rounded product-img">
                </div>
                <div class="col-md-4">
                    <h5 class="product-name"></h5>
                    <p class="text-muted product-category"></p>
                </div>
             
<div class="col-md-2">
    <div class="quantity-controls">
        <div class="input-group">
            <button class="btn decrease-qty" type="button">
                <i class="fas fa-minus"></i>
            </button>
            <input type="number" class="form-control product-quantity" value="1" min="1" max="99">
            <button class="btn increase-qty" type="button">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
</div>
                <div class="col-md-2">
                    <span class="product-price"></span>
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-link text-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

let cartProducts = new Map();
let isUpdating = false;


document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    initializeCheckoutButton();
});


async function loadCart() {
    const cartContent = document.getElementById('cartContent');
    const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    
    if (cart.length === 0) {
        showEmptyCartMessage(cartContent);
        updateSummary(0);
        return;
    }

    try {
        await loadProductDetails(cart);
        renderCartItems(cart);
    } catch (error) {
        showErrorMessage(cartContent);
    }
}

function showEmptyCartMessage(container) {
    container.innerHTML = `
        <div class="alert alert-info">
            Your cart is empty. <a href="products.php">Continue shopping</a>
        </div>
    `;
}

function showErrorMessage(container) {
    container.innerHTML = `
        <div class="alert alert-danger">
            Error loading cart. Please try again.
        </div>
    `;
}

async function loadProductDetails(cart) {
    const productIds = cart.map(item => item.id);
    const response = await fetch('api/get_products_bulk.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: productIds })
    });
    
    const products = await response.json();
    products.forEach(product => cartProducts.set(product.id, product));
}

function renderCartItems(cart) {
    const cartContent = document.getElementById('cartContent');
    cartContent.innerHTML = '';
    let subtotal = 0;

    cart.forEach(item => {
        const product = cartProducts.get(item.id);
        if (product) {
            renderCartItem(product, item.quantity);
            subtotal += product.price * item.quantity;
        }
    });

    updateSummary(subtotal);
}

function renderCartItem(product, quantity) {
    const template = document.getElementById('cartItemTemplate');
    const clone = template.content.cloneNode(true);
    const cartItem = clone.querySelector('.cart-item');
    
  
    cartItem.dataset.productId = product.id;
    const img = clone.querySelector('.product-img');
    img.src = product.image_url_1;
    img.alt = product.name;
    
    clone.querySelector('.product-name').textContent = product.name;
    clone.querySelector('.product-category').textContent = product.category_name;
    
   
    setupQuantityControls(clone, product, quantity);
    
 
    updateItemPrice(clone, product.price, quantity);
    
    document.getElementById('cartContent').appendChild(clone);
}

function setupQuantityControls(element, product, quantity) {
    const quantityInput = element.querySelector('.product-quantity');
    const increaseBtn = element.querySelector('.increase-qty');
    const decreaseBtn = element.querySelector('.decrease-qty');
    const removeBtn = element.querySelector('.remove-item');
    
    quantityInput.value = quantity;
    
    quantityInput.addEventListener('change', (e) => {
        let value = parseInt(e.target.value);
        value = Math.max(1, Math.min(99, value || 1));
        e.target.value = value;
        updateQuantity(product.id, value);
    });
    
    increaseBtn.addEventListener('click', () => {
        const newQty = parseInt(quantityInput.value) + 1;
        if (newQty <= 99) {
            quantityInput.value = newQty;
            updateQuantity(product.id, newQty);
        }
    });
    
    decreaseBtn.addEventListener('click', () => {
        const newQty = parseInt(quantityInput.value) - 1;
        if (newQty >= 1) {
            quantityInput.value = newQty;
            updateQuantity(product.id, newQty);
        }
    });
    
    removeBtn.addEventListener('click', () => removeItem(product.id));
}


function updateQuantity(productId, newQuantity) {
    if (isUpdating) return;
    isUpdating = true;
    
    const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    const itemIndex = cart.findIndex(item => item.id === productId);
    
    if (itemIndex !== -1) {
        cart[itemIndex].quantity = newQuantity;
        sessionStorage.setItem('cart', JSON.stringify(cart));
        
        const product = cartProducts.get(productId);
        const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
        
        if (product && cartItem) {
            updateItemPrice(cartItem, product.price, newQuantity);
            updateSummaryFromCart(cart);
            updateCartCount();
            showToast('Cart updated', 'success');
        }
    }
    
    setTimeout(() => { isUpdating = false; }, 300);
}

function removeItem(productId) {
    const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    const updatedCart = cart.filter(item => item.id !== productId);
    sessionStorage.setItem('cart', JSON.stringify(updatedCart));
    
    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    if (cartItem) {
        animateRemoval(cartItem, () => {
            if (updatedCart.length === 0) {
                loadCart();
            } else {
                updateSummaryFromCart(updatedCart);
            }
        });
    }
    
    updateCartCount();
    showToast('Item removed from cart', 'info');
}


function updateItemPrice(element, price, quantity) {
    const total = price * quantity;
    element.querySelector('.product-price').textContent = `$${total.toFixed(2)}`;
}

function updateSummaryFromCart(cart) {
    let subtotal = 0;
    cart.forEach(item => {
        const product = cartProducts.get(item.id);
        if (product) {
            subtotal += product.price * item.quantity;
        }
    });
    updateSummary(subtotal);
}

function updateSummary(subtotal) {
    const shipping = calculateShipping(subtotal);
    const total = subtotal + shipping;
    
    animateNumber('subtotal', subtotal);
    animateNumber('shipping', shipping);
    animateNumber('total', total);
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


function calculateShipping(subtotal) {
    if (subtotal === 0) return 0;
    return subtotal >= 100 ? 0 : 10;
}

function animateNumber(elementId, target) {
    const element = document.getElementById(elementId);
    const start = parseFloat(element.textContent.replace(/[^0-9.-]+/g, ''));
    const duration = 500;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = start + (target - start) * progress;
        element.textContent = `$${current.toFixed(2)}`;
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

function animateRemoval(element, callback) {
    element.style.transition = 'all 0.3s ease';
    element.style.opacity = '0';
    element.style.transform = 'translateX(-20px)';
    
    setTimeout(() => {
        element.remove();
        if (callback) callback();
    }, 300);
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

function initializeCheckoutButton() {
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
        if (cart.length === 0) {
            showToast('Your cart is empty', 'error');
            return;
        }
        window.location.href = 'checkout.php';
    });
}
</script>


<style>
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 4px;
    color: white;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.toast-notification.show {
    opacity: 1;
    transform: translateY(0);
}

.toast-success { background-color: #28a745; }
.toast-error { background-color: #dc3545; }
.toast-info { background-color: #17a2b8; }


</style>

<?php include 'includes/footer.php'; ?>

<style>
    .cart-section {
    min-height: 60vh;
}

.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-img {
    max-width: 100px;
    height: auto;
}

.quantity-controls {
    width: 120px;
    margin: 0 auto;
}

.quantity-controls .input-group {
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.quantity-controls .form-control {
    width: 50px;
    height: 38px;
    text-align: center;
    border: none;
    background: white;
    font-weight: 600;
    color: #333;
}

.quantity-controls .btn {
    width: 35px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border: none;
    color: #333;
    transition: all 0.2s ease;
}

.quantity-controls .btn:hover {
    background: #e9ecef;
}

.quantity-controls input::-webkit-outer-spin-button,
.quantity-controls input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-controls input[type=number] {
    -moz-appearance: textfield;
}


@media (max-width: 768px) {
    .quantity-controls {
        width: 120px;
        margin: 10px auto;
    }
    
    .cart-item .quantity-controls {
        margin-bottom: 15px;
    }
}
.cart-summary {
    position: sticky;
    top: 20px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.summary-item {
    font-size: 1.1rem;
}

.remove-item {
    transition: all 0.3s ease;
}

.remove-item:hover {
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .cart-item .row {
        flex-direction: column;
        text-align: center;
    }
    
    .cart-item .col-md-2,
    .cart-item .col-md-4 {
        margin-bottom: 15px;
    }
    
    .product-img {
        margin: 0 auto;
    }
    
    .quantity-controls {
        display: flex;
        justify-content: center;
    }
}
</style>