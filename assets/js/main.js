// Document ready function
$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // View switching (grid/list)
    $('.view-grid').on('click', function() {
        $('.products-grid').removeClass('d-none');
        $('.products-list').addClass('d-none');
        $('.view-grid').addClass('active');
        $('.view-list').removeClass('active');
    });

    $('.view-list').on('click', function() {
        $('.products-grid').addClass('d-none');
        $('.products-list').removeClass('d-none');
        $('.view-grid').removeClass('active');
        $('.view-list').addClass('active');
    });

    // Product sorting
    $('#sort-select').on('change', function() {
        const sortValue = $(this).val();
        let products;
        
        // Determine which view is active
        if ($('.products-grid').hasClass('d-none')) {
            products = $('.product-list-item');
        } else {
            products = $('.products-grid .col-6');
        }
        
        // Sort products based on selected option
        products.sort(function(a, b) {
            switch(sortValue) {
                case 'price-asc':
                    return extractPrice($(a)) - extractPrice($(b));
                case 'price-desc':
                    return extractPrice($(b)) - extractPrice($(a));
                case 'name-asc':
                    return extractProductName($(a)).localeCompare(extractProductName($(b)));
                case 'name-desc':
                    return extractProductName($(b)).localeCompare(extractProductName($(a)));
                default: // newest
                    return $(a).index() - $(b).index();
            }
        });
        
        // Re-append sorted products
        if ($('.products-grid').hasClass('d-none')) {
            $('.products-list').append(products);
        } else {
            $('.products-grid').append(products);
        }
    });

    // Helper function to extract price
    function extractPrice(element) {
        const priceText = element.find('.product-price, .card-text.text-primary').text();
        return parseFloat(priceText.replace(/[^0-9.-]+/g, ''));
    }
    
    // Helper function to extract product name
    function extractProductName(element) {
        return element.find('.product-title, .card-title').text().trim();
    }

    // Add to cart functionality
    $('.add-to-cart').on('click', function(e) {
        e.preventDefault();
        
        const productId = $(this).data('product-id');
        const quantity = $('#quantity').val();
        
        addToCart(productId, quantity);
        
        // Show success message
        showAlert('Product added to cart!', 'success');
    });

    // Quantity buttons
    $('#increment').on('click', function() {
        let qty = parseInt($('#quantity').val());
        $('#quantity').val(qty + 1);
    });
    
    $('#decrement').on('click', function() {
        let qty = parseInt($('#quantity').val());
        if (qty > 1) {
            $('#quantity').val(qty - 1);
        }
    });

    // Product image thumbnails
    $('.thumbnail-img').on('click', function() {
        const mainImage = $('#mainImage');
        const newSrc = $(this).data('image');
        
        // Change main image source
        mainImage.attr('src', newSrc);
        
        // Update active thumbnail
        $('.thumbnail-img').removeClass('active');
        $(this).addClass('active');
    });

    // Newsletter subscription
    $('.newsletter-form').on('submit', function(e) {
        e.preventDefault();
        
        const email = $(this).find('input[type="email"]').val();
        
        // Here you would typically make an AJAX call to subscribe the email
        
        // For demo, just show success message
        $(this).find('input[type="email"]').val('');
        showAlert('Thank you for subscribing!', 'success');
    });

    // Show alert message
    function showAlert(message, type = 'info') {
        // Create alert element
        const alertDiv = $('<div class="alert-toast position-fixed top-0 end-0 p-3" style="z-index: 9999;">').html(`
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type} text-white">
                    <strong class="me-auto">Cycle Shop</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `);
        
        // Add to body and auto-remove after 5s
        $('body').append(alertDiv);
        
        setTimeout(function() {
            alertDiv.remove();
        }, 5000);
    }

    // Cart functionality
    function addToCart(productId, quantity) {
        // Get existing cart from session storage or create new cart
        let cart = JSON.parse(sessionStorage.getItem('cart')) || [];
        
        // Check if product is already in cart
        const existingProductIndex = cart.findIndex(item => item.id === productId);
        
        if (existingProductIndex !== -1) {
            // Update quantity if product already exists in cart
            cart[existingProductIndex].quantity = parseInt(cart[existingProductIndex].quantity) + parseInt(quantity);
        } else {
            // Add new product to cart
            cart.push({
                id: productId,
                quantity: parseInt(quantity)
            });
        }
        
        // Save cart back to session storage
        sessionStorage.setItem('cart', JSON.stringify(cart));
        
        // Update cart count in UI
        updateCartCount();
    }
    
    // Update cart count display
    function updateCartCount() {
        let cart = JSON.parse(sessionStorage.getItem('cart')) || [];
        let totalItems = 0;
        
        cart.forEach(item => {
            totalItems += parseInt(item.quantity);
        });
        
        // Update cart badge
        if (totalItems > 0) {
            $('.cart-badge').text(totalItems).removeClass('d-none');
        } else {
            $('.cart-badge').addClass('d-none');
        }
    }
    
    // Initialize cart count on page load
    updateCartCount();
});