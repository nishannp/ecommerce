<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

require_once("../config.php");

// Function to generate a slug from the category name
function generateSlug($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
    return trim($slug, '-');
}

// Function to generate a unique slug
function generateUniqueSlug($slug, $conn, $exclude_id = null) {
    $originalSlug = $slug;
    $count = 1;
    while (true) {
        $sql = "SELECT COUNT(*) as count FROM categories WHERE category_slug='$slug'";
        if ($exclude_id) {
            $sql .= " AND id != $exclude_id";
        }
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

    // Preserve transparency for PNG images
    if ($imageType == 'image/png') {
        imagealphablending($image_p, false);
        imagesavealpha($image_p, true);
        $transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
        imagefilledrectangle($image_p, 0, 0, $width, $height, $transparent);
    }

    switch ($imageType) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagealphablending($image, true);
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
            imagepng($image_p, $destination, 9);
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

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
   
    $stmt = $conn->prepare("SELECT category_image FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $imagePath = "../" . $row['category_image'];
        
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        
        
        $pathInfo = pathinfo($imagePath);
        $resizedPath = $pathInfo['dirname'] . '/resized-' . $pathInfo['basename'];
        if (file_exists($resizedPath)) {
            unlink($resizedPath);
        }
    }
    
   
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $delete_message = "Category deleted successfully";
    } else {
        $delete_error = "Error deleting category: " . $stmt->error;
    }
    $stmt->close();
}


$edit_mode = false;
$category_id = 0;
$category_name = "";
$current_image = "";

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $category_id = (int)$_GET['edit'];
    
    $stmt = $conn->prepare("SELECT id, name, category_image FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $category_name = $row['name'];
        $current_image = $row['category_image'];
    } else {
        $edit_error = "Category not found";
        $edit_mode = false;
    }
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $editing = isset($_POST['edit_id']) && $_POST['edit_id'] > 0;
    $category_id = $editing ? (int)$_POST['edit_id'] : 0;
    
   
    $uploadDir = '../categories/';
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $category_image = null;
    $image_updated = false;
    
   
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $imageTmpPath = $_FILES['category_image']['tmp_name'];
        $imageType = mime_content_type($imageTmpPath);
        
        if (in_array($imageType, $allowedTypes)) {
            $newFileName = uniqid() . '-' . basename($_FILES['category_image']['name']);
            $uploadFilePath = $uploadDir . $newFileName;
            
            move_uploaded_file($imageTmpPath, $uploadFilePath);
            
            $resizedFilePath = $uploadDir . 'resized-' . $newFileName;
            if (!resizeAndCompressImage($uploadFilePath, $resizedFilePath, 480, 270, 75, $imageType)) {
                $error_message = "Failed to resize and compress image.";
            }
            $category_image = substr($resizedFilePath, 3); // Remove '../' prefix
            $image_updated = true;
            
            
            if ($editing && !empty($_POST['current_image'])) {
                $oldImagePath = "../" . $_POST['current_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                
                
                $pathInfo = pathinfo($oldImagePath);
                $oldResizedPath = $pathInfo['dirname'] . '/resized-' . $pathInfo['basename'];
                if (file_exists($oldResizedPath)) {
                    unlink($oldResizedPath);
                }
            }
        } else {
            $error_message = "Invalid image type. Allowed types: JPEG, PNG, GIF, WebP.";
        }
    } else if ($_FILES['category_image']['error'] != 4) { 
        $error_message = "Image upload failed. Error code: " . $_FILES['category_image']['error'];
    } else if ($editing) {
        
        $category_image = $_POST['current_image'];
    } else {
        $error_message = "Please select an image for the category.";
    }
    
    if (!isset($error_message)) {
        if ($editing) {
            // Update existing category
            $slug = generateSlug($name);
            $category_slug = generateUniqueSlug($slug, $conn, $category_id);
            
            if ($image_updated) {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, category_image = ?, category_slug = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $category_image, $category_slug, $category_id);
            } else {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, category_slug = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $category_slug, $category_id);
            }
            
            if ($stmt->execute()) {
                $success_message = "Category updated successfully";
                
                $edit_mode = false;
                $category_name = "";
                $current_image = "";
                $category_id = 0;
            } else {
                $error_message = "Error updating category: " . $stmt->error;
            }
        } else {
            // Create new category
            $slug = generateSlug($name);
            $category_slug = generateUniqueSlug($slug, $conn);
            
            $stmt = $conn->prepare("INSERT INTO categories (name, category_image, category_slug) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $category_image, $category_slug);
            
            if ($stmt->execute()) {
                $success_message = "New category created successfully";
                
                $category_name = "";
            } else {
                $error_message = "Error creating category: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}


$categories = [];
$sql = "SELECT id, name, category_image FROM categories ORDER BY name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

require_once("header.php");
?>

<div class="content-container">
    <div class="content-header">
        <h1><?php echo $edit_mode ? 'Edit Category' : 'Category Management'; ?></h1>
    </div>
    
   
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($delete_message)): ?>
    <div class="alert alert-success">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <?php echo $delete_message; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($delete_error)): ?>
    <div class="alert alert-danger">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <?php echo $delete_error; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($edit_error)): ?>
    <div class="alert alert-danger">
        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
        <?php echo $edit_error; ?>
    </div>
    <?php endif; ?>
    
    <div class="card-container">
        
        <div class="card">
            <div class="card-header">
                <h2><?php echo $edit_mode ? 'Edit Category' : 'Add New Category'; ?></h2>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data" id="categoryForm">
                    <?php if ($edit_mode): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $category_id; ?>">
                    <input type="hidden" name="current_image" value="<?php echo $current_image; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Category Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category_name); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_image">Category Image:</label>
                        <div class="file-input-container">
                            <input type="file" id="category_image" name="category_image" accept="image/jpeg, image/jpg, image/png, image/gif, image/webp" <?php echo $edit_mode ? '' : 'required'; ?>>
                            <label for="category_image" class="file-input-label">Choose File</label>
                            <span class="file-name" id="file-name">No file chosen</span>
                        </div>
                    </div>
                    
                    <?php if ($edit_mode && !empty($current_image)): ?>
                    <div class="form-group">
                        <label>Current Image:</label>
                        <div class="current-image-container">
                            <img src="../<?php echo htmlspecialchars($current_image); ?>" alt="Current Image" class="current-image">
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <div class="image-preview-container">
                            <img class="image-preview" alt="Image Preview">
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <?php if ($edit_mode): ?>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                        <a href="add_category.php" class="btn btn-secondary">Cancel</a>
                        <?php else: ?>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                        <button type="reset" class="btn btn-secondary" id="resetForm">Clear Form</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Category List -->
        <div class="card">
            <div class="card-header">
                <h2>Existing Categories</h2>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                <p class="no-data">No categories found.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="img-cell">
                                    <img src="../<?php echo htmlspecialchars($category['category_image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td class="actions-cell">
                                    <a href="add_category.php?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $category['id']; ?>" data-name="<?php echo htmlspecialchars($category['name']); ?>" title="Delete"><i class="fas fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete the category "<span id="deleteCategoryName"></span>"?</p>
        <p class="warning">This action cannot be undone.</p>
        <div class="modal-buttons">
            <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
            <button class="btn btn-secondary" id="cancelDelete">Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  
    const input = document.getElementById('category_image');
    const preview = document.querySelector('.image-preview');
    const previewContainer = document.querySelector('.image-preview-container');
    const fileNameSpan = document.getElementById('file-name');
    
    input.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
            fileNameSpan.textContent = file.name;
        } else {
            preview.src = '';
            previewContainer.style.display = 'none';
            fileNameSpan.textContent = 'No file chosen';
        }
    });
    
    
    const resetButton = document.getElementById('resetForm');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            setTimeout(function() {
                preview.src = '';
                previewContainer.style.display = 'none';
                fileNameSpan.textContent = 'No file chosen';
            }, 100);
        });
    }
    

    const modal = document.getElementById('deleteModal');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const closeModalBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelDelete');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const deleteCategoryNameSpan = document.getElementById('deleteCategoryName');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            
            deleteCategoryNameSpan.textContent = categoryName;
            confirmDeleteBtn.href = 'add_category.php?delete=' + categoryId;
            modal.style.display = 'block';
        });
    });
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
    
   
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(function() {
            alert.style.display = 'none';
        }, 5000);
    });
});
</script>

<style>

.content-container {
    padding: 20px;
   
}

.content-header h1 {
    color: #333;
    margin-bottom: 20px;
    font-size: 28px;
}


.card-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
}

.card-header h2 {
    margin: 0;
    font-size: 20px;
    color: #333;
}

.card-body {
    padding: 20px;
}


.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}

.form-group input[type="text"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}


.file-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

input[type="file"] {
    position: absolute;
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    z-index: -1;
}

.file-input-label {
    display: inline-block;
    padding: 8px 16px;
    background-color: #4a6fdc;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 10px;
}

.file-input-label:hover {
    background-color: #3b5fcb;
}

.file-name {
    color: #555;
    font-style: italic;
}


.image-preview-container {
    display: none;
    margin-top: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    text-align: center;
}

.image-preview {
    max-width: 100%;
    max-height: 200px;
    border-radius: 4px;
}

.current-image-container {
    margin-top: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    text-align: center;
}

.current-image {
    max-width: 100%;
    max-height: 150px;
    border-radius: 4px;
}


.btn {
    display: inline-block;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: background-color 0.3s, transform 0.1s;
}

.btn:active {
    transform: translateY(1px);
}

.btn-primary {
    background-color: #4a6fdc;
    color: white;
}

.btn-primary:hover {
    background-color: #3b5fcb;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 14px;
}


.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.data-table th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
}

.data-table tr:hover {
    background-color: #f8f9fa;
}

.img-cell {
    width: 80px;
}

.thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.actions-cell {
    width: 180px;
    text-align: center;
}

.no-data {
    text-align: center;
    color: #6c757d;
    font-style: italic;
}


.alert {
    padding: 12px 20px;
    margin-bottom: 20px;
    border-radius: 4px;
    position: relative;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.closebtn {
    position: absolute;
    top: 10px;
    right: 20px;
    color: inherit;
    font-weight: bold;
    cursor: pointer;
}


.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    max-width: 90%;
    position: relative;
}

.close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 24px;
    cursor: pointer;
}

.modal h2 {
    margin-top: 0;
    color: #333;
}

.warning {
    color: #dc3545;
    font-weight: 500;
}

.modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}


@media (max-width: 992px) {
    .content-container {
        margin-left: 0;
    }
    
    .card-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .actions-cell {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .data-table th,
    .data-table td {
        padding: 10px;
    }
}

@media (max-width: 576px) {
    .form-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}


body {
    background-color: #f5f8fa;
}
</style>