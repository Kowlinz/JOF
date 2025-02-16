<?php
session_start();
include 'database.php';

if (isset($_GET['token']))
{
    $token = $_GET['token'];
    $verify_query = "SELECT verify_token, verify_status, temp_email FROM customer_tbl WHERE verify_token='$token'";
    $verify_query_run = mysqli_query($conn, $verify_query);

    if(mysqli_num_rows($verify_query_run) > 0)
    {
        $row = mysqli_fetch_array($verify_query_run);
        if($row['verify_status'] == "0")
        {
            $clicked_token = $row['verify_token'];
            
            // Check if this is an email change (temp_email exists)
            if($row['temp_email'] !== NULL) {
                // Update email with temp_email
                $update_query = "UPDATE customer_tbl SET 
                    email = temp_email,
                    temp_email = NULL,
                    verify_status = '1',
                    verify_token = NULL 
                    WHERE verify_token='$clicked_token'";
                $update_query_run = mysqli_query($conn, $update_query);

                if($update_query_run) {
                    $_SESSION['status'] = "Email changed and verified successfully";
                    header("location: login.php");
                    exit(0);
                }
            } else {
                // Regular email verification for new account
                $update_query = "UPDATE customer_tbl SET verify_status='1' WHERE verify_token='$clicked_token'";
                $update_query_run = mysqli_query($conn, $update_query);

                if($update_query_run) {
                    $_SESSION['status'] = "Verification Success";
                    header("location: login.php");
                    exit(0);
                }
            }
            
            $_SESSION['status'] = "Verification Failed";
            header("location: login.php");
            exit(0);
        }
        else
        {
            $_SESSION['status'] = "Email Already Verified please login";
            header("location: login.php"); 
            exit(0);
        }
    } 
    else 
    {
        $_SESSION['status'] = "This Token does not Exist";
        header("location: login.php"); 
    }
}
else
{
    $_SESSION['status'] = "Not Allowed";
    header("location: login.php"); 
}

?>