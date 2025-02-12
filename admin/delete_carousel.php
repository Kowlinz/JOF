<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['barberId'])) {
    try {
        // Delete the database record
        $deleteQuery = "DELETE FROM barberpic_tbl WHERE barberpicID = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $data['barberId']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($conn->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in delete_carousel.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?> 