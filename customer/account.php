<?php
session_start();
require '../database.php';

// Check if user is logged in
if (!isset($_SESSION['customerID'])) {
    header('Location: ../login.php');
    exit();
}

$customerID = $_SESSION['customerID'];

// Fetch user details from customer_tbl
$sql = "SELECT * FROM customer_tbl WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$firstName = $user['firstName']; // For navbar display
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - Jack of Fades</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="../css/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #171717;
            font-family: 'Lexend', sans-serif;
        }

        .account-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .account-table {
            background-color: #171717;
            border-radius: 10px;
        }
        .table th {
            background-color: #171717;
            color: #FFDE59;
            border: none;
        }
        .table td {
            vertical-align: middle;
            color: #fff;
            border-color: #333;
            background-color: #171717;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #202020;
        }
        .table-striped > tbody > tr:nth-of-type(even) > * {
            background-color: #171717;
        }
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-header h2 {
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            color: #FFDE59;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }
        .change-email-btn {
            background-color: #FFDE59;
            color: #171717;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-left: 15px;
            transition: all 0.3s ease;
            float: right;
        }
        .change-email-btn:hover {
            background-color: #e6c84f;
        }
        .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }
        .modal-content {
            background-color: #171717;
            color: #fff;
            margin: auto;
            width: 100%;
        }
        .modal-header {
            border-bottom-color: #333;
        }
        .modal-footer {
            border-top-color: #333;
        }
        .form-control {
            background-color: #202020;
            border-color: #333;
            color: #fff;
        }
        .form-control:focus {
            background-color: #202020;
            border-color: #FFDE59;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(255, 222, 89, 0.25);
        }
        .alert {
            margin: 20px auto;
            max-width: 800px;
        }
        .alert-success {
            background-color: #FFDE59;
            color: #171717;
            border: none;
        }
        .alert-danger {
            background-color: #dc3545;
            color: #fff;
            border: none;
        }
        .info-text {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <nav class="navbar navbar-expand-lg py-4">
            <div class="container ps-5">
                <a class="navbar-brand ms-0" href="../index.php">
                    <img src="../css/images/jof_logo_black.png" alt="logo" width="45" height="45" class="desktop-logo">
                    <img src="../css/images/jof_logo_yellow.png" alt="logo" width="45" height="45" class="mobile-logo">
                </a>

                <button class="menu-btn d-lg-none" type="button">
                    <i class='bx bx-menu'></i>
                </button>

                <div class="menu-dropdown">
                    <div class="menu-header">
                        <button class="menu-close">&times;</button>
                    </div>
                    <div class="menu-links">
                        <a href="../index.php" class="menu-link">HOME</a>
                        <a href="../haircuts.php" class="menu-link">HAIRCUTS & SERVICES</a>
                        <a href="appointment.php" class="menu-link">MY APPOINTMENT</a>
                        <a href="../logout.php" class="menu-link">LOGOUT</a>
                    </div>
                </div>

                <div class="navbar-nav mx-auto ps-5">
                    <a class="nav-link mx-4 nav-text fs-5" href="../index.php">Home</a>
                    <a class="nav-link mx-4 nav-text fs-5" href="../haircuts.php">HAIRCUTS & SERVICES</a>
                    <a class="nav-link mx-4 nav-text fs-5" href="appointment.php">My Appointment</a>
                </div>

                <div class="navbar-nav pe-5 me-4">
                    <button class="btn btn-dark me-2 px-4" 
                        onclick="document.location='booking.php'" 
                        type="button" 
                        style="background-color: #000000; color: #FFDE59; border-radius: 12px;">Book Now
                    </button>
                    
                    <div class="dropdown">
                        <div class="user-header d-flex align-items-center" id="userDropdown">
                            <div class="user-icon">
                                <i class='bx bxs-user'></i>
                            </div>
                            <div class="user-greeting">
                                <span class="user-name"><?php echo htmlspecialchars($firstName); ?></span>
                            </div>
                        </div>

                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="account.php" class="dropdown-item">Account</a>
                            <a href="../logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div class="account-container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <h2>Account Details</h2>
        </div>
        
        <div class="account-table">
            <table class="table">
                <tbody>
                    <tr>
                        <th>First Name</th>
                        <td><?php echo htmlspecialchars($user['firstName']); ?></td>
                    </tr>
                    <tr>
                        <th>Middle Name</th>
                        <td><?php echo empty($user['middleName']) ? 'N/A' : htmlspecialchars($user['middleName']); ?></td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td><?php echo htmlspecialchars($user['lastName']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>
                            <span class="info-text"><?php echo htmlspecialchars($user['email']); ?></span>
                            <button type="button" class="change-email-btn" data-bs-toggle="modal" data-bs-target="#changeEmailModal">
                                Edit
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>Contact Number</th>
                        <td>
                            <span class="info-text"><?php echo htmlspecialchars($user['contactNum']); ?></span>
                            <button type="button" class="change-email-btn" data-bs-toggle="modal" data-bs-target="#changePhoneModal">
                                Edit
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Change Email Modal -->
    <div class="modal fade" id="changeEmailModal" tabindex="-1" aria-labelledby="changeEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeEmailModalLabel">Change Email Address</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changeEmailForm" action="change_email.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="currentEmail" class="form-label">Current Email</label>
                            <input type="email" class="form-control" id="currentEmail" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="newEmail" class="form-label">New Email</label>
                            <input type="email" class="form-control" id="newEmail" name="newEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Phone Modal -->
    <div class="modal fade" id="changePhoneModal" tabindex="-1" aria-labelledby="changePhoneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePhoneModalLabel">Change Contact Number</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changePhoneForm" action="change_phone.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="currentPhone" class="form-label">Current Contact Number</label>
                            <input type="text" class="form-control" id="currentPhone" value="<?php echo htmlspecialchars($user['contactNum']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="newPhone" class="form-label">New Contact Number</label>
                            <input type="tel" class="form-control" id="newPhone" name="newPhone" required 
                                   maxlength="11" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)" 
                                   pattern="[0-9]{11}" 
                                   title="Contact number must be 11 digits">
                        </div>
                        <div class="mb-3">
                            <label for="phonePassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="phonePassword" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Contact Number</button>
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

        // Dropdown menu functionality
        const dropdownToggle = document.getElementById('userDropdown');
        const dropdownMenu = document.getElementById('dropdownMenu');

        dropdownToggle.addEventListener('click', function () {
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
