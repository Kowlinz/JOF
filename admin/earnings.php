<?php
    session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
        exit;
    }

    include 'db_connect.php'; // Include database connection

    // Fetch the number of pending appointments
    $notificationQuery = "SELECT COUNT(*) AS pending_count FROM appointment_tbl WHERE status = 'Pending'";
    $notificationResult = mysqli_query($conn, $notificationQuery);
    $notificationData = mysqli_fetch_assoc($notificationResult);
    $pendingCount = $notificationData['pending_count'];

    // Query to fetch today's earnings
    $todayQuery = "SELECT e.adminEarnings, e.barberEarnings, CONCAT(b.firstName, ' ', b.lastName) AS barberFullName, a.date, a.timeSlot
               FROM earnings_tbl e 
               JOIN barbers_tbl b ON e.barberID = b.barberID
               JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
               WHERE DATE(a.date) = CURDATE()";
    $stmtToday = $conn->prepare($todayQuery);
    $stmtToday->execute();
    $resultToday = $stmtToday->get_result();

    // Query to calculate total adminEarnings for today
    $totalAdminEarningsQuery = "SELECT SUM(adminEarnings) AS totalAdminEarnings 
    FROM earnings_tbl e 
    JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
    WHERE DATE(a.date) = CURDATE()";
    $stmtTotalAdmin = $conn->prepare($totalAdminEarningsQuery);
    $stmtTotalAdmin->execute();
    $resultTotalAdmin = $stmtTotalAdmin->get_result();
    $totalAdminEarnings = $resultTotalAdmin->fetch_assoc()['totalAdminEarnings'] ?? 0; // Default to 0 if null

    // Query to calculate total adminEarnings for the current month
    $monthlyAdminEarningsQuery = "SELECT SUM(adminEarnings) AS totalMonthlyAdminEarnings 
    FROM earnings_tbl e 
    JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
    WHERE MONTH(a.date) = MONTH(CURDATE()) AND YEAR(a.date) = YEAR(CURDATE())";

    $stmtMonthlyAdmin = $conn->prepare($monthlyAdminEarningsQuery);
    $stmtMonthlyAdmin->execute();
    $resultMonthlyAdmin = $stmtMonthlyAdmin->get_result();
    $totalMonthlyAdminEarnings = $resultMonthlyAdmin->fetch_assoc()['totalMonthlyAdminEarnings'] ?? 0; // Default to 0 if null
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
    <title>Earnings</title>
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
        <div class="row g-3 mb-3 ms-5">
            <div class="col-md-4 col-sm-6">
                <div class="alert mb-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon revenue">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <div class="flex-fill text-wrap">
                            <div class="h5">Today's Admin Earnings</div>
                            <div class="h6">₱<?= number_format($totalAdminEarnings, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="alert mb-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon revenue">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div class="flex-fill text-wrap">
                            <div class="h5">This Month Earnings</div>
                            <div class="h6">₱<?= number_format($totalMonthlyAdminEarnings, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-3 ms-5">
            <div class="col-md-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <h2 class="fw-bold">Today</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">  
                                <thead>
                                    <tr>
                                        <td>Total</td>
                                        <td>My Earnings</td>
                                        <td>Barber Name</td>
                                        <td>Barber Earnings</td>
                                        <td>Time</td>
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
                <h5>Admin</h5>
            </div>
            <a href="a_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
            </a>

            <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple d-flex align-items-center justify-content-between">
                <div>
                    <i class="fa-solid fa-users fa-fw me-3"></i>
                    <span>Appointment</span>
                </div>
                <?php if ($pendingCount > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
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
