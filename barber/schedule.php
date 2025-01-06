<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="css/images/favicon-32x32.png">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Schedule</title>
</head>
<body>
    
<?php include 'db_connect.php'; // Ensure the barberID is fetched from the session or passed via URL
$barberID = isset($_SESSION['barberID']) ? $_SESSION['barberID'] : 1; // Defaulting to 1 for testing
?>

<div class="body d-flex py-3 mt-5">
    <div class="container-xxl">
        <h1 class="dashboard mb-5 ms-0">Schedule</h1>
        <div class="calendar-container">
            <header class="calendar-header">
                <p class="calendar-current-date"></p>
                <div class="calendar-navigation">
                    <span id="calendar-prev" class="material-symbols-rounded">chevron_left</span>
                    <span id="calendar-next" class="material-symbols-rounded">chevron_right</span>
                </div>
            </header>

            <div class="calendar-body">
                <ul class="calendar-weekdays">
                    <li>Sun</li>
                    <li>Mon</li>
                    <li>Tue</li>
                    <li>Wed</li>
                    <li>Thu</li>
                    <li>Fri</li>
                    <li>Sat</li>
                </ul>
                <ul class="calendar-dates"></ul>
            </div>
        </div>
        <script src="js/calendar.js"></script>

        <div class="col-md-12">
            <div class="card border-danger">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                    <h4 class="ms-2 mt-2 fw-bold" style="color: #000000;">Upcoming Customers</h4>
                    <h4>Total: 
                        <?php
                        // Query to count the upcoming appointments for the logged-in barber
                        $barberID = 1; // Replace with dynamic value (e.g., session or URL parameter)
                        $countQuery = "SELECT COUNT(*) AS total_upcoming
                                       FROM barb_apps_tbl ba
                                       JOIN appointment_tbl a ON ba.appointmentID = a.appointmentID
                                       WHERE ba.barberID = $barberID AND a.date >= CURDATE() AND a.status = 'Pending'";
                        $countResult = mysqli_query($conn, $countQuery);
                        if ($countResult) {
                            $countData = mysqli_fetch_assoc($countResult);
                            echo $countData['total_upcoming'];
                        } else {
                            echo "0"; // In case of an error, show 0
                        }
                        ?>
                    </h4>
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
                            // Query to display upcoming customers for the logged-in barber
                            $upcomingQuery = "SELECT 
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
                                a.timeSlot ASC";
                            $upcomingResult = mysqli_query($conn, $upcomingQuery);

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
    </div>
</div>

<nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
    <div class="position-sticky">
        <div class="list-group list-group-flush mx-3 mt-5">
            <a href="b_dashboard.php"><img src="css/images/jof_logo_black.png" alt="logo" width="45" height="45"></a>
            <img src="css/images/barber.jpg" alt="Avatar" width="140r" height="140" style="border: 5px solid #000000; border-radius: 50%;">

            <h5 style="text-align: center;"> Barber </h5>
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
            <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
            </a>
        </div>
    </div>
</nav>

</body>
</html>
