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

// Get the selected filter from the request
$statusFilter = $_GET['status'] ?? 'all';

// Function to get filtered orders for the current user
function getUserOrders($email, $statusFilter)
{
    global $connection;

    if (empty($email)) {
        return [];
    }

    // Use a different approach - first get all order IDs that belong to this user
    $sql = "SELECT DISTINCT o.order_id 
            FROM orders o 
            INNER JOIN shipping s ON o.order_id = s.order_id 
            WHERE s.email = ?";
    
    // Add status filter if not 'all'
    if ($statusFilter !== 'all') {
        $sql .= " AND o.order_status = ?";
    }

    // Prepare the statement
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        error_log("MySQL Error: " . mysqli_error($connection));
        return [];
    }

    // Bind parameters based on filter
    if ($statusFilter !== 'all') {
        mysqli_stmt_bind_param($stmt, "ss", $email, $statusFilter);
    } else {
        mysqli_stmt_bind_param($stmt, "s", $email);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $orderIds = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orderIds[] = $row['order_id'];
        }
    }
    mysqli_stmt_close($stmt);
    
    // If no orders found, return empty
    if (empty($orderIds)) {
        return [];
    }
    
    // Now get full order details for these IDs
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $sql = "SELECT o.*, CONCAT(s.firstname, ' ', s.lastname) AS customer_name,
                  s.address, s.city, s.state, s.zip, s.phone, s.email
           FROM orders o 
           INNER JOIN shipping s ON o.order_id = s.order_id 
           WHERE o.order_id IN ($placeholders) AND s.email = ?
           ORDER BY o.order_date DESC";

    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        error_log("MySQL Error: " . mysqli_error($connection));
        return [];
    }
    
    // Create types string (all 'i' for order_ids plus 's' for email)
    $types = str_repeat('i', count($orderIds)) . 's';
    
    // Create array of parameters
    $params = array_merge($orderIds, [$email]);
    
    // Use reflection to bind the array of parameters
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }

    mysqli_stmt_close($stmt);
    return $orders;
}

// Function to get order items with statuses, ensuring they belong to the order
function getOrderItems($orderId, $statusFilter)
{
    global $connection;
    
    if (empty($orderId)) {
        return [];
    }

    // Base query - ensure we're only getting items for this specific order_id
    $sql = "SELECT oi.*, p.order_status, oi.image AS product_image
            FROM order_items oi
            INNER JOIN orders p ON oi.order_id = p.order_id
            WHERE oi.order_id = ?";

    // Add status filter if not 'all'
    if ($statusFilter !== 'all') {
        $sql .= " AND p.order_status = ?";
    }

    // Prepare the statement
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        error_log("MySQL Error: " . mysqli_error($connection));
        return [];
    }

    // Bind parameters
    if ($statusFilter !== 'all') {
        mysqli_stmt_bind_param($stmt, "is", $orderId, $statusFilter);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $orderId);
    }

    mysqli_stmt_execute($stmt);

    // Get results
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

// Function to get order status badge
function getOrderStatusBadge($status)
{
    $status = strtolower(trim($status ?? 'pending'));
    
    switch ($status) {
        case 'completed':
        case 'delivered':
            return ['Delivered', 'available'];
        case 'to ship':
            return ['To Ship', 'available'];
        case 'processing':
        case 'pending':
            return ['Pending', 'low-stock'];
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

// Get filtered orders for the current user
$userOrders = getUserOrders($email, $statusFilter);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - FATIMA HOME WORLD CENTER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="track.css">
</head>

<body>
    <?php include("./nav.php") ?>
    <div class="container">
        <header>
            <h1>FATIMA HOME WORLD CENTER</h1>
        </header>

        <div class="my-orders-container">
            <h2>My Order History</h2>

            <!-- Status Filters -->
            <div class="status-filters">
                <a href="?status=all" class="status-filter <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
                    All Orders
                </a>
                <a href="?status=" class="status-filter <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                    Pending
                </a>
                <a href="?status=to ship" class="status-filter <?php echo $statusFilter === 'to ship' ? 'active' : ''; ?>">
                    To Ship
                </a>
                <a href="?status=shipped" class="status-filter <?php echo $statusFilter === 'shipped' ? 'active' : ''; ?>">
                    Shipped
                </a>
                <a href="?status=out for delivery" class="status-filter <?php echo $statusFilter === 'out for delivery' ? 'active' : ''; ?>">
                    Out for Delivery
                </a>
                <a href="?status=delivered" class="status-filter <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">
                    Delivered
                </a>
            </div>

            <?php if (empty($userOrders)): ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>No Orders Found</h3>
                    <p>You don't have any orders in your history yet.</p>
                    <a href="shop.php" class="btn">Start Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($userOrders as $order): ?>
                    <?php 
                    // Get order items with the same status filter
                    $orderItems = getOrderItems($order['order_id'], $statusFilter); 
                    
                    // Skip empty orders (when filtered)
                    if (empty($orderItems) && $statusFilter !== 'all') {
                        continue;
                    }
                    ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-header-left">
                                <h3>Order #<?php echo htmlspecialchars($order['order_id']); ?></h3>
                                <p class="tracking-number">Tracking #: <?php echo htmlspecialchars($order['tracking_number'] ?? 'Not available'); ?></p>
                                <p class="order-date">Order Date: <?php echo formatDate($order['order_date']); ?></p>
                            </div>
                            <div class="order-header-right">
                                <span class="order-status <?php echo strtolower(str_replace(' ', '-', $order['order_status'])); ?>">
                                    <?php echo htmlspecialchars(ucwords($order['order_status'])); ?>
                                </span>
                                <p class="order-total">Total: <?php echo formatPrice($order['total_amount']); ?></p>
                            </div>
                        </div>
                        
                        <div class="order-items">
                            <?php if (!empty($orderItems)): ?>
                                <div class="products-grid">
                                    <?php foreach ($orderItems as $item): 
                                        // Calculate tax properly - 8% of price * quantity
                                        $tax = $item['price'] * 0.08 * $item['quantity'];
                                        
                                        // Set fixed shipping cost per item
                                        $shipping = 10;
                                        
                                        // Calculate total with tax and shipping
                                        $total = ($item['price'] * $item['quantity']) + $tax + $shipping;
                                    ?>
                                        <div class="product-card">
                                            <div class="product-image-container">
                                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                    alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                    class="product-image">
                                                <span class="product-status-badge <?php echo htmlspecialchars(getOrderStatusBadge($item['order_status'])[1]); ?>">
                                                    <?php echo htmlspecialchars(getOrderStatusBadge($item['order_status'])[0]); ?>
                                                </span>
                                            </div>
                                            <div class="product-details">
                                                <h4 class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                                <p class="product-code">Product Code: <?php echo htmlspecialchars($item['order_id']); ?></p>
                                                <p class="product-price"><?php echo formatPrice($item['price']); ?></p>
                                                <div class="product-meta">
                                                    <span>Qty: <?php echo htmlspecialchars($item['quantity']); ?></span>
                                                    <span><?php echo formatDate($order['order_date']); ?></span>
                                                </div>
                                                <!-- Added tax, shipping, and total information -->
                                                <div class="product-cost-breakdown">
                                                    <div class="cost-row">
                                                        <span class="cost-label">Tax (8%):</span>
                                                        <span class="cost-value"><?php echo formatPrice($tax); ?></span>
                                                    </div>
                                                    <div class="cost-row">
                                                        <span class="cost-label">Shipping:</span>
                                                        <span class="cost-value"><?php echo formatPrice($shipping); ?></span>
                                                    </div>
                                                    <div class="cost-row total">
                                                        <span class="cost-label">Total:</span>
                                                        <span class="cost-value"><?php echo formatPrice($total); ?></span>
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
                        
                        <div class="order-footer">
                            <div class="shipping-info">
                                <h4>Shipping Information</h4>
                                <p><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><?php echo htmlspecialchars($order['address']); ?></p>
                                <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['zip']); ?></p>
                                <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                                <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
                            </div>
                            <div class="order-totals">
                                <table class="totals-table">
                                    <tr>
                                        <td>Subtotal:</td>
                                        <td><?php echo formatPrice($order['total_amount'] - ($order['total_amount'] * 0.08)); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tax (8%):</td>
                                        <td><?php echo formatPrice($order['total_amount'] * 0.08); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping:</td>
                                        <td><?php echo formatPrice(count($orderItems) * 10); ?></td>
                                    </tr>
                                    <tr class="grand-total">
                                        <td>Total:</td>
                                        <td><?php echo formatPrice($order['total_amount'] + (count($orderItems) * 10)); ?></td>
                                    </tr>
                                </table>
                            </div>
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