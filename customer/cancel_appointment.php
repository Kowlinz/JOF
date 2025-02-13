<?php
session_start();

// Check if the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    header("Location: ../login.php");
    exit();
}

// Include the database connection file
include 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['appointmentID']) && isset($_GET['reason'])) {
    $appointmentID = mysqli_real_escape_string($conn, $_GET['appointmentID']);
    $reason = mysqli_real_escape_string($conn, $_GET['reason']);
    $customerID = $_SESSION["customerID"];

    // Verify the appointment belongs to the customer
    $checkQuery = "SELECT * FROM appointment_tbl WHERE appointmentID = ? AND customerID = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "ii", $appointmentID, $customerID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Update the appointment status
        $updateQuery = "UPDATE appointment_tbl SET status = 'Cancelled', payment_status = 'Cancelled', reason = ? WHERE appointmentID = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "si", $reason, $appointmentID);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid appointment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
}

mysqli_close($conn);
?>