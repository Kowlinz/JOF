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
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-background" style="background-image: url(css/images/barbershop.jpg);">
        <div class="container">
            <div class="login-container fade-in">

                <?php 
                    // validate the submit button
                    if (isset($_POST["Register"])){
                        $FirstName = $_POST["FirstName"];
                        $MiddleName = $_POST["MiddleName"];
                        $LastName = $_POST["LastName"];
                        $dateOfBirth = $_POST["dateOfBirth"];
                        $email = $_POST["Email"];
                        $contactNum = $_POST["contactNum"];
                        $password = $_POST["password"];
                        $RepeatPassword = $_POST["repeat_password"];
                        
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                        $errors = array();
                        // validate if all fields are empty
                        if (empty ($FirstName) OR empty ($MiddleName) OR empty ($LastName) OR empty ($dateOfBirth) OR empty ($email) OR empty ($contactNum) OR empty ($password) OR empty ($RepeatPassword)) {
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
                        if(!$password = $RepeatPassword){
                            array_push($errors, "Password does not match");
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
                            $sql = "INSERT INTO customer_tbl (firstName, middleName, lastName, dateOfBirth, email, contactNum, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            
                            // initializes a statement and returns an object suitable for mysqli_stmt_prepare()
                            $stmt = mysqli_stmt_init($conn); 
                            $preparestmt = mysqli_stmt_prepare($stmt, $sql);
                            
                            if ($preparestmt) {
                                mysqli_stmt_bind_param($stmt, "sssssss", $FirstName, $MiddleName, $LastName, $dateOfBirth, $email, $contactNum, $passwordHash);
                                mysqli_stmt_execute($stmt);
                                echo "<div class = 'alert alert-success'> You are registered succesfully! </div>";
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
                            <input type="text" class="form-control" name="MiddleName" placeholder="Middle Name" required>
                        </div> 

                        <div class="form-group">
                            <input type="text" class="form-control" name="LastName" placeholder="Last Name" required>
                        </div> 

                        <div class="form-group">
                            <input type="date" class="form-control" name="dateOfBirth" placeholder="Date of Birth" required>
                        </div> 

                        <div class="form-group">
                            <input type="email" class="form-control" name="Email" placeholder="Email" required>
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control" name="contactNum" placeholder="Contact Number" required>
                        </div> 

                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="Password" required> 
                        </div> 

                        <div class="form-group">
                            <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password" required>
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