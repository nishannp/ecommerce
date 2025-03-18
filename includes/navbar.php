<nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
    <div class="container">
      
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-bicycle brand-icon"></i>
            <span class="brand-text">Umesh Cycle</span>
        </a>
        
       
        <div class="d-flex d-lg-none me-2">
            <a href="cart.php" class="nav-link text-white position-relative">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </a>
        </div>
        
       
        <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home d-lg-none me-2"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>" href="products.php">
                        <i class="fas fa-bicycle d-lg-none me-2"></i>All Products
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-tags d-lg-none me-2"></i>Categories
                    </a>
                    <ul class="dropdown-menu custom-dropdown" aria-labelledby="navbarDropdown">
                        <?php
                        $categories = getCategories($conn);
                        foreach($categories as $category) {
                        ?>
                        <li>
                            <a class="dropdown-item" href="category.php?slug=<?php echo $category['category_slug']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
            
          
            <form class="d-flex search-form me-lg-3" action="products.php" method="get">
                <div class="input-group">
                    <input class="form-control custom-search" type="search" name="search" placeholder="Search products..." aria-label="Search">
                    <button class="btn search-btn" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
           
            <div class="d-none d-lg-flex">
                <a href="cart.php" class="nav-link text-white position-relative cart-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        0
                    </span>
                </a>
            </div>
        </div>
    </div>
</nav>

<style>

.custom-navbar {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 0.7rem 1rem;
}


.brand-icon {
    font-size: 1.4rem;
    margin-right: 0.5rem;
    color: #f8f9fa;
}

.brand-text {
    font-weight: 600;
    letter-spacing: 0.5px;
}

.navbar-brand {
    display: flex;
    align-items: center;
    transition: transform 0.3s ease;
}

.navbar-brand:hover {
    transform: scale(1.05);
}


.navbar-dark .navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.85);
    font-weight: 500;
    padding: 0.8rem 1rem;
    transition: all 0.3s ease;
    border-radius: 4px;
}

.navbar-dark .navbar-nav .nav-link:hover {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.1);
}

.navbar-dark .navbar-nav .nav-link.active {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.15);
}

.cart-badge {
    font-size: 0.6rem;
    padding: 0.25em 0.6em;
    transition: transform 0.2s ease;
    display: none; 
}

.cart-link {
    font-size: 1.2rem;
    margin-left: 0.5rem;
}

.cart-link:hover .cart-badge {
    transform: scale(1.1);
}


.custom-dropdown {
    border: none;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    padding: 0.5rem 0;
    min-width: 200px;
}

.dropdown-item {
    padding: 0.6rem 1.2rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f1f5f9;
    transform: translateX(5px);
}


.custom-search {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
}

.search-btn {
    background-color: #ffffff;
    border: 1px solid #ced4da;
    border-left: none;
    color: #6c757d;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    transition: all 0.3s ease;
}

.search-btn:hover {
    color: #2a5298;
    background-color: #f8f9fa;
}

.search-form {
    width: 100%;
    max-width: 300px;
}


.custom-toggler {
    border: 1px solid rgba(255,255,255,0.3);
    padding: 0.25rem 0.5rem;
}


@media (max-width: 991.98px) {
    .navbar-collapse {
        background-color: #1e3c72;
        border-radius: 8px;
        margin-top: 0.5rem;
        padding: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .navbar-nav .nav-link {
        padding: 0.7rem 1rem;
        border-radius: 4px;
    }
    
    .search-form {
        margin: 0.5rem 0;
        max-width: 100%;
    }
    
    .dropdown-menu {
        background-color: rgba(255, 255, 255, 0.05);
        border: none;
        box-shadow: none;
    }
    
    .dropdown-item {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }
}


.dropdown-menu {
    animation: fadeInDown 0.3s ease forwards;
    transform-origin: top center;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>

document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
    
 
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});


function updateCartBadge() {
    const cart = JSON.parse(sessionStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + parseInt(item.quantity), 0);
    
    const cartBadges = document.querySelectorAll('.cart-badge');
    cartBadges.forEach(badge => {
        badge.style.display = totalItems > 0 ? 'inline-block' : 'none';
        badge.textContent = totalItems;
    });
}
</script>