<?php
session_start();
require '../database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (!isset($_SESSION['customerID']) || empty($_POST['newEmail']) || empty($_POST['password'])) {
    header('Location: account.php');
    exit();
}

$customerID = $_SESSION['customerID'];
$newEmail = $_POST['newEmail'];
$password = $_POST['password'];

// Verify password
$sql = "SELECT password FROM customer_tbl WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Incorrect password";
    header('Location: account.php');
    exit();
}

// Generate verification token
$verificationToken = bin2hex(random_bytes(32));

// Update customer table with new email and token
$sql = "UPDATE customer_tbl SET email = ?, verify_token = ?, verify_status = 0 WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $newEmail, $verificationToken, $customerID);

if ($stmt->execute()) {
    // Create verification link
    $verificationLink = "http://localhost/JOF/verify_email.php?token=" . $verificationToken;
    
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for debugging
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jackoffadeswebsite@gmail.com'; // Update to match registration.php
        $mail->Password = 'edol rcjc oakv imen';          // Update to match registration.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('jackoffadeswebsite@gmail.com', 'Jack of Fades');
        $mail->addAddress($newEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your New Email Address';
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #ffffff;'>
            <div style='max-width: 600px; background: #121212; padding: 20px; margin: auto; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);'>
                <img src='cid:jof_logo' alt='Jack of Fades Logo' 
                style='max-width: 90px; display: block; margin: 20px auto;'>

                <h2 style='color: #F3CD32; text-align: center;'>Email Address Change</h2>
                <p style='font-size: 16px; color: #fff; text-align: center;'>
                    Please click the button below to verify your new email address:
                </p>
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='$verificationLink' 
                       style='background: #F3CD32; color: #121212; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-size: 18px;'>
                       Verify Email
                    </a>
                </div>
                <p style='font-size: 14px; color: #fff; text-align: center;'>
                    If you did not request this change, you can ignore this email.
                </p>
                <hr style='border: 0; height: 1px; background: #ddd;'>
                <p style='font-size: 14px; color: #F3CD32; text-align: center;'>
                    Need help? Contact us at 
                    <a href='mailto:jackoffades11@gmail.com' style='color: #ffffff;'>jackoffades11@gmail.com</a>
                </p>
            </div>
        </div>";

        $mail->AddEmbeddedImage('../css/images/jof_logo_yellow.png', 'jof_logo', 'jof_logo_yellow.png');
        
        $mail->AltBody = "Please click the following link to verify your new email address:\n\n" . $verificationLink;

        $mail->send();
        $_SESSION['success'] = "Verification email has been sent to your new email address. Please verify to complete the change.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to send verification email. Please try again. Error: {$mail->ErrorInfo}";
    }
} else {
    $_SESSION['error'] = "Failed to update email. Please try again.";
}

header('Location: account.php');
exit(); 