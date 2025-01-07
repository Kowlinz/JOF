<?php
    session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
        exit;
    }

    include 'db_connect.php'; // Include database connection

    // Query to fetch today's earnings
    $todayDate = date('Y-m-d'); // Current date
    $todayQuery = "SELECT e.adminEarnings, e.barberEarnings, CONCAT(b.firstName, ' ', b.lastName) AS barberFullName, a.date, a.timeSlot
                   FROM earnings_tbl e 
                   JOIN barbers_tbl b ON e.barberID = b.barberID
                   JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
                   WHERE DATE(a.date) = ?";
    $stmtToday = $conn->prepare($todayQuery);
    $stmtToday->bind_param("s", $todayDate);
    $stmtToday->execute();
    $resultToday = $stmtToday->get_result();
    
    // Query to fetch previous earnings
    $previousQuery = "SELECT e.adminEarnings, e.barberEarnings, CONCAT(b.firstName, ' ', b.lastName) AS barberFullName, a.date, a.timeSlot
                      FROM earnings_tbl e 
                      JOIN barbers_tbl b ON e.barberID = b.barberID
                      JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
                      WHERE DATE(a.date) < ?";
    $stmtPrevious = $conn->prepare($previousQuery);
    $stmtPrevious->bind_param("s", $todayDate);
    $stmtPrevious->execute();
    $resultPrevious = $stmtPrevious->get_result();

    // Query to calculate total adminEarnings for today
    $totalAdminEarningsQuery = "SELECT SUM(adminEarnings) AS totalAdminEarnings 
                                FROM earnings_tbl e 
                                JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
                                WHERE DATE(a.date) = ?";
    $stmtTotalAdmin = $conn->prepare($totalAdminEarningsQuery);
    $stmtTotalAdmin->bind_param("s", $todayDate);
    $stmtTotalAdmin->execute();
    $resultTotalAdmin = $stmtTotalAdmin->get_result();
    $totalAdminEarnings = $resultTotalAdmin->fetch_assoc()['totalAdminEarnings'] ?? 0; // Default to 0 if null
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
    <title>Earnings</title>
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
        /* Logo and Avatar container styling */
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
        .alert {
            background-color: white !important;
            border-radius: 15px !important;
            padding: 20px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        .stat-icon.revenue { 
            color: #F3CD32; 
        }
        .h5 { 
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }
        .h6 {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin: 0;
        }
        .card {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            border-bottom: 1px solid #eee;
        }
        .card-header h4 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #000;
        }
        /* Responsive text styles */
        .h5 {
            font-size: clamp(14px, 2vw, 16px);
            word-wrap: break-word;
        }
        
        .h6 {
            font-size: clamp(18px, 2.5vw, 24px);
        }
        
        /* Responsive card styles */
        .alert {
            padding: clamp(15px, 3vw, 20px) !important;
        }
        
        @media (max-width: 576px) {
            .stat-icon {
                font-size: 20px;
                margin-right: 10px;
            }
            
            .alert {
                margin: 0 -10px;
            }
        }

        /* Responsive table styles */
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
            table th:nth-child(1), 
            table td:nth-child(1),
            table th:nth-child(2), 
            table td:nth-child(2),
            table th:nth-child(4), 
            table td:nth-child(4) {
                min-width: 120px;
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
            
            /* Stats card adjustments */
            .row.g-1.mb-5.ms-5 {
                margin-left: 0 !important;
            }
            
            /* Table section adjustments */
            .row.g-3.mb-5.ms-5 {
                margin-left: 0 !important;
            }
            
            /* Card adjustments */
            .card {
                margin-right: 15px;
            }
            
            /* Alert adjustments */
            .alert {
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
            
            /* Adjust alert on mobile */
            .alert {
                margin: 0 10px 15px 10px;
            }
            
            /* Adjust stats card spacing */
            .row.g-1.mb-5 {
                margin: 0 0 20px 0;
            }
            
            /* Ensure proper spacing for earnings table */
            .row.g-3.mb-5 {
                margin: 0;
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
        <h1 class="dashboard mb-5 ms-5">Earnings</h1>
        <div class="row g-1 mb-5 ms-5">
    <div class="col-md-4 col-sm-6">
        <div class="alert mb-0">
            <div class="d-flex align-items-center">
                <div class="stat-icon revenue">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <div class="flex-fill text-wrap">
                    <div class="h5">Today's Admin Earnings</div>
                    <div class="h6">₱<?= number_format($totalAdminEarnings, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="row g-3 mb-5 ms-5">
            <div class="col-md-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold">Today</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">  
                                <thead>
                                    <tr>
                                        <th>Total</th>
                                        <th>My Earnings</th>
                                        <th>Barber Name</th>
                                        <th>Barber Earnings</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultToday->num_rows > 0): ?>
                                        <?php while ($row = $resultToday->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= number_format($row['adminEarnings'] + $row['barberEarnings'], 2) ?></td>
                                                <td><?= number_format($row['adminEarnings'], 2) ?></td>
                                                <td><?= htmlspecialchars($row['barberFullName']) ?></td>
                                                <td><?= number_format($row['barberEarnings'], 2) ?></td>
                                                <td><?= htmlspecialchars($row['timeSlot']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No earnings data for today</td>
                                        </tr>
                                    <?php endif; ?>
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
            <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
            </a>
            <a href="earnings.php" class="list-group-item list-group-item-action py-2 ripple active">
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

    <!-- Add this script before closing body tag -->
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
</body>
</html>
