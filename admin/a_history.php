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
    <title>Appointment History</title>
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

        /* Update the calendar responsive styles */
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
                font-size: 13px;
                height: 35px;
                width: 35px;
                line-height: 35px;
            }
        }

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
                font-size: 12px;
                height: 33px;
                width: 33px;
                line-height: 33px;
            }
            
            .calendar-header {
                padding: 12px !important;
            }
            
            .calendar-navigation span {
                font-size: 20px;
            }
            
            .calendar-current-date {
                font-size: 15px;
            }
        }

        /* Add this to ensure the dropdown has enough space */
        .dropdown {
            margin-bottom: 50px;
        }

        /* Add these responsive table styles */
        @media screen and (max-width: 712px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .card {
                margin: 0 -15px;
                border-radius: 0 !important;
            }

            .card-body {
                padding: 10px;
            }

            table {
                white-space: nowrap;
                font-size: 14px;
            }

            /* Adjust column widths for better mobile view */
            table th, table td {
                min-width: 100px;
                padding: 8px !important;
            }

            /* Make sure the money columns don't wrap */
            table th:first-child, 
            table td:first-child {
                min-width: 120px; /* Name column */
            }
            
            table th:nth-child(2), 
            table td:nth-child(2) {
                min-width: 100px; /* Date/Time column */
            }
            
            table th:nth-child(3), 
            table td:nth-child(3) {
                min-width: 100px; /* Service/Time column */
            }
            
            table th:nth-child(4), 
            table td:nth-child(4) {
                min-width: 150px; /* Reason/Barber column */
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
            .row.mb-4.ms-5 {
                margin-left: 0 !important;
            }
            
            /* Table section adjustments */
            .row.ms-5 {
                margin-left: 0 !important;
            }
            
            /* Card adjustments */
            .card {
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
            
            /* Adjust table container on mobile */
            .table-responsive {
                margin: 0;
                padding: 0;
            }
            
            .card {
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'db_connect.php'; ?>

    <!-- Add the mobile toggle button -->
    <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <h1 class="dashboard mb-5 ms-5">Appointments History</h1>

            <!-- Cancelled Appointments Row -->
            <div class="row mb-4 ms-5">
                <div class="col-md-12">
                    <div class="card p-3">
                        <h4 class="fw-bold mb-8">Cancelled</h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        <?php
                                            // Modify the query to select only cancelled appointments for the current date
                                            $cancelledQuery = "SELECT a.*, c.firstName, c.lastName 
                                            FROM appointment_tbl a
                                            LEFT JOIN customer_tbl c ON a.customerID = c.customerID
                                            WHERE a.status = 'Cancelled' 
                                            AND a.date";
                                            
                                            $cancelledResult = mysqli_query($conn, $cancelledQuery);
                                            
                                            if ($cancelledResult && mysqli_num_rows($cancelledResult) > 0) {
                                                while ($row = mysqli_fetch_assoc($cancelledResult)) {
                                                    // Check if firstName or lastName is null (for admin bookings)
                                                    $firstName = isset($row['firstName']) ? $row['firstName'] : 'Admin';
                                                    $lastName = isset($row['lastName']) ? $row['lastName'] : 'Booking';
                                            
                                                    echo "<tr>
                                                            <td>{$firstName} {$lastName}</td>
                                                            <td>{$row['date']}</td>
                                                            <td>{$row['timeSlot']}</td>
                                                            <td>{$row['reason']}</td>
                                                        </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4' class='text-center'>No cancelled appointments found for today.</td></tr>";
                                            }
                                        ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previous Customers Row -->
            <div class="row mb-4 ms-5">
                <div class="col-md-12">
                    <div class="card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold">Previous Customers</h4>
                            <!-- <h4>Total: <?php echo $countData['total_previous'] ?? '0'; ?></h4> -->
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">  
                                    <thead>
                                        <tr>
                                            <td>Name</td>
                                            <td>Date</td>
                                            <td>Time</td>
                                            <td>Service</td>
                                            <td>Barber</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $completedQuery = "SELECT 
                                                a.appointmentID,
                                                a.date,
                                                a.timeSlot,
                                                a.status,
                                                CASE 
                                                    WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
                                                    ELSE 'Walk In' 
                                                END AS fullName,
                                                s.serviceName, 
                                                b.firstName AS barberFirstName, 
                                                b.lastName AS barberLastName
                                            FROM 
                                                appointment_tbl a
                                            LEFT JOIN 
                                                customer_tbl c ON a.customerID = c.customerID
                                            LEFT JOIN 
                                                service_tbl s ON a.serviceID = s.serviceID
                                            LEFT JOIN 
                                                barb_apps_tbl ba ON a.appointmentID = ba.appointmentID
                                            LEFT JOIN 
                                                barbers_tbl b ON b.barberID = ba.barberID
                                            WHERE 
                                                a.date AND a.status = 'Completed'
                                            GROUP BY
                                                a.appointmentID
                                            ORDER BY 
                                                a.timeSlot DESC";

                                            $completedResult = mysqli_query($conn, $completedQuery);
                                            
                                            if ($completedResult && mysqli_num_rows($completedResult) > 0) {
                                                while ($row = mysqli_fetch_assoc($completedResult)) {
                                                    echo "<tr>
                                                            <td>{$row['fullName']}</td>
                                                            <td>{$row['date']}</td>
                                                            <td>{$row['timeSlot']}</td>
                                                            <td>{$row['serviceName']}</td>
                                                            <td>{$row['barberFirstName']} {$row['barberLastName']}</td>
                                                          </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4' class='text-center'>No completed appointments found.</td></tr>";
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
                <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-users fa-fw me-3"></i><span>Appointment</span>
                </a>
                <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple active">
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
    <script src="js/calendar.js"></script>
    <script>
        // Initialize Bootstrap dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            });
        });

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
</body>
</html>
