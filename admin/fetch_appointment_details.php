<?php
include 'db_connect.php'; // Ensure correct DB connection

// Ensure no whitespace or output before this point
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error output to prevent it from corrupting JSON

// Buffer all output
ob_start();

try {
    if (!isset($_GET['appointmentID'])) {
        throw new Exception('No appointment ID provided');
    }

    $appointmentID = mysqli_real_escape_string($conn, $_GET['appointmentID']);
    
    // Get all data including payment_proof in one query
    $query = "SELECT 
        a.*,
        s.serviceName as hcName,
        ad.addonName,
        a.feedback
    FROM appointment_tbl a
    LEFT JOIN service_tbl s ON a.serviceID = s.serviceID
    LEFT JOIN addon_tbl ad ON a.addonID = ad.addonID
    WHERE a.appointmentID = ?";

    if (!$stmt = mysqli_prepare($conn, $query)) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }

    if (!mysqli_stmt_bind_param($stmt, "i", $appointmentID)) {
        throw new Exception('Failed to bind parameters: ' . mysqli_stmt_error($stmt));
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to execute statement: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception('Failed to get result: ' . mysqli_error($conn));
    }

    if ($row = mysqli_fetch_assoc($result)) {
        $response = array(
            'addonName' => $row['addonName'] ?? 'N/A',
            'hcName' => $row['hcName'] ?? 'N/A',
            'remarks' => $row['remarks'] ?? 'N/A',
            'paymentStatus' => $row['payment_status'] ?? 'Pending',
            'paymentAmount' => $row['payment_amount'] ?? '0.00',
            'gcashReference' => $row['gcash_reference'] ?? 'N/A',
            'feedback' => $row['feedback'] ?? 'No feedback provided'
        );
    } else {
        throw new Exception('No appointment found with ID: ' . $appointmentID);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Clear any output buffered so far
    ob_clean();
    
    // Send the JSON response
    echo json_encode($response);
} catch (Exception $e) {
    // Log the detailed error
    error_log("Error in fetch_appointment_details.php: " . $e->getMessage());
    
    ob_clean();
    // Return a more specific error message to the client
    echo json_encode([
        'error' => $e->getMessage(),
        'details' => 'Please check the server logs for more information'
    ]);
}
?>
