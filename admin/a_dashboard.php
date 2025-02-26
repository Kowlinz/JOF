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
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <title>Admin Dashboard</title>
</head>
<body>
<?php

include 'db_connect.php';

// Pending, Completed, and Cancelled Counts
$pendingQuery = "SELECT COUNT(*) AS pending_count FROM appointment_tbl WHERE status = 'Pending' AND date = CURDATE()";
$completedQuery = "SELECT COUNT(*) AS completed_count FROM appointment_tbl WHERE status = 'Completed' AND date = CURDATE()";
$cancelledQuery = "SELECT COUNT(*) AS cancelled_count FROM appointment_tbl WHERE status = 'Cancelled' AND date = CURDATE()";

$pendingResult = mysqli_query($conn, $pendingQuery);
$completedResult = mysqli_query($conn, $completedQuery);
$cancelledResult = mysqli_query($conn, $cancelledQuery);

$pendingCount = mysqli_fetch_assoc($pendingResult)['pending_count'];
$completedCount = mysqli_fetch_assoc($completedResult)['completed_count'];
$cancelledCount = mysqli_fetch_assoc($cancelledResult)['cancelled_count'];

// Fetch the number of pending appointments without an assigned barber
$notificationQuery = "
    SELECT COUNT(*) AS notif_count 
    FROM appointment_tbl a
    LEFT JOIN barb_apps_tbl b ON a.appointmentID = b.appointmentID
    WHERE a.status = 'Pending' AND b.barberID IS NULL";

$notificationResult = mysqli_query($conn, $notificationQuery);
$notificationData = mysqli_fetch_assoc($notificationResult);
$notificationCount = $notificationData['notif_count'];

// Today's Admin Earnings
$earningsQuery = "
    SELECT SUM(e.adminEarnings) AS total_earnings
    FROM earnings_tbl e
    JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
    WHERE DATE(a.date) = CURDATE() AND a.status = 'Completed'
";
$earningsResult = mysqli_query($conn, $earningsQuery);
$totalRevenue = mysqli_fetch_assoc($earningsResult)['total_earnings'] ?? "0.00";

// Pending Customers
$upcomingQuery = "
    SELECT 
        a.appointmentID,
        CASE 
            WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
            ELSE 'Walk In' 
        END AS fullName,
        a.timeSlot
    FROM 
        appointment_tbl a
    LEFT JOIN 
        customer_tbl c ON a.customerID = c.customerID
    WHERE 
        a.date = CURDATE() AND a.status = 'Pending'
    ORDER BY 
        a.timeSlot ASC
";
$upcomingResult = mysqli_query($conn, $upcomingQuery);

// Completed Customers
$previousQuery = "
    SELECT 
        a.appointmentID,
        CASE 
            WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
            ELSE 'Walk In' 
        END AS fullName,
        a.timeSlot,
        a.date
    FROM 
        appointment_tbl a
    LEFT JOIN 
        customer_tbl c ON a.customerID = c.customerID
    WHERE 
        a.date = CURDATE() AND a.status = 'Completed'
    ORDER BY 
        a.date DESC, a.timeSlot ASC
";
$previousResult = mysqli_query($conn, $previousQuery);

// Cancelled Customers
$cancelledQuery = "
    SELECT 
        a.appointmentID,
        CASE 
            WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
            ELSE 'Walk In' 
        END AS fullName,
        a.timeSlot,
        a.date
    FROM 
        appointment_tbl a
    LEFT JOIN 
        customer_tbl c ON a.customerID = c.customerID
    WHERE 
        a.date <= CURDATE() AND a.status = 'Cancelled'
    ORDER BY 
        a.date DESC, a.timeSlot ASC
";
$cancelledResult = mysqli_query($conn, $cancelledQuery);
?>

<!-- First container for stats -->
<div class="body d-flex py-3 mt-5">
    <div class="container-xxl">
        <div class="position-relative">
            <h1 class="dashboard mb-5 ms-5">Admin Dashboard</h1>
            <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- Stats cards row -->
        <div class="row g-3 mb-3 row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4 ms-5">
            <!-- Stats cards remain the same -->
            <div class="col">
                <div class="alert mb-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon pending">
                            <i class="fa-solid fa-users"></i>
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
                            <i class="fa-solid fa-check"></i>
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
                        <div class="stat-icon cancelled">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="h5">Cancelled</div>
                            <div class="h6"><?php echo $cancelledCount; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="alert mb-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon revenue">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="h5">Today's Earnings</div>
                            <div class="h6">₱<?php echo number_format($totalRevenue, 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables now go inside the same container -->
        <!-- Upcoming Customers -->
        <div class="row ms-5">
            <div class="col-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white">
                        <h2 class="fw-bold">Pending Customers Today</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>#</td>
                                        <td>Name</td>
                                        <td>Time</td>
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
                                                echo "</tr>";
                                                $counter++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='3' class='text-center'>No pending appointments found.</td></tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous Customers -->
        <div class="row ms-5">
            <div class="col-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white">
                        <h2 class="fw-bold">Previous Customers Today</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>Name</td>
                                        <td>Time</td>
                                        <td>Date</td>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($previousResult && mysqli_num_rows($previousResult) > 0) {
                                    while ($row = mysqli_fetch_assoc($previousResult)) {
                                        echo "<tr>";
                                        echo "<td>{$row['fullName']}</td>";
                                        echo "<td>{$row['timeSlot']}</td>";
                                        echo "<td>{$row['date']}</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center'>No completed appointments found.</td></tr>";
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

<!-- Sidebar Menu -->
<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
  <div class="position-sticky">
    <div class="list-group list-group-flush mx-3 mt-5">
        <div class="avatar-container">
            <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo">
            <h5>Admin</h5>
        </div>
        <a href="a_dashboard.php" class="list-group-item list-group-item-action py-2 ripple active">
            <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
        </a>
        <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple d-flex align-items-center justify-content-between">
            <div>
                <i class="fa-solid fa-users fa-fw me-3"></i>
                <span>Appointment</span>
            </div>
            <?php if ($notificationCount > 0): ?>
                <span class="badge bg-danger rounded-pill"><?php echo $notificationCount; ?></span>
            <?php endif; ?>
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
        <a href="manage_services.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-gear fa-fw me-3"></i><span>Manage Services</span>
        </a>
        <a href="configuration.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-gear fa-fw me-3"></i><span>Website Configuration</span>
        </a>
        <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
        </a>
    </div>
  </div>
</nav>

<!-- Add this script at the bottom of the body -->
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
