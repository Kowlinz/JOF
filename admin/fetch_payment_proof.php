<?php
include 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['appointmentID'])) {
    $appointmentID = intval($_GET['appointmentID']);
    
    $query = "SELECT payment_proof FROM appointment_tbl WHERE appointmentID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $appointmentID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $imageData);
        mysqli_stmt_fetch($stmt);
        
        if ($imageData) {
            // Convert BLOB to base64
            $base64Image = base64_encode($imageData);
            echo json_encode([
                'success' => true,
                'image' => 'data:image/jpeg;base64,' . $base64Image
            ]);
            exit;
        }
    }
}

echo json_encode([
    'success' => false,
    'message' => 'No image found'
]);
?> 