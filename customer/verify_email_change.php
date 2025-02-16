<?php
session_start();
require '../database.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token exists and is valid
    $verify_query = "SELECT customerID, temp_email, verify_status FROM customer_tbl WHERE verify_token = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row['verify_status'] == "0") {
            // Update email and verification status
            $update_query = "UPDATE customer_tbl SET email = temp_email, verify_status = 1, temp_email = NULL, verify_token = NULL WHERE verify_token = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("s", $token);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Email verification successful! Your email has been updated.";
            } else {
                $_SESSION['error'] = "Verification failed. Please try again.";
            }
        } else {
            $_SESSION['error'] = "Email already verified.";
        }
    } else {
        $_SESSION['error'] = "Invalid verification token.";
    }
} else {
    $_SESSION['error'] = "No verification token provided.";
}

header('Location: account.php');
exit(); 