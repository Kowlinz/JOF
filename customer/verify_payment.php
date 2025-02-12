<?php
session_start();
include 'db_connect.php';

// Get payment details
$appointmentID = $_POST["appointmentID"];
$gcashRef = $_POST["gcashRef"];
$amount = $_POST["amount"];

$uploadDir = 'uploads/'; // Ensure it's NOT inside 'customer/'
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create directory if missing
}

$fileName = null; // Default to null if no file uploaded

if (isset($_FILES["paymentProof"]) && $_FILES["paymentProof"]["error"] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES["paymentProof"]["tmp_name"];
    $fileOriginalName = $_FILES["paymentProof"]["name"];
    $fileExtension = pathinfo($fileOriginalName, PATHINFO_EXTENSION);

    // Generate a unique filename to avoid duplicates
    $fileName = uniqid() . "." . $fileExtension;
    $fileDestination = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $fileDestination)) {
        error_log("DEBUG: ✅ File successfully uploaded to - " . $fileDestination);
    } else {
        error_log("DEBUG: ❌ Failed to move uploaded file to - " . $fileDestination);
        $fileName = null; // Prevent incorrect database entry
    }
} else {
    error_log("DEBUG: 🛑 No valid file uploaded or file upload error.");
}

// Update payment information in database
$updateQuery = "UPDATE appointment_tbl SET payment_amount = ?, gcash_reference = ?, payment_proof = ? WHERE appointmentID = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("dssi", $amount, $gcashRef, $fileName, $appointmentID);
$stmt->execute();
$stmt->close();

header("Location: appointment.php");
exit();
?>