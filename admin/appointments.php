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
    <title>Appointments</title>
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

    <div class="container-xxl">
        <h1 class="dashboard mb-5 ms-0">Appointments</h1>

        <!-- Top Section: Calendar and Canceled -->
        <div class="row mb-4">
            <!-- Calendar Section -->
            <div class="col-md-5">
                <div class="calendar-container card p-3">
                    <header class="calendar-header d-flex justify-content-between align-items-center">
                        <p class="calendar-current-date fw-bold">June 2024</p>
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
                    <script src="js/calendar.js"></script>
                </div>
            </div>

            <!-- Canceled Appointments Section -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h4 class="fw-bold mb-8">Cancelled</h4>
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $cancelledQuery = "SELECT a.*, c.firstName, c.lastName 
                                                   FROM appointment_tbl a
                                                   JOIN admin_tbl c ON a.adminID = c.adminID
                                                   WHERE a.status = 'Cancelled' AND a.date = CURDATE()";
                                $cancelledResult = mysqli_query($conn, $cancelledQuery);

                                if ($cancelledResult && mysqli_num_rows($cancelledResult) > 0) {
                                    while ($row = mysqli_fetch_assoc($cancelledResult)) {
                                        echo "<tr>
                                                <td>{$row['firstName']} {$row['lastName']}</td>
                                                <td>{$row['date']}</td>
                                                <td>{$row['timeSlot']}</td>
                                                <td>{$row['reason']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No cancelled appointments found.</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <button button class="btn btn-warning" onclick="window.location.href='booking.php';">Add Customer +</button>

        <!-- Bottom Section: Upcoming Customers -->
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">Upcoming Customers</h4>
                <?php
                    $countQuery = "
                        SELECT COUNT(*) AS total_upcoming
                        FROM appointment_tbl a
                        LEFT JOIN customer_tbl c ON a.customerID = c.customerID
                        WHERE a.date = CURDATE() AND a.status = 'Pending'
                    ";
                    $countResult = mysqli_query($conn, $countQuery);
                    $countData = mysqli_fetch_assoc($countResult);
                    $totalUpcoming = $countData['total_upcoming'];
                ?>
                <h4>Total: <?php echo $totalUpcoming; ?></h4>
            </div>
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Time</th>
                        <th>Service</th>
                        <th>Barber</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Modified query to include barberID from the barb_apps_tbl table
                        $upcomingQuery = "
                            SELECT 
                                a.appointmentID,
                                a.date,
                                a.timeSlot,
                                a.status,
                                CASE 
                                    WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
                                    ELSE 'Admin Booking' 
                                END AS fullName,
                                s.serviceName, 
                                ba.barberID  -- Get barberID from the barb_apps_tbl
                            FROM 
                                appointment_tbl a
                            LEFT JOIN 
                                customer_tbl c ON a.customerID = c.customerID
                            LEFT JOIN 
                                service_tbl s ON a.serviceID = s.serviceID
                            LEFT JOIN 
                                barb_apps_tbl ba ON a.appointmentID = ba.appointmentID  -- Join barb_apps_tbl to get barberID
                            WHERE 
                                a.date = CURDATE() AND a.status = 'Pending'
                            ORDER BY 
                                a.timeSlot ASC
                        ";
                        $upcomingResult = mysqli_query($conn, $upcomingQuery);

                        // Query to fetch all barbers
                        $barbersQuery = "SELECT * FROM barbers_tbl";
                        $barbersResult = mysqli_query($conn, $barbersQuery);
                        $barbers = [];
                        if ($barbersResult && mysqli_num_rows($barbersResult) > 0) {
                            while ($barberRow = mysqli_fetch_assoc($barbersResult)) {
                                $barbers[] = $barberRow;
                            }
                        }

                        // Check if there are any upcoming appointments
                        if ($upcomingResult && mysqli_num_rows($upcomingResult) > 0) {
                            while ($row = mysqli_fetch_assoc($upcomingResult)) {
                                echo "<tr>
                                        <td>{$row['appointmentID']}</td>
                                        <td>{$row['fullName']}</td>
                                        <td>{$row['timeSlot']}</td>
                                        <td>{$row['serviceName']}</td>
                                        <td>
                                            <form action='assign_barber.php' method='POST'>
                                                <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                <select name='barberID' class='form-select' onchange='this.form.submit()'>
                                                    <option value=''>Select Barber</option>";
                                                    // Loop through all barbers to display them in the dropdown
                                                    foreach ($barbers as $barber) {
                                                        // Check if the barber is assigned to this appointment
                                                        $selected = ($row['barberID'] == $barber['barberID']) ? "selected" : "";
                                                        echo "<option value='{$barber['barberID']}' $selected>{$barber['firstName']} {$barber['lastName']}</option>";
                                                    }
                                echo "</select>
                                            </form>
                                        </td>
                                        <td>
                                            <div class='dropdown'>
                                                <i class='fas fa-ellipsis-v' style='cursor: pointer;' data-bs-toggle='dropdown'></i>
                                                <ul class='dropdown-menu'>
                                                    <li>
                                                        <form action='update_status.php' method='POST' style='display: inline;'>
                                                            <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                            <input type='hidden' name='status' value='Completed'>
                                                            <button type='submit' class='dropdown-item'>
                                                                <i class='fas fa-check text-success'></i> Done
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action='update_status.php' method='POST' style='display: inline;'>
                                                            <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                            <input type='hidden' name='status' value='Cancelled'>
                                                            <button type='submit' class='dropdown-item'>
                                                                <i class='fas fa-times text-danger'></i> Cancel
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No upcoming appointments found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
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
                <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple active">
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Handle barber assignment click
            document.querySelectorAll('.assign-barber').forEach(icon => {
                icon.addEventListener('click', () => {
                    const appointmentID = icon.getAttribute('data-id');
                    // Open a modal or redirect to barber assignment page
                    console.log(`Assign barber for appointment ID: ${appointmentID}`);
                });
            });

            // Handle mark done/cancel actions
            document.querySelectorAll('.mark-done, .mark-cancel').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const appointmentID = link.getAttribute('data-id');
                    const action = link.classList.contains('mark-done') ? 'done' : 'cancel';
                    // Send action to server via AJAX or redirect
                    console.log(`Mark ${action} for appointment ID: ${appointmentID}`);
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</body>
</html>
