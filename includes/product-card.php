<div class="card product-card h-100">
    <a href="product-details.php?slug=<?php echo $product['title_slug']; ?>" class="text-decoration-none">
        <div class="product-img-container">
            <img src="<?php echo getProductImage($product); ?>" class="card-img-top product-img" alt="<?php echo $product['name']; ?>">
        </div>
        <div class="card-body">
            <h5 class="card-title product-title"><?php echo $product['name']; ?></h5>
            <p class="card-text product-price"><?php echo formatPrice($product['price']); ?></p>
            <?php 
            $productCategories = getProductCategories($conn, $product['id']);
            if (count($productCategories) > 0) { 
            ?>
                <div class="product-categories">
                    <?php foreach($productCategories as $category) { ?>
                        <span class="badge bg-primary"><?php echo $category['name']; ?></span>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </a>
    <div class="card-footer bg-white border-top-0">
        <a href="product-details.php?slug=<?php echo $product['title_slug']; ?>" class="btn btn-outline-primary w-100">View Details</a>
    </div>
</div>