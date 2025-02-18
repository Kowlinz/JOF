<?php
session_start();

// Check if the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include the database connection file
include 'db_connect.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$appointmentID = isset($_POST['appointmentID']) ? $_POST['appointmentID'] : null;
$reason = isset($_POST['reason']) ? $_POST['reason'] : null;
$customerID = $_SESSION['customerID'];

// Validate required parameters
if (!$appointmentID || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Update appointment status
    $updateSql = "UPDATE appointment_tbl SET 
                  status = 'Cancelled', 
                  reason = ?,
                  payment_status = 'cancelled'
                  WHERE appointmentID = ? AND customerID = ?";
    
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sii", $reason, $appointmentID, $customerID);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
    } else {
        throw new Exception("Failed to update appointment");
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>