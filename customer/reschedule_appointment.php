<?php
session_start();
include 'db_connect.php';

// Check if user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get POST data
$appointmentID = isset($_POST['appointmentID']) ? $_POST['appointmentID'] : null;
$newDate = isset($_POST['newDate']) ? $_POST['newDate'] : null;
$newTime = isset($_POST['newTime']) ? $_POST['newTime'] : null;
$customerID = $_SESSION['customerID'];

// Validate required parameters
if (!$appointmentID || !$newDate || !$newTime) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Update appointment
    $updateSql = "UPDATE appointment_tbl SET 
                  date = ?,
                  timeSlot = ?,
                  status = 'Pending'
                  WHERE appointmentID = ? AND customerID = ?";
    
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssii", $newDate, $newTime, $appointmentID, $customerID);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Appointment rescheduled successfully']);
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