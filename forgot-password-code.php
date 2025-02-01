<?php
session_start();
include 'database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

function send_password_reset($get_firstName, $get_lastName, $get_email, $token) {
    $mail = new PHPMailer(true);

    try {
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for debugging
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jackoffadeswebsite@gmail.com'; // Your Gmail address
        $mail->Password = 'edol rcjc oakv imen';       // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('jackoffadeswebsite@gmail.com', "$get_firstName $get_lastName");
        $mail->addAddress($get_email);

        $mail->isHTML(true);
        $mail->Subject = 'Account JOF Reset Password';
        $mail->Body = "
            <h2>Hello</h2>
            <h3>You are receiving this email because we received a password reset request from your account</h3>
            <br/><br/>
            <a href='http://localhost/JOF/password-change.php?token=$token&email=$get_email'>Click Me</a>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

if(isset($_POST['password_reset_link']))
{
    $email = mysqli_real_escape_string ($conn, $_POST['email']);
    $token = md5(rand());
    
    $check_email = "SELECT email FROM customer_tbl WHERE email='$email'";
    $check_email_run = mysqli_query($conn, $check_email);

    if(mysqli_num_rows($check_email_run) > 0)
    {
        $row = mysqli_fetch_array($check_email_run);
        $get_firstName = $row['firstName'];
        $get_lastName = $row['lastName'];
        $get_email = $row['email'];

        $update_token = "UPDATE customer_tbl SET verify_token='$token' WHERE email = '$get_email' ";
        $update_token_run = mysqli_query($conn, $update_token);

        if($update_token_run)
        {
            send_password_reset($get_firstName, $get_lastName, $get_email, $token);
            $_SESSION['status'] = "Password reset link has been sent to your email";
            header("location: forgot-password.php"); 
            exit(0);

        }
        else
        {
            $_SESSION['status'] = "Something went wrong";
            header("location: forgot-password.php"); 
            exit(0);
        }
    }
    else
    {
        $_SESSION['status'] = "No email found";
        header("location: forgot-password.php"); 
        exit(0);
    }
}

if (isset($_POST['password_update'])) {
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $token = mysqli_real_escape_string($conn, $_POST['password_token']);

    if (!empty($token)) {
        if (!empty($email) && !empty($new_password) && !empty($confirm_password)) {
            // Check if token is valid
            $check_token = "SELECT verify_token FROM customer_tbl WHERE verify_token='$token'";
            $check_token_run = mysqli_query($conn, $check_token);

            if (mysqli_num_rows($check_token_run) > 0) {
                if ($new_password == $confirm_password) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                    $update_password = "UPDATE customer_tbl SET password='$hashed_password', verify_token='' WHERE verify_token='$token'";
                    $update_password_run = mysqli_query($conn, $update_password);

                    if ($update_password_run) {
                        $new_token = md5(rand());
                        $update_to_new_token = "UPDATE customer_tbl SET verify_token='$new_token' WHERE verify_token='$token'";
                        $update_to_new_token_run = mysqli_query($conn, $update_to_new_token);

                        $_SESSION['status'] = "New password has been successfully updated";
                        header("location: login.php");
                        exit(0);
                    } else {
                        $_SESSION['status'] = "Did not update password, something went wrong";
                        header("location: password-change.php?token=$token&email=$email");
                        exit(0);
                    }
                } else {
                    $_SESSION['status'] = "Password and Confirm password do not match";
                    header("location: password-change.php?token=$token&email=$email");
                    exit(0);
                }
            } else {
                $_SESSION['status'] = "Invalid Token";
                header("location: password-change.php?token=$token&email=$email");
                exit(0);
            }
        } else {
            $_SESSION['status'] = "All fields are required";
            header("location: password-change.php?token=$token&email=$email");
            exit(0);
        }
    } else {
        $_SESSION['status'] = "No token found";
        header("location: forgot-password.php");
        exit(0);
    }
}
?>