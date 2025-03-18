<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
require_once("../config.php");

// Function to generate a slug from the product name
function generateSlug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
    return trim($slug, '-');
}

// Function to generate a unique slug
function generateUniqueSlug($slug, $conn, $currentId) {
    $originalSlug = $slug;
    $count = 1;
    while (true) {
        $sql = "SELECT COUNT(*) as count FROM products WHERE title_slug='$slug' AND id != $currentId";
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




$categoriesResult = $conn->query("SELECT id, name FROM categories");


$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId === 0) {
    die("Invalid product ID");
}


$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found");
}


$stmt = $conn->prepare("SELECT category_id FROM product_categories WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$categoryResult = $stmt->get_result();
$currentCategories = [];
while ($row = $categoryResult->fetch_assoc()) {
    $currentCategories[] = $row['category_id'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
    $keywords = $_POST['keywords'];
    $age_range = $_POST['age_range'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $slug = generateSlug($name);
    $title_slug = generateUniqueSlug($slug, $conn, $productId);
    
    
    $uploadDir = '../uploads/';
    $resizedDir = '../resized/';
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $imageUrls = [];
    $resizedImageUrl = $product['resized_image_url'];
    $imageFields = ['image_url_1', 'image_url_2', 'image_url_3', 'image_url_4'];

    foreach ($imageFields as $imageField) {
        if (isset($_FILES[$imageField]) && $_FILES[$imageField]['error'] == 0) {
            $imageTmpPath = $_FILES[$imageField]['tmp_name'];
            $imageType = mime_content_type($imageTmpPath);

            if (in_array($imageType, $allowedTypes)) {
               // $newFileName = uniqid() . '-' . basename($_FILES[$imageField]['name']);
               $fileExtension = pathinfo($_FILES[$imageField]['name'], PATHINFO_EXTENSION);
$newFileName = uniqid() . '.' . $fileExtension;
                $uploadFilePath = $uploadDir . $newFileName;

                move_uploaded_file($imageTmpPath, $uploadFilePath);
                
                if ($imageField == 'image_url_1') {
                    $resizedFilePath = $resizedDir . $newFileName;
                    if (!resizeAndCompressImage($uploadFilePath, $resizedFilePath, 480, 270, 75, $imageType)) {
                        die("Failed to resize and compress image.");
                    }
                   
                    $resizedImageUrl = substr($resizedFilePath, 3); 
                } else {
                   
                    $resizedFilePath = $resizedDir . $newFileName;
                    if (!resizeAndCompressImage($uploadFilePath, $resizedFilePath, 640, 360, 75, $imageType)) {
                        die("Failed to resize and compress image.");
                    }
                    $imageUrls[$imageField] = substr($resizedFilePath, 3); 
                }

                // Store original path without '../'
                $imageUrls[$imageField] = substr($uploadFilePath, 3); 
            }
        } else {
       
            $imageUrls[$imageField] = $product[$imageField];
        }
    }

 
$stmt = $conn->prepare("UPDATE products SET name = ?, title_slug = ?, description = ?, price = ?, keywords = ?, age_range = ?, featured = ?, image_url_1 = ?, image_url_2 = ?, image_url_3 = ?, image_url_4 = ?, resized_image_url = ? WHERE id = ?");

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param(
    "sssdssisssssi", 
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
    $resizedImageUrl,
    $productId
);

// Execute the statement
if ($stmt->execute()) {
   
    $conn->query("DELETE FROM product_categories WHERE product_id = $productId");
    foreach ($category_ids as $category_id) {
        $conn->query("INSERT INTO product_categories (product_id, category_id) VALUES ($productId, $category_id)");
    }

    echo "Product updated successfully";
    // Refresh the product data
    $result = $conn->query("SELECT * FROM products WHERE id = $productId");
    $product = $result->fetch_assoc();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
}

require_once("header.php");
?>
<style>
 
  .container-update{
    max-width:97%;
    margin: 0 auto;
  }

    h2 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #34495e;
    }

    input[type="text"],
    input[type="number"],
    textarea,
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    textarea {
        height: 120px;
        resize: vertical;
    }

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
    }

    .checkbox-item input[type="checkbox"] {
        margin-right: 5px;
    }

    .image-upload-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .image-upload {
        position: relative;
        height: 200px;
        border: 2px dashed #ddd;
        border-radius: 4px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }

    .image-upload input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .image-upload label {
        text-align: center;
        color: #888;
        pointer-events: none;
    }

    .image-preview {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .submit-btn {
        background-color: #3498db;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    .submit-btn:hover {
        background-color: #2980b9;
    }

    @media (max-width: 600px) {
        .container {
            padding: 20px;
        }

        .image-upload-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-update">
    <form action="" method="post" enctype="multipart/form-data">
        <h2>Update Product</h2>
        
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Categories:</label>
            <div class="checkbox-group">
                <?php
                if ($categoriesResult->num_rows > 0) {
                    while($category = $categoriesResult->fetch_assoc()) {
                        $checked = in_array($category['id'], $currentCategories) ? 'checked' : '';
                        echo "<div class='checkbox-item'>";
                        echo "<input type='checkbox' id='category_" . $category['id'] . "' name='category_ids[]' value='" . $category['id'] . "' $checked>";
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
            <label for="age_range">Age Range:</label>
            <select id="age_range" name="age_range">
                <option value="0-3" <?php echo $product['age_range'] == '0-3' ? 'selected' : ''; ?>>0-3</option>
                <option value="4-7" <?php echo $product['age_range'] == '4-7' ? 'selected' : ''; ?>>4-7</option>
                <option value="7-12" <?php echo $product['age_range'] == '7-12' ? 'selected' : ''; ?>>7-12</option>
                <option value="13+" <?php echo $product['age_range'] == '13+' ? 'selected' : ''; ?>>13+</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="keywords">Keywords:</label>
            <input type="text" id="keywords" name="keywords" value="<?php echo htmlspecialchars($product['keywords']); ?>">
        </div>
        
        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" id="featured" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                <label for="featured">Featured</label>
            </div>
        </div>
        
        <div class="image-upload-container">
            <?php
            for ($i = 1; $i <= 4; $i++) {
                $imageField = "image_url_$i";
                echo "<div class='image-upload'>";
                echo "<input type='file' id='$imageField' name='$imageField' accept='image/jpeg, image/jpg, image/png, image/gif, image/webp'>";
                echo "<label for='$imageField'>Choose Image $i</label>";
                if (!empty($product[$imageField])) {
                    echo "<img id='preview_$imageField' class='image-preview' src='../" . htmlspecialchars($product[$imageField]) . "' alt='Current Image $i'>";
                } else {
                    echo "<img id='preview_$imageField' class='image-preview' src='' alt='Image Preview' style='display:none;'>";
                }
                echo "</div>";
            }
            ?>
        </div>
        
        <input type="submit" value="Update Product" class="submit-btn">
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageFields = ['image_url_1', 'image_url_2', 'image_url_3', 'image_url_4'];
        imageFields.forEach(function(imageField) {
            const input = document.getElementById(imageField);
            const preview = document.getElementById('preview_' + imageField);
            const label = input.nextElementSibling;
            
            input.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        label.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                    label.style.display = 'block';
                }
            });
        });
    });
</script>