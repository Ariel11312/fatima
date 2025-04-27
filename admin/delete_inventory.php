<?php 
include './database/connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);



header('Content-Type: application/json');
$response = [
    'status' => 'error',
    'message' => 'Unknown error occurred'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = mysqli_real_escape_string($connection, $_POST['item_id']);
    $category = mysqli_real_escape_string($connection, $_POST['category']);
    
    // First, get the image path to delete the file
    $sql = "SELECT image FROM $category WHERE id = $item_id";
    $result = mysqli_query($connection, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $imagePath = $row['image'];
        
        // Delete the item from database
        $deleteSql = "DELETE FROM $category WHERE id = $item_id";
        
        if (mysqli_query($connection, $deleteSql)) {
            // If deletion is successful
            $response['status'] = 'success';
            $response['message'] = 'Item deleted successfully';
        } else {
            // If deletion fails, capture the MySQL error
            $response['message'] = 'Database error: ' . mysqli_error($connection);
        }
    } else {
        $response['message'] = 'Item not found';
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
mysqli_close($connection);
?>