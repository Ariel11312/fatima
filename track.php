<?php
session_start();
error_reporting(E_ALL);
include_once("./admin/database/connection.php");

if (!isset($_SESSION['email'])) {
    die("Please login to view your orders.");
}

$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';
$email = $_SESSION['email'] ?? '';
include('./nav.php')
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <style>
        #order-filters button {
    background-color: #eee;
    border: none;
    padding: 10px 15px;
    margin: 0 5px 10px 0;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
}

#order-filters button:hover {
    background-color: #ccc;
}

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        
        h1 {
            margin-top: 140px;
            color: #444;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        
        .status-section {
            margin-bottom: 40px;
        }
        
        .status-heading {
            font-size: 1.3em;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid;
        }
        
        .status-pending .status-heading { color: #e67e22; border-color: #e67e22; }
        .status-processing .status-heading { color: #3498db; border-color: #3498db; }
        .status-completed .status-heading { color: #27ae60; border-color: #27ae60; }
        .status-cancelled .status-heading { color: #e74c3c; border-color: #e74c3c; }
        
        .order-card {
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .order-card h2 {
            margin-top: 0;
            color: #555;
            font-size: 1.3em;
        }
        
        .order-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            color: #666;
            font-size: 0.9em;
        }
        
        .order-items {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
            position: relative;
        }
        
        .order-item img {
            border-radius: 3px;
            object-fit: cover;
            width: 80px;
            height: 80px;
        }
        
        .item-status {
            position: absolute;
            top: 12px;
            right: 0;
            font-size: 0.8em;
            padding: 3px 8px;
            border-radius: 3px;
        }
        
        .pending { background-color: #fdf0e6; color: #e67e22; }
        .processing { background-color: #ebf5fb; color: #3498db; }
        .completed { background-color: #e8f8f0; color: #27ae60; }
        .cancelled { background-color: #fdedec; color: #e74c3c; }
        
        .order-summary {
            background: #fafafa;
            padding: 15px;
            border-radius: 3px;
            margin-top: 15px;
        }
        
        .order-summary p {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            max-width: 300px;
        }
        
        .order-summary p:last-child {
            font-weight: bold;
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .no-orders {
            text-align: center;
            padding: 50px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Your Orders</h1>
    <div id="order-filters" style="margin-bottom: 20px;">
    <button onclick="filterOrders('all')">All</button>
    <button onclick="filterOrders('pending')">Pending</button>
    <button onclick="filterOrders('to ship')">To Ship</button>
    <button onclick="filterOrders('shipped')">Shipped</button>
    <button onclick="filterOrders('out for delivery')">Out For Delivery</button>
    <button onclick="filterOrders('delivered')">Delivered</button>
</div>

    <?php
    // First get all distinct statuses for this user's orders
    $status_sql = "SELECT DISTINCT o.order_status 
                   FROM orders o
                   JOIN shipping s ON o.order_id = s.order_id
                   WHERE s.firstname = ? AND s.lastname = ? AND s.email = ?";
    $status_stmt = mysqli_prepare($connection, $status_sql);
    mysqli_stmt_bind_param($status_stmt, "sss", $firstname, $lastname, $email);
    mysqli_stmt_execute($status_stmt);
    $status_result = mysqli_stmt_get_result($status_stmt);
    
    $all_statuses = [];
    while ($status_row = mysqli_fetch_assoc($status_result)) {
        $all_statuses[] = $status_row['order_status'];
    }
    
    // If no orders found
    if (empty($all_statuses)) {
        echo '<div class="no-orders"><p>You haven\'t placed any orders yet.</p></div>';
    }
    
    // Display orders grouped by status
    foreach ($all_statuses as $status) {
        // Get orders for this status
        $order_sql = "SELECT o.*, s.* 
                     FROM orders o
                     JOIN shipping s ON o.order_id = s.order_id
                     WHERE o.order_status = ?
                     AND s.firstname = ? AND s.lastname = ? AND s.email = ?
                     ORDER BY o.order_date DESC";
        $order_stmt = mysqli_prepare($connection, $order_sql);
        mysqli_stmt_bind_param($order_stmt, "ssss", $status, $firstname, $lastname, $email);
        mysqli_stmt_execute($order_stmt);
        $order_result = mysqli_stmt_get_result($order_stmt);
        
        if (mysqli_num_rows($order_result) > 0) {
            echo '<div class="status-section status-' . htmlspecialchars($status) . '">';
            echo '<h2 class="status-heading">' . ucwords(htmlspecialchars($status)) . ' Orders</h2>';
            
            while ($order = mysqli_fetch_assoc($order_result)) {
                $order_id = $order['order_id'];
                $order_date = $order['order_date'];
                $total_amount = $order['total_amount'];
                
                // Get order items
                $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
                $items_stmt = mysqli_prepare($connection, $items_sql);
                mysqli_stmt_bind_param($items_stmt, "s", $order_id);
                mysqli_stmt_execute($items_stmt);
                $items_result = mysqli_stmt_get_result($items_stmt);
                ?>
                
                <div class="order-card">
                    <h2>Order #<?php echo htmlspecialchars($order_id); ?></h2>
                    
                    <div class="order-meta">
                        <span>Placed on: <?php echo date('F j, Y, g:i a', strtotime($order_date)); ?></span>
                    </div>
                    
                    <?php if (mysqli_num_rows($items_result) > 0) { ?>
                        <div class="order-items">
                            <?php 
                            $subtotal = 0;
                            while ($item = mysqli_fetch_assoc($items_result)) {
                                $quantity = $item['quantity'];
                                $price = $item['price'];
                                $item_total = $quantity * $price;
                                $subtotal += $item_total;
                                ?>
                                <div class="order-item">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product">
                                    <div>
                                        <h3><?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?></h3>
                                        <p>Quantity: <?php echo htmlspecialchars($quantity); ?></p>
                                        <p>Price: ₱<?php echo number_format($price, 2); ?></p>
                                    </div>
                                    <span class="item-status <?php echo htmlspecialchars($status); ?>">
                                        <?php echo ucwords(htmlspecialchars($status)); ?>
                                    </span>
                                </div>
                                <?php
                            }
                            
                            $tax = $subtotal * 0.08;
                            $shipping = 10;
                            $grand_total = $subtotal + $tax + $shipping;
                            ?>
                        </div>
                        
                        <div class="order-summary">
                            <p><span>Subtotal:</span> <span>₱<?php echo number_format($subtotal, 2); ?></span></p>
                            <p><span>Tax (8%):</span> <span>₱<?php echo number_format($tax, 2); ?></span></p>
                            <p><span>Shipping:</span> <span>₱<?php echo number_format($shipping, 2); ?></span></p>
                            <p><span>Total:</span> <span>₱<?php echo number_format($grand_total, 2); ?></span></p>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            
            echo '</div>'; // Close status-section
        }
    }
    
    mysqli_close($connection);
    ?>
</body>
</html>
<script>
function filterOrders(status) {
    const sections = document.querySelectorAll('.status-section');
    sections.forEach(section => {
        const sectionStatus = section.classList[1].replace('status-', '').toLowerCase();
        if (status === 'all' || sectionStatus === status.toLowerCase()) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}
</script>
