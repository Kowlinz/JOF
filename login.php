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
    <div class="login-background" style="background-color: #171717;">
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

            <div class="login-container fade-in">
                <div class="d-flex justify-content-start mb-4">
                    <a href="index.php" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <!-- Add the logo above the Login header -->
                <div class="text-center mb-4">
                    <img src="css/images/jof_logo_black.png" alt="Logo" style="max-width: 60px; height: auto;">
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
                    <div class="form-group position-relative">
                        <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Password" required>
                        <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
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

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
            background: none;
            border: none;
            padding: 0;
        }

        .password-toggle:hover {
            color: #000;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1rem;
        }

        .form-control {
            padding-right: 40px;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function(e) {
                e.preventDefault();
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
    });
    </script>
</body>
</html>
