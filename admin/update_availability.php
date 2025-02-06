<?php
session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
    }
    
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barberID = $_POST['barberID'];
    $availability = $_POST['availability'];

    $sql = "UPDATE barbers_tbl SET availability = ? WHERE barberID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $availability, $barberID);

    $response = array();
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Availability updated successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'Error updating availability';
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
