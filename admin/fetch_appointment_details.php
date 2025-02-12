<?php
include 'db_connect.php'; // Ensure correct DB connection

header('Content-Type: application/json'); // Set JSON response
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start(); // Prevent unwanted output

$response = [];

if (isset($_GET['appointmentID'])) {
    $appointmentID = intval($_GET['appointmentID']);

    $query = "
    SELECT 
        a.appointmentID, 
        COALESCE(ad.addonName, 'N/A') AS addonName, 
        COALESCE(hc.hcName, 'N/A') AS hcName, 
        COALESCE(a.remarks, 'N/A') AS remarks,
        COALESCE(a.payment_status, 'N/A') AS paymentStatus,
        COALESCE(a.payment_amount, '0.00') AS paymentAmount,
        COALESCE(a.gcash_reference, 'N/A') AS gcashReference,
        a.payment_proof AS paymentProof
    FROM appointment_tbl a
    LEFT JOIN addon_tbl ad ON a.addonID = ad.addonID
    LEFT JOIN haircut_tbl hc ON a.hcID = hc.hcID
    WHERE a.appointmentID = $appointmentID";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        $response['error'] = "Database query failed: " . mysqli_error($conn);
    } else {
        if ($row = mysqli_fetch_assoc($result)) {
            // If payment proof is stored as a file path, return the file URL
            if (!empty($row['paymentProof'])) {
                $filePath = 'customer/uploads/' . $row['paymentProof']; // Ensure correct folder
            
                if (file_exists($filePath)) {
                    error_log("DEBUG:  File found at path: " . $filePath);
                    $row['paymentProof'] = $filePath;
                } else {
                    error_log("DEBUG:  File does not exist at path: " . $filePath);
                    $row['paymentProof'] = null;
                }
            } else {
                error_log("DEBUG:  paymentProof column is empty or NULL");
                $row['paymentProof'] = null;
            }       
            $response = $row;
        } else {
            $response['error'] = "No appointment found";
        }
    }
} else {
    $response['error'] = "Invalid request: appointmentID missing";
}

ob_end_clean(); // Remove any unwanted output before JSON
echo json_encode($response);
exit;
?>
