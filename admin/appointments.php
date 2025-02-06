<?php
    session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
    }

    include 'db_connect.php'; // Make sure the database is connected

    // Fetch all distinct dates that have pending appointments
    $appointmentsQuery = "SELECT DISTINCT date FROM appointment_tbl WHERE status = 'Pending'";
    $appointmentsResult = mysqli_query($conn, $appointmentsQuery);

    $appointmentDates = [];
    while ($row = mysqli_fetch_assoc($appointmentsResult)) {
        $appointmentDates[] = $row['date'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Appointments</title>
</head>
<body>
    <?php include 'db_connect.php'; 
    
    // Fetch the number of pending appointments
    $notificationQuery = "SELECT COUNT(*) AS pending_count FROM appointment_tbl WHERE status = 'Pending'";
    $notificationResult = mysqli_query($conn, $notificationQuery);
    $notificationData = mysqli_fetch_assoc($notificationResult);
    $pendingCount = $notificationData['pending_count'];
    ?>

    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        $status = $_GET['status'];
        $message = $_GET['message'];

        // Display a JavaScript alert with the message
        echo "
        <script>
            window.onload = function() {
                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                document.getElementById('statusMessage').innerText = '$message';
                modal.show();
            }
        </script>";
    }
    ?>
<div class="body d-flex py-3 mt-5">
    <div class="container-xxl">
        <div class="position-relative">
            <h1 class="dashboard mb-5 ms-5">Appointments</h1>
            <button class="mobile-toggle d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <?php

        // Check if there is a status or message in the query string
        if (isset($_GET['status']) && isset($_GET['message'])) {
            $status = $_GET['status']; // success or error
            $message = $_GET['message']; // Message to display in the popup

            // Display a JavaScript alert with the message
            echo "
            <script>
                window.onload = function() {
                    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                    document.getElementById('statusMessage').innerText = '$message';
                    modal.show();
                }
            </script>";
        }
        ?>

        <!-- Add Customer Button -->
        <div class="row ms-5 mb-4">
            <div class="col-12">
                <button class="btn btn-warning" onclick="window.location.href='walk-in.php';">+ Add Walk-In Customer</button>
            </div>
        </div>

        <!-- Upcoming Customers -->
        <div class="row ms-5">
            <div class="col-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <h2 class="fw-bold">Upcoming Customers</h2>
                        <div class="d-flex align-items-center gap-3">
                            <!-- Date Picker for Filtering -->
                            <div class="mb-0">
                            <input type="date" id="appointmentDate" class="form-control" 
                            value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>" 
                            oninput="filterAppointments()">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>#</td>
                                        <td>Name</td>
                                        <td>Date</td>
                                        <td>Time</td>
                                        <td>Service</td>
                                        <td>Barber</td>
                                        <td>Actions</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $selectedDate = isset($_GET['date']) ? $_GET['date'] : null;
                                        $upcomingQuery = "
                                            SELECT 
                                                a.appointmentID,
                                                a.date,
                                                a.timeSlot,
                                                a.status,
                                                CASE 
                                                    WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
                                                    ELSE 'Walk In' 
                                                END AS fullName,
                                                s.serviceName, 
                                                ba.barberID
                                            FROM 
                                                appointment_tbl a
                                            LEFT JOIN 
                                                customer_tbl c ON a.customerID = c.customerID
                                            LEFT JOIN 
                                                service_tbl s ON a.serviceID = s.serviceID
                                            LEFT JOIN 
                                                barb_apps_tbl ba ON a.appointmentID = ba.appointmentID
                                            WHERE 
                                                a.status = 'Pending'";

                                    // Add date filtering if a date is selected
                                    if (!empty($selectedDate)) {
                                        $upcomingQuery .= " AND a.date = '" . mysqli_real_escape_string($conn, $selectedDate) . "'";
                                    }

                                    $upcomingQuery .= " ORDER BY a.timeSlot ASC";

                                    $upcomingResult = mysqli_query($conn, $upcomingQuery);

                                    if (!$upcomingResult) {
                                        die("Query Error: " . mysqli_error($conn)); // Debugging line
                                    }
                                    $upcomingResult = mysqli_query($conn, $upcomingQuery);

                                    // Modified query to fetch only available barbers
                                    $barbersQuery = "SELECT * FROM barbers_tbl WHERE availability = 'available'";
                                    $barbersResult = mysqli_query($conn, $barbersQuery);
                                    $barbers = [];
                                    if ($barbersResult && mysqli_num_rows($barbersResult) > 0) {
                                        while ($barberRow = mysqli_fetch_assoc($barbersResult)) {
                                            $barbers[] = $barberRow;
                                        }
                                    }

                                    $counter = 1;

                                    // Check if there are any upcoming appointments
                                    if ($upcomingResult && mysqli_num_rows($upcomingResult) > 0) {
                                        while ($row = mysqli_fetch_assoc($upcomingResult)) {
                                            echo "  <tr>
                                                    <td>{$counter}</td>
                                                    <td>{$row['fullName']}</td>
                                                    <td>{$row['date']}</td>
                                                    <td>{$row['timeSlot']}</td>
                                                    <td>{$row['serviceName']}</td>
                                                    <td>
                                                        <form action='assign_barber.php' method='POST'>
                                                            <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                            <select name='barberID' class='form-select' onchange='this.form.submit()'>
                                                                <option value='' disabled selected hidden>Select Barber</option>";
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
                                                        <button type='button' class='btn btn-link' onclick='openStatusModal({$row['appointmentID']})'>
                                                            <i class='fas fa-ellipsis-v'></i>
                                                        </button>
                                                    </td>
                                                </tr>";
                                                $counter++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>No upcoming appointments found.</td></tr>";
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

            <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple active d-flex align-items-center justify-content-between">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

<script>
let timeout = null;

function filterAppointments() {
    clearTimeout(timeout); // Clear previous timeout to avoid multiple reloads
    timeout = setTimeout(() => {
        let selectedDate = document.getElementById("appointmentDate").value;
        if (selectedDate.length === 10) { // Ensure full date is entered
            window.location.href = "appointments.php?date=" + selectedDate;
        }
    }, 800); // Delay execution to allow typing
}
</script>

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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.querySelector('.mobile-toggle');
        if (toggleButton) {
            toggleButton.setAttribute('onclick', 'toggleSidebar()');
        }
    });
</script>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Appointment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm" action="update_status.php" method="POST">
                    <input type="hidden" id="appointmentID" name="appointmentID">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success" name="status" value="Completed">
                            <i class="fas fa-check me-2"></i>Mark as Done
                        </button>
                        <button type="button" class="btn btn-danger" onclick="openCancelModal()">
                            <i class="fas fa-times me-2"></i>Cancel Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reason for Cancellation Modal -->
<div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelReasonModalLabel">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="cancelForm" action="update_status.php" method="POST">
                <input type="hidden" id="cancelAppointmentID" name="appointmentID">
                <input type="hidden" name="status" value="Cancelled">
                <label for="cancelReason" class="form-label">Reason for Cancellation (Required)</label>
                <textarea id="cancelReason" name="reason" class="form-control" rows="3" required></textarea>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-danger">Confirm</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<script>
function openStatusModal(appointmentId) {
    document.getElementById('appointmentID').value = appointmentId;
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}
</script>

<script>
function openCancelModal() {
    // Get the appointment ID from the Status Modal
    let appointmentID = document.getElementById("appointmentID").value;

    // Set the appointment ID inside the Cancel Reason Modal
    document.getElementById("cancelAppointmentID").value = appointmentID;

    // Close the Status Modal
    let statusModalEl = document.getElementById("statusModal");
    let statusModal = bootstrap.Modal.getInstance(statusModalEl);
    if (statusModal) {
        statusModal.hide();
    }

    // Open the Cancel Reason Modal
    let cancelModal = new bootstrap.Modal(document.getElementById("cancelReasonModal"));
    cancelModal.show();
}

</script>
</body>
</html>
