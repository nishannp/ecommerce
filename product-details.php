<?php include 'includes/header.php'; ?>

<?php
// Get product slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';


$product = getProductBySlug($conn, $slug);


if (!$product) {
    header('Location: products.php');
    exit;
}


$productCategories = getProductCategories($conn, $product['id']);


$relatedProducts = [];
if (count($productCategories) > 0) {
    $categoryId = $productCategories[0]['id'];
    $relatedProductsTemp = getProductsByCategory($conn, $categoryId);
    
    // Filter out current product and limit to 4 products
    foreach($relatedProductsTemp as $relatedProduct) {
        if ($relatedProduct['id'] != $product['id']) {
            $relatedProducts[] = $relatedProduct;
            if (count($relatedProducts) >= 4) {
                break;
            }
        }
    }
}
?>


<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                        <?php if (count($productCategories) > 0) : ?>
                            <li class="breadcrumb-item">
                                <a href="category.php?slug=<?php echo $productCategories[0]['category_slug']; ?>">
                                    <?php echo $productCategories[0]['name']; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="product-images">
                    <div class="main-image mb-3">
                        <img src="<?php echo getProductImage($product); ?>" class="img-fluid rounded" id="mainImage" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="thumbnail-images d-flex">
                        <?php
                        $images = [];
                        if (!empty($product['image_url_1'])) $images[] = $product['image_url_1'];
                        if (!empty($product['image_url_2'])) $images[] = $product['image_url_2'];
                        if (!empty($product['image_url_3'])) $images[] = $product['image_url_3'];
                        if (!empty($product['image_url_4'])) $images[] = $product['image_url_4'];
                        
                        foreach($images as $index => $image) :
                        ?>
                            <div class="thumbnail me-2">
                                <img src="<?php echo $image; ?>" class="img-fluid rounded thumbnail-img <?php echo ($index === 0) ? 'active' : ''; ?>" alt="<?php echo $product['name']; ?>" data-image="<?php echo $image; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
           
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo $product['name']; ?></h1>
                
                <div class="mb-3">
                    <?php foreach($productCategories as $category) : ?>
                        <span class="badge bg-primary me-1"><?php echo $category['name']; ?></span>
                    <?php endforeach; ?>
                    <?php if (!empty($product['age_range'])) : ?>
                        <span class="badge bg-info"><?php echo $product['age_range']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="price-container mb-4">
                    <h2 class="text-primary fw-bold"><?php echo formatPrice($product['price']); ?></h2>
                    <span class="text-success">In Stock</span>
                </div>
                
                <div class="product-description mb-4">
                    <?php echo nl2br($product['description']); ?>
                </div>
                
                <div class="mb-4">
                    <div class="quantity-selector d-flex align-items-center mb-3">
                        <label for="quantity" class="me-3">Quantity:</label>
                        <div class="input-group" style="width: 130px;">
                            <button class="btn btn-outline-secondary quantity-btn" type="button" id="decrement">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="quantity" value="1" min="1">
                            <button class="btn btn-outline-secondary quantity-btn" type="button" id="increment">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <a href="#" class="btn btn-primary btn-lg me-2 add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </a>
                </div>
                
                <div class="product-meta">
                    <?php if (!empty($product['keywords'])) : ?>
                        <div class="mb-2">
                            <strong>Tags:</strong> 
                            <?php
                            $keywords = explode(',', $product['keywords']);
                            foreach($keywords as $index => $keyword) :
                                echo trim($keyword);
                                if ($index < count($keywords) - 1) echo ', ';
                            endforeach;
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-share mt-4">
                    <span class="me-3">Share:</span>
                    <a href="#" class="me-2 social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="me-2 social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="me-2 social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>


<?php if (count($relatedProducts) > 0) : ?>
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4">Related Products</h2>
        <div class="row">
            <?php foreach($relatedProducts as $product) : ?>
                <div class="col-6 col-md-3 mb-4">
                    <?php include 'includes/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<style>
 


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


.product-images {
  position: relative;
}

.main-image {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  background-color: #fff;
  position: relative;
}

.main-image img {
  width: 100%;
  height: auto;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.main-image:hover img {
  transform: scale(1.02);
}

.thumbnail-images {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  padding-bottom: 5px;
  scrollbar-width: thin;
}

.thumbnail {
  flex: 0 0 80px;
  height: 80px;
  border-radius: 6px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumbnail-img.active {
  border-color: #4a6cf7;
}

.thumbnail:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}


.price-container {
  display: flex;
  align-items: baseline;
  gap: 15px;
}

.text-primary.fw-bold {
  font-size: 2rem;
  color: #4a6cf7 !important;
}

.text-success {
  background-color: rgba(25, 135, 84, 0.1);
  color: #198754;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.product-description {
  line-height: 1.8;
  color: #555;
  font-size: 1.05rem;
}


.quantity-selector {
  margin-bottom: 1.5rem;
}

.quantity-selector label {
  font-weight: 600;
  color: #333;
}

.input-group {
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
  border-radius: 6px;
  overflow: hidden;
}

.quantity-btn {
  background-color: #f8f9fa;
  border-color: #dee2e6;
  color: #555;
  transition: all 0.2s ease;
}

.quantity-btn:hover {
  background-color: #e9ecef;
  color: #333;
}

#quantity {
  border-left: none;
  border-right: none;
  font-weight: 600;
}


.btn-primary {
  background-color: #4a6cf7;
  border-color: #4a6cf7;
  padding: 12px 24px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(74, 108, 247, 0.2);
}

.btn-primary:hover {
  background-color: #2945c3;
  border-color: #2945c3;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(74, 108, 247, 0.3);
}

.btn-primary:active {
  transform: translateY(0);
}


.product-meta {
  padding: 15px 0;
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
  color: #666;
}


.product-share {
  display: flex;
  align-items: center;
}

.social-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background-color: #f8f9fa;
  border-radius: 50%;
  color: #666;
  transition: all 0.3s ease;
}

.social-icon:hover {
  background-color: #4a6cf7;
  color: white;
  transform: translateY(-3px);
}


.badge {
  padding: 6px 12px;
  font-weight: 500;
  border-radius: 20px;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.badge.bg-primary {
  background-color: #4a6cf7 !important;
}

.badge.bg-info {
  background-color: #17a2b8 !important;
}


.bg-light {
  background-color: #f8f9fa !important;
}


@keyframes addedToCart {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.added-to-cart {
  animation: addedToCart 0.5s ease;
  background-color: #28a745 !important;
  border-color: #28a745 !important;
}


@media (max-width: 767px) {
  .text-primary.fw-bold {
    font-size: 1.75rem;
  }
  
  .product-share span {
    display: none;
  }
}


.zoom-container {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.9);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.zoom-container.active {
  opacity: 1;
  visibility: visible;
}

.zoom-image {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
}

.zoom-close {
  position: absolute;
  top: 20px;
  right: 20px;
  color: white;
  font-size: 30px;
  cursor: pointer;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail-img');
    
    if (thumbnails) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                if(mainImage) {
                    mainImage.src = this.getAttribute('data-image');
                }
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    if (mainImage) {
        const zoomContainer = document.createElement('div');
        zoomContainer.className = 'zoom-container';
        
        const zoomImage = document.createElement('img');
        zoomImage.className = 'zoom-image';
        
        const closeButton = document.createElement('div');
        closeButton.className = 'zoom-close';
        closeButton.innerHTML = '&times;';
        
        zoomContainer.appendChild(zoomImage);
        zoomContainer.appendChild(closeButton);
        document.body.appendChild(zoomContainer);
        
        mainImage.addEventListener('click', function() {
            zoomImage.src = this.src;
            zoomContainer.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeButton.addEventListener('click', function() {
            zoomContainer.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        zoomContainer.addEventListener('click', function(e) {
            if (e.target === zoomContainer) {
                zoomContainer.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && zoomContainer.classList.contains('active')) {
                zoomContainer.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    const quantityInput = document.getElementById('quantity');
    const incrementBtn = document.getElementById('increment');
    const decrementBtn = document.getElementById('decrement');
    
    if (quantityInput && incrementBtn && decrementBtn) {
        incrementBtn.addEventListener('click', function() {
            quantityInput.value = parseInt(quantityInput.value) + 1; // Corrected to increment by 1
        });
        
        decrementBtn.addEventListener('click', function() {
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1; // Corrected to decrement by 1
            }
        });
        
        quantityInput.addEventListener('change', function() {
            if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                this.value = 1;
            }
        });
    }
    
    const addToCartBtn = document.querySelector('.add-to-cart');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            const quantity = document.getElementById('quantity').value;
            
            this.classList.add('added-to-cart');
            
            setTimeout(() => {
                this.classList.remove('added-to-cart');
                
                const successMessage = document.createElement('div');
                successMessage.className = 'alert alert-success mt-3';
                successMessage.innerHTML = '<i class="fas fa-check-circle me-2"></i>Product added to cart!';
                this.parentNode.appendChild(successMessage);
                
                setTimeout(() => {
                    successMessage.remove();
                }, 3000);
            }, 500);
        });
    }
    
    const relatedProductsSection = document.querySelector('.related-products');
    const viewRelatedBtn = document.querySelector('.view-related');
    
    if (relatedProductsSection && viewRelatedBtn) {
        viewRelatedBtn.addEventListener('click', function(e) {
            e.preventDefault();
            relatedProductsSection.scrollIntoView({
                behavior: 'smooth'
            });
        });
    }
});
</script>