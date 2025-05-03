<?php
require_once './database/connection.php'; // Database connection
require_once 'vendor/autoload.php';
session_start();

// Function to sanitize input
function sanitize($data)
{
    global $connection;
    return mysqli_real_escape_string($connection, trim($data));
}

// Display result message
$resultMessage = '';
$resultClass = '';

// Handle delivery confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = sanitize($_POST['order_id']);

    $selectSql = "SELECT * FROM order_items WHERE order_id = '$orderId'";
    $resultMessage = mysqli_query($connection, $selectSql);
    if (mysqli_num_rows($resultMessage) > 0) {
        $resultMessage = mysqli_fetch_assoc($resultMessage);
        echo $resultMessage['product_id'] . " - " . $resultMessage['product_name'] . " - " . $resultMessage['category'] . "<br>";
        $categoryItem = $resultMessage['category'];
        $quantityItem = $resultMessage['quantity'];
        $sql = "UPDATE $categoryItem SET quantity = quantity - '$quantityItem'  WHERE id = '$resultMessage[product_id]'";
        $quantityItem = mysqli_query($connection, $sql);
        if ($quantityItem) {
            $resultMessage = 'Product quantity updated successfully.';
            $resultClass = 'success-message';
        } else {
            $resultMessage = 'Error updating product quantity: ' . mysqli_error($connection);
            $resultClass = 'error-message';
        }
        if (!mysqli_query($connection, $sql)) {
            $resultMessage = 'Error updating product quantity: ' . mysqli_error($connection);
            $resultClass = 'error-message';
        }
    } else {
        $resultMessage = 'No items found for this order.';
        $resultClass = 'error-message';
    }
    $sql = "UPDATE orders SET order_status = 'Delivered'  WHERE order_id = '$orderId'";

    if (mysqli_query($connection, $sql)) {
        $resultMessage = 'Delivery confirmed successfully.';
        $resultClass = 'success-message';
    } else {
        $resultMessage = 'Error: ' . mysqli_error($connection);
        $resultClass = 'error-message';
    }
}

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
function getOrderById($orderId)
{
    global $connection;
    $sql = "SELECT o.*, CONCAT(s.firstname, ' ', s.lastname) AS customer_name
            FROM orders o
            LEFT JOIN shipping s ON o.order_id = s.order_id
            WHERE o.order_id = '$orderId'";
    $result = mysqli_query($connection, $sql);
    return mysqli_fetch_assoc($result);
}
?>

<?php include("sidebar.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delivery</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6fdc;
            --primary-dark: #3a5cb8;
            --primary-light: #eef2ff;
            --success: #28a745;
            --success-light: #d4edda;
            --success-dark: #155724;
            --danger: #dc3545;
            --danger-light: #f8d7da;
            --danger-dark: #721c24;
            --gray: #6c757d;
            --light-gray: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .container {
            margin: 20px auto;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .header .order-id {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 5px;
        }

        .content {
            padding: 30px;
        }

        .order-status {
            text-align: center;
            margin-bottom: 25px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-to-ship {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-out-for-delivery {
            background-color: #ffe8d9;
            color: #d85908;
        }

        .status-delivered {
            background-color: var(--success-light);
            color: var(--success-dark);
        }

        .order-info {
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 25px;
        }

        .order-info h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 18px;
            color: var(--primary-dark);
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 500;
            color: var(--gray);
        }

        .info-value {
            font-weight: 600;
            color: var(--dark);
        }

        .action-container {
            text-align: center;
            margin-top: 10px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-confirm {
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 4px 8px rgba(74, 111, 220, 0.2);
        }

        .btn-confirm:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 6px 12px rgba(74, 111, 220, 0.3);
            transform: translateY(-2px);
        }

        .btn-disabled {
            background-color: var(--light-gray);
            color: var(--gray);
            cursor: not-allowed;
        }

        .message-container {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            z-index: 1000;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            animation: slideIn 0.5s ease forwards;
            max-width: 350px;
        }

        .message-icon {
            margin-right: 12px;
            font-size: 20px;
        }

        .success-message {
            background-color: var(--success-light);
            color: var(--success-dark);
            border-left: 4px solid var(--success);
        }

        .error-message {
            background-color: var(--danger-light);
            color: var(--danger-dark);
            border-left: 4px solid var(--danger);
        }

        .delivered-animation {
            text-align: center;
            margin-bottom: 25px;
        }

        .delivered-icon {
            font-size: 60px;
            color: var(--success);
            animation: pulse 2s infinite;
        }

        .delivered-text {
            font-size: 18px;
            font-weight: 600;
            color: var(--success-dark);
            margin-top: 15px;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(50px);
            }
        }

        .slideOut {
            animation: slideOut 0.5s ease forwards;
        }

        @media (max-width: 576px) {
            .container {
                max-width: 100%;
            }

            .content {
                padding: 20px;
            }

            .info-item {
                flex-direction: column;
                margin-bottom: 15px;
            }

            .info-value {
                margin-top: 5px;
            }

            .message-container {
                left: 20px;
                right: 20px;
                max-width: none;
            }
        }
    </style>
</head>

<body>

    <style>
        .side-bar-container {
            margin-top: 100px;
        }
    </style>
    <?php if (!empty($resultMessage)): ?>
        <div class="message-container <?php echo $resultClass; ?>">
            <div class="message-icon">
                <?php if ($resultClass === 'success-message'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php else: ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php endif; ?>
            </div>
            <div class="message-text">
                <?php echo htmlspecialchars($resultMessage); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($order): ?>
        <div class="container">
            <div class="header">
                <h1>Delivery Confirmation</h1>
                <div class="order-id">Order #<?php echo htmlspecialchars($order['order_id']); ?></div>
            </div>

            <div class="content">
                <div class="order-status">
                    <p>Current Status:</p>
                    <?php
                    $statusClass = '';
                    switch ($order['order_status']) {
                        case 'Pending':
                            $statusClass = 'status-pending';
                            break;
                        case 'To Ship':
                            $statusClass = 'status-to-ship';
                            break;
                        case 'Shipped':
                            $statusClass = 'status-shipped';
                            break;
                        case 'Out for Delivery':
                            $statusClass = 'status-out-for-delivery';
                            break;
                        case 'Delivered':
                            $statusClass = 'status-delivered';
                            break;
                    }
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($order['order_status']); ?>
                    </span>
                </div>

                <div class="order-info">
                    <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                    <div class="info-item">
                        <div class="info-label">Customer:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Order Date:</div>
                        <div class="info-value"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Amount:</div>
                        <div class="info-value">₱<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?>
                        </div>
                    </div>
                    <?php if (!empty($order['tracking_number'])): ?>
                        <div class="info-item">
                            <div class="info-label">Tracking Number:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['tracking_number']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($order['order_status'] === 'Delivered'): ?>
                    <div class="delivered-animation">
                        <div class="delivered-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="delivered-text">
                            This order has been delivered successfully!
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" class="action-container">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <button type="submit" class="btn btn-confirm">
                            <i class="fas fa-truck"></i> Confirm Delivery
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="header">
                <h1>Order Not Found</h1>
            </div>
            <div class="content">
                <p style="text-align: center; margin: 20px 0;">The requested order could not be found.</p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Message auto-hide
        setTimeout(() => {
            const message = document.querySelector('.message-container');
            if (message) {
                message.classList.add('slideOut');
                setTimeout(() => message.remove(), 500);
            }
        }, 4000); // Hide after 4 seconds
    </script>
</body>

</html>