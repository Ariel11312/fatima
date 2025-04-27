<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include './admin/database/connection.php';

// Initialize shopping cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle logout
if (isset($_POST['logoutbtn'])) {
    unset($_SESSION['firstname']);
    unset($_SESSION['email']);
    unset($_SESSION['user_id']);
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get product details from URL parameters with sanitization
$category = isset($_GET["category"]) ? mysqli_real_escape_string($connection, $_GET["category"]) : '';
$subCategory = isset($_GET["subCategory"]) ? mysqli_real_escape_string($connection, $_GET["subCategory"]) : '';
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

// Function to get product by ID
function getProductById($connection, $id, $category) {
    $id = (int)$id;
    $category = mysqli_real_escape_string($connection, $category);
    
    $sql = "SELECT * FROM `$category` WHERE id = $id";
    $query = mysqli_query($connection, $sql);
    
    if (!$query) {
        error_log("Database error: " . mysqli_error($connection));
        return false;
    }
    
    return mysqli_fetch_assoc($query);
}

// Fetch product data if ID and category are provided
$product = null;
if ($id > 0 && !empty($category)) {
    $product = getProductById($connection, $id, $category);
    
    if ($product) {
        $product['image'] = "./admin/" . $product['image'];
    }
}

// Handle add to cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be at least 1";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Get product details
    $product = getProductById($connection, $product_id, $_POST['category']);
    
    if ($product) {
        // Prepare product data for cart
        $cart_item = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'quantity' => $quantity,
            'image' => "./admin/" . $product['image'],
            'category' => $product['category'],
            'subcategory' => $product['subCategory']
        ];
        
        // Check if product already exists in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // Add new item if not found
        if (!$found) {
            $_SESSION['cart'][] = $cart_item;
        }
        
        $_SESSION['success'] = "Product added to cart!";
    } else {
        $_SESSION['error'] = "Product not found!";
    }
    
    // Redirect to prevent form resubmission
    $redirect_url = $_SERVER['PHP_SELF'];
    $params = [];
    
    if (!empty($category)) $params[] = "category=" . urlencode($category);
    if (!empty($subCategory)) $params[] = "subCategory=" . urlencode($subCategory);
    if (!empty($id)) $params[] = "id=" . urlencode($id);
    
    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }
    
    header('Location: ' . $redirect_url);
    exit;
}

// Handle cart updates (increase, decrease, remove, clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'increase':
            if (isset($_POST['item_id'])) {
                $item_id = (int)$_POST['item_id'];
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $item_id) {
                        $item['quantity']++;
                        break;
                    }
                }
            }
            break;
            
        case 'decrease':
            if (isset($_POST['item_id'])) {
                $item_id = (int)$_POST['item_id'];
                foreach ($_SESSION['cart'] as $key => &$item) {
                    if ($item['id'] == $item_id) {
                        $item['quantity']--;
                        if ($item['quantity'] <= 0) {
                            unset($_SESSION['cart'][$key]);
                            $_SESSION['cart'] = array_values($_SESSION['cart']);
                        }
                        break;
                    }
                }
            }
            break;
            
        case 'remove':
            if (isset($_POST['item_id'])) {
                $item_id = (int)$_POST['item_id'];
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['id'] == $item_id) {
                        unset($_SESSION['cart'][$key]);
                        $_SESSION['cart'] = array_values($_SESSION['cart']);
                        break;
                    }
                }
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            break;
    }
    
    // Preserve URL parameters when redirecting
    $params = [];
    if (!empty($category)) $params[] = "category=" . urlencode($category);
    if (!empty($subCategory)) $params[] = "subCategory=" . urlencode($subCategory);
    if (!empty($id)) $params[] = "id=" . urlencode($id);
    
    $query_string = !empty($params) ? '?' . implode('&', $params) : '';
    
    header('Location: ' . $_SERVER['PHP_SELF'] . $query_string);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FATIMA HOME WORLD CENTER.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        /* Top Bar */
        .top-bar {
            background-color: #014a23;
            color: white;
            padding: 6px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .social-icons a {
            color: white;
            margin-right: 12px;
            text-decoration: none;
        }
        
        .contact-info {
            display: flex;
            gap: 20px;
        }
        
        .contact-info a {
            color: white;
            text-decoration: none;
        }
        
        /* Header */
        header {
            background-color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #000;
        }
        
        .logo img {
            height: 50px;
            margin-right: 10px;
        }
        
        .logo h1 {
            font-size: 28px;
            font-weight: 700;
        }
        
        .search-box {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        .user-actions a {
            margin-left: 15px;
            color: #333;
            text-decoration: none;
        }
        
        /* Navigation */
        nav {
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }
        
        .main-menu {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
        }
        
        .main-menu a {
            display: inline-block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .main-menu a:hover {
            color: #77b81e;
        }
        
        /* Category Header */
        .category-header {
            max-width: 1200px;
            margin: 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-title {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .filter-button {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        
        .pagination-info {
            font-size: 14px;
        }
        
        .sort-dropdown {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        /* Product Grid */
        .product-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            padding: 20px 0;
        }
        
        .product-card {
            background-color: white;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 25px;
            height: 25px;
        }
        
        .quick-view {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #77b81e;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s;
            text-decoration: none;
        }
        
        .product-card:hover .quick-view {
            opacity: 1;
        }
        
        .product-image {
            width: 100%;
            height: 180px;
            object-fit: contain;
            padding: 10px;
        }
        
        .product-info {
            padding: 10px;
        }
        
        .product-category {
            font-size: 11px;
            color: #777;
            text-transform: uppercase;
        }
        
        .product-name {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
            color: #333;
        }
        
        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 40px 0;
            margin-top: 40px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        
        .footer-column h3 {
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column a {
            color: #ccc;
            text-decoration: none;
        }
        
        .footer-column a:hover {
            color: white;
        }
        
        .copyright {
            max-width: 1200px;
            margin: 20px auto 0;
            padding-top: 20px;
            border-top: 1px solid #444;
            text-align: center;
            font-size: 14px;
            color: #aaa;
        }
        /* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1000; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fff;
    margin: 5% auto; /* 5% from the top and centered */
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    width: 80%; /* Could be more or less, depending on screen size */
    max-width: 500px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    position: relative;
}

/* The Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    right: 15px;
    top: 10px;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Cart Toggle Button */

.cart-toggle-btn .cart-count {
    background-color: #ff6b6b;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    margin-left: 5px;
}
.cart-toggle-btn{
    background-color: #fff;
    border: none;
    cursor: pointer;
    
}

/* Cart Styles */
.cart-summary {
    width: 100%;
}

.cart-summary h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.cart-items {
    max-height: 300px;
    overflow-y: auto;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.cart-item-info {
    display: flex;
    align-items: center;
}

.cart-item-image {
    width: 60px;
    height: 60px;
    margin-right: 10px;
    overflow: hidden;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.quantity-controls {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.quantity-form {
    display: flex;
    align-items: center;
    margin-right: 10px;
}

.quantity-btn {
    width: 25px;
    height: 25px;
    border: 1px solid #ddd;
    background: white;
    cursor: pointer;
}

.item-quantity {
    margin: 0 5px;
    width: 20px;
    text-align: center;
}

.remove-btn {
    background: none;
    border: none;
    color: #ff6b6b;
    cursor: pointer;
    font-size: 12px;
}

.cart-total {
    padding: 15px 0;
    text-align: right;
    font-size: 18px;
    font-weight: bold;
}

.cart-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}

.clear-cart-btn {
    padding: 8px 15px;
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
}

.checkout-btn {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
}
.actionsBtn{
    display: none;
    position: fixed;
    justify-content: center;
    align-items: center;
    margin-left: 30px;
    margin-top: 5px;
}
.actionsBtn button{
display: flex;
border: none;
cursor: pointer;
}
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
        <div class="contact-info">
            <a href="mailto:example@example.com"><i class="far fa-envelope"></i> example@example.com</a>
            <a href="tel:09991234567"><i class="fas fa-phone"></i> 0999 123 4567</a>
        </div>
    </div>
    
    <!-- Header -->
    <br><br><br>
    <header>
        <div class="header-content" style="position:fix; z-index: 1;">
        <div class="search">
    <div class="search-container">
        <input type="text" id="search-input" class="search-box" placeholder="Search products...">
        <button class="search-button">
            <i class="fas fa-search"></i>
        </button>
    </div>
</div>
<?php

if (isset($_SESSION["firstname"])) {

?>
            <div class="user-actions">
                <a href="#" class="icon-button"><i class="far fa-user-circle"></i> <?php echo $_SESSION['firstname']?></a>
                <button id="cart-toggle" class="cart-toggle-btn">
    <i class="fa fa-shopping-cart"></i> 
    <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
</button>
<form class="actionsBtn" method="post">
                        <button type="submit" name="logoutbtn">Logout</button>
                    </form>
            </div>
            <?php } else{?>
                <div class="user-actions">
                    <a href="#" class="icon-button"><i class="far fa-user-circle"></i></a>
                    <button id="cart-toggle" class="cart-toggle-btn">
    <i class="fa fa-shopping-cart"></i> 
    <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
</button>
<form class="actionsBtn" action="login.php">
                        <button type="submit" name="logoutbtn">Login</button>
                    </form>
                </div>

                <?php }?>

        </div>
    </header>
    <br>
    <!-- Navigation -->
    <?php include './nav.php'; ?>
    
    <!-- Category Header -->
    <div class="category-header">
        <div class="left-section">
            <h2 class="category-title"><?php echo $category?></h2>
            <button class="filter-button" style="display: none;">
                <i class="fas fa-sliders-h"></i> FILTER
            </button>
        </div>
        <div class="right-section">
            <span class="pagination-info">Showing 1–15 of 329 results</span>
            <select class="sort-dropdown">
                <option>Sort by latest</option>
                <option>Sort by popularity</option>
                <option>Sort by price: low to high</option>
                <option>Sort by price: high to low</option>
            </select>
        </div>
    </div>
    
    <!-- Product Grid -->
    <div class="product-grid">
        <!-- Product 1 -->
        <?php 
        include './admin/database/connection.php'; // Include the database connection file
        error_reporting(0);
        $category = $_GET['category'];
        $subCategory = $_GET['subCategory'];
        if (isset($subCategory)) {
            $sql = "SELECT * FROM $category WHERE subCategory = '$subCategory'";
            
        }else{
            $sql = "SELECT * FROM $category";
        }
$query = mysqli_query($connection, $sql);
while ($row = mysqli_fetch_array($query)) {
    $price = isset($row["price"]) ? $row["price"] : 0;
    $dateAdded = isset($row["date_added"]) ? $row["date_added"] : date("Y-m-d"); // Adjust based on your table column
?>
<div class="product-card" data-price="<?php echo $price; ?>" data-date="<?php echo $dateAdded; ?>">
    <img src="./images/green-leaf.png" alt="Eco Friendly" class="product-badge">
    <img src="<?php echo "./admin/" . $row["image"]; ?>" alt="<?php echo $row["subCategory"]; ?>" class="product-image">
    <a href="http://localhost/fatima/item.php?id=<?php echo $row["id"] ?>&category=<?php echo $row["category"]?>&subCategory=<?php echo $row["subCategory"] ?>" class="quick-view">QUICK VIEW</a>
    <div class="product-info">
        <div class="product-category"><?php echo $row["name"]; ?></div> <!-- name is category -->
        <h3 class="product-name"><?php echo $row["subCategory"]; ?></h3> <!-- subCategory is name -->
        <?php if (isset($row["price"])) { ?>
        <div class="product-price">₱<?php echo number_format($row["price"]); ?></div>
        <?php } ?>
    </div>
</div>
<?php } ?>
        <!-- Product 2 -->

    </div>
    
    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Shipping Information</a></li>
                    <li><a href="#">Returns & Exchanges</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Our Company</h3>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Categories</h3>
                <ul>
                    <li><a href="#">Living Room</a></li>
                    <li><a href="#">Bedroom</a></li>
                    <li><a href="#">Dining Room</a></li>
                    <li><a href="#">Home Office</a></li>
                    <li><a href="#">Outdoor</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Stay Connected</h3>
                <ul>
                    <li><a href="#">Sign up for our newsletter</a></li>
                    <li><a href="#"><i class="fab fa-facebook-f"></i> Facebook</a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                    <li><a href="#"><i class="fab fa-pinterest"></i> Pinterest</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            &copy; 2025 FATIMA HOME WORLD CENTER. All Rights Reserved.
        </div>
    </footer>
    <div id="cartModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        
        <div class="cart-summary">
            <h2>Your Cart</h2>
            
            <?php if (empty($_SESSION['cart'])): ?>
                <p>Your cart is empty.</p>
            <?php else: ?>
                <div class="cart-items">
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $item):
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div>
                                <div><?php echo htmlspecialchars($item['name']); ?></div>
                                <div>₱<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                        <div class="cart-item-actions">
                            <div class="quantity-controls">
                                <div class="quantity-form">
                                    <form method="post" action="" style="display: inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="decrease">
                                        <button type="submit" class="quantity-btn minus-btn">-</button>
                                    </form>
                                    
                                    <span class="item-quantity"><?php echo $item['quantity']; ?></span>
                                    
                                    <form method="post" action="" style="display: inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="increase">
                                        <button type="submit" class="quantity-btn plus-btn">+</button>
                                    </form>
                                </div>
                                
                                <form method="post" action="" class="remove-form">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="remove-btn">Remove</button>
                                </form>
                            </div>
                            <div class="item-total">
                                <strong>₱<?php echo number_format($item_total, 2); ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-total">
                    Total: ₱<?php echo number_format($total, 2); ?>
                </div>
                
                <div class="cart-actions">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="clear-cart-btn">Clear Cart</button>
                    </form>
                    <?php if(!$_SESSION["firstname"]){?>
                    <a class="checkout-btn" href="login.php">Proceed to Checkout</a>
                    <?php }else{?>
                        <a class="checkout-btn" href="checkout.php">Proceed to Checkout</a>
                        
                <?php }?>
                
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    <script>
        // Product Search, Sort and Filter Functions

document.addEventListener('DOMContentLoaded', function() {
    // Data structure to store all products (would be populated from PHP)
    let allProducts = [];

initializeProducts();
setupEventListeners();

function initializeProducts() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const category = card.querySelector('.product-category').textContent;
        const name = card.querySelector('.product-name').textContent;
        const imageSrc = card.querySelector('.product-image').src;
        
        const price = card.getAttribute('data-price') || '0';
        const dateAdded = card.getAttribute('data-date') || new Date().toISOString();
        
        allProducts.push({
            element: card,
            category: category,
            name: name,
            image: imageSrc,
            price: parseFloat(price),
            dateAdded: new Date(dateAdded)
        });
    });
}
    /**
     * Set up event listeners for search, sort, and filter
     */
    function setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                searchProducts(searchTerm);
            });
        }
        
        // Sort dropdown
        const sortDropdown = document.querySelector('.sort-dropdown');
        if (sortDropdown) {
            sortDropdown.addEventListener('change', function() {
                sortProducts(this.value);
            });
        }
        
        // Filter button (toggles filter panel)
        const filterButton = document.querySelector('.filter-button');
        if (filterButton) {
            filterButton.addEventListener('click', toggleFilterPanel);
        }
        
        // Add filter panel event listeners
        setupFilterPanelListeners();
    }
    
    /**
     * Search products based on a search term
     */
    function searchProducts(searchTerm) {
        if (!searchTerm) {
            // If search term is empty, show all products
            allProducts.forEach(product => {
                product.element.style.display = '';
            });
            updatePaginationInfo();
            return;
        }
        
        // Filter products by search term
        const filteredProducts = allProducts.filter(product => {
            return product.name.toLowerCase().includes(searchTerm) || 
                   product.category.toLowerCase().includes(searchTerm);
        });
        
        // Hide all products first
        allProducts.forEach(product => {
            product.element.style.display = 'none';
        });
        
        // Show matching products
        filteredProducts.forEach(product => {
            product.element.style.display = '';
        });
        
        updatePaginationInfo(filteredProducts.length);
    }
    
    /**
     * Sort products based on the selected option
     */
    function sortProducts(sortOption) {
        let sortedProducts = [...allProducts];
        
        switch(sortOption) {
            case 'Sort by latest':
                sortedProducts.sort((a, b) => b.dateAdded - a.dateAdded);
                break;
            case 'Sort by price: low to high':
                sortedProducts.sort((a, b) => a.price - b.price);
                break;
            case 'Sort by price: high to low':
                sortedProducts.sort((a, b) => b.price - a.price);
                break;
            case 'Sort by popularity':
                // Sorting by popularity would require a popularity metric
                // For now, we'll just leave the default order
                break;
            default:
                // Default case, no sorting applied
                break;
        }
        
        // Rearrange products in the DOM based on sorting
        const productGrid = document.querySelector('.product-grid');
        sortedProducts.forEach(product => {
            productGrid.appendChild(product.element);
        });
    }
    
    /**
     * Toggle filter panel visibility
     */
    function toggleFilterPanel() {
        let filterPanel = document.getElementById('filter-panel');
        
        if (!filterPanel) {
            // Create the filter panel if it doesn't exist
            filterPanel = createFilterPanel();
            document.querySelector('.category-header').after(filterPanel);
        }
        
        // Toggle visibility
        if (filterPanel.style.display === 'none' || filterPanel.style.display === '') {
            filterPanel.style.display = 'block';
        } else {
            filterPanel.style.display = 'none';
        }
    }
    
    /**
     * Create the filter panel HTML
     */
    function createFilterPanel() {
        const panel = document.createElement('div');
        panel.id = 'filter-panel';
        panel.className = 'filter-panel';
        
        // Extract unique categories from products
        const categories = [...new Set(allProducts.map(p => p.category))];
        
        // Create filter panel content
        panel.innerHTML = `
            <div class="filter-content">
                <div class="filter-section">
                    <h3>Categories</h3>
                    <div class="filter-options">
                        <label>
                            <input type="checkbox" class="category-filter" value="all" checked> All
                        </label>
                        ${categories.map(category => `
                            <label>
                                <input type="checkbox" class="category-filter" value="${category}"> ${category}
                            </label>
                        `).join('')}
                    </div>
                </div>
                
                <div class="filter-section">
                    <h3>Price Range</h3>
                    <div class="price-range">
                        <input type="range" id="price-slider" min="0" max="100000" step="1000">
                        <div class="price-values">
                            <span id="min-price">₱0</span>
                            <span id="max-price">₱100,000</span>
                        </div>
                    </div>
                </div>
                
                <div class="filter-buttons">
                    <button id="apply-filters" class="apply-filters">Apply Filters</button>
                    <button id="reset-filters" class="reset-filters">Reset</button>
                </div>
            </div>
        `;
        
        // Add styles to the filter panel
        panel.style.display = 'none';
        panel.style.backgroundColor = 'white';
        panel.style.padding = '20px';
        panel.style.marginBottom = '20px';
        panel.style.borderRadius = '4px';
        panel.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
        
        return panel;
    }
    
    /**
     * Set up event listeners for filter panel
     */
    function setupFilterPanelListeners() {
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'apply-filters') {
                applyFilters();
            }
            
            if (e.target && e.target.id === 'reset-filters') {
                resetFilters();
            }
        });
        
        // Listen for price slider changes
        document.addEventListener('input', function(e) {
            if (e.target && e.target.id === 'price-slider') {
                updatePriceDisplay(e.target.value);
            }
        });
    }
    
    /**
     * Apply selected filters
     */
    function applyFilters() {
        // Get selected categories
        const selectedCategories = [];
        const categoryCheckboxes = document.querySelectorAll('.category-filter:checked');
        
        categoryCheckboxes.forEach(checkbox => {
            if (checkbox.value !== 'all') {
                selectedCategories.push(checkbox.value);
            }
        });
        
        // Get price range
        const priceSlider = document.getElementById('price-slider');
        const maxPrice = priceSlider ? parseInt(priceSlider.value) : 100000;
        
        // Apply filters
        allProducts.forEach(product => {
            const matchesCategory = selectedCategories.length === 0 || 
                                   (document.querySelector('.category-filter[value="all"]:checked')) ||
                                   selectedCategories.includes(product.category);
            
            const matchesPrice = product.price <= maxPrice;
            
            product.element.style.display = (matchesCategory && matchesPrice) ? '' : 'none';
        });
        
        // Update pagination info
        const visibleCount = allProducts.filter(p => p.element.style.display !== 'none').length;
        updatePaginationInfo(visibleCount);
    }
    
    /**
     * Reset all filters
     */
    function resetFilters() {
        // Reset category checkboxes
        const allCheckbox = document.querySelector('.category-filter[value="all"]');
        if (allCheckbox) {
            allCheckbox.checked = true;
        }
        
        document.querySelectorAll('.category-filter:not([value="all"])').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Reset price slider
        const priceSlider = document.getElementById('price-slider');
        if (priceSlider) {
            priceSlider.value = priceSlider.max;
            updatePriceDisplay(priceSlider.max);
        }
        
        // Show all products
        allProducts.forEach(product => {
            product.element.style.display = '';
        });
        
        // Update pagination info
        updatePaginationInfo();
    }
    
    /**
     * Update the price display based on slider value
     */
    function updatePriceDisplay(value) {
        const maxPriceElement = document.getElementById('max-price');
        if (maxPriceElement) {
            maxPriceElement.textContent = `₱${parseInt(value).toLocaleString()}`;
        }
    }
    
    /**
     * Update pagination info with the count of visible products
     */
    function updatePaginationInfo(visibleCount = null) {
        const paginationInfo = document.querySelector('.pagination-info');
        if (paginationInfo) {
            if (visibleCount === null) {
                visibleCount = allProducts.length;
            }
            
            const totalCount = allProducts.length;
            paginationInfo.textContent = `Showing 1–${visibleCount} of ${totalCount} results`;
        }
    }
    updatePaginationInfo(); // Initial call to set pagination info
});

// Get the modal
var modal = document.getElementById("cartModal");

// Get the button that opens the modal
var btn = document.getElementById("cart-toggle");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Ajax functionality for cart updates
document.addEventListener('DOMContentLoaded', function() {
    // You can add Ajax functionality here to handle form submissions
    // without page reloads for a smoother experience
    
    // Example:
    /*
    const forms = document.querySelectorAll('.cart-item form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Update the cart display with new data
                // This would depend on your server response structure
            })
            .catch(error => console.error('Error:', error));
        });
    });
    */
});
document.querySelector(".icon-button").addEventListener("click", function() {
    const actionsBtn = document.querySelector(".actionsBtn");
    
    // Toggle between "flex" and "none"
    if (actionsBtn.style.display === "flex") {
        actionsBtn.style.display = "none";
    } else {
        actionsBtn.style.display = "flex";
    }
});
</script>
<?php                     echo         $_SESSION['cart']["image"];?>
</body>
</html>
