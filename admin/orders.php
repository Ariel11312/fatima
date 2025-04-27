<?php
require_once './database/connection.php'; // Include the database connection file
require 'vendor/autoload.php';

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;

$options = new QROptions([
    'eccLevel' => QRCode::ECC_L, // L = Low (more data capacity)
]);

session_start();

// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: login.php");
//     exit;
// }

function sanitize($data)
{
    global $connection;
    return mysqli_real_escape_string($connection, trim($data));
}

// Function to get order by ID
function getOrderById($orderId)
{
    global $connection;
    $orderId = sanitize($orderId);
    $sql = "SELECT o.*, CONCAT(s.firstname, ' ', s.lastname) AS customer_name,
            s.address, s.city, s.state, s.zip, s.phone, s.email
            FROM orders o 
            LEFT JOIN shipping s ON o.order_id = s.order_id 
            WHERE o.order_id = '$orderId'";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return null;
}

// Function to get order items
function getOrderItems($orderId)
{
    global $connection;
    $orderId = sanitize($orderId);
    $sql = "SELECT oi.*, p.product_name, p.price 
            FROM order_items oi
            LEFT JOIN order_items p ON oi.product_id = p.product_id
            WHERE oi.order_id = '$orderId'";
    $result = mysqli_query($connection, $sql);
    $items = [];

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }

    return $items;
}

function generateTrackingNumber()
{
    return 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 5));
}

function generateQRCode($orderId, $trackingNumber)
{
    $path = 'qrcodes/';
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $filename = $path . $orderId . '.png';

    $options = new QROptions([
        'version' => 5,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_L,
    ]);

    // Encode the URL into the QR code instead of plain text
    $baseUrl = 'http://localhost/fatima/admin/deliver_confirm.php';
    $qrContent = $baseUrl . '?order_id=' . urlencode($orderId) . '&tracking_number=' . urlencode($trackingNumber);

    (new QRCode($options))->render($qrContent, $filename);

    return $filename; // returns the path to the QR code image
}


function updateOrderStatus($orderId, $status)
{
    global $connection;
    $orderId = sanitize($orderId);
    $status = sanitize($status);

    if ($status == 'To Ship') {
        $trackingNumber = generateTrackingNumber();
        $qrCodePath = generateQRCode($orderId, $trackingNumber);

        $sql = "UPDATE orders SET 
                order_status = '$status',
                tracking_number = '$trackingNumber',
                qr_code_path = '$qrCodePath'
                WHERE order_id = '$orderId'";
    } else {
        $sql = "UPDATE orders SET order_status = '$status' WHERE order_id = '$orderId'";
    }

    return mysqli_query($connection, $sql);
}

function getOrders()
{
    global $connection;
    $sql = "SELECT o.*, CONCAT(s.firstname, ' ', s.lastname) AS customer_name 
            FROM orders o 
            LEFT JOIN shipping s ON o.order_id = s.order_id 
            ORDER BY o.order_date DESC";
    $result = mysqli_query($connection, $sql);
    $orders = [];

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }

    return $orders;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $orderId = sanitize($_POST['order_id']);
        $newStatus = sanitize($_POST['new_status']);

        if (updateOrderStatus($orderId, $newStatus)) {
            $message = "Order status updated successfully";
            $messageType = "success";

            if ($newStatus == 'To Ship') {
                $order = getOrderById($orderId);
                $message .= "<br>Tracking Number: " . $order['tracking_number'];
            }
        } else {
            $message = "Error updating order status: " . mysqli_error($connection);
            $messageType = "error";
        }
    }

    if (isset($_POST['confirm_delivery'])) {
        $orderId = sanitize($_POST['order_id']);

        if (updateOrderStatus($orderId, 'Delivered')) {
            $message = "Order marked as delivered successfully";
            $messageType = "success";
        } else {
            $message = "Error confirming delivery: " . mysqli_error($connection);
            $messageType = "error";
        }
    }
}

$orders = getOrders();

// Get order details if requested via AJAX
if (isset($_GET['get_order_details']) && isset($_GET['order_id'])) {
    $orderId = sanitize($_GET['order_id']);
    $order = getOrderById($orderId);
    $orderItems = getOrderItems($orderId);

    $response = [
        'order' => $order,
        'items' => $orderItems
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6fdc;
            --primary-dark: #3a5cb8;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 6px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1400px;
            margin-right: 200px;
            margin-left: 400px;
            margin-top: 80px;
            padding: 20px;
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 20px;
        }

        h1,
        h2,
        h3,
        h4 {
            margin-bottom: 10px;
        }

        .page-title {
            font-size: 28px;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }

        .alert {
            padding: 12px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #0f5132;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f4f6f9;
            font-weight: 600;
            color: #495057;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-to-ship {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }

        .status-out-for-delivery {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #c3e6cb;
            color: #0f5132;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            border-radius: var(--border-radius);
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn-primary {
            color: var(--white);
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-success {
            color: var(--white);
            background-color: var(--success);
            border-color: var(--success);
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.76562rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .status-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: var(--white);
            font-size: 14px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .qr-code {
            max-width: 80px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .qr-code:hover {
            transform: scale(1.1);
        }

        .action-btn {
            color: var(--white);
            background-color: var(--primary);
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.3s;
        }

        .action-btn:hover {
            background-color: var(--primary-dark);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 50px auto;
            padding: 0;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 15px 20px;
            background-color: var(--primary);
            color: var(--white);
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            color: var(--white);
            margin: 0;
            font-size: 18px;
        }

        .close {
            color: var(--white);
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #ddd;
        }

        .modal-body {
            padding: 20px;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .order-detail {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
        }

        .order-detail h3 {
            margin-top: 0;
            color: var(--primary-dark);
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }

        .detail-label {
            width: 120px;
            font-weight: 600;
            color: #555;
        }

        .detail-value {
            flex: 1;
        }

        .qr-section {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
        }

        .qr-section img {
            max-width: 150px;
        }

        .tracking-number {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-dark);
            margin-top: 10px;
        }

        .modal-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
            text-align: right;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .print-section {
            display: none;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .print-section,
            .print-section * {
                visibility: visible;
            }

            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                display: block;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .icon-button:hover {
            background-color: #f1f1f1;
        }

        .icon-button i {
            font-size: 16px;
        }

        .view-btn i {
            color: var(--primary);
        }

        .print-btn i {
            color: var(--secondary);
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .order-info-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
            }
        }

        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }

            .container {
                width: 95%;
            }
        }

    </style>
</head>
<body>
    <?php include("sidebar.php");?>
<style>
            .side-bar-container {
            margin-top: 60px;
        }
</style>

    <div class="container">
        <h1 class="page-title"><i class="fas fa-box"></i> Order Management</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
            <div class="card-header">
                <h2>Orders List</h2>
                <div class="header-actions">
                    <button class="btn btn-primary btn-sm" onclick="window.location.reload();">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Tracking</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo $order['customer_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>
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
                                        <?php echo $order['order_status']; ?>
                                    </span>
                                    <form method="post" class="status-form" style="margin-top: 5px;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="new_status" style="width: 150px;">
                                            <option value="Pending" <?php echo $order['order_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="To Ship" <?php echo $order['order_status'] == 'To Ship' ? 'selected' : ''; ?>>To Ship</option>
                                            <option value="Shipped" <?php echo $order['order_status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Out for Delivery" <?php echo $order['order_status'] == 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="Delivered" <?php echo $order['order_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <button type="submit" name="update_status" value="1" class="btn btn-primary btn-sm">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo $order['tracking_number'] ?? 'N/A'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-button view-btn"
                                            onclick="openOrderModal('<?php echo $order['order_id']; ?>')">
                                            <i class="fas fa-eye"></i> View
                                        </button>

                                        <?php if ($order['order_status'] == 'Out for Delivery'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <button type="submit" name="confirm_delivery" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Confirm Delivery
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle"></i> Order Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded via JavaScript -->
                <div class="loading-spinner">Loading...</div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="printOrderDetails()">
                    <i class="fas fa-print"></i> Print Details
                </button>
            </div>
        </div>
    </div>

    <div id="printSection" class="print-section">
        <!-- Content for printing will be loaded here -->
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("orderModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Function to open the modal and fetch order details
        function openOrderModal(orderId) {
    modal.style.display = "block";
    document.getElementById('modalBody').innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading order details...</div>';

    fetch('?get_order_details=true&order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            let order = data.order;
            let modalContent = `
                <div class="order-info-grid">
                    <div class="order-detail">
                        <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                        <div class="detail-row">
                            <div class="detail-label">Order ID:</div>
                            <div class="detail-value">${order.order_id}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Order Date:</div>
                            <div class="detail-value">${new Date(order.order_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value">${order.order_status}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Total Amount:</div>
                            <div class="detail-value">₱${parseFloat(order.total_amount).toFixed(2)}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Payment Method:</div>
                            <div class="detail-value">${order.payment_method || 'N/A'}</div>
                        </div>
                    </div>

                    <div class="order-detail">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        <div class="detail-row">
                            <div class="detail-label">Name:</div>
                            <div class="detail-value">${order.customer_name}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value">${order.email || 'N/A'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Phone:</div>
                            <div class="detail-value">${order.phone || 'N/A'}</div>
                        </div>
                    </div>

                    <div class="order-detail">
                        <h3><i class="fas fa-map-marker-alt"></i> Shipping Address</h3>
                        <div class="detail-row">
                            <div class="detail-label">Address:</div>
                            <div class="detail-value">${order.address || 'N/A'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">City:</div>
                            <div class="detail-value">${order.city || 'N/A'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">State:</div>
                            <div class="detail-value">${order.state || 'N/A'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Zip Code:</div>
                            <div class="detail-value">${order.zip || 'N/A'}</div>
                        </div>
                    </div>

                    <div class="qr-section">
                        <h3><i class="fas fa-qrcode"></i> Tracking Information</h3>
                        ${order.qr_code_path ? `
                            <img src="${order.qr_code_path}" alt="QR Code">
                            <div class="tracking-number">
                                <i class="fas fa-truck"></i> ${order.tracking_number || 'N/A'}
                            </div>` : 
                            '<p>No tracking information available</p>'
                        }
                    </div>
                </div>
            `;

            document.getElementById('modalBody').innerHTML = modalContent;

            // Print Section
            // Print Section
document.getElementById('printSection').innerHTML = `
    <div style="padding: 20px; max-width: 800px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h1>Order Details</h1>
            <p>Order #${order.order_id}</p>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="width: 48%;">
                <h3>Order Information</h3>
                <p><strong>Order Date:</strong> ${new Date(order.order_date).toLocaleDateString()}</p>
                <p><strong>Status:</strong> ${order.order_status}</p>
                <p><strong>Total Amount:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>
                <p><strong>Payment Method:</strong> ${order.payment_method || 'N/A'}</p>
            </div>
            <div style="width: 48%;">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> ${order.customer_name}</p>
                <p><strong>Email:</strong> ${order.email || 'N/A'}</p>
                <p><strong>Phone:</strong> ${order.phone || 'N/A'}</p>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <h3>Shipping Address</h3>
            <p>${order.address || 'N/A'}</p>
            <p>${order.city || 'N/A'}, ${order.state || 'N/A'} ${order.zip || 'N/A'}</p>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="width: 48%;">
                <h3>Tracking Information</h3>
                ${order.tracking_number ? 
                    `<p><strong>Tracking Number:</strong> ${order.tracking_number}</p>
                     <p><strong>Status:</strong> ${order.order_status}</p>
                     <p><strong>Last Updated:</strong> ${new Date().toLocaleDateString()}</p>` : 
                    '<p>No tracking information available</p>'
                }
            </div>
            <div style="width: 48%; text-align: center;">
                ${order.qr_code_path ? 
                    `<h3>Scan QR Code for Tracking</h3>
                     <img src="${order.qr_code_path}" alt="QR Code" style="max-width: 150px;">` : 
                    ''
                }
            </div>
        </div>

        <div style="margin-top: 30px;">
            <h3>Order Items</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Product</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Price</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Quantity</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.items.map(item => `
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">${item.product_name}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">$${parseFloat(item.price).toFixed(2)}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${item.quantity}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">$${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong>Total:</strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><strong>$${parseFloat(order.total_amount).toFixed(2)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
`;
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
            document.getElementById('modalBody').innerHTML = '<div class="alert alert-error">Error loading order details. Please try again.</div>';
        });
}

// Function to print order details
function printOrderDetails() {
    window.print();
}

    </script>
</body>

</html>