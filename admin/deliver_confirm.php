<?php
require_once './database/connection.php'; // Database connection
require_once 'vendor/autoload.php';
session_start();

// Function to sanitize input
function sanitize($data) {
    global $connection;
    return mysqli_real_escape_string($connection, trim($data));
}

// Display result message
$resultMessage = '';
$resultClass = '';

// Fetch order details
$order = null;
if (isset($_GET['order_id']) || isset($_POST['order_id'])) {
    $orderId = isset($_GET['order_id']) ? sanitize($_GET['order_id']) : sanitize($_POST['order_id']);
    $order = getOrderById($orderId);
    if (!$order) {
        die('Order not found.');
    }
}

// Fetch order function
function getOrderById($orderId) {
    global $connection;
    $sql = "SELECT o.*, CONCAT(s.firstname, ' ', s.lastname) AS customer_name 
            FROM orders o 
            LEFT JOIN shipping s ON o.shipping_id = s.id 
            WHERE o.order_id = '$orderId'";
    $result = mysqli_query($connection, $sql);
    return mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Delivery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Same styles from before... */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f5f7fb;
        }
        .container { max-width: 500px; height: 500px; margin: auto; background-color: white; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .delivered-btn { padding: 12px 24px; background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 16px; font-weight: bold; }
        .order-info { text-align: left; margin-bottom: 20px; background-color: #f9f9f9; padding: 15px; border-radius: 4px; }
        .success-message, .error-message { margin-top: 20px; padding: 10px; border-radius: 4px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 14px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-out-for-delivery { background: #d4edda; color: #155724; }
        .status-delivered { background: #c3e6cb; color: #0f5132; }
    </style>
</head>
<body>
<?php include("sidebar.php");?>
<style>
            .side-bar-container {
            margin-top: 50px;
        }
</style>
    <div class="container">
        <h1><i class="fas fa-truck"></i> Confirm Delivery</h1>

        <?php if ($resultMessage): ?>
            <div class="<?php echo $resultClass; ?>">
                <i class="fas <?php echo $resultClass === 'success-message' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $resultMessage; ?>
            </div>
        <?php endif; ?>

        <?php if ($order): ?>
            <div class="order-info">
                <h3>Order Information</h3>
                <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                <p><strong>Customer:</strong> <?php echo $order['customer_name']; ?></p>
                <p><strong>Tracking Number:</strong> <?php echo $order['tracking_number']; ?></p>
                <p>
                    <strong>Status:</strong> 
                    <?php 
                        $statusClass = '';
                        switch ($order['order_status']) {
                            case 'Pending': $statusClass = 'status-pending'; break;
                            case 'Shipped': $statusClass = 'status-shipped'; break;
                            case 'Out for Delivery': $statusClass = 'status-out-for-delivery'; break;
                            case 'Delivered': $statusClass = 'status-delivered'; break;
                        }
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo $order['order_status']; ?></span>
                </p>
            </div>

            <div class="qr-code">
                <img src="<?php echo $order['qr_code_path']; ?>" width="200" alt="Delivery QR Code">
                <p>QR code for order tracking</p>
            </div>

            <form method="POST" action="delivery_confirm.php">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <?php if ($order['order_status'] !== 'Delivered'): ?>
                    <button type="submit" class="delivered-btn">
                        <i class="fas fa-check-circle"></i> Mark as Delivered
                    </button>
                <?php else: ?>
                    <p><i class="fas fa-check-circle" style="color: #4CAF50; font-size: 24px;"></i> This order has been delivered</p>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <p>No order specified. Please scan a valid QR code.</p>
        <?php endif; ?>
    </div>
</body>
</html>
