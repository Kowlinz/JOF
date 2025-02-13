<?php
session_start();
include 'db_connect.php';

// Get payment details
$appointmentID = $_POST["appointmentID"];
$gcashRef = $_POST["gcashRef"];
$amount = $_POST["amount"];

// Handle the file upload for payment proof
$paymentProof = file_get_contents($_FILES["paymentProof"]["tmp_name"]);

// Update payment status in database
$updateQuery = "UPDATE appointment_tbl SET payment_amount = ?, gcash_reference = ?, payment_proof = ? WHERE appointmentID = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("dssi", $amount, $gcashRef, $paymentProof, $appointmentID);
$stmt->send_long_data(2, $paymentProof); // Send large binary data
$stmt->execute();
$stmt->close();

header("Location: appointment.php");
exit();
?>