<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointmentID = $_POST['appointmentID'];

    $deleteQuery = "DELETE FROM appointment_tbl WHERE appointmentID = '$appointmentID'";
    
    if (mysqli_query($conn, $deleteQuery)) {
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit();
    } else {
        echo "<script>alert('Error deleting appointment: " . mysqli_error($conn) . "'); window.location.href = document.referrer;</script>";
    }
}
?>
