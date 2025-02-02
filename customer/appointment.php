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
$sql = "SELECT * FROM appointment_tbl WHERE customerID = $customerID ORDER BY date DESC, timeSlot DESC";
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
            /* Appointment Section */
            .appointments-container {
                max-width: 800px;
                margin: 20px auto;
                background-color: #ffffff;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            }

            .appointments-table {
                width: 100%;
                border-collapse: collapse;
            }

            .appointments-table th,
            .appointments-table td {
                text-align: left;
                padding: 10px 50px;
                border-bottom: 1px solid #D3D3D3;
            }

            .appointments-table th {
                color: #000000;
                font-size: 18px;
            }

            .appointments-table td {
                color: #000000;
            }

            .cancel-button {
                background-color: #ff0000;
                color: #fff;
                border: none;
                padding: 5px 10px;
                cursor: pointer;
                border-radius: 5px;
            }

            .cancel-button:hover {
                background-color: #cc0000;
            }

            .appointments-table th:first-child,
            .appointments-table td:first-child {
                padding-left: 0;  /* Remove left padding for first column */
            }

            .appointments-table th:last-child,
            .appointments-table td:last-child {
                padding-right: 0;  /* Remove right padding for last column */
                text-align: right; /* Align the content to the right */
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
            <h1 class="appointments-header">My Appointments</h1>
        </div>

        <div class="appointments-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php
if ($result->num_rows > 0) {
    $currentTime = date('Y-m-d H:i:s'); // Get current time once to avoid repeating it
    
    while ($row = $result->fetch_assoc()) {
        $statusClass = '';
        $statusText = $row['status'];  // Default to the current status

        // Check if the status is "Pending" and the current time has passed the appointment time
        if ($row['status'] == 'Pending' && $row['date'] > $currentTime) {
            // Update status to "Missed Appointment"
            $statusText = 'Missed Appointment';
            $statusClass = 'status-missed';  // Apply class for "Missed Appointment"
            
            // Update the status in the database to "Missed Appointment"
            $updateSql = "UPDATE appointment_tbl SET status='Missed Appointment' WHERE appointmentID = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("i", $row['appointmentID']);
            $stmt->execute();
            $stmt->close();
        } else {
            // Switch class for regular statuses
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
                case 'Missed Appointment':
                    $statusClass = 'status-missed';
                    break;
            }
        }
        
        // Safe output with htmlspecialchars to prevent XSS
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['timeSlot']) . "</td>";
        echo "<td class='" . $statusClass . "'>" . htmlspecialchars($statusText) . "</td>";
        
        // Only show cancel button for Pending appointments
        if ($row['status'] !== "Cancelled" && $row['status'] !== "Completed" && $row['status'] !== "Missed Appointment") {
            echo "<td><button class='cancel-button' data-id='" . $row['appointmentID'] . 
            "' data-bs-toggle='modal' data-bs-target='#cancelModal' onclick='openCancelModal(" . 
            $row['appointmentID'] . ", `" . 
            htmlspecialchars($row['date']) . "`, `" . 
            htmlspecialchars($row['timeSlot']) . "`)'>Cancel</button></td>";
        } else {
            echo "<td></td>";
        }
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' class='text-center'>No appointments found.</td></tr>";
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Service: <span id="serviceName"></span></p>
                        <p>Date: <span id="appointmentDate"></span></p>
                        <p>Time: <span id="appointmentTime"></span></p>
                        <div class="mb-3">
                        <label for="cancelReason" class="form-label">Reason for Cancellation</label>
                        <input type="text" class="form-control" id="cancelReason" placeholder="Enter your reason (optional)">
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Discard</button>
                        <button type="button" class="btn btn-danger" id="confirmCancelButton">Confirm</button>
                    </div>
                </div>
            </div>
        </div>


        <script>
        // Function to handle appointment cancellation
        function openCancelModal(appointmentID, date, time) {
            document.getElementById('appointmentDate').textContent = date;
            document.getElementById('appointmentTime').textContent = time;

            // Set up the Confirm button to handle the cancellation
            const confirmButton = document.getElementById('confirmCancelButton');
                confirmButton.onclick = function () {
                const reason = document.getElementById('cancelReason').value;

                // No validation needed anymore for the reason (allow empty reason)
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