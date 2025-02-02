<?php
session_start();

// Check if the user is logged in as a barber
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "barber") {
    header("Location: ../login-staff.php"); // Redirect to login if not logged in or not a barber
    exit();
}

// Get the logged-in barber's ID from the session
$barberID = $_SESSION["barberID"];

include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Customer History</title>
    <style>
        /* ... */
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
        /* Logo and Avatar container styling */
        .avatar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .avatar-container .logo {
            margin-bottom: 30px;
        }
        .avatar-container img.avatar {
            display: block;
            margin: 0 auto;
        }
        .avatar-container h5 {
            margin-top: 10px;
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

        /* Add these date picker styles */
        .dropdown-menu {
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
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
        }

        /* Add this to ensure the dropdown has enough space */
        .dropdown {
            margin-bottom: 50px;
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
            .col-md-12.ms-5 {
                margin-left: 0 !important;
            }
            
            /* Card adjustments */
            .card {
                margin-right: 15px;
            }
            
            /* Table section adjustments */
            .table-responsive {
                margin-right: 15px;
            }
        }

        /* Update mobile styles */
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
            
            /* Adjust calendar container on mobile */
            .calendar-container {
                margin: 0 10px;
            }
            
            /* Adjust table container on mobile */
            .table-responsive {
                margin: 0;
                padding: 0;
            }
            
            .card {
                margin: 0 10px;
            }
            
            /* Adjust calendar dropdown on mobile */
            .dropdown {
                margin: 0 10px 20px 10px;
            }
            
            /* Adjust text sizes for better mobile display */
            .card-header h4 {
                font-size: 1rem;
            }
            
            /* Ensure proper spacing for content */
            .col-md-12.ms-5 {
                margin-left: 0 !important;
                padding: 0;
            }
        }

        /* Responsive table styles */
        @media screen and (max-width: 768px) {
            .table {
                white-space: nowrap;
            }
            
            .table th, 
            .table td {
                padding: 8px !important;
            }
            
            .card-body {
                padding: 10px;
            }
            
            .card-header {
                padding: 12px;
            }
            
            /* Adjust calendar styles for better mobile view */
            .calendar-weekdays li, 
            .calendar-dates li {
                font-size: 14px;
                height: 35px;
                width: 35px;
                line-height: 35px;
            }
            
            .calendar-header {
                padding: 10px;
            }
            
            .calendar-navigation span {
                font-size: 20px;
            }
        }

        /* Add background color for body */
        body {
            background-color: #000000;
        }

        /* Add text color for dashboard heading */
        .dashboard {
            color: white;
        }
    </style>
</head>
<body>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <div class="position-relative">
                <h1 class="dashboard mb-5 ms-5">Customer History</h1>
                <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="col-md-12 ms-5">
                <div class="row mb-3">
                    <div class="col-md-4">
                    </div>
                </div>

                <div class="card border-0 rounded-4"> 
                    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                        <h4 class="ms-2 mt-2 fw-bold" style="color: black;">Previous Customers</h4>
                        <h4 style="color: black;">Total: <?php
                            // Query to count the number of completed appointments
                            $countQuery = "
                                SELECT COUNT(*) AS total_previous
                                FROM barb_apps_tbl ba
                                JOIN appointment_tbl a ON ba.appointmentID = a.appointmentID
                                WHERE a.date AND a.status = 'Completed'
                            ";
                            $countResult = mysqli_query($conn, $countQuery);
                            if ($countResult) {
                                $countData = mysqli_fetch_assoc($countResult);
                                echo $countData['total_previous'];
                            } else {
                                echo "0"; // In case of an error, show 0
                            }
                        ?></h4>
                    </div>
                    <div class="card-body"> 
                        <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Service</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query to display previous customers for the logged-in barber
                                $previousQuery = "SELECT 
                                    a.appointmentID,
                                    CONCAT(c.firstName, ' ', c.lastName) AS fullName,
                                    a.date,
                                    a.timeSlot,
                                    s.serviceName
                                FROM 
                                    appointment_tbl a
                                LEFT JOIN 
                                    customer_tbl c ON a.customerID = c.customerID
                                LEFT JOIN 
                                    barb_apps_tbl ba ON a.appointmentID = ba.appointmentID
                                LEFT JOIN 
                                    service_tbl s ON a.serviceID = s.serviceID
                                WHERE 
                                    a.date 
                                    AND a.status = 'Completed'
                                    AND ba.barberID = '$barberID'
                                ORDER BY 
                                    a.timeSlot ASC";
                                $previousResult = mysqli_query($conn, $previousQuery);

                                // Check if there are any previous results
                                if (mysqli_num_rows($previousResult) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($previousResult)) {
                                        $fullName = !empty($row['fullName']) ? $row['fullName'] : 'Walk In';  // Use "Walk In" if no name exists
                                        $timeSlot = $row['timeSlot'];
                                        $date = $row['date'];  
                                        $serviceName = !empty($row['serviceName']) ? $row['serviceName'] : 'No Service';  // Ensure service name is set
                                        echo "<tr>
                                                <td>{$no}</td>
                                                <td>{$fullName}</td>
                                                <td>{$date}</td>
                                                <td>{$timeSlot}</td>
                                                <td>{$serviceName}</td>
                                              </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No Previous Customers</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
        <div class="position-sticky">
            <div class="list-group list-group-flush mx-3 mt-5">
                <div class="avatar-container text-center">
                    <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo mb-4">
                    <h5 class="mt-3" style="font-weight: bold; font-size: 20px;">Barber</h5>
                </div>
                <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                </a>
                <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-users fa-fw me-3"></i><span>Schedule</span>
                </a>
                <a href="b_history.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
                </a>
                <a href="income.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-money-bill-trend-up fa-fw me-3"></i><span>Income</span>
                </a>
                <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebarMenu');
            const toggle = document.querySelector('.mobile-toggle');
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="js/calendar.js"></script>
    <script>
        // Initialize Bootstrap dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Prevent dropdown from closing when clicking inside calendar
            document.querySelector('.calendar-container').addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>
