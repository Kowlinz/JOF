<?php
session_start();
require 'db_connect.php'; // Ensure this file connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve customer ID from session
    if (!isset($_SESSION['customerID'])) {
        die("Error: Customer not logged in.");
    }
    
    $customerID = $_SESSION['customerID'];
    
    // Fetch and sanitize input data
    $serviceID = $_POST['serviceID'] ?? null;
    $addonID = !empty($_POST['addonID']) ? $_POST['addonID'] : NULL;
    $date = $_POST['date'] ?? null;
    $timeSlot = $_POST['timeSlot'] ?? null;
    $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
    $paymentOption = $_POST['paymentOption'] ?? null;
    $gcashRef = $_POST['gcashRef'] ?? null;
    $paymentAmount = $_POST['amount'] ?? null;
    
    // Validate required fields
    if (!$serviceID || !$date || !$timeSlot || !$paymentOption || !$gcashRef || !$paymentAmount) {
        die("Error: Missing required fields.");
    }
    
    // Determine payment status based on payment option
    if ($paymentOption === 'full') {
        $payment_status = 'paid';
    } elseif ($paymentOption === 'downpayment') {
        $payment_status = 'partial';
    } else {
        die("Error: Invalid payment option.");
    }
    
    // Get payment proof (BLOB storage)
    $paymentProof = NULL;
    if (isset($_FILES['paymentProof']) && $_FILES['paymentProof']['error'] === UPLOAD_ERR_OK) {
        $paymentProof = file_get_contents($_FILES['paymentProof']['tmp_name']);
    }
    
    // Default appointment status
    $status = 'Pending';
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO appointment_tbl 
        (customerID, serviceID, addonID, date, timeSlot, remarks, payment_status, payment_amount, gcash_reference, payment_proof, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("iiissssdsss", 
        $customerID, 
        $serviceID, 
        $addonID,  // Accepts NULL
        $date, 
        $timeSlot, 
        $remarks,  // Accepts NULL
        $payment_status, 
        $paymentAmount, 
        $gcashRef, 
        $paymentProof, 
        $status
    );
    
    if ($stmt->execute()) {
        header("Location: appointment.php"); // Redirect after successful booking
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
}
?>