<?php
session_start();

// Check if the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    header("Location: ../login.php"); // Redirect to login if not logged in or not a customer
    exit();
}

// Get the logged-in customer's ID from the session
$customerID = $_SESSION["customerID"];

// Database connection
include 'db_connect.php';

// Fetch customer's firstName if not in session
if (!isset($_SESSION['firstName'])) {
    $sql = "SELECT firstName FROM customer_tbl WHERE customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['firstName'] = $row['firstName'];
    }
    $stmt->close();
}

// Fetch appointments for the logged-in customer
$sql = "
    SELECT 
        a.appointmentID,
        a.date, 
        a.timeSlot, 
        a.status, 
        s.serviceName, 
        ad.addonName, 
        (s.servicePrice + ad.addonPrice) AS totalPrice
    FROM 
        appointment_tbl a
    LEFT JOIN 
        service_tbl s ON a.serviceID = s.serviceID
    LEFT JOIN 
        addon_tbl ad ON a.addonID = ad.addonID
    WHERE 
        a.customerID = $customerID
    ORDER BY 
        a.date DESC, a.timeSlot DESC
";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | My Appointment</title>
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style1.css">
    <link rel="stylesheet" href="css/customer.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lexend', sans-serif;
        }

        /* Initial state for fade-in elements */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeIn 1s ease-out forwards;
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

        /* Add animation delay for the appointments container */
        .appointments-container.fade-in {
        animation-delay: 0.2s;
        }

        /* Modal pop-up animation */
        .modal.fade .modal-dialog {
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
            opacity: 1;
        }

        /* Optional: Add a nice bounce effect */
        @keyframes modalPop {
            0% {                
                transform: scale(0.7);
                opacity: 0;
                }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .modal.show .modal-dialog {
            animation: modalPop 0.3s ease-out forwards;
        }

        /* Navbar animation */
        .header {
            opacity: 0;
            transform: translateY(-20px);
            animation: navSlideDown 0.8s ease forwards;
        }

        @keyframes navSlideDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Adjust other animations to start after navbar */
        .fade-in {
            animation-delay: 0.3s; /* Start after navbar animation */
        }

        .appointments-container.fade-in {
            animation-delay: 0.5s; /* Further delay for container */
        }
    </style>
</head>
<body>
    <div class="main-page">
        <div class="header">
            <nav class="navbar navbar-expand-lg py-4">
                <div class="container ps-5">
                    <a class="navbar-brand ms-0" href="../index.php">
                        <img src="../css/images/jof_logo_black.png" alt="logo" width="45" height="45" class="desktop-logo">
                        <img src="../css/images/jof_logo_yellow.png" alt="logo" width="45" height="45" class="mobile-logo">
                    </a>

                    <button class="menu-btn d-lg-none" type="button" id="menuBtn">
                        <i class='bx bx-menu'></i>
                    </button>

                    <div class="menu-dropdown" id="menuDropdown">
                        <div class="menu-header">
                            <button class="menu-close" id="menuClose">&times;</button>
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
                            style="background-color: #000000; color: #FFDE59; border-radius: 12px;">Book Now</button>
                        <div class="dropdown">
                            <div class="user-header d-flex align-items-center" id="userDropdown">
                                <div class="user-icon">
                                    <i class='bx bxs-user'></i>
                                </div>
                                <div class="user-greeting">
                                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['firstName'] ?? ''); ?></span>
                                </div>
                            </div>
                            <div class="dropdown-menu" id="dropdownMenu">
                                <a href="../logout.php" class="dropdown-item">Logout</a>
                            </div>
                        </div>
                        <script>
                            // JavaScript to toggle dropdown visibility
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
                        </script>
                    </div>
                </div>
            </nav>
        </div>

        <div class="appointments-header-wrapper">
            <h1 class="appointments-header fade-in">My Appointments</h1>
        </div>

        <div class="appointments-container fade-in">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Service</th>
                        <th>Add-On</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = '';

                        switch ($row['status']) {
                            case 'Completed':
                                $statusClass = 'status-completed';
                                break;
                            case 'Cancelled':
                                $statusClass = 'status-cancelled';
                                break;
                            case 'Pending':
                                $statusClass = 'status-pending';
                                break;
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['timeSlot']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['serviceName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['addonName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['totalPrice']) . "</td>";
                        echo "<td class='" . $statusClass . "'>" . htmlspecialchars($row['status']) . "</td>";

                        if ($row['status'] === "Pending") {
                            echo "<td><button class='cancel-button' data-id='" . $row['appointmentID'] . 
                            "' data-bs-toggle='modal' data-bs-target='#cancelModal'>Cancel</button></td>";
                        } else {
                            echo "<td></td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No appointments found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel">Cancel Appointment</h5>
                    </div>
                    <div class="modal-body">
                        <p>Date: <span id="appointmentDate"></span></p>
                        <p>Time: <span id="appointmentTime"></span></p>
                        <p>Service: <span id="serviceName"></span></p>
                        <p>Add-On: <span id="addonName"></span></p>
                        <div class="mb-3">
                        <label for="cancelReason" class="form-label">Reason for Cancellation</label>
                        <input type="text" class="form-control" id="cancelReason" placeholder="Enter your reason (Required)" required>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Discard</button>
                        <button type="button" class="btn btn-danger" id="confirmCancelButton">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
<script>
// Function to handle appointment cancellation and populate modal
function openCancelModal(appointmentID, date, time, serviceName, addonName) {
    document.getElementById('appointmentDate').textContent = date;
    document.getElementById('appointmentTime').textContent = time;
    document.getElementById('serviceName').textContent = serviceName ? serviceName : 'No service selected';
    document.getElementById('addonName').textContent = addonName ? addonName : 'No add-on selected';

    // Set up the Confirm button to handle the cancellation
    const confirmButton = document.getElementById('confirmCancelButton');
    confirmButton.onclick = function () {
        const reasonInput = document.getElementById('cancelReason');
        const reason = reasonInput.value.trim();

        if (reason === "") {
            alert("Please provide a reason for cancellation.");
            reasonInput.focus(); // Focus on input if empty
            return;
        }

        // Redirect to cancellation PHP script with parameters
        window.location.href = "cancel_appointment.php?appointmentID=" + appointmentID + "&reason=" + encodeURIComponent(reason);
    };
}
</script>
        <script>
            // JavaScript to toggle mobile menu
            const menuBtn = document.getElementById('menuBtn');
            const menuDropdown = document.getElementById('menuDropdown');
            const menuClose = document.getElementById('menuClose');

            menuBtn.addEventListener('click', function () {
                menuDropdown.classList.toggle('show');
            });

            menuClose.addEventListener('click', function () {
                menuDropdown.classList.remove('show');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function (event) {
                if (!menuBtn.contains(event.target) && !menuDropdown.contains(event.target)) {
                    menuDropdown.classList.remove('show');
                }
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>
</html>
