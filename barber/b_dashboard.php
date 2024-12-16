<?php
session_start();
include('db_connect.php');

// Ensure the barberID is fetched from the session or passed via URL
$barberID = isset($_SESSION['barberID']) ? $_SESSION['barberID'] : 1; // Defaulting to 1 for testing

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
    SELECT SUM(barberEarnings) AS total_income
    FROM earnings_tbl
    WHERE barberID = '$barberID'
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
</head>
<body>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <h1 class="dashboard mb-5 ms-0">Barber Dashboard</h1>
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
                    <div class="card border-danger">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                            <h4 class="ms-2 mt-2 fw-bold" style="color: #000000;">Upcoming Customers Today</h4>
                            <button>View All</button>
                        </div>

                        <div class="card-body">
                            <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">  
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Time</th>
                                        <th>Service</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Check if there are any upcoming results
                                    if (mysqli_num_rows($upcomingResult) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($upcomingResult)) {
                                            $fullName = !empty($row['fullName']) ? $row['fullName'] : 'Admin Booking';  // Use "Admin Booking" if no name exists
                                            $timeSlot = $row['timeSlot'];
                                            $serviceName = !empty($row['serviceName']) ? $row['serviceName'] : 'No Service';  // Ensure service name is set

                                            echo "<tr>
                                                    <td>{$no}</td>
                                                    <td>{$fullName}</td>
                                                    <td>{$timeSlot}</td>
                                                    <td>{$serviceName}</td>
                                                  </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>No Upcoming Customers</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Previous Customers -->
                <div class="col-md-12">
                    <div class="card border-danger"> 
                        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                            <h4 class="ms-2 mt-2 fw-bold" style="color: #000000;">Previous Customers Today</h4>
                            <button>View All</button>
                        </div>

                        <div class="card-body"> 
                            <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">  
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Time</th>
                                        <th>Service</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Check if there are any previous results
                                    if (mysqli_num_rows($previousResult) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($previousResult)) {
                                            $fullName = !empty($row['fullName']) ? $row['fullName'] : 'Admin Booking';  // Use "Admin Booking" if no name exists
                                            $timeSlot = $row['timeSlot'];
                                            $serviceName = !empty($row['serviceName']) ? $row['serviceName'] : 'No Service';  // Ensure service name is set
                                            echo "<tr>
                                                    <td>{$no}</td>
                                                    <td>{$fullName}</td>
                                                    <td>{$timeSlot}</td>
                                                    <td>{$serviceName}</td>
                                                  </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>No Previous Customers</td></tr>";
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
                        <a href="b_dashboard.php"><img src="css/images/jof_logo_black.png" alt="logo" width="45" height="45"></a>
                        <img src="css/images/mickey.jpg" alt="Avatar" width="140r" height="140" style="border: 5px solid #000000; border-radius: 50%;">
                        <h5 style="text-align: center;">Barber</h5>
                        <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                            <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                        </a>
                        <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
                            <i class="fa-solid fa-users fa-fw me-3"></i><span>Schedule</span>
                        </a>
                        <a href="b_history.php" class="list-group-item list-group-item-action py-2 ripple">
                            <i class="fa-solid fa-pills fa-fw me-3"></i><span>History</span>
                        </a>
                        <a href="income.php" class="list-group-item list-group-item-action py-2 ripple">
                            <i class="fa-solid fa-receipt fa-fw me-3"></i><span>Income</span>
                        </a>
                        <a href="../logout.php" class="list-group-item list-group-item-action py-2 ripple">
                            <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</body>
</html>
