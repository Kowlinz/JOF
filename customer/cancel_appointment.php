<?php
session_start();

// Check if the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    header("Location: ../login.php");
    exit();
}

// Include the database connection file
include 'db_connect.php';

if (isset($_GET['appointmentID'])) {
    $appointmentID = $_GET['appointmentID'];
    $reason = isset($_GET['reason']) ? $_GET['reason'] : '';  // If no reason is provided, set it as an empty string

    $sql = "UPDATE appointment_tbl SET status = 'Cancelled', reason = ? WHERE appointmentID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $reason, $appointmentID);
    $stmt->execute();

    header("Location: appointment.php");
    exit();
}
?>