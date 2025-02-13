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

        $mail->setFrom('jackoffadeswebsite@gmail.com', 'Jack of Fades');
        $mail->addAddress($get_email);

        $mail->isHTML(true);
        $mail->Subject = 'Account JOF Reset Password';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                <div style='max-width: 600px; background: #121212; padding: 20px; margin: auto; border-radius: 8px; 
                            box-shadow: 0px 0px 10px rgba(0,0,0,0.1); text-align: center;'>

                    <img src='cid:jof_logo' alt='Jack of Fades Logo' 
                         style='max-width: 90px; display: block; margin: 20px auto;'>

                    <p style='font-size: 16px; color: #fff;'>
                        We received a request to reset your password for your Jack of Fades account.
                        Click the button below to set a new password.
                    </p>
                    <div style='margin: 20px 0;'>
                        <a href='http://localhost/JOF/password-change.php?token=$token&email=$get_email' 
                           style='background: #F3CD32; color: #121212; padding: 12px 20px; text-decoration: none; 
                                  border-radius: 5px; font-size: 18px;'>
                           Reset Password
                        </a>
                    </div>
                    <p style='font-size: 14px; color: #fff;'>
                        If you did not request this, you can safely ignore this email. Your password will not be changed.
                    </p>
                    <hr style='border: 0; height: 1px; background: #ddd;'>
                    <p style='font-size: 14px; color: #F3CD32;'>
                        Need help? Contact us at 
                        <a href='mailto:jackoffades11@gmail.com' style='color: #ffffff;'>jackoffades11@gmail.com</a>
                    </p>
                </div>
            </div>
        ";

        $mail->AddEmbeddedImage('css/images/jof_logo_yellow.png', 'jof_logo', 'jof_logo_yellow.png');

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
            $_SESSION['status_type'] = "success"; // Set alert to success
            header("location: forgot-password.php"); 
            exit(0);

        }
        else
        {
            $_SESSION['status'] = "Something went wrong";
            $_SESSION['status_type'] = "danger"; // Set alert to danger
            header("location: forgot-password.php"); 
            exit(0);
        }
    }
    else
    {
        $_SESSION['status'] = "No email found";
        $_SESSION['status_type'] = "danger"; // Set alert to danger
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
            
            $errors = []; // Initialize error array
    
            // Check if password is at least 8 characters long
            if (strlen($new_password) < 8) {
                $_SESSION['status'] = "Password must be at least 8 characters long";
                $_SESSION['status_type'] = "danger"; // Set alert to danger
                header("location: password-change.php?token=$token&email=$email");
                exit();
            }
    
            // Check if password contains at least one uppercase letter and one number
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d).+$/', $new_password)) {
                $_SESSION['status'] = "Password must contain at least one uppercase letter and one number";
                $_SESSION['status_type'] = "danger"; // Set alert to danger
                header("location: password-change.php?token=$token&email=$email");
                exit();
            }

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
                        $_SESSION['status_type'] = "success"; // Set alert to success
                        header("location: login.php");
                        exit(0);
                    } else {
                        $_SESSION['status'] = "Did not update password, something went wrong";
                        $_SESSION['status_type'] = "danger"; // Set alert to danger
                        header("location: password-change.php?token=$token&email=$email");
                        exit(0);
                    }
                } else {
                    $_SESSION['status'] = "Password does not match";
                    $_SESSION['status_type'] = "danger"; // Set alert to danger
                    header("location: password-change.php?token=$token&email=$email");
                    exit(0);
                }
            } else {
                $_SESSION['status'] = "This link is already used";
                $_SESSION['status_type'] = "danger"; // Set alert to danger
                header("location: password-change.php?token=$token&email=$email");
                exit(0);
            }
        } else {
            $_SESSION['status'] = "All fields are required";
            $_SESSION['status_type'] = "danger"; // Set alert to danger
            header("location: password-change.php?token=$token&email=$email");
            exit(0);
        }
    } else {
        $_SESSION['status'] = "No token found";
        $_SESSION['status_type'] = "danger"; // Set alert to danger
        header("location: forgot-password.php");
        exit(0);
    }
}
?>