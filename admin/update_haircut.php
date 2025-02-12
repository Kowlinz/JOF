<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['hcID']) && isset($data['hcName'])) {
    try {
        $stmt = $conn->prepare("UPDATE haircut_tbl SET hcName = ? WHERE hcID = ?");
        $stmt->bind_param("si", $data['hcName'], $data['hcID']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($conn->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in update_haircut.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
}
?> 