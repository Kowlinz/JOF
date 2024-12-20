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
    <title>Jack of Fades | Login</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Link to custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-background" style="background-image: url(css/images/barbershop.jpg);">
        <div class="container">
            <div class="login-container">

                <?php
                        if (isset($_POST["Login"])) {
                        $email = $_POST["email"];
                        $password = $_POST["password"];
                        
                        require_once "database.php";
                        
                        // Check if user exists in the customer table
                        $sql_customer = "SELECT * FROM customer_tbl WHERE email = '$email'";
                        $result_customer = mysqli_query($conn, $sql_customer);
                        $user_customer = mysqli_fetch_array($result_customer, MYSQLI_ASSOC);
                        
                        
                        // Validate the user based on the role
                        if ($user_customer) {
                            if (password_verify($password, $user_customer["password"])) {
                                $_SESSION["user"] = "customer";
                                $_SESSION["customerID"] = $user_customer["customerID"];
                                header("Location: index.php");
                                die();

                            } else {
                                echo "<div class='alert alert-danger'>Invalid Credentials</div>";
                            }

                        } else {
                            echo "<div class='alert alert-danger'>Email does not exist, register first</div>";
                        }
                    }
                ?>

                <h2 class="login-header">Login</h2>

                <!-- Login form -->
                <form action="login.php" method="post">
                    <div class="form-group">
                        <!-- Email input -->
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>

                    <div class="form-group">
                        <!-- Password input -->
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>

                    <div class="forgot-password">
                        <!-- Link to Forgot Password page -->
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>

                    <div class="form-btn">
                        <!-- Submit button for the form -->
                        <input type="submit" value="Login" name="Login" class="btn btn-primary">
                    </div>
                </form>

                <div>
                    <!-- Link to registration page for new users -->
                    <p>New User? <a href="registration.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
