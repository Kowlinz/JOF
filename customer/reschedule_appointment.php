<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointmentID = $_POST["appointmentID"];
    $newDate = $_POST["newDate"];
    $newTime = $_POST["newTime"];

    if (!$appointmentID || !$newDate || !$newTime) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    }

    // Update the appointment date and time without changing the status
    $sql = "UPDATE appointment_tbl SET date = ?, timeSlot = ? WHERE appointmentID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $newDate, $newTime, $appointmentID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to reschedule."]);
    }
}
?>