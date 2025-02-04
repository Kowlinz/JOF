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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>History</title>
</head>
<body>
    <?php include 'db_connect.php'; 
    
    // Fetch the number of pending appointments
    $notificationQuery = "SELECT COUNT(*) AS pending_count FROM appointment_tbl WHERE status = 'Pending'";
    $notificationResult = mysqli_query($conn, $notificationQuery);
    $notificationData = mysqli_fetch_assoc($notificationResult);
    $pendingCount = $notificationData['pending_count'];

    ?>

    <!-- Add the mobile toggle button -->
    <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <h1 class="dashboard mb-5 ms-5">Appointments History</h1>
            <!-- Previous Customers Row -->
            <div class="row mb-4 ms-5">
                <div class="col-md-12">
                    <div class="card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="fw-bold">Previous Customers</h2>
                            <!-- Date Picker for Filtering -->
                            <div class="mb-0">
                                <input type="date" id="appointmentDate" class="form-control" 
                                value="<?php echo isset($_GET['appointment_date']) ? $_GET['appointment_date'] : ''; ?>" 
                                oninput="filterAppointments()">
                            </div>
                            <button type="button" class="btn btn-delete" 
                                    onclick="confirmDeletion('previous_customer')" 
                                    >
                                <i class="fa-solid fa-trash-alt fa-lg"></i>
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">  
                                    <thead>
                                        <tr>
                                            <td>Name</td>
                                            <td>Date</td>
                                            <td>Time</td>
                                            <td>Service</td>
                                            <td>Barber</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $selectedDate = isset($_GET['appointment_date']) ? $_GET['appointment_date'] : null;

                                            // Default query to fetch previous completed appointments
                                            $completedQuery = "SELECT 
                                                a.appointmentID,
                                                a.date,
                                                a.timeSlot,
                                                a.status,
                                                CASE 
                                                    WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
                                                    ELSE 'Walk In' 
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
                                                a.status = 'Completed'";

                                            // Add date filtering if a date is selected
                                            if (!empty($selectedDate)) {
                                                $completedQuery .= " AND a.date = '" . mysqli_real_escape_string($conn, $selectedDate) . "'";
                                            }

                                            $completedQuery .= " ORDER BY a.timeSlot ASC";

                                            // Execute the query to get the results
                                            $completedResult = mysqli_query($conn, $completedQuery);

                                            if (!$completedResult) {
                                                die("Query Error: " . mysqli_error($conn)); // Debugging line
                                            }
                                            
                                            if ($completedResult && mysqli_num_rows($completedResult) > 0) {
                                                while ($row = mysqli_fetch_assoc($completedResult)) {
                                                    echo "<tr>
                                                            <td>{$row['fullName']}</td>
                                                            <td>{$row['date']}</td>
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


            <!-- Cancelled Appointments Row -->
            <div class="row mb-4 ms-5">
                <div class="col-md-12">
                    <div class="card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="fw-bold">Cancelled</h2>
                            <!-- Date Picker for Filtering -->
                            <div class="mb-0">
                                <input type="date" id="cancelledDate" class="form-control" 
                                value="<?php echo isset($_GET['cancelled_date']) ? $_GET['cancelled_date'] : ''; ?>"
                                oninput="filterCancelledAppointments()">
                            </div>
                            <button type="button" class="btn btn-delete" 
                                    onclick="confirmDeletion('cancelled')" 
                                    >
                                <i class="fa-solid fa-trash-alt fa-lg"></i>
                            </button> 
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <td>Name</td>
                                            <td>Date</td>
                                            <td>Time</td>
                                            <td>Reason</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            <?php
                                                $selectedCancelledDate = isset($_GET['cancelled_date']) ? $_GET['cancelled_date'] : null;
                                                // Modify the query to select only cancelled appointments for the current date
                                                $cancelledQuery = "SELECT a.*, c.firstName, c.lastName 
                                                FROM appointment_tbl a
                                                LEFT JOIN customer_tbl c ON a.customerID = c.customerID
                                                WHERE a.status = 'Cancelled'";

                                                if (!empty($selectedCancelledDate)) {
                                                    $cancelledQuery .= " AND a.date = '" . mysqli_real_escape_string($conn, $selectedCancelledDate) . "'";
                                                }

                                                $cancelledQuery .= " ORDER BY a.timeSlot ASC";

                                                $cancelledResult = mysqli_query($conn, $cancelledQuery);

                                                if (!$cancelledResult) {
                                                    die("Query Error: " . mysqli_error($conn)); // Debugging line
                                                }
                                                $cancelledResult = mysqli_query($conn, $cancelledQuery);
                                                

                                                if ($cancelledResult && mysqli_num_rows($cancelledResult) > 0) {
                                                    while ($row = mysqli_fetch_assoc($cancelledResult)) {
                                                        // Check if firstName or lastName is null (for admin bookings)
                                                        $firstName = isset($row['firstName']) ? $row['firstName'] : 'Admin';
                                                        $lastName = isset($row['lastName']) ? $row['lastName'] : 'Booking';
                                                
                                                        echo "<tr>
                                                                <td>{$firstName} {$lastName}</td>
                                                                <td>{$row['date']}</td>
                                                                <td>{$row['timeSlot']}</td>
                                                                <td>{$row['reason']}</td>
                                                            </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center'>No cancelled appointments found for today.</td></tr>";
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

                <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
                </a>
                <a href="earnings.php" class="list-group-item list-group-item-action py-2 ripple">
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="js/calendar.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let appointmentDates = <?php echo json_encode($appointmentDates); ?>;
            
            document.querySelectorAll('.calendar-day').forEach(day => {
                let date = day.getAttribute('data-date'); // Assuming your calendar days have `data-date`
                if (appointmentDates.includes(date)) {
                    day.innerHTML += `<span class="badge bg-danger ms-1">!</span>`;
                }
            });
        });
    </script>

    <script> // Filtering previous appointments
    let timeout = null;

    function filterAppointments() {
        clearTimeout(timeout); // Clear previous timeout to avoid multiple reloads
        timeout = setTimeout(() => {
            let selectedDate = document.getElementById("appointmentDate").value;
            if (selectedDate.length === 10) { // Ensure full date is entered
                window.location.href = "a_history.php?appointment_date=" + selectedDate;
            }
        }, 800); // Delay execution to allow typing
    }

    function filterCancelledAppointments() {
        clearTimeout(timeout); // Clear previous timeout to avoid multiple reloads
        timeout = setTimeout(() => {
            let selectedDate = document.getElementById("cancelledDate").value;
            if (selectedDate.length === 10) { // Ensure full date is entered
                window.location.href = "a_history.php?cancelled_date=" + selectedDate;
            }
        }, 800); // Delay execution to allow typing
    }
    </script>

    <script>
        // Initialize Bootstrap dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            });
        });

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

    <script>
    function confirmDeletion(type) {
        if (confirm("This should be done after the service hours. Are you sure you want to delete all data? This action cannot be undone.")) {
            window.location.href = `delete_data.php?table=${type}`; // Redirect to a delete script
        }
    }
    </script>

</body>
</html>
