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

// Query for Total Barber Earnings (Income) and Time
$incomeQuery = "
    SELECT a.timeSlot, e.barberEarnings 
    FROM earnings_tbl e
    JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
    WHERE DATE(a.date) = CURDATE() AND e.barberID = '$barberID'
    ORDER BY a.timeSlot ASC
";

$incomeResult = mysqli_query($conn, $incomeQuery);
if (!$incomeResult) {
    echo "Error in income query: " . mysqli_error($conn);
}

// Query to calculate the total income
$totalIncomeQuery = "
    SELECT SUM(e.barberEarnings) AS total_income
    FROM earnings_tbl e
    JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
    WHERE DATE(a.date) = CURDATE() AND e.barberID = '$barberID'
";
$totalIncomeResult = mysqli_query($conn, $totalIncomeQuery);
$totalIncome = mysqli_fetch_assoc($totalIncomeResult)['total_income'];
$totalIncome = !empty($totalIncome) ? number_format($totalIncome, 2) : '0.00'; // Format the income
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
    <title>Income</title>
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
        /* ... */

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
            .row.g-3.mb-4.ms-5 {
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
            .row.g-3.mb-4 {
                margin: 0 0 20px 0;
            }
            
            /* Ensure proper spacing for tables */
            .row.g-3.mb-5 {
                margin: 0 !important;
            }
            
            /* Adjust text sizes for better mobile display */
            .h5 {
                font-size: 14px;
            }
            
            .h6 {
                font-size: 20px;
            }
            
            /* Adjust stat icons on mobile */
            .stat-icon {
                font-size: 20px;
                margin-right: 10px;
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
            
            .card-header h4 {
                font-size: 1rem;
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
            <h1 class="dashboard mb-5 ms-5">Income</h1>
            <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="row g-3 mb-4 ms-5">
            <div class="col-3">
                <div class="alert mb-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon revenue">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div class="flex-fill text-wrap">
                    <div class="h5">Today's Income</div>
                    <div class="h6">₱<?= number_format($totalIncome, 2) ?></div>
                </div>
                    </div>
                </div>
            </div>
        </div>
          <div class="row g-3 mb-5 ms-5">
            <div class="col-md-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white">
                        <h4 class="mb-0" style="color: black;">Today</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($incomeResult && mysqli_num_rows($incomeResult) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($incomeResult)): ?>
                                        <tr>
                                            <td><?= date("h:i A", strtotime($row['timeSlot'])) ?></td>
                                            <td>₱ <?= number_format($row['barberEarnings'], 2) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No earnings data for today</td>
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
        <div class="avatar-container text-center">
            <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo mb-4">
            <img src="css/images/barber.jpg" alt="Avatar" width="140" height="140" style="border: 5px solid #000000; border-radius: 50%;" class="avatar">
            <h5 class="mt-3">Barber</h5>
        </div>
        <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
        </a>
        <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-users fa-fw me-3"></i><span>Schedule</span>
        </a>
        <a href="b_history.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
        </a>
        <a href="income.php" class="list-group-item list-group-item-action py-2 ripple active">
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

</body>
</html>