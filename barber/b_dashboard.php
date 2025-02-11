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

// Query to get the barber's full name
$barberQuery = "SELECT firstName, lastName FROM barbers_tbl WHERE barberID = '$barberID'";
$barberResult = mysqli_query($conn, $barberQuery);
if (!$barberResult) {
    echo "Error fetching barber's name: " . mysqli_error($conn);
}
$barber = mysqli_fetch_assoc($barberResult);
$barberFullName = $barber ? $barber['firstName'] . ' ' . $barber['lastName'] : 'Unknown Barber'; // Default if no name found

// Fetch the number of pending appointments and their status
$notificationQuery = "
SELECT COUNT(*) AS notif_count 
FROM barb_apps_tbl AS b 
JOIN appointment_tbl AS a 
ON b.appointmentID = a.appointmentID 
WHERE b.barberID = '$barberID' 
AND a.status = 'Pending'
";
    
$notificationResult = mysqli_query($conn, $notificationQuery);
$notificationData = mysqli_fetch_assoc($notificationResult);
$notificationCount = $notificationData['notif_count'];
    

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
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/barber.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <title>Barber Dashboard</title>

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
                    <div class="alert mb-0">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon pending">
                                <i class="fa-solid fa-clock fa-fw"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="h5 pending-label">Pending Customers</div>
                                <div class="h6"><?php echo $pendingCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="alert mb-0">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon completed">
                                <i class="fa-solid fa-check fa-fw"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="h5">Completed</div>
                                <div class="h6"><?php echo $completedCount; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="alert mb-0">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon revenue">
                                <i class="fa-solid fa-peso-sign fa-fw"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="h5">Total Income</div>
                                <div class="h6">₱<?php echo $totalIncome; ?></div>
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
                            <h2 class="fw-bold">Upcoming Customers Today</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td>#</td>
                                            <td>Name</td>
                                            <td>Time</td>
                                            <td>Service</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Check if there are any upcoming results
                                        if (mysqli_num_rows($upcomingResult) > 0) {
                                            $counter = 1;
                                            while ($row = mysqli_fetch_assoc($upcomingResult)) {
                                                $fullName = !empty($row['fullName']) ? $row['fullName'] : 'Walk In';
                                                $timeSlot = $row['timeSlot'];
                                                $serviceName = !empty($row['serviceName']) ? $row['serviceName'] : 'No Service';
                                                echo "<tr>";
                                                echo "<td>{$counter}</td>";
                                                echo "<td>{$fullName}</td>";
                                                echo "<td>{$timeSlot}</td>";
                                                echo "<td>{$serviceName}</td>";
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
                </div>

                <!-- Previous Customers -->
                <div class="col-md-12">
                    <div class="card border-0 rounded-4">
                        <div class="card-header py-3 bg-white">
                            <h2 class="fw-bold">Previous Customers Today</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td>#</td>
                                            <td>Name</td>
                                            <td>Time</td>
                                            <td>Service</td>
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
            </div>
            <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
                <div class="position-sticky">
                    <div class="list-group list-group-flush mx-3 mt-5">
                        <div class="avatar-container text-center">
                            <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo mb-4">
                            <h5 class="mt-3" style="font-weight: bold; font-size: 20px;"><?php echo $barberFullName; ?></h5> <!-- Display Barber's Name -->
                        </div>
                        <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple active">
                            <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                        </a>
                        <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
                            <i class="fa-solid fa-users fa-fw me-3"></i><span>Schedule</span>
                            <?php if ($notificationCount > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
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
