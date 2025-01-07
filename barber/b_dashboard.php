<?php
session_start();

// Check if the user is logged in as a barber
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "barber") {
    header("Location: ../login-staff.php"); // Redirect to login if not logged in or not a barber
    exit();
}

// Get the logged-in barber's ID from the session
$barberID = $_SESSION["barberID"];

include('db_connect.php');

// Query for Pending Customers
$pendingQuery = "
    SELECT COUNT(*) AS pending_count
    FROM barb_apps_tbl ba
    JOIN appointment_tbl a ON ba.appointmentID = a.appointmentID
    WHERE a.status = 'Pending' AND a.date = CURDATE() AND ba.barberID = '$barberID'
";
$pendingResult = mysqli_query($conn, $pendingQuery);
if (!$pendingResult) {
    echo "Error in pending query: " . mysqli_error($conn);
}
$pendingCount = mysqli_fetch_assoc($pendingResult)['pending_count'];

// Query for Completed Customers
$completedQuery = "
    SELECT COUNT(*) AS completed_count
    FROM barb_apps_tbl ba
    JOIN appointment_tbl a ON ba.appointmentID = a.appointmentID
    WHERE a.status = 'Completed' AND a.date = CURDATE() AND ba.barberID = '$barberID'
";
$completedResult = mysqli_query($conn, $completedQuery);
if (!$completedResult) {
    echo "Error in completed query: " . mysqli_error($conn);
}
$completedCount = mysqli_fetch_assoc($completedResult)['completed_count'];

// Query for Upcoming Customers Today
$upcomingQuery = "
    SELECT 
        a.appointmentID,
        CONCAT(c.firstName, ' ', c.lastName) AS fullName,
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
        a.date = CURDATE() 
        AND a.status = 'Pending'
        AND ba.barberID = '$barberID'
    ORDER BY 
        a.timeSlot ASC
";
$upcomingResult = mysqli_query($conn, $upcomingQuery);
if (!$upcomingResult) {
    echo "Error in upcoming query: " . mysqli_error($conn);
}

// Query for Previous Customers Today
$previousQuery = "
    SELECT 
        a.appointmentID,
        CONCAT(c.firstName, ' ', c.lastName) AS fullName,
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
        a.date = CURDATE() 
        AND a.status = 'Completed'
        AND ba.barberID = '$barberID'
    ORDER BY 
        a.timeSlot ASC
";
$previousResult = mysqli_query($conn, $previousQuery);
if (!$previousResult) {
    echo "Error in previous query: " . mysqli_error($conn);
}

// Query for Total Barber Earnings (Income)
$incomeQuery = "
    SELECT SUM(e.barberEarnings) AS total_income
    FROM earnings_tbl e
    JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
    WHERE DATE(a.date) = CURDATE() AND e.barberID = '$barberID'
";
$incomeResult = mysqli_query($conn, $incomeQuery);
if (!$incomeResult) {
    echo "Error in income query: " . mysqli_error($conn);
}
$totalIncome = mysqli_fetch_assoc($incomeResult)['total_income'];
$totalIncome = !empty($totalIncome) ? number_format($totalIncome, 2) : '0.00';  // Format the income
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
    <title>Barber Dashboard</title>
    <style>
        body {
            background-color: #000000;
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
        .alert {
            background-color: white !important;
            border-radius: 15px !important;
            padding: 20px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .alert-warning {
            background-color: white !important;
            border: none !important;
            color: black !important;
        }
        .stat-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        .stat-icon.pending { color: #FFDE59; }
        .stat-icon.completed { color: #FFDE59; }
        .stat-icon.revenue { color: #FFDE59; }
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
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            border-bottom: 1px solid #eee;
            color: #000;
            font-weight: 600;
            padding: 12px 8px;
        }
        .table tbody td {
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
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
    </style>
</head>
<body>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <div class="position-relative">
                <h1 class="dashboard mb-5 ms-5">Barber Dashboard</h1>
                <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4 ms-5">
                <div class="col">
                    <div class="alert-warning alert mb-0">
                        <div class="d-flex align-items-center">
                            <div><i class="fa-solid fa-users fa-lg"></i></div>
                            <div class="flex-fill ms-3 text-truncate">
                                <div class="h5 mb-0 mt-2">Pending Customers</div>
                                <div class="h5 mb-0"><?php echo $pendingCount; ?></div> <!-- Display Pending Count -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="alert-warning alert mb-0">
                        <div class="d-flex align-items-center">
                            <div><i class="fa-solid fa-circle-check fa-lg"></i></div>
                            <div class="flex-fill ms-3 text-truncate">
                                <div class="h5 mb-0 mt-2">Completed</div>
                                <div class="h5 mb-0"><?php echo $completedCount; ?></div> <!-- Display Completed Count -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="alert-warning alert mb-0">
                        <div class="d-flex align-items-center">
                            <div><i class="fa-solid fa-peso-sign fa-lg" aria-hidden="true"></i></div>
                            <div class="flex-fill ms-3 text-truncate">
                                <div class="h5 mb-0 mt-2">Total Income</div>
                                <div class="h5 mb-0"><?php echo $totalIncome; ?></div> <!-- Display Total Income -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3 ms-5">
                <!-- Upcoming Customers -->
                <div class="col-md-12">
                    <div class="card border-0 rounded-4">
                        <div class="card-header py-3 bg-white">
                            <h4 class="mb-0">Upcoming Customers Today</h4>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="60%">Name</th>
                                        <th width="30%">Time</th>
                                        <th>Service</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $counter = 1;

                                        if ($upcomingResult && mysqli_num_rows($upcomingResult) > 0) {
                                            while ($row = mysqli_fetch_assoc($upcomingResult)) {
                                                echo "<tr>";
                                                echo "<td>{$counter}</td>";
                                                echo "<td>{$row['fullName']}</td>";
                                                echo "<td>{$row['timeSlot']}</td>";
                                                echo "<td>{$row['serviceName']}</td>";
                                                echo "</tr>";
                                                $counter++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center'>No upcoming appointments found.</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Previous Customers -->
                <div class="col-md-12">
                    <div class="card border-0 rounded-4">
                        <div class="card-header py-3 bg-white">
                            <h4 class="mb-0">Previous Customers Today</h4>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="60%">Name</th>
                                        <th width="30%">Time</th>
                                        <th>Service</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $counter = 1;

                                        if ($previousResult && mysqli_num_rows($previousResult) > 0) {
                                            while ($row = mysqli_fetch_assoc($previousResult)) {
                                                echo "<tr>";
                                                echo "<td>{$counter}</td>";
                                                echo "<td>{$row['fullName']}</td>";
                                                echo "<td>{$row['timeSlot']}</td>";
                                                echo "<td>{$row['serviceName']}</td>";
                                                echo "</tr>";
                                                $counter++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center'>No previous appointments found.</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
                <div class="position-sticky">
                    <div class="list-group list-group-flush mx-3 mt-5">
                        <div class="avatar-container text-center">
                            <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo mb-4">
                            <img src="css/images/barber.jpg" alt="Avatar" width="140" height="140" style="border: 5px solid #000000; border-radius: 50%;" class="avatar">
                            <h5 class="mt-3">Barber</h5>
                        </div>
                        <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple active">
                            <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                        </a>
                        <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
                            <i class="fa-solid fa-users fa-fw me-3"></i><span>Schedule</span>
                        </a>
                        <a href="b_history.php" class="list-group-item list-group-item-action py-2 ripple">
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
        </div>
    </div>
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
