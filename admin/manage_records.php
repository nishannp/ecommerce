<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

require_once("../config.php");



function deleteProduct($conn, $productId) {
 
    $conn->begin_transaction();

    try {
       
        $stmt = $conn->prepare("DELETE FROM product_categories WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->close();

       
        $stmt = $conn->prepare("SELECT image_url_1, image_url_2, image_url_3, image_url_4, resized_image_url FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        
        $imagesToDelete = [$product['image_url_1'], $product['image_url_2'], $product['image_url_3'], $product['image_url_4'], $product['resized_image_url']];
        foreach ($imagesToDelete as $image) {
            if ($image && file_exists("../" . $image)) {
                unlink("../" . $image);
            }
        }

        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->close();

       
        $conn->commit();

        return true;
    } catch (Exception $e) {
        
        $conn->rollback();
        error_log("Error deleting product: " . $e->getMessage());
        return false;
    }
}


if (isset($_POST['delete'])) {
    $productId = $_POST['delete'];
    $result = deleteProduct($conn, $productId);
    if ($result) {
        echo "<script>alert('Product deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting product. Please try again.');</script>";
    }
}

require_once("header.php");

?>


    <style>
     
        h1 {
            text-align: center;
            color: #333;
        }
        #searchInput {
            width: 200px;
            height:20px;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        @media (max-width: 600px) {
            table, tr, td {
                display: block;
            }
            tr {
                margin-bottom: 10px;
            }
            td {
                border: none;
                position: relative;
                padding-left: 50%;
            }
            td:before {
                content: attr(data-label);
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
            }
        }
    </style>

    <div class="container-manage">
        <h1>Manage Products</h1>
        <input type="text" id="searchInput" placeholder="Search products...">
        <table id="productsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td data-label='Name'>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td data-label='Price'>$" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td data-label='Featured'>" . ($row['featured'] ? 'Yes' : 'No') . "</td>";
                    echo "<td data-label='Actions'>";
                    echo "<a href='update.php?id=" . $row['id'] . "' class='action-btn edit-btn'>Edit</a> ";
                    echo "<form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this product?\");'>";
                    echo "<button type='submit' name='delete' value='" . $row['id'] . "' class='action-btn delete-btn'>Delete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const table = document.getElementById('productsTable');
            const rows = table.getElementsByTagName('tr');

            searchInput.addEventListener('keyup', function() {
                const searchTerm = searchInput.value.toLowerCase();

                for (let i = 1; i < rows.length; i++) {
                    const nameCell = rows[i].getElementsByTagName('td')[0];
                    if (nameCell) {
                        const nameText = nameCell.textContent || nameCell.innerText;
                        if (nameText.toLowerCase().indexOf(searchTerm) > -1) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
            });
        });
    </script>
