<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

include 'db_connect.php';

$response = array();

if (isset($_GET['appointmentID'])) {
    $appointmentID = $_GET['appointmentID'];
    
    // Check if a barber is assigned to this appointment
    $query = "SELECT barberID FROM barb_apps_tbl WHERE appointmentID = $appointmentID";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $response['hasBarber'] = true;
        $response['message'] = "Barber is assigned.";
    } else {
        $response['hasBarber'] = false;
        $response['message'] = "No barber assigned.";
    }
} else {
    $response['hasBarber'] = false;
    $response['message'] = "Invalid appointment ID.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 