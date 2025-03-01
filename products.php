<?php include 'includes/header.php'; ?>

<?php
// Get search query if exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get all products (with search filter if provided)
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql = "SELECT * FROM products WHERE 
            name LIKE '%$search%' OR 
            description LIKE '%$search%' OR 
            keywords LIKE '%$search%'
            ORDER BY id DESC";
    $result = $conn->query($sql);
    $products = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    $products = getProducts($conn);
}
?>


<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">All Products</li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-0">
                    <?php if (!empty($search)) : ?>
                        Search Results for "<?php echo htmlspecialchars($search); ?>"
                    <?php else : ?>
                        All Products
                    <?php endif; ?>
                </h1>
                <p class="text-muted">
                    <?php echo count($products); ?> products found
                </p>
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
                            foreach($categories as $category) { 
                            ?>
                                <li class="mb-2">
                                    <a href="category.php?slug=<?php echo $category['category_slug']; ?>" class="text-decoration-none text-dark">
                                        <?php echo $category['name']; ?>
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
                        <form action="products.php" method="get">
                            <?php if (!empty($search)) : ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            
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
                                                    // Short description
                                                    echo substr(strip_tags($product['description']), 0, 150);
                                                    if (strlen($product['description']) > 150) echo '...';
                                                ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <?php 
                                                    $productCategories = getProductCategories($conn, $product['id']);
                                                    if (count($productCategories) > 0) { 
                                                        foreach($productCategories as $category) { 
                                                    ?>
                                                        <span class="badge bg-primary me-1"><?php echo $category['name']; ?></span>
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
                        <p class="mb-0">No products found. Please try a different search.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>