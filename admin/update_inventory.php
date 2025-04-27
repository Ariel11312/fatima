<?php
include './database/connection.php';

$response = [
    'status' => 'error',
    'message' => 'Unknown error occurred'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $item_id = mysqli_real_escape_string($connection, $_POST['item_id']);
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $category = mysqli_real_escape_string($connection, $_POST['category']);
    $subCategory = mysqli_real_escape_string($connection, $_POST['subCategory']);
    $sku = mysqli_real_escape_string($connection, $_POST['sku']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    
    // Image upload handling
    $imageUpdated = false;
    $imageFilePath = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = './uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imageFilePath = 'uploads/' . $fileName;
            $imageUpdated = true;
        } else {
            $response['message'] = 'Error uploading image';
            echo json_encode($response);
            exit;
        }
    }
    
    // Update query with or without image
    if ($imageUpdated) {
        $sql = "UPDATE $category SET 
                name = '$name', 
                category = '$category', 
                subCategory = '$subCategory', 
                quantity = $quantity, 
                price = $price, 
                status = '$status', 
                image = '$imageFilePath', 
                lastUpdate = NOW() 
                WHERE id = $item_id";
    } else {
        $sql = "UPDATE $category SET 
                name = '$name', 
                category = '$category', 
                subCategory = '$subCategory', 
                quantity = $quantity, 
                price = $price, 
                status = '$status', 
                lastUpdate = NOW() 
                WHERE id = $item_id";
    }
    
    if (mysqli_query($connection, $sql)) {
        $response['status'] = 'success';
        $response['message'] = 'Item updated successfully';
    } else {
        $response['message'] = 'Database error: ' . mysqli_error($connection);
    }
}

echo json_encode($response);
mysqli_close($connection);

