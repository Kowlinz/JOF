<?php
session_start();
include 'db_connect.php';

// Get payment details
$appointmentID = $_POST["appointmentID"];
$gcashRef = $_POST["gcashRef"];
$amount = $_POST["amount"];

// Update payment status
$updateQuery = "UPDATE appointment_tbl SET payment_amount = ?, gcash_reference = ? WHERE appointmentID = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("dsi", $amount, $gcashRef, $appointmentID);
$stmt->execute();
$stmt->close();

header("Location: appointment.php");
exit();
?>