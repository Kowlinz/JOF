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
mysqli_query($conn, "SET time_zone = '+08:00'");
date_default_timezone_set('Asia/Manila'); // Set to your desired timezone

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
    SELECT COUNT(*) AS pending_count 
    FROM barb_apps_tbl AS b 
    JOIN appointment_tbl AS a 
    ON b.appointmentID = a.appointmentID 
    WHERE b.barberID = '$barberID' 
    AND a.status = 'Pending'
    ";
    
    $notificationResult = mysqli_query($conn, $notificationQuery);
    $notificationData = mysqli_fetch_assoc($notificationResult);
    $pendingCount = $notificationData['pending_count'];
    

// Query for Total Barber Earnings (Income) and Time
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$incomeQuery = "
    SELECT a.timeSlot, e.barberEarnings 
    FROM earnings_tbl e
    JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
    WHERE DATE(a.date) = '$selectedDate' AND e.barberID = '$barberID'
    ORDER BY a.timeSlot ASC
";

$incomeResult = mysqli_query($conn, $incomeQuery);
if (!$incomeResult) {
    echo "Error in income query: " . mysqli_error($conn);
}

// Query to calculate total BarberEarnings for today
$totalBarberEarningsQuery = "SELECT SUM(BarberEarnings) AS totalBarberEarnings 
FROM earnings_tbl e 
JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
WHERE DATE(a.date) = '$selectedDate' AND e.barberID = '$barberID'";
$stmtTotalBarber = $conn->prepare($totalBarberEarningsQuery);
$stmtTotalBarber->execute();
$resultTotalBarber = $stmtTotalBarber->get_result();
$totalBarberEarnings = $resultTotalBarber->fetch_assoc()['totalBarberEarnings'] ?? 0; // Default to 0 if null

// Query to calculate total BarberEarnings for the current month
$monthlyBarberEarningsQuery = "SELECT SUM(BarberEarnings) AS totalMonthlyBarberEarnings 
FROM earnings_tbl e 
JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
WHERE MONTH(a.date) = MONTH('$selectedDate') 
AND YEAR(a.date) = YEAR('$selectedDate') 
AND e.barberID = '$barberID'";

$stmtMonthlyBarber = $conn->prepare($monthlyBarberEarningsQuery);
$stmtMonthlyBarber->execute();
$resultMonthlyBarber = $stmtMonthlyBarber->get_result();
$totalMonthlyBarberEarnings = $resultMonthlyBarber->fetch_assoc()['totalMonthlyBarberEarnings'] ?? 0; // Default to 0 if null
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/barber.css">
    <title>Income</title>
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
            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="alert mb-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon revenue">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="h5">This Day Income</div>
                            <div class="h6">₱<?= number_format($totalBarberEarnings, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="alert mb-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon revenue">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="h5">This Month Income</div>
                            <div class="h6">₱<?= number_format($totalMonthlyBarberEarnings, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
          <div class="row g-3 mb-5 ms-5">
            <div class="col-md-12">
                <div class="card border-0 rounded-4">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <?php
                            $prettyDate = date('F j, Y', strtotime($selectedDate));
                        ?>
                        <h2 class="fw-bold"><?= $prettyDate ?> Income</h2>
                        <input type="date" id="earningsDate" class="form-control w-auto" 
                        value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>Time</td>
                                        <td>Total</td>
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
          </div>


<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
  <div class="position-sticky">
    <div class="list-group list-group-flush mx-3 mt-5">
        <div class="avatar-container text-center">
            <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo mb-4">
            <h5 class="mt-3" style="font-weight: bold; font-size: 20px;"><?php echo $barberFullName; ?></h5> <!-- Display Barber's Name -->
        </div>
        <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
        </a>
        <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
            <i class="fa-solid fa-users fa-fw me-3"></i>
            <span>Schedule</span>
            <?php if ($pendingCount > 0): ?>
            <span class="badge bg-danger ms-2"><?php echo $pendingCount; ?></span>
            <?php endif; ?>
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

<script>
        document.getElementById("earningsDate").addEventListener("change", function () {
            const selectedDate = this.value;
    
            // Format selected date to a more readable format
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            const prettyDate = new Date(selectedDate).toLocaleDateString('en-US', options);
    
            // Redirect to update earnings based on the selected date
            window.location.href = "income.php?date=" + selectedDate;
        });
    </script>

</body>
</html>