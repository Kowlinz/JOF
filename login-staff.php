<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION["user"])) {
    // Check the user's role
    if (isset($_SESSION["role"])) {
        if ($_SESSION["role"] === "admin") {
            header("Location: admin/a_dashboard.php");
            exit(); // Stop further script execution
        } elseif ($_SESSION["role"] === "barber") {
            header("Location: barber/b_dashboard.php");
            exit();
        } else {
            // Handle case for unexpected roles
            echo "Unauthorized access!";
            exit();
        }
    } 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | Staff Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="css/images/favicon.ico">
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
                    </div>
                </nav>
            </div>

            <div class="login-container">

                <?php
                        if (isset($_POST["Login"])) {
                        $email = $_POST["email"];
                        $password = $_POST["password"];
                        
                        require_once "database.php";
                        
                        // Check if user exists in the barber table
                        $sql_barber = "SELECT * FROM barbers_tbl WHERE email = '$email'";
                        $result_barber = mysqli_query($conn, $sql_barber);
                        $user_barber = mysqli_fetch_array($result_barber, MYSQLI_ASSOC);
                        
                        // Check if user exists in the admin table
                        $sql_admin = "SELECT * FROM admin_tbl WHERE email = '$email'";
                        $result_admin = mysqli_query($conn, $sql_admin);
                        $user_admin = mysqli_fetch_array($result_admin, MYSQLI_ASSOC);
                        
                        // Validate the user based on the role
                        if ($user_barber) {
                            if (password_verify($password, $user_barber["password"])) {
                                $_SESSION["user"] = "barber";
                                $_SESSION["barberID"] = $user_barber["barberID"];
                                header("Location: ./barber/b_dashboard.php");
                                die();

                            } else {
                                echo "<div class='alert alert-danger'>Password and Email does not match</div>";
                            }

                        } elseif ($user_admin) {
                            if (password_verify($password, $user_admin["password"])) {
                                $_SESSION["user"] = "admin";
                                $_SESSION["adminID"] = $user_admin["adminID"];
                                header("Location: ./admin/a_dashboard.php");
                                die();

                            } else {
                                echo "<div class='alert alert-danger'>Password and Email does not match</div>";
                            }

                        } else {
                            echo "<div class='alert alert-danger'>Email does not exist or you don't have staff access</div>";
                        }
                    }
                ?>

                <h2 class="login-header">Staff Login</h2>
                <form action="login-staff.php" method="post">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group position-relative">
                        <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Password" required>
                        <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                    </div>
                    <div class="form-btn">
                        <input type="submit" value="Login" name="Login" class="btn btn-primary">
                    </div>
                </form>
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
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        togglePassword.addEventListener('click', function() {
            // Toggle the password input type
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle the eye icon
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    });
    </script>
</body>
</html> 