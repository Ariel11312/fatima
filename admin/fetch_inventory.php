<?php
include '../admin/database/connection.php'; // Include the database connection file

$sql = "
    SELECT *, 'bedroom' AS category FROM bedroom
    UNION ALL
    SELECT *, 'livingroom' AS category FROM livingroom
    UNION ALL
    SELECT *, 'diningroom' AS category FROM diningroom
    UNION ALL
    SELECT *, 'office' AS category FROM office
";

$query = mysqli_query($connection, $sql);
$results = array();

while ($row = mysqli_fetch_assoc($query)) {
    $results[] = $row;
}

header('Content-Type: application/json');
echo json_encode($results);
?>
