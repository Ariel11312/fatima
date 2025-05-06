<?php
session_start();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: product-page.php');
    exit;
}

// Calculate cart totals
$subtotal = 0;
$shipping = 10.00; // Default shipping cost
$tax_rate = 0.08;  // 8% tax rate

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $tax + $shipping;

// Process checkout form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate form fields
    $required_fields = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State/Province',
        'zip' => 'ZIP/Postal Code',
        'country' => 'Country',
        'payment_method' => 'Payment Method'
    ];
    
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            $errors[] = "$label is required";
        }
    }
    
    // Validate email format
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        // Generate a unique order ID
        $order_id = 'ORD-' . uniqid();
        
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "fatima_db";
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // 1. Insert shipping information
        $sql = "INSERT INTO shipping (firstname, lastname, email, phone, address, 
                address2, city, state, zip, country, order_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Get data from POST
        $firstname = $_POST['first_name'];
        $lastname = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'];
        $address2 = $_POST['address2'] ?? '';
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip = $_POST['zip'];
        $country = $_POST['country'];
        
        $stmt->bind_param("sssssssssss", 
            $firstname,
            $lastname,
            $email,
            $phone,
            $address,
            $address2,
            $city,
            $state,
            $zip,
            $country,
            $order_id
        );
        
        $stmt->execute();
        $stmt->close();
        
        // 2. Insert order information
        $payment_method = $_POST['payment_method'];
        $order_status = 'Confirmed';
        
        $sql = "INSERT INTO orders (order_id, order_date, payment_method, order_status, total_amount, subtotal, tax, shipping_cost) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $order_date = date('Y-m-d H:i:s');
        $stmt->bind_param("ssssdddd", 
            $order_id, 
            $order_date, 
            $payment_method, 
            $order_status, 
            $total, 
            $subtotal, 
            $tax, 
            $shipping
        );
        $stmt->execute();
        $stmt->close();
        
        // 3. Insert order items
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal, image, category) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            foreach ($_SESSION['cart'] as $item) {
                // Ensure all required fields exist with defaults
                $product_id = $item['id'] ?? 0;
                $product_name = $item['name'] ?? 'Unknown Product';
                $quantity = $item['quantity'] ?? 1;
                $price = $item['price'] ?? 0;
                $image = $item['image'] ?? 'default.jpg';
                $category = $item['category'] ?? 'Uncategorized';
                $item_subtotal = $price * $quantity;
                
                $stmt->bind_param("sissddss", 
                    $order_id, 
                    $product_id, 
                    $product_name, 
                    $quantity, 
                    $price, 
                    $item_subtotal,
                    $image,
                    $category
                );
                
                if (!$stmt->execute()) {
                    error_log("Error inserting order item: " . $stmt->error);
                }
            }
            
            $stmt->close();
        }
        
        // Close connection
        $conn->close();
        
        // Store order confirmation in session
        $_SESSION['order_confirmation'] = [
            'order_id' => $order_id,
            'order_date' => $order_date,
            'payment_method' => $payment_method,
            'total' => $total,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shipping,
            'items' => $_SESSION['cart'],
            'shipping_address' => [
                'name' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'country' => $country
            ]
        ];
        
        // Clear the cart
        $_SESSION['cart'] = [];
        
        // Redirect to confirmation page
        header('Location: order-confirmation.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
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
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #333;
        }
        
        .checkout-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 80px;
        }
        
        .checkout-form {
            flex: 1;
            min-width: 300px;
        }
        
        .order-summary {
            flex: 0 0 350px;
        }
        
        .section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-row {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .form-row-2 {
            display: flex;
            gap: 15px;
        }
        
        .form-row-2 .form-group {
            flex: 1;
        }
        
        .payment-methods {
            margin-bottom: 20px;
        }
        
        .payment-method {
            display: block;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .payment-method:hover {
            background-color: #f9f9f9;
        }
        
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-details {
            margin-top: 20px;
            display: none;
        }
        
        .payment-details.active {
            display: block;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
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
        
        .item-image img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #666;
            font-size: 14px;
        }
        
        .item-quantity {
            background-color: #f0f0f0;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .order-summary-totals {
            margin-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }
        
        .summary-row.total {
            font-size: 18px;
            font-weight: bold;
            border-top: 1px solid #ddd;
            margin-top: 10px;
            padding-top: 15px;
        }
        
        .submit-btn {
            background-color: #3498db;
            color: white;
            border: none;
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        
        .submit-btn:hover {
            background-color: #2980b9;
        }
        
        .return-to-cart {
            text-align: center;
            margin-top: 20px;
        }
        
        .return-to-cart a {
            color: #3498db;
            text-decoration: none;
        }
        
        .return-to-cart a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-message ul {
            margin: 5px 0 0 20px;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column-reverse;
            }
            
            .form-row-2 {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <?php include("./nav.php")?>
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Please correct the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="checkout-form">
                <form method="post" action="">
                    <div class="section">
                        <h2 class="section-title">Contact Information</h2>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo isset($_SESSION['lastname']) ? htmlspecialchars($_SESSION['lastname']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone (optional)</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="section">
                        <h2 class="section-title">Shipping Address</h2>
                        <div class="form-group">
                            <label class="form-label" for="address">Address</label>
                            <input type="text" id="address" name="address" class="form-control" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="address2">Apartment, suite, etc. (optional)</label>
                            <input type="text" id="address2" name="address2" class="form-control" value="<?php echo isset($_POST['address2']) ? htmlspecialchars($_POST['address2']) : ''; ?>">
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="state">Province</label>
                                <input type="text" id="state" name="state" class="form-control" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="zip">ZIP/Postal Code</label>
                                <input type="text" id="zip" name="zip" class="form-control" value="<?php echo isset($_POST['zip']) ? htmlspecialchars($_POST['zip']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="country">Country</label>
                                <select id="country" name="country" class="form-control" required>
                                    <option value="">Select Country</option>
                                    <option value="PHP" <?php echo (isset($_POST['country']) && $_POST['country'] === 'PHP') ? 'selected' : ''; ?>>Philippines</option>
 
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <h2 class="section-title">Payment Method</h2>
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="Cash On Delivery" <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] === 'Cash') ? 'checked' : ''; ?>>Cash On Delivery</label>
                        </div>
                        
                    </div>
                    
                    <button type="submit" name="place_order" class="submit-btn">Place Order</button>
                    
                    <div class="return-to-cart">
                        <a href="product-list.php">Return to Cart</a>
                    </div>
                </form>
            </div>
            
            <div class="order-summary">
                <div class="section">
                    <h2 class="section-title">Order Summary</h2>
                    
                    <div class="order-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <div style="display: flex; align-items: center;">
                                    <div class="item-image">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="item-info">
                                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="item-price">
                                        ₱<?php echo number_format($item['price'], 2); ?>
                                            <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-summary-totals">
                        <div class="summary-row">
                            <div>Subtotal</div>
                            <div>₱<?php echo number_format($subtotal, 2); ?></div>
                        </div>
                        <div class="summary-row">
                            <div>Shipping</div>
                            <div>₱<?php echo number_format($shipping, 2); ?></div>
                        </div>
                        <div class="summary-row">
                            <div>Tax (8%)</div>
                            <div>₱<?php echo number_format($tax, 2); ?></div>
                        </div>
                        <div class="summary-row total">
                            <div>Total</div>
                            <div>₱<?php echo number_format($total, 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Payment method toggle
            const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
            const creditCardDetails = document.getElementById('credit-card-details');
            const paypalDetails = document.getElementById('paypal-details');
            
            paymentRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this.value === 'credit_card') {
                        creditCardDetails.classList.add('active');
                        paypalDetails.classList.remove('active');
                    } else if (this.value === 'paypal') {
                        creditCardDetails.classList.remove('active');
                        paypalDetails.classList.add('active');
                    }
                });
            });
            
            // Credit card input formatting
            const cardNumberInput = document.getElementById('card_number');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    // Remove all non-digits
                    let value = this.value.replace(/\D/g, '');
                    
                    // Add a space after every 4 digits
                    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                    
                    // Update the input value
                    this.value = value;
                });
            }
            
            const cardExpiryInput = document.getElementById('card_expiry');
            if (cardExpiryInput) {
                cardExpiryInput.addEventListener('input', function(e) {
                    // Remove all non-digits
                    let value = this.value.replace(/\D/g, '');
                    
                    // Format as MM/YY
                    if (value.length > 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    
                    // Update the input value
                    this.value = value;
                });
            }
        });
    </script>
</body>
</html>