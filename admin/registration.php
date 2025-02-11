<?php
    session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | Registration</title>

    <!-- Link to Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Link to custom CSS -->
    <link rel="stylesheet" href="../css/login.css">
    
    <!-- Link to Favicon -->
    <link rel="icon" href="../css/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="login-background" style="background-color: #171717;">
        <div class="container">
            <div class="login-container">

                <div class="d-flex justify-content-start mb-4">
                    <a href="barbers.php" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <!-- Add the logo above the Register header -->
                <div class="text-center mb-4">
                    <img src="../css/images/jof_logo_black.png" alt="Logo" style="max-width: 60px; height: auto;">
                </div>

                <?php 
                    // validate the submit button
                    if (isset($_POST["Register"])){
                        $FirstName = $_POST["FirstName"];
                        $MiddleName = $_POST["MiddleName"];
                        $LastName = $_POST["LastName"];
                        $email = $_POST["Email"];
                        $contactNum = $_POST["contactNum"];
                        $password = $_POST["password"];
                        $RepeatPassword = $_POST["repeat_password"];

                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                        $errors = array();
                        // validate if all fields are empty
                        if (empty ($FirstName) OR empty ($LastName) OR empty ($email) OR empty ($contactNum) OR empty ($password) OR empty ($RepeatPassword)) {
                            array_push($errors, "All fields are required"); 
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

                        require_once "db_connect.php"; 

                        // email validation
                        $sql = "SELECT * FROM barbers_tbl WHERE email = '$email'";
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
                            require_once "db_connect.php";
                            $sql = "INSERT INTO barbers_tbl (firstName, middleName, lastName, email, contactNum, password, availability) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            
                            // initializes a statement and returns an object suitable for mysqli_stmt_prepare()
                            $stmt = mysqli_stmt_init($conn); 
                            $preparestmt = mysqli_stmt_prepare($stmt, $sql);
                            
                            if ($preparestmt) {
                                $Availability = "Available";

                                mysqli_stmt_bind_param($stmt, "sssssss", $FirstName, $MiddleName, $LastName, $email, $contactNum, $passwordHash, $Availability);
                                mysqli_stmt_execute($stmt);
                                echo "<div class = 'alert alert-success'> Barber registered succesfully! </div>";
                            } else {
                                die("Something went wrong!");
                            }
                        }
                    }
                    ?>

                    <h2 class="login-header">Register a Barber</h2>

                    <!-- Registration form -->
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

                        <div class="form-group position-relative">
                            <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password" required maxlength="20" oninput="limitPasswordLength(this)">
                            <i class="bi bi-eye-slash password-toggle" id="togglePassword2"></i>
                        </div> 

                        <div class="form-btn">
                            <input type="submit" class="btn btn-primary" value="Register" name="Register">
                        </div>
                    
                    </form>
                    
                    <div>
                    <!-- Link to registration page for new users -->
                    <!-- Remove the Cancel button -->
                    <!-- <p><a href="barbers.php">Cancel</a></p> -->
                    </div>
            </div>
        </div>
    </div>

    <style>
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
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality for the first password field
        const togglePassword1 = document.getElementById('togglePassword1');
        const passwordInput1 = document.querySelector('input[name="password"]');

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