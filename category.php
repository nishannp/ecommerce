<?php include 'includes/header.php'; ?>

<?php
// Get category slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Get category details
$category = getCategoryBySlug($conn, $slug);

if (!$category) {
    header('Location: products.php');
    exit;
}


$products = getProductsByCategory($conn, $category['id']);
?>


<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $category['name']; ?></li>
                    </ol>
                </nav>
                <div class="d-md-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="fw-bold mb-0"><?php echo $category['name']; ?></h1>
                        <p class="text-muted"><?php echo count($products); ?> products found</p>
                    </div>
                    <?php if (!empty($category['category_image'])) : ?>
                        <div class="category-image-container">
                            <img src="<?php echo $category['category_image']; ?>" alt="<?php echo $category['name']; ?>" class="img-fluid category-page-image rounded">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="py-5">
    <div class="container">
        <div class="row">
        
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled category-list">
                            <?php 
                            $categories = getCategories($conn);
                            foreach($categories as $cat) { 
                                $activeClass = ($cat['id'] == $category['id']) ? 'fw-bold text-primary' : 'text-dark';
                            ?>
                                <li class="mb-2">
                                    <a href="category.php?slug=<?php echo $cat['category_slug']; ?>" class="text-decoration-none <?php echo $activeClass; ?>">
                                        <?php echo $cat['name']; ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Age Range</h5>
                    </div>
                    <div class="card-body">
                        <form action="category.php" method="get">
                            <input type="hidden" name="slug" value="<?php echo $category['category_slug']; ?>">
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="age_range[]" value="Adult" id="adult">
                                <label class="form-check-label" for="adult">Adult</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="age_range[]" value="Teen" id="teen">
                                <label class="form-check-label" for="teen">Teen</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="age_range[]" value="Kids" id="kids">
                                <label class="form-check-label" for="kids">Kids</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">Apply Filter</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <label for="sort-select" class="me-2">Sort by:</label>
                        <select id="sort-select" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="newest">Newest</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                            <option value="name-asc">Name: A to Z</option>
                            <option value="name-desc">Name: Z to A</option>
                        </select>
                    </div>
                    <div class="view-options">
                        <button class="btn btn-sm btn-outline-secondary me-1 view-grid active">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary view-list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
                
                <?php if (count($products) > 0) : ?>
                    <div class="row products-grid">
                        <?php foreach($products as $product) : ?>
                        <div class="col-6 col-md-4 mb-4">
                            <?php include 'includes/product-card.php'; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                  
                    <div class="products-list d-none">
                        <?php foreach($products as $product) : ?>
                            <div class="card mb-3 product-list-item">
                                <div class="row g-0">
                                    <div class="col-md-3">
                                        <a href="product-details.php?slug=<?php echo $product['title_slug']; ?>">
                                            <img src="<?php echo getProductImage($product); ?>" class="img-fluid rounded-start" alt="<?php echo $product['name']; ?>">
                                        </a>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="card-title">
                                                        <a href="product-details.php?slug=<?php echo $product['title_slug']; ?>" class="text-decoration-none text-dark">
                                                            <?php echo $product['name']; ?>
                                                        </a>
                                                    </h5>
                                                    <p class="card-text text-primary fw-bold"><?php echo formatPrice($product['price']); ?></p>
                                                </div>
                                            </div>
                                            <p class="card-text">
                                                <?php 
                                                   
                                                    echo substr(strip_tags($product['description']), 0, 150);
                                                    if (strlen($product['description']) > 150) echo '...';
                                                ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <?php 
                                                    $productCategories = getProductCategories($conn, $product['id']);
                                                    if (count($productCategories) > 0) { 
                                                        foreach($productCategories as $cat) { 
                                                    ?>
                                                        <span class="badge bg-primary me-1"><?php echo $cat['name']; ?></span>
                                                    <?php 
                                                        }
                                                    } 
                                                    ?>
                                                </div>
                                                <a href="product-details.php?slug=<?php echo $product['title_slug']; ?>" class="btn btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="alert alert-info">
                        <p class="mb-0">No products found in this category.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
 
.breadcrumb {
  background-color: transparent;
  padding: 0;
  margin-bottom: 1.5rem;
}

.breadcrumb-item a {
  color: #4a6cf7;
  text-decoration: none;
  transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
  color: #2945c3;
  text-decoration: underline;
}

.breadcrumb-item.active {
  color: #666;
  font-weight: 500;
}


.bg-light {
  background-color: #f8f9fa !important;
}

.category-image-container {
  max-width: 200px;
  margin-top: 1rem;
}

@media (min-width: 768px) {
  .category-image-container {
    margin-top: 0;
  }
}

.category-page-image {
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease;
}

.category-page-image:hover {
  transform: scale(1.03);
}


.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: box-shadow 0.3s ease;
}

.card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.card-header {
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1rem 1.25rem;
}

.card-header h5 {
  font-weight: 600;
  color: #333;
}

.category-list li {
  position: relative;
  padding: 0.25rem 0;
  transition: transform 0.2s ease;
}

.category-list li:hover {
  transform: translateX(5px);
}

.category-list a {
  display: block;
  color: #555;
  transition: color 0.2s ease;
}

.category-list a:hover {
  color: #4a6cf7;
}

.category-list a.fw-bold {
  position: relative;
  padding-left: 12px;
}

.category-list a.fw-bold:before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 80%;
  background-color: #4a6cf7;
  border-radius: 2px;
}


.form-check-input {
  cursor: pointer;
}

.form-check-input:checked {
  background-color: #4a6cf7;
  border-color: #4a6cf7;
}

.form-check-label {
  cursor: pointer;
  transition: color 0.2s ease;
}

.form-check-input:checked + .form-check-label {
  color: #4a6cf7;
  font-weight: 500;
}


.form-select {
  border-radius: 6px;
  border-color: #dee2e6;
  box-shadow: none;
  transition: border-color 0.2s ease;
}

.form-select:focus {
  border-color: #4a6cf7;
  box-shadow: 0 0 0 0.25rem rgba(74, 108, 247, 0.25);
}

.view-options .btn {
  border-radius: 6px;
  color: #555;
  border-color: #dee2e6;
  transition: all 0.2s ease;
}

.view-options .btn:hover {
  background-color: #f8f9fa;
}

.view-options .btn.active {
  background-color: #4a6cf7;
  color: white;
  border-color: #4a6cf7;
}


.products-grid .card {
  height: 100%;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.products-grid .card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.products-grid .card-img-top {
  height: 200px;
  object-fit: cover;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}


.product-list-item {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-list-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.product-list-item img {
  height: 100%;
  object-fit: cover;
}


.card-title {
  font-weight: 600;
  margin-bottom: 0.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.text-primary.fw-bold {
  color: #4a6cf7 !important;
}

.badge.bg-primary {
  background-color: #4a6cf7 !important;
  padding: 6px 12px;
  font-weight: 500;
  border-radius: 20px;
  font-size: 0.75rem;
}

.btn-outline-primary {
  color: #4a6cf7;
  border-color: #4a6cf7;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover {
  background-color: #4a6cf7;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(74, 108, 247, 0.2);
}

.btn-primary {
  background-color: #4a6cf7;
  border-color: #4a6cf7;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(74, 108, 247, 0.2);
}

.btn-primary:hover {
  background-color: #2945c3;
  border-color: #2945c3;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(74, 108, 247, 0.3);
}


.alert-info {
  background-color: rgba(74, 108, 247, 0.1);
  border-color: rgba(74, 108, 247, 0.2);
  color: #4a6cf7;
  border-radius: 8px;
  padding: 1.5rem;
}


@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.products-grid, .products-list {
  animation: fadeIn 0.5s ease;
}


@media (max-width: 767px) {
  .card-header {
    padding: 0.75rem 1rem;
  }
  
  .category-page-image {
    max-width: 150px;
  }
}
</style>

<script>
  

document.addEventListener('DOMContentLoaded', function() {
    
    const gridViewBtn = document.querySelector('.view-grid');
    const listViewBtn = document.querySelector('.view-list');
    const productsGrid = document.querySelector('.products-grid');
    const productsList = document.querySelector('.products-list');
    
    if (gridViewBtn && listViewBtn && productsGrid && productsList) {
      
        const savedView = localStorage.getItem('categoryViewMode') || 'grid';
        
        const setViewMode = (viewMode) => {
            if (viewMode === 'grid') {
                productsGrid.classList.remove('d-none');
                productsList.classList.add('d-none');
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
            } else {
                productsGrid.classList.add('d-none');
                productsList.classList.remove('d-none');
                gridViewBtn.classList.remove('active');
                listViewBtn.classList.add('active');
            }
            localStorage.setItem('categoryViewMode', viewMode);
        };
        
      
        setViewMode(savedView);
        
        
        gridViewBtn.addEventListener('click', function() {
            setViewMode('grid');
        });
        
        listViewBtn.addEventListener('click', function() {
            setViewMode('list');
        });
    }
    
   
    const sortSelect = document.getElementById('sort-select');
    
    if (sortSelect) {
       
        const savedSort = localStorage.getItem('categorySortMethod');
        if (savedSort) {
            sortSelect.value = savedSort;
        }
        
       
        sortSelect.addEventListener('change', function() {
            const sortMethod = this.value;
            localStorage.setItem('categorySortMethod', sortMethod);
            
            // Sort products
            sortProducts(sortMethod);
        });
        
       
        if (savedSort) {
            sortProducts(savedSort);
        }
    }
    
 
    function sortProducts(method) {
       
        const gridProducts = document.querySelectorAll('.products-grid .col-6');
        const listProducts = document.querySelectorAll('.products-list .product-list-item');
        
       
        const sortFunctions = {
            'newest': (a, b) => {
               
                return 0;
            },
            'price-asc': (a, b) => {
                const priceA = parseFloat(a.querySelector('.fw-bold').innerText.replace(/[^0-9.-]+/g, ""));
                const priceB = parseFloat(b.querySelector('.fw-bold').innerText.replace(/[^0-9.-]+/g, ""));
                return priceA - priceB;
            },
            'price-desc': (a, b) => {
                const priceA = parseFloat(a.querySelector('.fw-bold').innerText.replace(/[^0-9.-]+/g, ""));
                const priceB = parseFloat(b.querySelector('.fw-bold').innerText.replace(/[^0-9.-]+/g, ""));
                return priceB - priceA;
            },
            'name-asc': (a, b) => {
                const nameA = a.querySelector('.card-title').innerText;
                const nameB = b.querySelector('.card-title').innerText;
                return nameA.localeCompare(nameB);
            },
            'name-desc': (a, b) => {
                const nameA = a.querySelector('.card-title').innerText;
                const nameB = b.querySelector('.card-title').innerText;
                return nameB.localeCompare(nameA);
            }
        };
        
        
        if (gridProducts.length > 0) {
            const sortedGrid = Array.from(gridProducts).sort(sortFunctions[method]);
            const gridContainer = document.querySelector('.products-grid');
            
            sortedGrid.forEach(product => {
                gridContainer.appendChild(product);
            });
        }
        
       
        if (listProducts.length > 0) {
            const sortedList = Array.from(listProducts).sort(sortFunctions[method]);
            const listContainer = document.querySelector('.products-list');
            
            sortedList.forEach(product => {
                listContainer.appendChild(product);
            });
        }
    }
    
    
    const filterForm = document.querySelector('.card-body form');
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="age_range[]"]');
    
    if (filterForm && checkboxes.length > 0) {
       
        const urlParams = new URLSearchParams(window.location.search);
        const ageRangeParams = urlParams.getAll('age_range[]');
        
       
        if (ageRangeParams.length > 0) {
            checkboxes.forEach(checkbox => {
                checkbox.checked = ageRangeParams.includes(checkbox.value);
            });
        } else {
           
            const savedFilters = JSON.parse(localStorage.getItem('categoryFilters') || '[]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = savedFilters.includes(checkbox.value);
            });
        }
        
        
        filterForm.addEventListener('submit', function(e) {
            const selectedFilters = [];
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedFilters.push(checkbox.value);
                }
            });
            
            localStorage.setItem('categoryFilters', JSON.stringify(selectedFilters));
        });
    }
    
  
    if (document.referrer.includes('product-details.php')) {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
   
    const animateOnScroll = () => {
        const products = document.querySelectorAll('.products-grid .card, .product-list-item');
        const triggerBottom = window.innerHeight * 0.8;
        
        products.forEach(product => {
            const productTop = product.getBoundingClientRect().top;
            
            if (productTop < triggerBottom) {
                product.style.opacity = '1';
                product.style.transform = 'translateY(0)';
            }
        });
    };
    
    
    const initProductAnimations = () => {
        const products = document.querySelectorAll('.products-grid .card, .product-list-item');
        
        products.forEach(product => {
            product.style.opacity = '0';
            product.style.transform = 'translateY(20px)';
            product.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
        
      
        setTimeout(animateOnScroll, 100);
    };
    
   
    initProductAnimations();
    
   
    window.addEventListener('scroll', animateOnScroll);
});
</script>