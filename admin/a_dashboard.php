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
    <title>Admin Dashboard</title>
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
        .alert {
            background-color: white !important;
            border-radius: 15px !important;
            padding: 20px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .alert-warning, .alert-success, .alert-danger {
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
        .stat-icon.cancelled { color: #FF0000; }
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
    </style>
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

// Today's Revenue
$revenueQuery = "
    SELECT SUM(s.servicePrice) AS total_revenue
    FROM appointment_tbl a
    JOIN service_tbl s ON a.serviceID = s.serviceID
    WHERE DATE(a.date) = CURDATE() AND a.status = 'Completed'
";
$revenueResult = mysqli_query($conn, $revenueQuery);
$totalRevenue = mysqli_fetch_assoc($revenueResult)['total_revenue'] ?? "0.00";

// Pending Customers
$upcomingQuery = "
    SELECT 
        a.appointmentID,
        CASE 
            WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
            ELSE 'Admin Booking' 
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
            ELSE 'Admin Booking' 
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
            ELSE 'Admin Booking' 
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
        <h1 class="dashboard mb-5 ms-5">Admin Dashboard</h1>
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
                            <div class="h5">Pending Customers</div>
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
                            <div class="h5">Today's Revenue</div>
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
                        <h4 class="mb-0">Upcoming Customers Today</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="60%">Name</th>
                                    <th width="30%">Time</th>
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
    echo "<tr><td colspan='3' class='text-center'>No upcoming appointments found.</td></tr>";
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous Customers -->
        <div class="row ms-5">
            <div class="col-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white">
                        <h4 class="mb-0">Previous Customers</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Time</th>
                                    <th>Date</th>
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

<!-- Sidebar Menu -->
<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
  <div class="position-sticky">
    <div class="list-group list-group-flush mx-3 mt-5">
        <div class="avatar-container">
            <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo">
            <img src="css/images/admin.jpg" alt="Avatar" width="140" height="140" style="border: 5px solid #000000; border-radius: 50%;" class="avatar">
            <h5>Admin</h5>
        </div>
        <a href="a_dashboard.php" class="list-group-item list-group-item-action py-2 ripple active">
            <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
        </a>
        <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-users fa-fw me-3"></i><span>Appointment</span>
        </a>
        <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-pills fa-fw me-3"></i><span>History</span>
        </a>
        <a href="earnings.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-receipt fa-fw me-3"></i><span>Earnings</span>
        </a>
        <a href="barbers.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-receipt fa-fw me-3"></i><span>Barbers</span>
        </a>
        <a href="options.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-receipt fa-fw me-3"></i><span>Options</span>
        </a>
        <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
        </a>
    </div>
  </div>
</nav>

</body>
</html>
