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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="login-background" style="background-image: url(css/images/barbershop.jpg);">
        <div class="container">
            <div class="header">
                <nav class="navbar navbar-expand-lg py-4">
                    <div class="container ps-5">
                        <div class="navbar-brand">
                            <img src="css/images/jof_logo_yellow.png" alt="logo" width="45" height="45">
                        </div>

                        <button class="menu-btn d-lg-none" type="button">
                            <i class='bx bx-menu'></i>
                        </button>

                        <div class="menu-dropdown">
                            <div class="menu-header">
                                <button class="menu-close">&times;</button>
                            </div>
                            <div class="menu-links">
                                <a href="index.php" class="menu-link">HOME</a>
                                <?php if (isset($_SESSION["user"])): ?>
                                    <a href="haircuts.php" class="menu-link">HAIRCUTS</a>
                                    <a href="customer/appointment.php" class="menu-link">MY APPOINTMENT</a>
                                <?php else: ?>
                                    <a href="haircuts.php" class="menu-link">HAIRCUTS</a>
                                    <a href="login.php" class="menu-link">LOGIN</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

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
                <form action="login.php" method="post">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <!-- <div class="forgot-password">
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div> -->
                    <div class="form-btn">
                        <input type="submit" value="Login" name="Login" class="btn btn-primary">
                    </div>
                </form>
                <div>
                    <p>New User? <a href="registration.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuBtn = document.querySelector('.menu-btn');
        const menuDropdown = document.querySelector('.menu-dropdown');
        const menuClose = document.querySelector('.menu-close');

        menuBtn.addEventListener('click', function() {
            menuDropdown.classList.add('show');
        });

        menuClose.addEventListener('click', function() {
            menuDropdown.classList.remove('show');
        });
    });
    </script>
</body>
</html>
