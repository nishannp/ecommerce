</main>

    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h4 class="mb-4">About Cycle Shop</h4>
                    <p>We're passionate about cycling and providing the best bikes and accessories for all types of riders.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h4 class="mb-4">Quick Links</h4>
                    <ul class="list-unstyled footer-links">
                        <li><a href="index.php" class="text-decoration-none text-white-50 hover-white">Home</a></li>
                        <li><a href="products.php" class="text-decoration-none text-white-50 hover-white">All Products</a></li>
                        <?php 
                        $categories = getCategories($conn);
                        foreach(array_slice($categories, 0, 4) as $category) { 
                        ?>
                            <li><a href="category.php?slug=<?php echo $category['category_slug']; ?>" class="text-decoration-none text-white-50 hover-white"><?php echo $category['name']; ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h4 class="mb-4">Contact Info</h4>
                    <ul class="list-unstyled footer-links">
                        <li class="d-flex mb-3">
                            <i class="fas fa-map-marker-alt mt-1 me-2"></i>
                            <span>123 Cycling Street, Bike City</span>
                        </li>
                        <li class="d-flex mb-3">
                            <i class="fas fa-phone-alt mt-1 me-2"></i>
                            <span>+1 (555) 123-4567</span>
                        </li>
                        <li class="d-flex mb-3">
                            <i class="fas fa-envelope mt-1 me-2"></i>
                            <span>info@cycleshop.com</span>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-clock mt-1 me-2"></i>
                            <span>Mon-Sat: 9:00 AM - 8:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container border-top border-secondary mt-4 pt-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Cycle Shop. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Designed by <i class="fas fa-heart text-danger"></i> Suraj</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
