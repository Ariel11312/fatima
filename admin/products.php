<?php
if(isset($_POST['add-btn'])) {
    include 'database/connection.php';

    // Get the form data
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $sku = $_POST['sku'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Handle file upload for item image
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["item_image"]["name"]);
        move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file);
        $image_path = $target_file;
    } else {
        $image_path = null; // No image uploaded
    }

    // Insert the new item into the database
    $query = "INSERT INTO `$category` (name, category, subCategory, sku, quantity, price, status, image) VALUES ('$item_name', '$category', '$subcategory', '$sku', '$quantity', '$price', '$status', '$image_path')";
    
    if (mysqli_query($connection, $query)) {
        // Set a success flag for the modal
        $success = true;
    } else {
        $error_message = mysqli_error($connection);
    }
    
    mysqli_close($connection);
}
session_start();
echo $_SESSION['isAdmin'];
if( $_SESSION['isAdmin'] != "true"){
    header("Location: http://localhost/fatima/");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inventory Management - FATIMA HOME WORLD CENTER</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="./products.css">
<?php include("sidebar.php")?>
</head>
<!-- Add New Item Modal -->
<div id="addItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Inventory Item</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addItemForm " type="submit" method="POST" name="addItemForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="item-name">Item Name*</label>
                    <input type="text" id="item-name" name="item_name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-category">Category*</label>
                        <select id="item-category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="bedroom">Bedroom</option>
                            <option value="livingroom">Living Room</option>
                            <option value="diningroom">Dining Room</option>
                            <option value="office">Home & Office</option>
                            <option value="kitchen">Kitchen</option>
                            <option value="outdoor">Outdoor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="item-subcategory">Subcategory*</label>
                        <input type="text" id="item-subcategory" name="subcategory" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-sku">SKU*</label>
                        <input type="text" id="item-sku" name="sku" required>
                    </div>
                    <div class="form-group">
                        <label for="item-quantity">Quantity*</label>
                        <input type="number" id="item-quantity" name="quantity" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item-price">Price (PHP)*</label>
                        <input type="number" id="item-price" name="price" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="item-status">Status*</label>
                        <select id="item-status" name="status" required>
                            <option value="in-stock">In Stock</option>
                            <option value="low-stock">Low Stock</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="item-image">Item Image</label>
                    <input type="file" id="item-image" name="item_image" accept="image/*">
                    <small>Recommended size: 500x500px, Max size: 2MB</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelAddItem">Cancel</button>
                    <button type="submit" name="add-btn" class="submit-btn">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- View Item Modal -->
<div id="viewItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Item Details</h2>
            <span class="close-modal close-view-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="item-detail-container">
                <div class="item-image">
                    <img id="view-item-image" src="" alt="Item Image">
                </div>
                <div class="item-details">
                    <div class="detail-row">
                        <div class="detail-label">Item ID:</div>
                        <div id="view-item-id" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Name:</div>
                        <div id="view-item-name" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Category:</div>
                        <div id="view-item-category" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Subcategory:</div>
                        <div id="view-item-subcategory" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">SKU:</div>
                        <div id="view-item-sku" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Quantity:</div>
                        <div id="view-item-quantity" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Price:</div>
                        <div id="view-item-price" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div id="view-item-status" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Last Updated:</div>
                        <div id="view-item-lastUpdated" class="detail-value"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Description:</div>
                        <div id="view-item-description" class="detail-value"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button id="closeViewItem" class="btn cancel-btn">Close</button>
            <button id="editFromViewBtn" class="btn primary-btn">Edit Item</button>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="editItemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Item</h2>
            <span class="close-modal close-edit-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editItemForm">
                <input type="hidden" id="edit-item-id">
                
                <div class="form-group">
                    <label for="edit-item-name">Item Name</label>
                    <input type="text" id="edit-item-name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-item-category">Category</label>
                        <select id="edit-item-category" required>
                            <option value="">Select Category</option>
                            <option value="bedroom">Bedroom</option>
                            <option value="livingroom">Living Room</option>
                            <option value="diningroom">Dining Room</option>
                            <option value="office">Office</option>
                            <option value="kitchen">Kitchen</option>
                            <option value="outdoor">Outdoor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-item-subcategory">Subcategory</label>
                        <input type="text" id="edit-item-subcategory" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-item-sku">SKU</label>
                        <input type="text" id="edit-item-sku" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-item-quantity">Quantity</label>
                        <input type="number" id="edit-item-quantity" min="0" readonly>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-item-price">Price (PHP)</label>
                        <input type="number" id="edit-item-price" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-item-status">Status</label>
                        <select id="edit-item-status" required>
                            <option value="in-stock">In Stock</option>
                            <option value="low-stock">Low Stock</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-item-description">Description</label>
                    <textarea id="edit-item-description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit-item-image">Item Image</label>
                    <div class="image-preview-container">
                        <img id="edit-image-preview" src="" alt="Item Preview">
                    </div>
                    <input type="file" id="edit-item-image" accept="image/*">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button id="cancelEditItem" class="btn cancel-btn">Cancel</button>
            <button id="saveEditItem" class="btn primary-btn">Save Changes</button>
        </div>
    </div>
</div>

<!-- Delete Item Modal -->
<div id="deleteItemModal" class="modal">
    <div class="modal-content delete-modal">
        <div class="modal-header">
            <h2>Delete Item</h2>
            <span class="close-modal close-delete-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="delete-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <p>Are you sure you want to delete this item?</p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="delete-item-details">
                <p><strong>Item ID:</strong> <span id="delete-item-id"></span></p>
                <p><strong>Item Category:</strong> <span id="delete-item-category"></span></p>
                <p><strong>Name:</strong> <span id="delete-item-name"></span></p>
                <p><strong>SKU:</strong> <span id="delete-item-sku"></span></p>
            </div>
        </div>
        <div class="modal-footer">
            <button id="cancelDeleteItem" class="btn cancel-btn">Cancel</button>
            <button id="confirmDeleteItem" class="btn danger-btn">Delete Item</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="success-modal">
    <div class="success-modal-content">
        <div class="checkmark-circle">
            <div class="background"></div>
            <div class="checkmark draw"></div>
        </div>
        <p>Successfully added</p>
        <button id="closeSuccessModal">Close</button>
    </div>
</div>
<!-- Adjust Quantity Modal -->
<div id="adjustQuantityModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Adjust Quantity</h2>
            <span class="close-modal close-adjust-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="adjustQuantityForm">    
                <div class="form-group">
                    <label for="adjust-item-id">SKU</label>
                    <input type="text" id="adjust-item-id" readonly>
                </div>
                <div class="form-group">
                    <label for="adjust-item-name">Item Name</label>
                    <input type="text" id="adjust-item-name" readonly>
                </div>
                <div class="form-group">
                    <label for="adjust-item-category">Category</label>
                    <input type="text" id="adjust-item-category" readonly>
                </div>
                <div class="form-group">
                    <label for="current-quantity">Current Quantity</label>
                    <input type="number" id="current-quantity" readonly>
                </div>
                
                <div class="form-group">
                    <label for="adjustment-type">Adjustment Type</label>
                    <select id="adjustment-type" required>
                        <option value="add">Add to Stock</option>
                        <option value="subtract">Remove from Stock</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adjustment-item">Quantity</label>
                <input type="text" id="adjustment-item" placeholder="Enter quantity to add or remove" required> 
                </div>
                                
                <div class="form-group">
                    <label for="adjustment-reason">Reason (Optional)</label>
                    <input type="text" id="adjustment-reason" placeholder="e.g. Restock, Sale, etc.">
                </div>
                
                <div class="form-group">
                    <label for="new-quantity">New Quantity</label>
                    <input type="number" id="new-quantity" readonly>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button id="cancelAdjustQuantity" class="btn cancel-btn">Cancel</button>
            <button id="confirmAdjustQuantity" class="btn primary-btn">Save Adjustment</button>
        </div>
    </div>
</div>
<body>
    <!-- Navbar would be included here -->

    
    <div class="container">
        <div class="header">
            <h1>Inventory Management</h1>
            <button class="add-item-btn">+ Add New Item</button>
        </div>
        
        <div class="stats-container">
            <div class="stat-card primary">
                <h3>Total Items</h3>
                <p id="totalItem">0</p>
            </div>
            <div class="stat-card success">
                <h3>In Stock</h3>
                <p id="instock">0</p>
            </div>
            <div class="stat-card warning">
                <h3>Low Stock</h3>
                <p id="lowstock">0</p>
            </div>
            <div class="stat-card danger">
                <h3>Out of Stock</h3>
                <p id="outstock">0</p>
            </div >
        </div>
        
        <div class="controls">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Search by ID, name, or SKU...">
            </div>
            <div class="filter-box">
                <select id="category-filter">
                    <option value="">All Categories</option>
                    <option value="bedroom">Bedroom</option>
                    <option value="livingroom">Living Room</option>
                    <option value="diningroom">Dining Room</option>
                    <option value="office">Home & Office</option>
               
                </select>
                <select id="status-filter">
                    <option value="">All Status</option>
                    <option value="in-stock">In Stock</option>
                    <option value="low-stock">Low Stock</option>
                    <option value="out-of-stock">Out of Stock</option>
                </select>
                <select id="sort-filter">
                    <option value="default">Sort By</option>
                    <option value="name-asc">Name: A to Z</option>
                    <option value="name-desc">Name: Z to A</option>
                    <option value="quantity-low">Quantity: Low to High</option>
                    <option value="quantity-high">Quantity: High to Low</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                </select>
            </div>
        </div>
        
        <table class="inventory-table" id="inventory-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Item ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inventory-body">
                <!-- Table rows will be dynamically loaded here -->
            </tbody>
        </table>
        
        <div class="pagination" id="pagination">
            <!-- Pagination buttons will be dynamically loaded here -->
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <script src="./products.js"></script>
    
    <?php if(isset($success) && $success): ?>
    <script>
        // Show the success modal
        document.getElementById('successModal').style.display = 'block';
        
        // Close the success modal when the close button is clicked
        document.getElementById('closeSuccessModal').onclick = function() {
            document.getElementById('successModal').style.display = 'none';
        }
        
        // Auto-close the success modal after 3 seconds
        setTimeout(function() {
            document.getElementById('successModal').style.display = 'none';
        }, 3000);
    </script>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
    <script>
        alert("Error: <?php echo $error_message; ?>");
    </script>
    <?php endif; ?>
</body>
</html>