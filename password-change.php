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
    <title>Jack of Fades | Change Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" href="css/images/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="login-background" style="background-color: #171717;">
        <div class="container">
            <div class="login-container fade-in">
                <!-- Add Back button -->
                <div class="d-flex justify-content-start mb-4">
                    <a href="login.php" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <!-- Add the logo above the Change Password header -->
                <div class="text-center mb-4">
                    <img src="css/images/jof_logo_black.png" alt="Logo" style="max-width: 60px; height: auto;">
                </div>

                <?php
                if (isset($_SESSION['status'])) {
                    $alertType = isset($_SESSION['status_type']) ? $_SESSION['status_type'] : "success";
                    ?>
                    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                        <p><?= $_SESSION['status']; ?></p>
                    </div>
                    <?php
                    unset($_SESSION['status']);
                    unset($_SESSION['status_type']);
                }
                ?>

                <h2 class="login-header">Change Password</h2>
                <form action="forgot-password-code.php" method="post">
                    <input type="hidden" name="password_token" value="<?php if(isset($_GET['token'])) {echo $_GET['token'];} ?>">
                    <input type="hidden" name="email" value="<?php if(isset($_GET['email'])) {echo $_GET['email'];} ?>">
                    
                    <div class="form-group position-relative">
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                        <i class="bi bi-eye-slash password-toggle" id="togglePassword1"></i>
                    </div>

                    <div class="form-text text-muted mb-3">
                            <span>• At least 8 characters</span>
                            <br><span>• At least one uppercase</span>
                            <br><span>• At least one number</span>
                    </div>

                    <div class="form-group position-relative">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                        <i class="bi bi-eye-slash password-toggle" id="togglePassword2"></i>
                    </div>

                    <div class="form-btn">
                        <button type="submit" name="password_update" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
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
        // Password toggle functionality for the new password field
        const togglePassword1 = document.getElementById('togglePassword1');
        const passwordInput1 = document.querySelector('input[name="new_password"]');

        togglePassword1.addEventListener('click', function() {
            const type = passwordInput1.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput1.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Password toggle functionality for the confirm password field
        const togglePassword2 = document.getElementById('togglePassword2');
        const passwordInput2 = document.querySelector('input[name="confirm_password"]');

        togglePassword2.addEventListener('click', function() {
            const type = passwordInput2.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput2.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    });

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
    </script>
</body>
</html>
