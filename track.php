<?php
session_start();
error_reporting(E_ALL); // Change to 0 in production
include_once("./admin/database/connection.php");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fatima_db";

// Connect to database
function connectDB()
{
    global $servername, $username, $password, $dbname;
    $connection = mysqli_connect($servername, $username, $password, $dbname);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $connection;
}

// Function to sanitize input
function sanitize($data)
{
    global $connection;
    return mysqli_real_escape_string($connection, trim($data));
}

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';
$email = $_SESSION['email'] ?? '';

// Connect to database
$connection = connectDB();

// Function to get all orders for the current user
function getUserOrders()
{
    global $connection, $email;

    if (empty($email)) {
        return [];
    }

    // Use prepared statement to prevent SQL injection
    $sql = "SELECT o.*, CONCAT(s.firstname, ' ', s.lastname) AS customer_name,
                  s.address, s.city, s.state, s.zip, s.phone, s.email
           FROM orders o 
           LEFT JOIN shipping s ON o.order_id = s.order_id 
           WHERE s.email = ?
           ORDER BY o.order_date DESC";

    // Prepare the statement
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        return [];
    }

    // Bind parameters and execute
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    // Get results
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $orders[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $orders;
}

// Function to get order items with statuses
function getOrderItems($orderId)
{
    global $connection;

    if (empty($orderId)) {
        return [];
    }

    $sql = "SELECT oi.*, oi.product_name, p.order_status, oi.image AS product_image
            FROM order_items oi
            LEFT JOIN orders p ON oi.order_id = p.order_id
            WHERE oi.order_id = ?";

    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $items = [];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }

    mysqli_stmt_close($stmt);
    return $items;
}

// Function to get order status badgee
function getOrderStatusBadge($status)
{
    $status = strtolower($status ?? 'pending');
    switch ($status) {
        case 'completed':
        case 'delivered':
            return ['Delivered', 'available'];
        case 'to ship':
            return ['To Ship', 'available'];
        case 'processing':
            return ['Processing', 'low-stock'];
        case 'shipped':
            return ['Shipped', 'backorder'];
        case 'cancelled':
            return ['Cancelled', 'out-of-stock'];
        case 'out for delivery':
            return ['Out for Delivery', 'out-for-delivery'];

        default:
            return ['Pending', 'unknown'];
    }
}

// Format date properly
function formatDate($date)
{
    if (empty($date)) {
        return 'N/A';
    }
    return date('F j, Y, g:i a', strtotime($date));
}

// Format price
function formatPrice($price)
{
    return '₱' . number_format(floatval($price ?? 0), 2);
}

// Get all orders for the current user
$userOrders = getUserOrders();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - FATIMA HOME WORLD CENTER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --primary-dark: #357bd8;
            --available-color: #43a047;
            --low-stock-color: #ffb300;
            --out-of-stock-color: #e53935;
            --backorder-color: #8e24aa;
            --discontinued-color: #757575;
            --light-gray: #f5f5f5;
            --medium-gray: #e0e0e0;
            --dark-gray: #757575;
            --text-color: #333;
            --border-radius: 10px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
        }

        header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        header p {
            color: var(--dark-gray);
        }

        .my-orders-container {
            margin-bottom: 40px;
        }

        .my-orders-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }

        .no-orders {
            background-color: white;
            padding: 50px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        }

        .no-orders i {
            font-size: 48px;
            color: var(--medium-gray);
            margin-bottom: 20px;
        }

        .no-orders h3 {
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .no-orders p {
            color: var(--dark-gray);
            margin-bottom: 20px;
        }

        .no-orders .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: background-color 0.3s;
        }

        .no-orders .btn:hover {
            background-color: var(--primary-dark);
        }

        .order-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid var(--medium-gray);
        }

        .order-header-left h3 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
        }

        .tracking-number,
        .order-date {
            font-size: 14px;
            color: var(--dark-gray);
            margin-bottom: 5px;
        }

        .order-header-right {
            text-align: right;
        }

        .order-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .order-status.completed,
        .order-status.delivered {
            background-color: var(--available-color);
            color: white;
        }

        .order-status.processing {
            background-color: var(--low-stock-color);
            color: white;
        }

        .order-status.shipped {
            background-color: var(--backorder-color);
            color: white;
        }
        .out-for-delivery {
            background-color: var(--available-color);
            color: white;
        }

        .order-status.cancelled {
            background-color: var(--out-of-stock-color);
            color: white;
        }

        .order-status.pending {
            background-color: var(--dark-gray);
            color: white;
        }

        .order-total {
            font-weight: bold;
            font-size: 16px;
            color: var(--text-color);
        }

        .order-items {
            padding: 20px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .product-card {
            background-color: white;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image-container {
            height: 180px;
            overflow: hidden;
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 2;
        }

        .product-status-badge.available {
            background-color: var(--available-color);
            color: white;
        }

        .product-status-badge.low-stock {
            background-color: var(--low-stock-color);
            color: white;
        }

        .product-status-badge.out-of-stock {
            background-color: var(--out-of-stock-color);
            color: white;
        }

        .product-status-badge.backorder {
            background-color: var(--backorder-color);
            color: white;
        }

        .product-status-badge.unknown {
            background-color: var(--dark-gray);
            color: white;
        }

        .product-details {
            padding: 15px;
        }

        .product-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            color: var(--text-color);
        }

        .product-code {
            font-size: 12px;
            color: var(--dark-gray);
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 16px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 12px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: var(--dark-gray);
            padding-top: 10px;
            border-top: 1px solid var(--medium-gray);
        }

        .no-products {
            text-align: center;
            padding: 30px 0;
            color: var(--dark-gray);
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            background-color: #f9f9f9;
            border-top: 1px solid var(--medium-gray);
        }

        .shipping-info {
            width: 48%;
        }

        .shipping-info h4 {
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .shipping-info p {
            margin: 0 0 5px 0;
            color: var(--dark-gray);
            font-size: 14px;
        }

        .order-totals {
            width: 48%;
            text-align: right;
        }

        .totals-table {
            width: 100%;
            margin-left: auto;
        }

        .totals-table td {
            padding: 8px 0;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .totals-table tr.grand-total {
            font-size: 18px;
            color: var(--primary-color);
        }

        .totals-table tr.grand-total td {
            padding-top: 12px;
            border-top: 2px solid var(--medium-gray);
        }

        .help-section {
            text-align: center;
            margin: 40px 0;
        }

        .help-section h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .help-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .help-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 25px;
            background-color: white;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .help-button:hover {
            border-color: var(--primary-color);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .help-button i {
            margin-right: 10px;
            color: var(--primary-color);
            font-size: 20px;
        }

        footer {
            text-align: center;
            padding: 30px 0;
            background-color: white;
            margin-top: 40px;
            border-top: 1px solid var(--medium-gray);
        }

        footer p {
            color: var(--dark-gray);
            margin-bottom: 10px;
        }

        .social-icons {
            margin-top: 20px;
        }

        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: var(--light-gray);
            border-radius: 50%;
            margin: 0 5px;
            color: var(--dark-gray);
            transition: all 0.3s;
        }

        .social-icons a:hover {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {

            .order-header,
            .order-footer {
                flex-direction: column;
            }

            .order-header-right {
                text-align: left;
                margin-top: 15px;
            }

            .order-footer .shipping-info,
            .order-footer .order-totals {
                width: 100%;
            }

            .order-footer .order-totals {
                margin-top: 20px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include("./nav.php") ?>
    <div class="container">
        <header>
            <h1>FATIMA HOME WORLD CENTER</h1>
        </header>

        <div class="my-orders-container">
            <h2>My Order History</h2>

            <?php if (empty($userOrders)): ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>No Orders Found</h3>
                    <p>You don't have any orders in your history yet.</p>
                    <a href="shop.php" class="btn">Start Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($userOrders as $order): ?>
                    <?php $orderItems = getOrderItems($order['order_id']); ?>

                    <div class="order-card">
                    </div>

                    <div class="order-items">
                        <?php if (!empty($orderItems)): ?>
                            <div class="products-grid">
                                <?php foreach ($orderItems as $item):
                                    $statusInfo = getOrderStatusBadge($item['order_status'] ?? $order['order_status']);
                                    ?>
                                    <div class="product-card">
                                        <div class="product-image-container">
                                            <?php if (!empty($item['product_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?>"
                                                    class="product-image">
                                            <?php else: ?>
                                                <img src="./assets/images/product-placeholder.jpg" alt="Product image placeholder"
                                                    class="product-image">
                                            <?php endif; ?>
                                            <div class="product-status-badge <?php echo $statusInfo[1]; ?>">
                                                <?php echo $statusInfo[0]; ?>
                                            </div>
                                        </div>
                                        <div class="product-details">
                                            <h4 class="product-name">
                                                <?php echo htmlspecialchars($item['product_name'] ?? 'Unknown Product'); ?>
                                            </h4>
                                            <div class="product-code">Product Code:
                                                <?php echo htmlspecialchars($item['product_id'] ?? 'N/A'); ?>
                                            </div>
                                            <div class="product-price"><?php echo formatPrice($item['price'] ?? 0); ?></div>
                                            <div class="product-meta">
                                                <div class="product-quantity">Qty: <?php echo $item['quantity'] ?? 0; ?></div>
                                                <div class="product-subtotal">
                                                    <?php echo formatPrice(($item['quantity'] ?? 0) * ($item['price'] ?? 0)); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-products">
                                <i class="fas fa-shopping-basket"
                                    style="font-size: 48px; color: var(--medium-gray); margin-bottom: 20px;"></i>
                                <h3>No products found in this order</h3>
                                <p>This order doesn't have any products associated with it.</p>
                            </div>
                        <?php endif; ?>
                    </div>


                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="help-section">
        <h3>Need Help?</h3>
        <div class="help-buttons">
            <a href="contact.php" class="help-button">
                <i class="fas fa-envelope"></i> Contact Support
            </a>
            <a href="faq.php" class="help-button">
                <i class="fas fa-question-circle"></i> FAQ
            </a>
            <a href="tel:+639123456789" class="help-button">
                <i class="fas fa-phone"></i> Call Us
            </a>
        </div>
    </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> FATIMA HOME WORLD CENTER. All rights reserved.</p>
        <p>Your trusted partner for home improvement and construction materials.</p>
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
    </footer>
</body>

</html>