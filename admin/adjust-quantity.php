<?php
include_once './database/connection.php'; // Include your database configuration file
// Check for connection errors
if ($connection->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

// Check if required POST data is received
if (isset($_POST['item_id'], $_POST['newQuantity'], $_POST['category'])) {
    $item_id = $connection->real_escape_string($_POST['item_id']);
    $newQuantity = (int) $_POST['newQuantity'];
    $category = $connection->real_escape_string($_POST['category']);

    // Example: Assuming your inventory table is named `inventory`
    $sql = "UPDATE $category SET quantity = ? WHERE sku = ? AND category = ?";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iss", $newQuantity, $item_id, $category);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update item.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data provided.']);
}

$connection->close();
?>
