<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If no order confirmation data, redirect to product page
if (!isset($_SESSION['order_confirmation'])) {
    header('Location: product-list.php');
    exit;
}

// Get order details
$order = $_SESSION['order_confirmation'];
// Insert shipping information into database
// This should be done on the checkout page before redirecting here
// But for demonstration purposes, if we have POST data available:

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .success-header {
            text-align: center;
            margin: 40px 0;
        }
        
        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .page-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
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
        
        .order-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-column {
            flex: 1;
            min-width: 200px;
        }
        
        .info-box {
            margin-bottom: 20px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #333;
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
        
        .order-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .actions {
            text-align: center;
            margin-top: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-outline {
            background-color: transparent;
            color: #3498db;
            border: 1px solid #3498db;
            margin-left: 15px;
        }
        
        .btn-outline:hover {
            background-color: #f0f7fc;
        }
        
        .thank-you {
            text-align: center;
            margin-top: 50px;
            font-size: 16px;
            color: #666;
        }
        
        @media (max-width: 600px) {
            .order-info {
                flex-direction: column;
            }
            
            .actions {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-header">
            <div class="success-icon">✓</div>
            <h1 class="page-title">Order Confirmed!</h1>
            <p class="subtitle">Thank you for your purchase. Your order has been received.</p>
        </div>
        
        <div class="section">
            <h2 class="section-title">Order Details</h2>
            
            <div class="order-info">
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-label">Order Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['order_id']); ?></div>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-label">Order Date</div>
                        <div class="info-value"><?php echo date('F j, Y'); ?></div>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value">Credit Card</div>
                    </div>
                </div>
                
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-label">Shipping Address</div>
                        <div class="info-value">
                            <?php if (isset($order['shipping_address'])): ?>
                                <?php echo htmlspecialchars($order['shipping_address']['name']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_address']['address']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_address']['city']); ?>, 
                                <?php echo htmlspecialchars($order['shipping_address']['state']); ?> 
                                <?php echo htmlspecialchars($order['shipping_address']['zip']); ?><br>
                                <?php echo htmlspecialchars($order['shipping_address']['country']); ?>
                            <?php else: ?>
                                Information not available
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <h3 class="section-title">Items Ordered</h3>
            
            <div class="order-items">
                <?php 
                $total = 0;
                if (isset($order['items']) && is_array($order['items'])):
                    foreach ($order['items'] as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                ?>
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
                    ₱<?php echo number_format($item_total, 2); ?>
                    </div>
                </div>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
            
            <div class="order-total">
                Total: ₱<?php echo number_format($order['total'] ?? $total, 2); ?>
            </div>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn">Continue Shopping</a>
            <a href="#" class="btn btn-outline">Track Order</a>
        </div>
        
        <div class="thank-you">
            <p>A confirmation email has been sent to your email address.</p>
        </div>
    </div>
</body>
</html>