<?php include 'includes/header.php'; ?>


<section class="hero-section">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="hero-item bg-primary-gradient d-flex align-items-center">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-6 text-white">
                                <h1 class="display-4 fw-bold mb-4">Experience the Freedom of Cycling</h1>
                                <p class="lead mb-4">Discover our premium range of bicycles for all your adventures.</p>
                                <a href="products.php" class="btn btn-light btn-lg">Shop Now</a>
                            </div>
                            <div class="col-lg-6 d-none d-lg-block text-center">
                                <img src="assets/img/placeholder.jpg" alt="Bicycle" class="img-fluid hero-img">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="hero-item bg-success-gradient d-flex align-items-center">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-6 text-white">
                                <h1 class="display-4 fw-bold mb-4">Quality Cycling Accessories</h1>
                                <p class="lead mb-4">Enhance your riding experience with our high-quality accessories.</p>
                                <a href="products.php" class="btn btn-light btn-lg">Explore</a>
                            </div>
                            <div class="col-lg-6 d-none d-lg-block text-center">
                                <img src="assets/img/placeholder.jpg" alt="Accessories" class="img-fluid hero-img">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>


<section class="py-5">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="fw-bold">Browse Categories</h2>
            <p class="text-muted">Explore our wide range of cycling products</p>
        </div>
        <div class="row">
            <?php 
            $categories = getCategories($conn);
            foreach($categories as $category) { 
            ?>
            <div class="col-6 col-md-4 col-lg-3 mb-4">
                <a href="category.php?slug=<?php echo $category['category_slug']; ?>" class="text-decoration-none">
                    <div class="category-card text-center">
                        <div class="category-img-container mb-3">
                            <img src="<?php echo getCategoryImage($category); ?>" alt="<?php echo $category['name']; ?>" class="img-fluid rounded-circle category-img">
                        </div>
                        <h5 class="category-title"><?php echo $category['name']; ?></h5>
                    </div>
                </a>
            </div>
            <?php } ?>
        </div>
    </div>
</section>


<section class="py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="fw-bold">Featured Products</h2>
            <p class="text-muted">Our selection of premium bikes and accessories</p>
        </div>
        <div class="row">
            <?php 
            $featuredProducts = getProducts($conn, 8, true);
            if (count($featuredProducts) > 0) {
                foreach($featuredProducts as $product) { 
            ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                    <?php include 'includes/product-card.php'; ?>
                </div>
            <?php 
                }
            } else { 
                // If no featured products, show regular products
                $products = getProducts($conn, 8);
                foreach($products as $product) { 
            ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                    <?php include 'includes/product-card.php'; ?>
                </div>
            <?php 
                }
            } 
            ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-primary">View All Products</a>
        </div>
    </div>
</section>


<section class="py-5">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2 class="fw-bold">Why Choose Us</h2>
            <p class="text-muted">What makes Cycle Shop special</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-check-circle fa-3x text-primary"></i>
                    </div>
                    <h5>Quality Products</h5>
                    <p class="text-muted">We offer only high-quality bikes and accessories</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                    </div>
                    <h5>Fast Delivery</h5>
                    <p class="text-muted">Quick and reliable shipping to your doorstep</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-primary"></i>
                    </div>
                    <h5>Expert Support</h5>
                    <p class="text-muted">Our team is always ready to assist you</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-undo fa-3x text-primary"></i>
                    </div>
                    <h5>Easy Returns</h5>
                    <p class="text-muted">30-day hassle-free return policy</p>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h3 class="fw-bold">Subscribe to Our Newsletter</h3>
                <p class="mb-0">Get the latest updates on new products and special promotions</p>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form d-flex">
                    <input type="email" class="form-control" placeholder="Your email address" required>
                    <button type="submit" class="btn btn-light ms-2">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
    
</style>