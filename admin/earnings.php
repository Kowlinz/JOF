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
    </style>
</head>
<body>
    <div class="body d-flex py-3 mt-5">
      <div class="container-xxl">
        <h1 class="dashboard mb-5 ms-0">Earnings</h1>
        <div class="row g-1 mb-5">
    <div class="col-3 custom-width">
        <div class="alert-warning alert mb-0">
            <div class="d-flex align-items-center">
                <div class=""><i class="fa-solid fa-lg"></i></div>
                <div class="flex-fill ms-3 text-truncate">
                    <div class="h5 mb-0 mt-0">Today's Admin Earnings</div>
                    <div class="h4 fw-bold mt-1">₱<?= number_format($totalAdminEarnings, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="row g-3 mb-5 ms-0">
            <div class="col-md-10">
                <div class="card border-danger">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                        <h4 class="ms-2 mt-2 fw-bold" style="color: #000000;">Today</h4>
                        <td><button> View All </button></td>
                    </div>
                    <div class="card-body">
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
            <td colspan="4" class="text-center">No earnings data for today</td>
        </tr>
    <?php endif; ?>
</tbody>
                      </table>
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

</body>
</html>
