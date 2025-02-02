<?php
    session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="css/images/favicon-32x32.png">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Appointments</title>
    <style>
        body {
            background-color: #090909;
        }
        .dashboard {
            color: white;
        }
        /* Sidebar styling */
        .sidebar {
            background-color: #F3CD32 !important;
            min-height: 100vh;
        }
        .list-group-item {
            background-color: transparent !important;
            border: none !important;
            color: black !important;
            border-radius: 10px !important;
            margin-bottom: 5px;
        }
        .list-group-item:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }
        .list-group-item.active {
            background-color: black !important;
            color: #F3CD32 !important;
            border-radius: 10px !important;
        }
        .list-group-item.active i,
        .list-group-item.active span {
            color: #F3CD32 !important;
        }
        /* Avatar container styling */
        .avatar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .avatar-container .logo {
            margin-bottom: 20px;
        }
        .avatar-container img.avatar {
            display: block;
            margin: 0 auto;
        }
        .avatar-container h5 {
            margin-top: 10px;
            text-align: center;
        }
        /* Add these date picker styles */
        .dropdown-menu {
            padding: 8px 0 !important;
            background: white !important;
            border: 1px solid rgba(0,0,0,.15) !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            min-width: 120px !important;
        }
        .calendar-container {
            background: white;
            border-radius: 4px;
            padding: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-secondary {
            background-color: #F3CD32;
            border-color: #F3CD32;
            color: black;
        }
        .btn-secondary:hover {
            background-color: #dbb82e;
            border-color: #dbb82e;
            color: black;
        }
        /* Ensure dropdown menu shows properly */
        .dropdown-menu.show {
            display: block !important;
        }
        /* Table responsiveness styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                margin: 0 -15px;
            }
            
            .card {
                border-radius: 0;
            }
            
            .container-xxl {
                padding: 0 15px;
            }
        }
        /* Add these calendar responsive styles */
        @media screen and (max-width: 768px) {
            .calendar-container {
                transform: scale(0.95);
                transform-origin: top left;
                margin-bottom: 15px;
            }
            
            .dropdown-menu {
                width: 290px !important;
                padding: 10px !important;
                margin-top: 5px !important;
            }
            
            .calendar-header {
                padding: 15px !important;
            }
            
            .calendar-body {
                padding: 10px !important;
            }
            
            .calendar-weekdays li, 
            .calendar-dates li {
                font-size: 14px;
                height: 40px;
                width: 40px;
                line-height: 40px;
            }
            
            .calendar-navigation span {
                font-size: 22px;
            }

            .calendar-current-date {
                font-size: 16px;
            }
        }

        @media screen and (max-width: 576px) {
            .calendar-container {
                transform: scale(0.85);
                margin-bottom: 25px;
            }
            
            .dropdown-menu {
                width: 260px !important;
                padding: 15px !important;
            }
            
            .calendar-weekdays li, 
            .calendar-dates li {
                font-size: 12px;
            }
        }

        /* Add new styles for very small screens */
        @media screen and (max-width: 505px) {
            .calendar-container {
                transform: scale(0.8);
                margin-bottom: 30px;
            }
            
            .dropdown-menu {
                width: 240px !important;
                margin-left: -20px;
                padding: 20px !important;
                min-height: 380px;
            }
            
            .calendar-weekdays li, 
            .calendar-dates li {
                font-size: 11px;
                height: 30px;
                width: 30px;
                line-height: 30px;
            }
            
            .calendar-header {
                padding: 8px !important;
            }
            
            .calendar-navigation span {
                font-size: 18px;
            }
            
            .calendar-current-date {
                font-size: 14px;
            }
        }

        /* Add this to ensure the dropdown has enough space */
        .dropdown {
            margin-bottom: 50px;
        }

        /* Mobile toggle button styling */
        .mobile-toggle {
            position: fixed;
            top: 25px;
            left: 20px;
            z-index: 1000;
            background: none;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            display: none;
            color: #F3CD32;
            font-size: 24px;
        }

        /* Show toggle button only on smaller screens */
        @media (max-width: 991.98px) {
            .mobile-toggle {
                display: block;
                position: fixed;
                top: 25px;
                left: 20px;
            }
            .sidebar {
                display: none;
                background-color: #F3CD32 !important;
            }
            .sidebar.show {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 240px;
                z-index: 999;
            }
        }

        /* Adjust main content area to account for sidebar */
        .container-xxl {
            padding-left: 260px; /* Width of sidebar + some padding */
            width: 100%;
            transition: padding-left 0.3s ease;
        }

        /* Sidebar positioning */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            z-index: 999;
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .container-xxl {
                padding-left: 15px; /* Reset padding on mobile */
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }

        /* Adjust margin for content */
        @media (min-width: 992px) and (max-width: 1680px) {
            .ms-5 {
                margin-left: 0 !important;
            }
            
            .dashboard.mb-5.ms-5 {
                margin-left: 0 !important;
            }
            
            /* Calendar section adjustments */
            .row.ms-5.mb-4 {
                margin-left: 0 !important;
            }
            
            /* Table section adjustments */
            .row.ms-5 {
                margin-left: 0 !important;
            }
        }

        /* Add these new styles for the dropdown items */
        .dropdown-item {
            padding: 8px 16px;
            color: #212529;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'db_connect.php'; ?>

    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        $status = $_GET['status'];
        $message = $_GET['message'];

        // Display a JavaScript alert with the message
        echo "
        <script>
            window.onload = function() {
                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                document.getElementById('statusMessage').innerText = '$message';
                modal.show();
            }
        </script>";
    }
    ?>
<div class="body d-flex py-3 mt-5">
    <div class="container-xxl">
        <div class="position-relative">
            <h1 class="dashboard mb-5 ms-5">Appointments</h1>
            <button class="mobile-toggle d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <?php

        // Check if there is a status or message in the query string
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $status = $_GET['status']; // success or error
            $message = $_GET['message']; // Message to display in the popup

            // Display a JavaScript alert with the message
            echo "
            <script>
                window.onload = function() {
                    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                    document.getElementById('statusMessage').innerText = '$message';
                    modal.show();
                }
            </script>";
        }
        ?>

        <!-- Add Customer Button -->
        <div class="row ms-5 mb-4">
            <div class="col-12">
                <button class="btn btn-warning" onclick="window.location.href='walk-in.php';">+ Add Walk-In Customer</button>
            </div>
        </div>

        <!-- Bottom Section: Upcoming Customers -->
        <div class="row ms-5">
            <div class="col-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold">Upcoming Customers</h4>
                        <?php
                            $countQuery = "
                                SELECT COUNT(*) AS total_upcoming
                                FROM appointment_tbl a
                                LEFT JOIN customer_tbl c ON a.customerID = c.customerID
                                WHERE a.date AND a.status = 'Pending'
                            ";
                            $countResult = mysqli_query($conn, $countQuery);
                            $countData = mysqli_fetch_assoc($countResult);
                            $totalUpcoming = $countData['total_upcoming'];
                        ?>
                        <h4>Total: <?php echo $totalUpcoming; ?></h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Service</th>
                                    <th>Barber</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Modified query to include barberID from the barb_apps_tbl table
                                    $upcomingQuery = "
                                        SELECT 
                                            a.appointmentID,
                                            a.date,
                                            a.timeSlot,
                                            a.status,
                                            CASE 
                                                WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
                                                ELSE 'Walk In' 
                                            END AS fullName,
                                            s.serviceName, 
                                            ba.barberID
                                        FROM 
                                            appointment_tbl a
                                        LEFT JOIN 
                                            customer_tbl c ON a.customerID = c.customerID
                                        LEFT JOIN 
                                            service_tbl s ON a.serviceID = s.serviceID
                                        LEFT JOIN 
                                            barb_apps_tbl ba ON a.appointmentID = ba.appointmentID
                                        WHERE 
                                            a.date AND a.status = 'Pending'
                                        ORDER BY 
                                            a.timeSlot ASC
                                    ";
                                    $upcomingResult = mysqli_query($conn, $upcomingQuery);

                                    // Modified query to fetch only available barbers
                                    $barbersQuery = "SELECT * FROM barbers_tbl WHERE availability = 'available'";
                                    $barbersResult = mysqli_query($conn, $barbersQuery);
                                    $barbers = [];
                                    if ($barbersResult && mysqli_num_rows($barbersResult) > 0) {
                                        while ($barberRow = mysqli_fetch_assoc($barbersResult)) {
                                            $barbers[] = $barberRow;
                                        }
                                    }

                                    $counter = 1;

                                    // Check if there are any upcoming appointments
                                    if ($upcomingResult && mysqli_num_rows($upcomingResult) > 0) {
                                        while ($row = mysqli_fetch_assoc($upcomingResult)) {
                                            echo "  <tr>
                                                    <td>{$counter}</td>
                                                    <td>{$row['fullName']}</td>
                                                    <td>{$row['date']}</td>
                                                    <td>{$row['timeSlot']}</td>
                                                    <td>{$row['serviceName']}</td>
                                                    <td>
                                                        <form action='assign_barber.php' method='POST'>
                                                            <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                            <select name='barberID' class='form-select' onchange='this.form.submit()'>
                                                                <option value='' disabled selected hidden>Select Barber</option>";
                                                                // Loop through all barbers to display them in the dropdown
                                                                foreach ($barbers as $barber) {
                                                                    // Check if the barber is assigned to this appointment
                                                                    $selected = ($row['barberID'] == $barber['barberID']) ? "selected" : "";
                                                                    echo "<option value='{$barber['barberID']}' $selected>{$barber['firstName']} {$barber['lastName']}</option>";
                                                                }
                                                            echo "</select>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <div class='dropdown'>
                                                            <i class='fas fa-ellipsis-v' style='cursor: pointer;' data-bs-toggle='dropdown'></i>
                                                            <ul class='dropdown-menu'>
                                                                <li>
                                                                    <form action='update_status.php' method='POST' style='display: inline;'>
                                                                        <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                                        <input type='hidden' name='status' value='Completed'>
                                                                        <button type='submit' class='dropdown-item'>
                                                                            <i class='fas fa-check text-success'></i> Done
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form action='update_status.php' method='POST' style='display: inline;'>
                                                                        <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                                        <input type='hidden' name='status' value='Cancelled'>
                                                                        <button type='submit' class='dropdown-item'>
                                                                            <i class='fas fa-times text-danger'></i> Cancel
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>";
                                                $counter++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>No upcoming appointments found.</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
    <div class="position-sticky">
        <div class="list-group list-group-flush mx-3 mt-5">
            <div class="avatar-container">
                <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo">
                <img src="css/images/admin.jpg" alt="Avatar" width="140" height="140" style="border: 5px solid #000000; border-radius: 50%;" class="avatar">
                <h5>Admin</h5>
            </div>
            <a href="a_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
            </a>
            <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple active">
                <i class="fa-solid fa-users fa-fw me-3"></i><span>Appointment</span>
            </a>
            <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
            </a>
            <a href="earnings.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-money-bill-trend-up fa-fw me-3"></i><span>Earnings</span>
            </a>
            <a href="barbers.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-scissors fa-fw me-3"></i><span>Barbers</span>
            </a>
            <a href="options.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-gear fa-fw me-3"></i><span>Options</span>
            </a>
            <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
            </a>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
<script src="js/calendar.js"></script>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebarMenu');
        sidebar.classList.toggle('show');
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebarMenu');
        const toggle = document.querySelector('.mobile-toggle');
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.querySelector('.mobile-toggle');
        if (toggleButton) {
            toggleButton.setAttribute('onclick', 'toggleSidebar()');
        }
    });
</script>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="statusMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
