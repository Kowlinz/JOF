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
    <title>Jack of Fades | Password Reset</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Link to Favicon -->
    <link rel="icon" href="css/images/favicon.ico" type="image/x-icon">
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
                <!-- Add Back button -->
                <div class="d-flex justify-content-start mb-4">
                    <a href="login.php" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <?php
                if (isset($_SESSION['status'])) {
                    // Default to "success" if status_type is not set
                    $alertType = isset($_SESSION['status_type']) ? $_SESSION['status_type'] : "success";
                    ?>
                    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                        <p><?= $_SESSION['status']; ?></p>
                    </div>
                    <?php
                    unset($_SESSION['status']);
                    unset($_SESSION['status_type']); // Unset the type to prevent persistent styling
                }
                ?>

                <!-- Add the logo above the Password Reset header -->
                <div class="text-center mb-4">
                    <img src="css/images/jof_logo_black.png" alt="Logo" style="max-width: 60px; height: auto;">
                </div>

                <h2 class="login-header">Password Reset</h2>
                <form action="forgot-password-code.php" method="post">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <!-- Remove the Back to Login link -->
                    <div class="form-btn">
                        <button type="submit" name="password_reset_link" class="btn btn-primary">Send Password Reset Link</button>
                    </div>
                </form>
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
