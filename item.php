<?php
error_reporting(0);
include './admin/database/connection.php'; // Include the database connection file

session_start(); // Start the session
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize shopping cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get product details from URL parameters
$category = isset($_GET["category"]) ? $_GET["category"] : '';
$subCategory = isset($_GET["subCategory"]) ? $_GET["subCategory"] : '';
$id = isset($_GET["id"]) ? $_GET["id"] : '';

// Fetch product data from database if ID is provided
if (!empty($id) && !empty($category)) {
    $sql = "SELECT * FROM `$category` WHERE category = '$category' AND id = '$id'";
    $query = mysqli_query($connection, $sql);
    $row = mysqli_fetch_array($query);

    if ($row) {
        $product = [
            'id' => $row['id'],
            'image' => "./admin/" . $row['image'],
            'name' => $row['name'],
            'category' => $row['category'],
            'subCategory' => $row['subCategory'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'quantity' => $row['quantity'],
            'unitPrice' => $row['price'],
            'status' => $row['status'],
            'lastUpdate' => $row['lastUpdate'],
        ];
    }
}

// Handle add to cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    if(!$_SESSION['firstname']){
        echo "<script>window.location.href ='./login.php'</script>"; 
        return;
    } 
        // Check if product is already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    // If product not in cart, add it
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image' => $product['image'],
            'category' => $product['category'],
            'subCategory' => $product['subCategory'],
        ];
    }

    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF'] . '?added=' . time() . '&category=' . $category . '&subCategory=' . $subCategory . '&id=' . $id);
    exit;
}

// Handle cart updates (increase, decrease, remove, clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'increase':
            // Increase item quantity by 1
            if (isset($_POST['item_id'])) {
                $item_id = $_POST['item_id'];
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $item_id) {
                        $item['quantity']++;
                        break;
                    }
                }
            }
            break;

        case 'decrease':
            // Decrease item quantity by 1
            if (isset($_POST['item_id'])) {
                $item_id = $_POST['item_id'];
                foreach ($_SESSION['cart'] as $key => &$item) {
                    if ($item['id'] == $item_id) {
                        $item['quantity']--;
                        // Remove item if quantity is 0 or less
                        if ($item['quantity'] <= 0) {
                            unset($_SESSION['cart'][$key]);
                            // Reindex array
                            $_SESSION['cart'] = array_values($_SESSION['cart']);
                        }
                        break;
                    }
                }
            }
            break;

        case 'remove':
            // Remove item completely
            if (isset($_POST['item_id'])) {
                $item_id = $_POST['item_id'];
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['id'] == $item_id) {
                        unset($_SESSION['cart'][$key]);
                        // Reindex array
                        $_SESSION['cart'] = array_values($_SESSION['cart']);
                        break;
                    }
                }
            }
            break;

        case 'clear':
            // Clear entire cart
            $_SESSION['cart'] = [];
            break;
    }

    // Preserve URL parameters when redirecting
    $params = [];
    if (!empty($category))
        $params[] = "category=$category";
    if (!empty($subCategory))
        $params[] = "subCategory=$subCategory";
    if (!empty($id))
        $params[] = "id=$id";

    $query_string = !empty($params) ? '?' . implode('&', $params) : '';

    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF'] . $query_string);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Add to Cart</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .product-container {
            display: flex;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px 0;
            margin-top: 100px;
        }

        .product-image {
            flex: 0 0 40%;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
        }

        .product-details {
            flex: 0 0 60%;
            padding: 30px;
        }

        .product-title {
            font-size: 24px;
            margin-bottom: 10px;
            color: #222;
        }

        .product-category {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .product-price {
            font-size: 28px;
            font-weight: bold;
            color: #e63946;
            margin-bottom: 20px;
        }

        .product-meta {
            margin-bottom: 20px;
        }

        .meta-item {
            display: flex;
            margin-bottom: 10px;
        }

        .meta-label {
            flex: 0 0 100px;
            font-weight: bold;
            color: #555;
        }

        .meta-value {
            flex: 1;
            color: #333;
        }

        .in-stock {
            color: #2ecc71;
            font-weight: bold;
        }

        .add-to-cart-form {
            margin-top: 30px;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .quantity-input label {
            margin-right: 15px;
            font-weight: bold;
        }

        .quantity-input input {
            width: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .add-to-cart-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-to-cart-btn:hover {
            background-color: #2980b9;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }

        .cart-summary {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
        }

        .cart-summary h2 {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .cart-items {
            margin-bottom: 20px;
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
            border-radius: 4px;
            overflow: hidden;
            margin-right: 15px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-item-image img {
            max-width: 100%;
            max-height: 100%;
        }

        .cart-total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 15px;
        }

        .checkout-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }

        .checkout-btn:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
            }

            .product-image,
            .product-details {
                flex: 0 0 100%;
            }
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .cart-item-info {
            display: flex;
            align-items: center;
        }

        .cart-item-image {
            width: 80px;
            margin-right: 15px;
        }

        .cart-item-image img {
            max-width: 100%;
            height: auto;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .quantity-form {
            display: flex;
            align-items: center;
            margin-right: 15px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .item-quantity {
            margin: 0 10px;
            font-weight: bold;
        }

        .remove-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .cart-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin-top: 20px;
        }

        .clear-cart-btn {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .checkout-btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <?php include("./nav.php") ?>
    <div class="container">
        <?php if (isset($_GET['added'])): ?>
            <div class="success-message" id="success-message">
                Product successfully added to cart!
            </div>
        <?php endif; ?>

        <div class="product-container">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="product-details">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-category">
                    Category: <?php echo htmlspecialchars($product['category']); ?>
                    <?php if (!empty($product['subCategory'])): ?>
                        / <?php echo htmlspecialchars($product['subCategory']); ?>
                    <?php endif; ?>
                </div>

                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>

                <div class="product-meta">
                    <div class="meta-item">
                        <div class="meta-label">SKU:</div>
                        <div class="meta-value"><?php echo htmlspecialchars($product['sku']); ?></div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">Status:</div>
                        <div class="meta-value <?php echo $product['status'] === 'in-stock' ? 'in-stock' : ''; ?>">
                            <?php echo ucfirst(htmlspecialchars($product['status'])); ?>
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">Availability:</div>
                        <div class="meta-value">
                            <?php echo (int) $product['quantity']; ?> units available
                        </div>
                    </div>
                </div>

                <form class="add-to-cart-form" method="post" action="">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                    <div class="quantity-input">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1"
                            max="<?php echo $product['quantity']; ?>">
                    </div>

                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                        Add to Cart
                    </button>
                </form>
            </div>
        </div>

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
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div>
                                    <div><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div>$<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?>
                                    </div>
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
                                    <strong>$<?php echo number_format($item_total, 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-total">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>

                <div class="cart-actions">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="clear-cart-btn">Clear Cart</button>
                    </form>

                    <?php if (!$_SESSION["firstname"]) { ?>
                        <a class="checkout-btn" href="login.php">Proceed to Checkout</a>
                    <?php } else { ?>
                        <a class="checkout-btn" href="checkout.php">Proceed to Checkout</a>

                    <?php } ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    </div>

    <script>
        // Show success message with animation if it exists
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.display = 'block';
                setTimeout(function () {
                    successMessage.style.opacity = '0';
                    successMessage.style.transition = 'opacity 1s';
                    setTimeout(function () {
                        successMessage.style.display = 'none';
                    }, 1000);
                }, 3000);
            }

            // Quantity input validation
            const quantityInput = document.getElementById('quantity');
            if (quantityInput) {
                const maxQuantity = <?php echo (int) $product['quantity']; ?>;

                quantityInput.addEventListener('change', function () {
                    let value = parseInt(this.value);

                    if (isNaN(value) || value < 1) {
                        this.value = 1;
                    } else if (value > maxQuantity) {
                        this.value = maxQuantity;
                        alert('Sorry, there are only ' + maxQuantity + ' units available.');
                    }
                });
            }
        });
    </script>
</body>

</html>