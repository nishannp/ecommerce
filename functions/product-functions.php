<?php
// Get all products with optional limit and featured filter
function getProducts($conn, $limit = null, $featured = null) {
    $sql = "SELECT * FROM products";
    
    if ($featured !== null) {
        $sql .= " WHERE featured = " . ($featured ? '1' : '0');
    }
    
    $sql .= " ORDER BY id DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Get product by ID
function getProductById($conn, $id) {
    $id = $conn->real_escape_string($id);
    $sql = "SELECT * FROM products WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get product by slug
function getProductBySlug($conn, $slug) {
    $slug = $conn->real_escape_string($slug);
    $sql = "SELECT * FROM products WHERE title_slug = '$slug'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get product categories
function getProductCategories($conn, $product_id) {
    $product_id = $conn->real_escape_string($product_id);
    $sql = "SELECT c.* FROM categories c 
            JOIN product_categories pc ON c.id = pc.category_id
            WHERE pc.product_id = '$product_id'";
    
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Get products by category
function getProductsByCategory($conn, $category_id) {
    $category_id = $conn->real_escape_string($category_id);
    $sql = "SELECT p.* FROM products p 
            JOIN product_categories pc ON p.id = pc.product_id
            WHERE pc.category_id = '$category_id'
            ORDER BY p.id DESC";
    
    $result = $conn->query($sql);
    $products = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Format price
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Get product image or placeholder
function getProductImage($product) {
    if (!empty($product['resized_image_url'])) {
        return $product['resized_image_url'];
    } else if (!empty($product['image_url_1'])) {
        return $product['image_url_1'];
    }
    return 'assets/img/placeholder.jpg';
}
?>