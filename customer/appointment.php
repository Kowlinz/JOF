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
    a.feedback,
    IFNULL(s.serviceName, 'No Service') AS serviceName, 
    IFNULL(ad.addonName, 'No Add-on') AS addonName, 
    IFNULL(s.servicePrice, 0) + IFNULL(ad.addonPrice, 0) AS totalPrice,
    a.payment_status,
    a.payment_amount
FROM 
    appointment_tbl a
LEFT JOIN 
    service_tbl s ON a.serviceID = s.serviceID
LEFT JOIN 
    addon_tbl ad ON a.addonID = ad.addonID
WHERE 
    a.customerID = ?
ORDER BY 
    a.appointmentID DESC, a.date DESC, a.timeSlot DESC;
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        body {
            font-family: 'Lexend', sans-serif;
            background-color: #171717;
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
        .appointments-container {
            width: 75%;
            max-width: 1200px;
            margin: 0 auto;
            overflow-x: auto;
            padding: 0 20px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            background-color: #1f1f1f; /* Dark background */
        }

        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            color: #ffffff; /* Light text for dark background */
        }

        .appointments-table th,
        .appointments-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333333; /* Darker border */
            white-space: nowrap;
        }

        .appointments-table th {
            color: #FFDE59; /* Yellow headers to match theme */
            font-weight: 600;
            padding: 15px 12px;
            border-bottom: 2px solid #333333;
        }

        .appointments-table tbody tr:hover {
            background-color: #2a2a2a; /* Darker hover effect */
        }

        .appointments-table td .btn {
            margin: 2px;
        }

        .cancel-button {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin-right: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            font-size: 0.875rem;
            line-height: 1.5;
            display: inline-block;
        }

        .cancel-button:hover {
            background-color: #cc0000;
        }

        /* Make Pay button consistent with Cancel button */
        .btn-warning.btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-right: 5px;
            display: inline-block;
        }

        /* Status colors */
        .status-completed {
            color: #4CAF50; /* Brighter green */
            font-weight: 500;
        }

        .status-cancelled {
            color: #ff4444; /* Brighter red */
            font-weight: 500;
        }

        .status-pending {
            color: #FFDE59; /* Yellow to match theme */
            font-weight: 500;
        }

        /* Toast container styles */
        .toast-container {
            z-index: 1056; /* Higher than modal backdrop */
        }
        
        .toast {
            min-width: 300px;
        }

        /* Ensure header stays on top */
        .header {
            position: relative;
            z-index: 1000;
            background-color: white;
        }

        /* Menu dropdown positioning and styling */
        .menu-dropdown {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #000000; /* Changed to black */
            z-index: 1001;
            display: none;
            padding: 20px;
        }

        .menu-dropdown.show {
            display: block;
        }

        /* Style the menu links for better visibility on black background */
        .menu-links {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
        }

        .menu-link {
            color: #FFDE59; /* Yellow color to match theme */
            text-decoration: none;
            font-size: 1.2rem;
            padding: 10px;
            transition: color 0.3s ease;
        }

        .menu-link:hover {
            color: #ffffff; /* White on hover */
        }

        /* Style the close button */
        .menu-close {
            color: #FFDE59;
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            padding: 10px;
        }

        .menu-close:hover {
            color: #ffffff;
        }

        /* Adjust main content spacing */
        @media (max-width: 991px) {
            .appointments-header-wrapper {
                margin-top: 60px; /* Increased from 20px */
                position: relative;
                z-index: 1;
            }

            .appointments-container {
                position: relative;
                z-index: 1;
                padding: 0 15px; /* Added padding for better mobile spacing */
            }

            /* Add some bottom margin to the header */
            .appointments-header {
                margin-bottom: 30px;
            }
        }

        /* Time slot styles for both booking and reschedule modals */
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding: 15px;
        }

        .time-slot-btn {
            background-color: #FFDE59;
            border: none;
            padding: 10px;
            border-radius: 20px;
            color: black;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .time-slot-btn:hover {
            background-color: #e6c84f;
        }

        .time-slot-btn.selected {
            background-color: black;
            color: #FFDE59;
        }

        .time-slot-btn.booked {
            background-color: #d6d6d6;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .time-slot-btn.booked:hover {
            background-color: #d6d6d6;
        }

        /* Modal content styling */
        .modal-content {
            background-color: #1f1f1f;
            color: #ffffff;
        }

        /* Style for form labels in modals */
        .modal-body label {
            color: #ffffff;
            margin-bottom: 8px;
        }

        /* Style for form inputs in modals */
        .modal-body .form-control {
            background-color: #ffffff;
            color: #000000;
            border: none;
            margin-bottom: 15px;
        }

        /* Style for the date input placeholder */
        #newDate::placeholder {
            color: #000000 !important;
            opacity: 1; /* Firefox */
            text-align: center;
        }

        /* Style for the actual input text */
        #newDate {
            color: #000000;
            text-align: center;
        }

        /* Update the appointments header color */
        .appointments-header {
            color: #FFDE59; /* Yellow to match theme */
        }

        /* Update the "No appointments found" message color */
        .appointments-table tr td.text-center {
            color: #ffffff;
        }

        /* Update text colors in the table */
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            color: #ffffff; /* Light text for dark background */
        }

        /* Update dropdown menu text color */
        .dropdown-menu {
            background-color: #1f1f1f;
        }

        .dropdown-menu .dropdown-item {
            color: #ffffff;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #2a2a2a;
            color: #FFDE59;
        }

        /* Update payment status text colors */
        .text-danger {
            color: #ff4444 !important; /* Brighter red */
        }

        .text-warning {
            color: #FFDE59 !important; /* Yellow to match theme */
        }

        .text-success {
            color: #4CAF50 !important; /* Brighter green */
        }

        .text-muted {
            color: #aaaaaa !important; /* Light gray */
        }

        /* Update any remaining black text */
        .appointments-table td,
        .appointments-table th,
        .appointments-table tr {
            color: #ffffff;
        }

        /* Make sure modal text is visible */
        .modal-body {
            color: #ffffff;
        }

        .modal-header {
            color: #ffffff;
            border-bottom: 1px solid #333333;
        }

        .modal-footer {
            border-top: 1px solid #333333;
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
                                <a href="account.php" class="dropdown-item">Account</a>
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

        <div class="appointments-container">
            <table class="appointments-table fade-in">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Total Price</th>
                        <th>Appointment Status</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                date_default_timezone_set('Asia/Manila'); // Ensure the correct timezone is set
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
                
                        $paymentStatusText = "";
                        $payNowButton = "";
                
                        if ($row['payment_status'] === 'cancelled') {
                            $paymentStatusText = "<span class='text-danger'>Cancelled</span>";
                            $payNowButton = $row['status'] !== 'Cancelled' ? "<a href='gcash_payment.php?appointmentID=" . $row['appointmentID'] . "&amount=" . ($row['totalPrice'] * 0.5) . "' class='btn btn-warning btn-sm'>Pay Now</a>" : "";
                        } elseif ($row['payment_status'] === 'partial') {
                            $remainingBalance = $row['totalPrice'] - $row['payment_amount'];
                            $paymentStatusText = "<span class='text-warning'>Partially Paid (₱" . number_format($row['payment_amount'], 2) . ")</span>";
                            $payNowButton = $row['status'] !== 'Cancelled' ? "<a href='gcash_payment.php?appointmentID=" . $row['appointmentID'] . "&amount=" . $remainingBalance . "' class='btn btn-warning btn-sm'>Pay Remaining</a>" : "";
                        } else {
                            $paymentStatusText = "<span class='text-success'>Paid</span>";
                        }

                        // Calculate time difference in hours
                        $appointmentDateTime = strtotime($row['date'] . ' ' . $row['timeSlot']);
                        $currentDateTime = time();
                        $timeDifference = ($appointmentDateTime - $currentDateTime) / 3600; // Convert seconds to hours

                        // Disable reschedule button if within 24 hours
                        $disableReschedule = ($row['status'] !== "Cancelled" && $timeDifference < 24) ? "disabled" : "";
                
                        echo "<tr>";
                        echo "<td>" . date("F d, Y", strtotime($row['date'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['timeSlot']) . "</td>";
                        echo "<td>₱" . number_format($row['totalPrice'], 2) . "</td>";
                        echo "<td class='" . $statusClass . "'>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>" . $paymentStatusText . "</td>";
                        echo "<td>";

                        if ($row['status'] === "Upcoming") {
                            echo "<span class='reschedule-container' style='display: none;'>
                                    <button class='btn btn-warning btn-sm reschedule-button' 
                                    data-id='" . $row['appointmentID'] . "' 
                                    data-original-date='" . $row['date'] . "'
                                    data-original-time='" . $row['timeSlot'] . "'
                                    data-bs-toggle='modal' data-bs-target='#rescheduleModal' $disableReschedule>Reschedule</button>
                                </span> ";
                            echo "<button class='cancel-button' 
                                data-id='" . $row['appointmentID'] . "' 
                                data-date='" . date("F d, Y", strtotime($row['date'])) . "' 
                                data-time='" . $row['timeSlot'] . "' 
                                data-bs-toggle='modal' data-bs-target='#cancelModal'>Cancel</button>";
                        } elseif ($row['status'] === "Cancelled") {
                            echo "<button class='btn btn-warning btn-sm reschedule-button' 
                                data-id='" . $row['appointmentID'] . "' 
                                data-original-date='" . $row['date'] . "'
                                data-original-time='" . $row['timeSlot'] . "'
                                data-bs-toggle='modal' data-bs-target='#rescheduleModal' $disableReschedule>Reschedule</button>";
                        } elseif ($row['status'] === "Completed") {
                            if (empty($row['feedback'])) {
                                echo "<button class='btn btn-primary btn-sm feedback-button' 
                                      data-id='" . $row['appointmentID'] . "' 
                                      data-bs-toggle='modal' data-bs-target='#feedbackModal'>Give Feedback</button>";
                            } else {
                                echo "<span class='text-muted'>Feedback submitted</span>";
                            }
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                }   else {
                        echo "<tr><td colspan='6' class='text-center'>No appointments found.</td></tr>";
                    }              
                ?>
                </tbody>
            </table>
        </div>

        <!-- Reschedule Modal -->
        <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reschedule Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="newDate">Select New Date:</label>
                        <input type="text" class="form-control text-center" id="newDate" placeholder="Choose Date" data-input readonly>
                        
                        <label for="newTime" class="mt-3">Select New Time:</label>
                        <div class="time-slots-grid">
                            <?php
                            $timeSlots = [
                                '10:00 AM', '10:40 AM', '11:20 AM', '12:00 PM',
                                '12:40 PM', '1:20 PM', '2:00 PM', '2:40 PM',
                                '3:20 PM', '4:00 PM', '4:40 PM', '5:20 PM',
                                '6:00 PM', '6:40 PM', '7:20 PM', '8:00 PM',
                            ];

                            foreach ($timeSlots as $time): ?>
                                <button class="time-slot-btn" 
                                        type="button"
                                        data-time="<?php echo htmlspecialchars($time); ?>"
                                        onclick="selectRescheduleTime('<?php echo htmlspecialchars($time); ?>')">
                                    <?php echo htmlspecialchars($time); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="newTime">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Discard</button>
                        <button type="button" class="btn btn-success" id="confirmReschedule">Confirm</button>
                    </div>
                </div>
            </div>
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

        <!-- Update the success modal HTML with better styling -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: #1f1f1f; color: #ffffff;">
                    <div class="modal-header border-0 justify-content-center position-relative">
                        <h5 class="modal-title fs-4 fw-bold" id="successModalLabel">Success</h5>
                        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center border-0 py-4">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                        <p class="mb-0 fs-5">Appointment cancellation successful</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Container -->
        <div class="toast-container position-fixed top-50 start-50 translate-middle">
            <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" id="errorToast">
                <div class="d-flex">
                    <div class="toast-body">
                        Please fill in the reason for cancellation.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Modify the Feedback Modal -->
        <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header justify-content-center">
                        <h5 class="modal-title" id="feedbackModalLabel">Service Feedback</h5>
                    </div>
                    <div class="modal-body">
                        <form id="feedbackForm">
                            <input type="hidden" id="feedbackAppointmentID">
                            <div class="mb-3">
                                <label for="feedbackComment" class="form-label">Comments</label>
                                <textarea class="form-control" id="feedbackComment" rows="3" required 
                                    placeholder="Please share your experience with our service..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Discard</button>
                        <button type="button" class="btn btn-primary" id="submitFeedback">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modify the Success Feedback Modal -->
        <div class="modal fade" id="feedbackSuccessModal" tabindex="-1" aria-labelledby="feedbackSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: #1f1f1f; color: #ffffff;">
                    <div class="modal-header border-0 justify-content-center position-relative">
                        <h5 class="modal-title fs-4 fw-bold" id="feedbackSuccessModalLabel">Success</h5>
                        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center border-0 py-4">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                        <p class="mb-0 fs-5">Thank you for your feedback!</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this new modal after your other modals -->
        <div class="modal fade" id="dateTimeErrorModal" tabindex="-1" aria-labelledby="dateTimeErrorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: #1f1f1f; color: #ffffff;">
                    <div class="modal-header border-0 justify-content-center position-relative">
                        <h5 class="modal-title fs-4 fw-bold" id="dateTimeErrorModalLabel">Error</h5>
                        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center border-0 py-4">
                        <i class='bx bx-error-circle text-warning mb-3' style="font-size: 3rem;"></i>
                        <p class="mb-0 fs-5">Please select both date and time.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this new modal for reschedule success -->
        <div class="modal fade" id="rescheduleSuccessModal" tabindex="-1" aria-labelledby="rescheduleSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: #1f1f1f; color: #ffffff;">
                    <div class="modal-header border-0 justify-content-center position-relative">
                        <h5 class="modal-title fs-4 fw-bold" id="rescheduleSuccessModalLabel">Success</h5>
                        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center border-0 py-4">
                        <i class='bx bx-check-circle text-success mb-3' style="font-size: 3rem;"></i>
                        <p class="mb-0 fs-5">Appointment rescheduled successfully</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this new modal after your other modals -->
        <div class="modal fade" id="timeSlotErrorModal" tabindex="-1" aria-labelledby="timeSlotErrorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background-color: #1f1f1f; color: #ffffff;">
                    <div class="modal-header border-0 justify-content-center position-relative">
                        <h5 class="modal-title fs-4 fw-bold" id="timeSlotErrorModalLabel">Time Slot Not Available</h5>
                        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center border-0 py-4">
                        <i class='bx bx-time-five text-warning mb-3' style="font-size: 3rem;"></i>
                        <p class="mb-0 fs-5">Selected time slot is already booked.<br>Please choose another time.</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let selectedAppointmentID = null;
                let originalDate = null;
                let originalTime = null;

                // Handle Cancel Button Click
                document.querySelectorAll(".cancel-button").forEach(button => {
                    button.addEventListener("click", function () {
                        selectedAppointmentID = this.getAttribute("data-id");
                        document.getElementById("appointmentDate").textContent = this.getAttribute("data-date");
                        document.getElementById("appointmentTime").textContent = this.getAttribute("data-time");
                        // Clear the reason input when opening the modal
                        document.getElementById("cancelReason").value = "";
                    });
                });

                // Handle Reschedule Button Click
                document.querySelectorAll(".reschedule-button").forEach(button => {
                    button.addEventListener("click", function () {
                        selectedAppointmentID = this.getAttribute("data-id");
                        originalDate = this.getAttribute("data-original-date");
                        originalTime = this.getAttribute("data-original-time");
                        
                        // Reset any previously selected time slots
                        document.querySelectorAll('#rescheduleModal .time-slot-btn').forEach(btn => {
                            btn.classList.remove('selected', 'booked');
                            btn.disabled = false;
                        });

                        // Clear the date input when opening the modal
                        document.getElementById("newDate").value = "";
                        
                        // Initialize or reinitialize flatpickr with the original date disabled
                        flatpickr("#newDate", {
                            enableTime: false,
                            dateFormat: "Y-m-d",
                            minDate: "today",
                            disable: [
                                function(date) {
                                    // Disable Sundays
                                    if (date.getDay() === 0) return true;
                                    
                                    // Disable the original date
                                    const dateString = date.toISOString().split('T')[0];
                                    return dateString === originalDate;
                                }
                            ],
                            locale: {
                                firstDayOfWeek: 1 // Start week on Monday
                            }
                        });
                    });
                });

                // Update the time slot selection function
                window.selectRescheduleTime = function (time) {
                    // Don't allow selection if the slot is booked
                    const timeBtn = document.querySelector(`#rescheduleModal .time-slot-btn[data-time="${time}"]`);
                    if (timeBtn && timeBtn.classList.contains('booked')) {
                        return;
                    }

                    document.querySelectorAll('#rescheduleModal .time-slot-btn').forEach(btn => {
                        btn.classList.remove('selected');
                    });

                    if (timeBtn) {
                        timeBtn.classList.add('selected');
                    }

                    document.getElementById('newTime').value = time;
                };

                // Update the confirm reschedule validation
                document.getElementById("confirmReschedule").addEventListener("click", function () {
                    const newDate = document.getElementById("newDate").value;
                    const newTime = document.getElementById("newTime").value;

                    if (!newDate || !newTime) {
                        const dateTimeErrorModal = new bootstrap.Modal(document.getElementById('dateTimeErrorModal'));
                        dateTimeErrorModal.show();
                        return;
                    }

                    // Check if trying to reschedule to same date and time
                    if (newDate === originalDate && newTime === originalTime) {
                        const timeSlotErrorModal = new bootstrap.Modal(document.getElementById('timeSlotErrorModal'));
                        timeSlotErrorModal.show();
                        return;
                    }

                    // Close the reschedule modal
                    const rescheduleModal = bootstrap.Modal.getInstance(document.getElementById('rescheduleModal'));
                    rescheduleModal.hide();

                    // Get the button that was clicked to open the modal
                    const rescheduleButton = document.querySelector(`.reschedule-button[data-id="${selectedAppointmentID}"]`);
                    
                    fetch("reschedule_appointment.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `appointmentID=${selectedAppointmentID}&newDate=${newDate}&newTime=${newTime}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Find the table row containing the reschedule button
                            const tableRow = rescheduleButton.closest('tr');
                            if (tableRow) {
                                // Update the date and time cells
                                tableRow.cells[0].textContent = new Date(newDate).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                });
                                tableRow.cells[1].textContent = newTime;
                                
                                // Update the status cell
                                const statusCell = tableRow.cells[3];
                                statusCell.textContent = 'Pending';
                                statusCell.className = 'status-pending';
                                
                                // Update the actions cell
                                const actionsCell = tableRow.cells[5];
                                actionsCell.innerHTML = `
                                    <span class='reschedule-container' style='display: none;'>
                                        <button class='btn btn-warning btn-sm reschedule-button' 
                                        data-id='${selectedAppointmentID}' 
                                        data-bs-toggle='modal' data-bs-target='#rescheduleModal'>Reschedule</button>
                                    </span>
                                    <button class='cancel-button' 
                                        data-id='${selectedAppointmentID}' 
                                        data-date='${new Date(newDate).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'long', 
                                            day: 'numeric' 
                                        })}' 
                                        data-time='${newTime}' 
                                        data-bs-toggle='modal' data-bs-target='#cancelModal'>Cancel</button>`;
                            }

                            // Show success modal
                            const successModal = new bootstrap.Modal(document.getElementById('rescheduleSuccessModal'));
                            successModal.show();

                            // Add event listener to refresh page when success modal is closed
                            document.getElementById('rescheduleSuccessModal').addEventListener('hidden.bs.modal', function () {
                                window.location.reload();
                            }, { once: true });

                            // Reattach event listeners for the new buttons
                            attachEventListeners();
                        } else {
                            // Show the time slot error modal if the slot is already booked
                            if (data.message.includes("already booked")) {
                                const timeSlotErrorModal = new bootstrap.Modal(document.getElementById('timeSlotErrorModal'));
                                timeSlotErrorModal.show();
                                
                                // Reopen the reschedule modal when the error modal is closed
                                document.getElementById('timeSlotErrorModal').addEventListener('hidden.bs.modal', function () {
                                    const rescheduleModal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
                                    rescheduleModal.show();
                                }, { once: true });
                            } else {
                                alert("Error: " + data.message);
                            }
                        }
                    })
                    .catch(error => alert("Error: " + error));
                });

                // Function to attach event listeners to buttons
                function attachEventListeners() {
                    // Attach cancel button listeners
                    document.querySelectorAll(".cancel-button").forEach(button => {
                        button.addEventListener("click", function () {
                            selectedAppointmentID = this.getAttribute("data-id");
                            document.getElementById("appointmentDate").textContent = this.getAttribute("data-date");
                            document.getElementById("appointmentTime").textContent = this.getAttribute("data-time");
                            document.getElementById("cancelReason").value = "";
                        });
                    });

                    // Attach reschedule button listeners
                    document.querySelectorAll(".reschedule-button").forEach(button => {
                        button.addEventListener("click", function () {
                            selectedAppointmentID = this.getAttribute("data-id");
                        });
                    });
                }
            });
        </script>

        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const cancelButtons = document.querySelectorAll(".cancel-button");
            let selectedAppointmentID = null;

            // Handle Cancel Button Click
            cancelButtons.forEach(button => {
                button.addEventListener("click", function () {
                    selectedAppointmentID = this.getAttribute("data-id");
                    document.getElementById("appointmentDate").textContent = this.getAttribute("data-date");
                    document.getElementById("appointmentTime").textContent = this.getAttribute("data-time");
                    // Clear the reason input when opening the modal
                    document.getElementById("cancelReason").value = "";
                });
            });

            // Handle Confirm Cancel button click
            document.getElementById("confirmCancelButton").addEventListener("click", function () {
                const reason = document.getElementById("cancelReason").value.trim();

                if (!reason) {
                    // Show toast notification
                    const toast = new bootstrap.Toast(document.getElementById('errorToast'));
                    toast.show();
                    return;
                }

                // Create FormData object
                const formData = new URLSearchParams();
                formData.append('appointmentID', selectedAppointmentID);
                formData.append('reason', reason);
                formData.append('action', 'cancel'); // Add action parameter

                // Close the cancel modal
                const cancelModal = bootstrap.Modal.getInstance(document.getElementById('cancelModal'));
                cancelModal.hide();

                // Make the cancellation request using fetch
                fetch("cancel_appointment.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success modal
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();

                        // Reload the page after success modal is closed
                        document.getElementById('successModal').addEventListener('hidden.bs.modal', function () {
                            window.location.reload();
                        }, { once: true });
                    } else {
                        alert('Error cancelling appointment: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the appointment');
                });
            });
        });

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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Feedback button click handler
        document.querySelectorAll(".feedback-button").forEach(button => {
            button.addEventListener("click", function() {
                const appointmentId = this.getAttribute("data-id");
                document.getElementById("feedbackAppointmentID").value = appointmentId;
            });
        });

        // Submit feedback handler
        document.getElementById("submitFeedback").addEventListener("click", function() {
            const appointmentId = document.getElementById("feedbackAppointmentID").value;
            const comment = document.getElementById("feedbackComment").value;

            if (!comment) {
                alert("Please enter your feedback");
                return;
            }

            // Send feedback to server
            fetch("submit_feedback.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    appointmentId: appointmentId,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the feedback modal
                    const feedbackModal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
                    feedbackModal.hide();

                    // Show success modal
                    const successModal = new bootstrap.Modal(document.getElementById('feedbackSuccessModal'));
                    successModal.show();

                    // Reload the page after success modal is closed
                    document.getElementById('feedbackSuccessModal').addEventListener('hidden.bs.modal', function () {
                        window.location.reload();
                    }, { once: true });
                } else {
                    alert("Error submitting feedback: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while submitting feedback");
            });
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>
</html>
