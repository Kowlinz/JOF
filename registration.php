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
    <div class="login-background" style="background-color: #171717;">
        <div class="container">
            <div class="login-container fade-in">

                <div class="text-center mb-4">
                    <img src="css/images/jof_logo_black.png" alt="Logo" style="max-width: 60px; height: auto;">
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
                    
                            $mail->setFrom('jackoffadeswebsite@gmail.com', 'Jack of Fades');
                            $mail->addAddress($email);
                    
                            $mail->isHTML(true);
                            $mail->Subject = 'Email Verification from JOF';
                            $mail->Body = "
                            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #ffffff;'>

                                <div style='max-width: 600px; background: #121212; padding: 20px; margin: auto; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);'>
                                    <img src='cid:jof_logo' alt='Jack of Fades Logo' 
                                    style='max-width: 90px; display: block; margin: 20px auto;'>

                                    <h2 style='color: #F3CD32; text-align: center;'>Welcome to Jack of Fades, $FirstName!</h2>
                                    <p style='font-size: 16px; color: #fff; text-align: center;'>
                                        Thank you for signing up. To complete your registration, please verify your email address by clicking the button below:
                                    </p>
                                    <div style='text-align: center; margin: 20px 0;'>
                                        <a href='http://localhost/JOF/verify_email.php?token=$verify_token' 
                                           style='background: #F3CD32; color: #121212; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-size: 18px;'>
                                           Verify Email
                                        </a>
                                    </div>
                                    <p style='font-size: 14px; color: #fff; text-align: center;'>
                                        If you did not sign up for an account, you can ignore this email.
                                    </p>
                                    <hr style='border: 0; height: 1px; background: #ddd;'>
                                    <p style='font-size: 14px; color: #F3CD32; text-align: center;'>
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
                        // check if password contains uppercase and number
                        if (!preg_match('/^(?=.*[A-Z])(?=.*\d).+$/', $password)) {
                            array_push($errors, "Password must contain at least one uppercase letter and one number");
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
                                    $_SESSION['status'] = "You are registered successfully! Please check your email for verification. (If not in inbox check on spam)";
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

                        <div class="form-group position-relative">
                            <input type="password" class="form-control" name="password" placeholder="Password" required maxlength="20" oninput="limitPasswordLength(this)">
                            <i class="bi bi-eye-slash password-toggle" id="togglePassword1"></i>
                        </div> 
                        
                        <div class="form-text text-muted mb-3">
                            <span>• At least 8 characters</span>
                            <br><span>• At least one uppercase</span>
                            <br><span>• At least one number</span>
                        </div>

                        <div class="form-group position-relative">
                            <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password" required maxlength="20" oninput="limitPasswordLength(this)">
                            <i class="bi bi-eye-slash password-toggle" id="togglePassword2"></i>
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

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .password-toggle:hover {
            color: #000;
        }

        .form-text span {
            transition: color 0.3s ease;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality for the first password field
        const togglePassword1 = document.getElementById('togglePassword1');
        const passwordInput1 = document.querySelector('input[name="password"]');

        // Add password validation
        function validatePassword(password) {
            const minLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            return {
                minLength,
                hasUpperCase,
                hasNumber
            };
        }

        function updatePasswordFeedback(validationResult) {
            const bullets = document.querySelectorAll('.form-text.text-muted.mb-3 span');
            
            // Update the first bullet point
            bullets[0].style.color = validationResult.minLength ? 'green' : '#6c757d';
            // Update the second bullet point
            bullets[1].style.color = validationResult.hasUpperCase ? 'green' : '#6c757d';
            // Update the third bullet point
            bullets[2].style.color = validationResult.hasNumber ? 'green' : '#6c757d';
        }

        passwordInput1.addEventListener('input', function() {
            const validationResult = validatePassword(this.value);
            updatePasswordFeedback(validationResult);
        });

        togglePassword1.addEventListener('click', function() {
            const type = passwordInput1.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput1.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Password toggle functionality for the repeat password field
        const togglePassword2 = document.getElementById('togglePassword2');
        const passwordInput2 = document.querySelector('input[name="repeat_password"]');

        togglePassword2.addEventListener('click', function() {
            const type = passwordInput2.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput2.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    });
    </script>
</body>
</html>