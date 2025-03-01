<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

require_once("../config.php");

// Fetch categories for checkboxes
$categoriesResult = $conn->query("SELECT id, name FROM categories");

// Function to generate a slug from the product name
function generateSlug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
    return trim($slug, '-');
}

// Function to generate a unique slug
function generateUniqueSlug($slug, $conn) {
    $originalSlug = $slug;
    $count = 1;
    while (true) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE title_slug='$slug'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if ($row['count'] == 0) {
            return $slug;
        } else {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
    }
}

// Function to resize and compress an image
function resizeAndCompressImage($source, $destination, $width, $height, $quality, $imageType) {
    list($origWidth, $origHeight) = getimagesize($source);
    $image_p = imagecreatetruecolor($width, $height);

    switch ($imageType) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

    switch ($imageType) {
        case 'image/jpeg':
        case 'image/jpg':
            imagejpeg($image_p, $destination, $quality);
            break;
        case 'image/png':
            imagepng($image_p, $destination);
            break;
        case 'image/gif':
            imagegif($image_p, $destination);
            break;
        case 'image/webp':
            imagewebp($image_p, $destination, $quality);
            break;
    }

    imagedestroy($image_p);
    imagedestroy($image);

    return true;
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
    $keywords = $_POST['keywords'];
    $age_range = $_POST['age_range'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $slug = generateSlug($name);
    $title_slug = generateUniqueSlug($slug, $conn);
    
    // Image upload and processing
    $uploadDir = '../uploads/';
    $resizedDir = '../resized/';
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $imageUrls = [];
    $resizedImageUrl = null;
    $imageFields = ['image_url_1', 'image_url_2', 'image_url_3', 'image_url_4'];
    $oneImageUploaded = false;

    foreach ($imageFields as $imageField) {
        if (isset($_FILES[$imageField]) && $_FILES[$imageField]['error'] == 0) {
            $oneImageUploaded = true;
            $imageTmpPath = $_FILES[$imageField]['tmp_name'];
            $imageType = mime_content_type($imageTmpPath);

            if (in_array($imageType, $allowedTypes)) {
                $newFileName = uniqid() . '-' . basename($_FILES[$imageField]['name']);
                $uploadFilePath = $uploadDir . $newFileName;

                move_uploaded_file($imageTmpPath, $uploadFilePath);
                
                if ($imageField == 'image_url_1') {
                    $resizedFilePath = $resizedDir . $newFileName;
                    if (!resizeAndCompressImage($uploadFilePath, $resizedFilePath, 480, 270, 75, $imageType)) {
                        $errorMessage = "Failed to resize and compress image.";
                        break;
                    }
                   
                    $resizedImageUrl = substr($resizedFilePath, 3); 
                } else {
                    // For other images, store resized path without '../'
                    $resizedFilePath = $resizedDir . $newFileName;
                    if (!resizeAndCompressImage($uploadFilePath, $resizedFilePath, 640, 360, 75, $imageType)) {
                        $errorMessage = "Failed to resize and compress image.";
                        break;
                    }
                    $imageUrls[$imageField] = substr($resizedFilePath, 3); 
                }

                // Store original path without '../'
                $imageUrls[$imageField] = substr($uploadFilePath, 3); 
            }
        }
    }

    if (!$oneImageUploaded) {
        $errorMessage = "At least one image must be uploaded.";
    } else if (empty($errorMessage)) {
        // Prepare SQL and bind parameters
        $stmt = $conn->prepare("INSERT INTO products (name, title_slug, description, price, keywords, age_range, featured, image_url_1, image_url_2, image_url_3, image_url_4, resized_image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssisssss", 
            $name, 
            $title_slug, 
            $description, 
            $price, 
            $keywords, 
            $age_range,
            $featured,
            $imageUrls['image_url_1'], 
            $imageUrls['image_url_2'], 
            $imageUrls['image_url_3'], 
            $imageUrls['image_url_4'], 
            $resizedImageUrl
        );

        // Execute the statement
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            
            // Insert product-category relationships
            foreach ($category_ids as $category_id) {
                $conn->query("INSERT INTO product_categories (product_id, category_id) VALUES ($product_id, $category_id)");
            }

            $successMessage = "Product '$name' added successfully!";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

require_once("header.php");
?>

    <style>
       
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
         
            color: #333;
        }
        
        form {
            max-width: 900px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
        }

       
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            animation: fadeIn 0.5s;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Form elements */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        /* Checkboxes */
        .category-checkboxes {
            margin-bottom: 25px;
        }

        .category-checkboxes p {
            margin-bottom: 12px;
            font-weight: 600;
            color: #555;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-item label {
            margin-bottom: 0;
            cursor: pointer;
        }

        /* Image upload */
        .image-upload-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .image-upload {
            position: relative;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .image-upload:hover {
            border-color: #4CAF50;
            background-color: rgba(76, 175, 80, 0.05);
        }

        .image-upload input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .image-upload label {
            text-align: center;
            color: #666;
            font-weight: normal;
            z-index: 1;
        }

        .image-upload i {
            font-size: 36px;
            color: #aaa;
            margin-bottom: 10px;
        }

        .image-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            display: none;
            z-index: 1;
        }

        
        .required::after {
            content: ' *';
            color: #e74c3c;
        }

        /* Submit button */
        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .btn {
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background-color: #e9e9e9;
        }

       
        @media (max-width: 768px) {
            form {
                padding: 20px;
                margin: 10px;
            }

            .image-upload-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .image-upload-container {
                grid-template-columns: 1fr;
            }
            
            .btn-container {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <div class="container">
        <form id="productForm" action="" method="post" enctype="multipart/form-data">
            <h2>Add New Product</h2>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name" class="required">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" placeholder="Enter product description..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="price" class="required">Price:</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="category-checkboxes">
                <p>Categories:</p>
                <div class="checkbox-group">
                    <?php
                    if ($categoriesResult->num_rows > 0) {
                        while($category = $categoriesResult->fetch_assoc()) {
                            echo "<div class='checkbox-item'>";
                            echo "<input type='checkbox' id='category_" . $category['id'] . "' name='category_ids[]' value='" . $category['id'] . "'>";
                            echo "<label for='category_" . $category['id'] . "'>" . $category['name'] . "</label>";
                            echo "</div>";
                        }
                    } else {
                        echo "No categories available";
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="age_range" class="required">Age Range:</label>
                <select id="age_range" name="age_range" required>
                    <option value="">Select age range</option>
                    <option value="0-3">0-3</option>
                    <option value="4-7">4-7</option>
                    <option value="7-12">7-12</option>
                    <option value="13+">13+</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="keywords">Keywords:</label>
                <input type="text" id="keywords" name="keywords" placeholder="Enter keywords separated by commas">
            </div>
            
            <div class="checkbox-item">
                <input type="checkbox" id="featured" name="featured" value="1">
                <label for="featured">Featured Product</label>
            </div>
            
            <div class="form-group">
                <label>Product Images:</label>
                <p class="required" style="font-size: 14px; margin-top: -5px; color: #666;">At least one image is required</p>
                <div class="image-upload-container">
                    <?php
                    for ($i = 1; $i <= 4; $i++) {
                        echo "<div class='image-upload'>";
                        echo "<input type='file' id='image_url_$i' name='image_url_$i' accept='image/jpeg, image/jpg, image/png, image/gif, image/webp'" . ($i == 1 ? " required" : "") . ">";
                        echo "<i class='fas fa-cloud-upload-alt'></i>";
                        echo "<label for='image_url_$i'>" . ($i == 1 ? "Main Image (Required)" : "Image $i") . "</label>";
                        echo "<img id='preview_image_url_$i' class='image-preview' src='' alt='Image Preview'>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="btn-container">
                <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('productForm');
            const resetBtn = document.getElementById('resetBtn');
            const successAlert = document.querySelector('.alert-success');
            const imageFields = ['image_url_1', 'image_url_2', 'image_url_3', 'image_url_4'];
            
            
            imageFields.forEach(function(imageField) {
                const input = document.getElementById(imageField);
                const preview = document.getElementById('preview_' + imageField);
                const label = input.nextElementSibling.nextElementSibling;
                const icon = input.nextElementSibling;
                
                input.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                            label.style.display = 'none';
                            icon.style.display = 'none';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = '';
                        preview.style.display = 'none';
                        label.style.display = 'block';
                        icon.style.display = 'block';
                    }
                });
            });
            
            
            resetBtn.addEventListener('click', function() {
                form.reset();
                
                
                imageFields.forEach(function(imageField) {
                    const preview = document.getElementById('preview_' + imageField);
                    const label = document.querySelector(`label[for='${imageField}']`);
                    const icon = preview.previousElementSibling.previousElementSibling;
                    
                    preview.src = '';
                    preview.style.display = 'none';
                    label.style.display = 'block';
                    icon.style.display = 'block';
                });
            });
            
            
            if (successAlert) {
                form.reset();
                
                
                imageFields.forEach(function(imageField) {
                    const preview = document.getElementById('preview_' + imageField);
                    const label = document.querySelector(`label[for='${imageField}']`);
                    const icon = preview.previousElementSibling.previousElementSibling;
                    
                    preview.src = '';
                    preview.style.display = 'none';
                    label.style.display = 'block';
                    icon.style.display = 'block';
                });
                
                
                setTimeout(function() {
                    successAlert.style.animation = 'fadeOut 0.5s forwards';
                    setTimeout(function() {
                        successAlert.style.display = 'none';
                    }, 500);
                }, 5000);
            }
            
            
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        });
    </script>


<?php

require_once("footer.php");
?>


