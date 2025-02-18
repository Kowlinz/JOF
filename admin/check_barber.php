<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

include 'db_connect.php';

if (isset($_GET['appointmentID'])) {
    $appointmentID = mysqli_real_escape_string($conn, $_GET['appointmentID']);
    
    // Check if a barber is assigned to this appointment
    $query = "SELECT barberID FROM barb_apps_tbl WHERE appointmentID = '$appointmentID'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $response = array(
            'hasBarber' => !empty($row['barberID'])
        );
    } else {
        $response = array(
            'hasBarber' => false,
            'error' => mysqli_error($conn)
        );
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('Content-Type: application/json');
    echo json_encode(array('hasBarber' => false, 'error' => 'No appointment ID provided'));
}
?> 