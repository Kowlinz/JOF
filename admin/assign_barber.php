<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

// Include database connection
include 'db_connect.php';

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointmentID = $_POST['appointmentID'];
    $barberID = $_POST['barberID'];
    
    // Check if a barber is already assigned
    $checkQuery = "SELECT barberID FROM barb_apps_tbl WHERE appointmentID = $appointmentID";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        // Update existing assignment
        $query = "UPDATE barb_apps_tbl SET barberID = $barberID WHERE appointmentID = $appointmentID";
    } else {
        // Create new assignment
        $query = "INSERT INTO barb_apps_tbl (appointmentID, barberID) VALUES ($appointmentID, $barberID)";
    }
    
    if (mysqli_query($conn, $query)) {
        $response['success'] = true;
        $response['message'] = "Barber assigned successfully.";
    } else {
        $response['success'] = false;
        $response['message'] = "Error assigning barber: " . mysqli_error($conn);
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
