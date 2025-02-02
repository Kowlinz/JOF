<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST["Login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    require_once "database.php";

    $sql = "SELECT * FROM customer_tbl WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user['verify_status'] == "1") { // Check if email is verified
            if (password_verify($password, $user["password"])) {
                $_SESSION["user"] = "customer";
                $_SESSION["customerID"] = $user["customerID"];
                $_SESSION['auth_user'] = [
                    'firstName' => $user['firstName'],
                    'lastName' => $user['lastName'],
                    'email' => $user['email'],
                ];
                $_SESSION['status'] = "You are logged in successfully.";
                $_SESSION['status_type'] = "success"; // Alert type
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['status'] = "Invalid credentials.";
                $_SESSION['status_type'] = "danger"; // Error alert
            }
        } else {
            $_SESSION['status'] = "Email not verified. Please check your email.";
            $_SESSION['status_type'] = "warning"; // Warning alert
        }
    } else {
        $_SESSION['status'] = "Email does not exist. Please register.";
        $_SESSION['status_type'] = "danger"; // Error alert
    }
    header("Location: login.php"); // Reload login page with message
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" href="css/images/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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

                        <button class="menu-btn d-lg-none" type="button" id="menuBtn">
                            <i class='bx bx-menu'></i>
                        </button>

                        <div class="menu-dropdown" id="menuDropdown">
                            <div class="menu-header">
                                <button class="menu-close" id="menuClose">&times;</button>
                            </div>
                            <div class="menu-links">
                                <a href="index.php" class="menu-link">HOME</a>
                                <a href="haircuts.php" class="menu-link">HAIRCUTS & SERVICES</a>
                                <?php if (isset($_SESSION["user"])): ?>
                                    <a href="customer/appointment.php" class="menu-link">MY APPOINTMENT</a>
                                    <a href="logout.php" class="menu-link">LOGOUT</a>
                                <?php else: ?>
                                    <a href="login.php" class="menu-link">LOGIN</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

            <div class="login-container fade-in">
                <div class="d-flex justify-content-start mb-4">
                    <a href="index.php" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <!-- Display Session Alerts Dynamically -->
                <?php if (isset($_SESSION['status'])): ?>
                    <div class="alert alert-<?= $_SESSION['status_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                        <?= $_SESSION['status']; ?>
                    </div>
                    <?php unset($_SESSION['status']); unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <h2 class="login-header">Login</h2>
                <form action="login.php" method="post">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuBtn = document.getElementById('menuBtn');
        const menuClose = document.getElementById('menuClose');
        const menuDropdown = document.getElementById('menuDropdown');

        menuBtn.addEventListener('click', function() {
            menuDropdown.classList.add('show');
        });

        menuClose.addEventListener('click', function() {
            menuDropdown.classList.remove('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!menuDropdown.contains(event.target) && !menuBtn.contains(event.target)) {
                menuDropdown.classList.remove('show');
            }
        });
    });
    </script>
</body>
</html>
