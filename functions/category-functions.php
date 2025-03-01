<?php
// Get all categories
function getCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Get category by ID
function getCategoryById($conn, $id) {
    $id = $conn->real_escape_string($id);
    $sql = "SELECT * FROM categories WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get category by slug
function getCategoryBySlug($conn, $slug) {
    $slug = $conn->real_escape_string($slug);
    $sql = "SELECT * FROM categories WHERE category_slug = '$slug'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Get category image or placeholder
function getCategoryImage($category) {
    if (!empty($category['category_image'])) {
        return $category['category_image'];
    }
    return 'assets/img/placeholder.jpg';
}
?>