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
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Appointment History</title>
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
        /* Avatar container styling */
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
    <?php include 'db_connect.php'; ?>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <h1 class="dashboard mb-5 ms-0">Appointments History</h1>
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
                        <h4 class="ms-2 mt-2 fw-bold" style="color: #000000;">Previous Customers</h4>
                        <h4>
                            Total: 
                            <?php
                                // Query to count the number of completed appointments
                                $countQuery = "SELECT COUNT(*) AS total_previous
                                               FROM appointment_tbl a
                                               WHERE a.date >= CURDATE() AND a.status = 'Completed'";

                                $countResult = mysqli_query($conn, $countQuery);
                                if ($countResult) {
                                    $countData = mysqli_fetch_assoc($countResult);
                                    echo $countData['total_previous'];
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
                                    <td>Name</td>
                                    <td>Time</td>
                                    <td>Service</td>
                                    <td>Barber</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $completedQuery = "SELECT 
                                        a.appointmentID,
                                        a.date,
                                        a.timeSlot,
                                        a.status,
                                        CASE 
                                            WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
                                            ELSE 'Admin Booking' 
                                        END AS fullName,
                                        s.serviceName, 
                                        b.firstName AS barberFirstName, 
                                        b.lastName AS barberLastName
                                    FROM 
                                        appointment_tbl a
                                    LEFT JOIN 
                                        customer_tbl c ON a.customerID = c.customerID
                                    LEFT JOIN 
                                        service_tbl s ON a.serviceID = s.serviceID
                                    LEFT JOIN 
                                        barb_apps_tbl ba ON a.appointmentID = ba.appointmentID
                                    LEFT JOIN 
                                        barbers_tbl b ON b.barberID = ba.barberID
                                    WHERE 
                                        a.date = CURDATE() AND a.status = 'Completed'
                                    GROUP BY
                                        a.appointmentID
                                    ORDER BY 
                                        a.timeSlot ASC";

                                    $completedResult = mysqli_query($conn, $completedQuery);
                                    
                                    if ($completedResult && mysqli_num_rows($completedResult) > 0) {
                                        while ($row = mysqli_fetch_assoc($completedResult)) {
                                            echo "<tr>
                                                    <td>{$row['fullName']}</td>
                                                    <td>{$row['timeSlot']}</td>
                                                    <td>{$row['serviceName']}</td>
                                                    <td>{$row['barberFirstName']} {$row['barberLastName']}</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>No completed appointments found.</td></tr>";
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
                <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple active">
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
