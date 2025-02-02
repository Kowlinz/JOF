<?php
    session_start();
    if (isset($_SESSION["user"])) {
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="icon" href="css/images/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-background" style="background-image: url(css/images/barbershop.jpg);">
        <div class="container">
            <div class="login-container fade-in">

                <div class="d-flex justify-content-start mb-4">
                    <a href="index.php" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <?php   
                    use PHPMailer\PHPMailer\PHPMailer;
                    use PHPMailer\PHPMailer\SMTP;
                    use PHPMailer\PHPMailer\Exception;
                    
                    //Load Composer's autoloader
                    require 'vendor/autoload.php';

                    function sendemail_verify($FirstName, $LastName, $email, $verify_token) {
                        $mail = new PHPMailer(true);
                    
                        try {
                            //$mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for debugging
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'jackoffadeswebsite@gmail.com'; // Your Gmail Address
                            $mail->Password = 'edol rcjc oakv imen';       // Your Gmail App Password
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;
                    
                            $mail->setFrom('jackoffadeswebsite@gmail.com');
                            $mail->addAddress($email);
                    
                            $mail->isHTML(true);
                            $mail->Subject = 'Email Verification from JOF';
                            $mail->Body = "
                                <h2>You have registered your email</h2>
                                <h5>Verify your email address to login with the given link</h5>
                                <br/><br/>
                                <a href='http://localhost/JOF/verify_email.php?token=$verify_token'>Click Me</a>
                            ";
                    
                            $mail->send();
                        } catch (Exception $e) {
                            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        }
                    }

                    // validate the submit button
                    if (isset($_POST["Register"])){
                        $FirstName = $_POST["FirstName"];
                        $MiddleName = $_POST["MiddleName"];
                        $LastName = $_POST["LastName"];
                        $email = $_POST["Email"];
                        $contactNum = $_POST["contactNum"];
                        $password = $_POST["password"];
                        $RepeatPassword = $_POST["repeat_password"];
                        $verify_token = md5(rand());
                        
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                        $errors = array();
                        // validate if all fields are empty
                        if (empty ($FirstName) OR empty ($LastName) OR empty ($email) OR empty ($password) OR empty ($RepeatPassword)) {
                            array_push($errors, "All fields are required except Middle Name"); 
                        }
                        // validate if the email is not validated 
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            array_push($errors, "Email is not valid");
                        }
                        // password should not be less than 8 
                        if (strlen($password)<8) {
                            array_push($errors, "Password must be at least 8 characters long");
                        }
                        // check if password is the same 
                        if($password !== $RepeatPassword){
                            array_push($errors, "Password does not match");
                        }
                        // validate contact number length and numeric value
                        if (!preg_match('/^[0-9]{11}$/', $contactNum)) {
                            array_push($errors, "Contact number must be 11 digits and numeric");
                        }

                        require_once "database.php"; 

                        // email validation
                        $sql = "SELECT * FROM customer_tbl WHERE email = '$email'";
                        $result = mysqli_query($conn, $sql);
                        $rowCount = mysqli_num_rows($result);
                        if ($rowCount>0) {
                            array_push($errors, "Email Already Exist!");
                        }

                        if (count($errors)>0){
                            foreach($errors as $error) {
                                echo "<div class='alert alert-danger'>$error</div>";
                            }
                        } else {
                            require_once "database.php";
                            $sql = "INSERT INTO customer_tbl (firstName, middleName, lastName, email, contactNum, password, verify_token) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            
                            // initializes a statement and returns an object suitable for mysqli_stmt_prepare()
                            $stmt = mysqli_stmt_init($conn); 
                            $preparestmt = mysqli_stmt_prepare($stmt, $sql);
                            
                            if ($preparestmt) {
                                sendemail_verify("$FirstName", "$LastName", "$email", "$verify_token");

                                mysqli_stmt_bind_param($stmt, "sssssss", $FirstName, $MiddleName, $LastName, $email, $contactNum, $passwordHash, $verify_token);
                                mysqli_stmt_execute($stmt);
                                    // Store success message in session
                                    $_SESSION['status'] = "You are registered successfully! Please check your email for verification.";
                                    $_SESSION['status_type'] = "success"; // Set alert type to success

                                    // Redirect to login page
                                    header("location: login.php");
                                    exit(0);
                            } else {
                                die("Something went wrong!");
                            }
                        }
                    }
                    ?>
                    <h2 class="login-header">Register</h2>
                    <form action="registration.php" method="post">

                        <div class="form-group">
                            <input type="text" class="form-control" name="FirstName" placeholder="First Name" required>
                        </div> 

                        <div class="form-group">
                            <input type="text" class="form-control" name="MiddleName" placeholder="Middle Name">
                        </div> 

                        <div class="form-group">
                            <input type="text" class="form-control" name="LastName" placeholder="Last Name" required>
                        </div> 

                        <div class="form-group">
                            <input type="email" class="form-control" name="Email" placeholder="Email" required>
                        </div>

                        <div class="form-group">
                            <input type="tel" class="form-control" name="contactNum" placeholder="Contact Number" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)" title="Contact number must be 11 digits">
                        </div> 

                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="Password" required maxlength="20" oninput="limitPasswordLength(this)">
                        </div> 

                        <div class="form-group">
                            <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password" required maxlength="20" oninput="limitPasswordLength(this)">
                        </div> 

                        <div class="form-btn">
                            <input type="submit" class="btn btn-primary" value="Register" name="Register">
                        </div>
                    </form>
                    <div>
                     <p>Already registered? <a href="login.php">Login</a></p>
                    </div>
            </div>
        </div>
    </div>
    <style>
        .fade-in {
            animation: fadeIn 1s ease-out;
            opacity: 1;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html>