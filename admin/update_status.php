<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

include 'db_connect.php';

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointmentID = $_POST['appointmentID'];
    $status = $_POST['status'];
    
    try {
        if ($status === 'Cancelled') {
            $reason = mysqli_real_escape_string($conn, $_POST['reason']);
            $query = "UPDATE appointment_tbl SET status = '$status', reason = '$reason' WHERE appointmentID = $appointmentID";
        } else {
            $query = "UPDATE appointment_tbl SET status = '$status' WHERE appointmentID = $appointmentID";
        }
        
        if (mysqli_query($conn, $query)) {
            $response['success'] = true;
            $response['message'] = "Appointment has been " . strtolower($status) . " successfully.";
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = "Error updating appointment status: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
