<?php
    session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
    }

    include 'db_connect.php'; // Make sure the database is connected
    mysqli_query($conn, "SET time_zone = '+08:00'");
    date_default_timezone_set('Asia/Manila'); 


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
    
    // Fetch the number of pending appointments without an assigned barber
    $notificationQuery = "
        SELECT COUNT(*) AS notif_count 
        FROM appointment_tbl a
        LEFT JOIN barb_apps_tbl b ON a.appointmentID = b.appointmentID
        WHERE a.status = 'Pending' AND b.barberID IS NULL";

    $notificationResult = mysqli_query($conn, $notificationQuery);
    $notificationData = mysqli_fetch_assoc($notificationResult);
    $notificationCount = $notificationData['notif_count'];
    ?>

    <?php
    if (isset($_GET['status']) && isset($_GET['message']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        // Only show the modal for non-AJAX requests
        $status = $_GET['status'];
        $message = $_GET['message'];

        echo "
        <script>
            window.onload = function() {
                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
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

        <!-- Add Customer Button -->
        <div class="row ms-5 mb-4">
            <div class="col-12">
                <button class="btn btn-warning" onclick="window.location.href='walk-in.php';">+ Add Walk-In Customer</button>
            </div>
        </div>

        <!-- Pending Customers -->
        <div class="row ms-5">
            <div class="col-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <h2 class="fw-bold">Pending Customers</h2>
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
                                        <td>Actions</td>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $selectedDate = isset($_GET['date']) ? $_GET['date'] : null;
                                    $selectedBarber = isset($_GET['barber']) ? $_GET['barber'] : null;

                                    $upcomingQuery = "
                                        SELECT 
                                            a.appointmentID,
                                            a.date,
                                            a.timeSlot,
                                            a.status,
                                            c.customerID,
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

                                    if (!empty($selectedDate)) {
                                        $upcomingQuery .= " AND a.date = '" . mysqli_real_escape_string($conn, $selectedDate) . "'";
                                    }

                                    if (!empty($selectedBarber)) {
                                        $upcomingQuery .= " AND ba.barberID = '" . mysqli_real_escape_string($conn, $selectedBarber) . "'";
                                    }

                                    $upcomingQuery .= " ORDER BY a.timeSlot ASC";
                                    $upcomingResult = mysqli_query($conn, $upcomingQuery);

                                    if (!$upcomingResult) {
                                        die("Query Error: " . mysqli_error($conn));
                                    }

                                    $barbersQuery = "SELECT * FROM barbers_tbl WHERE availability = 'available'";
                                    $barbersResult = mysqli_query($conn, $barbersQuery);
                                    $barbers = [];
                                    if ($barbersResult && mysqli_num_rows($barbersResult) > 0) {
                                        while ($barberRow = mysqli_fetch_assoc($barbersResult)) {
                                            $barbers[] = $barberRow;
                                        }
                                    }

                                    $counter = 1;

                                    if ($upcomingResult && mysqli_num_rows($upcomingResult) > 0) {
                                        while ($row = mysqli_fetch_assoc($upcomingResult)) {
                                            $formattedDate = date("F d, Y", strtotime($row['date']));
                                            echo "<tr>
                                                    <td>{$counter}</td>
                                                    <td>
                                                        <a href='#' onclick='showAppointmentDetails({$row['appointmentID']}, " . ($row['customerID'] ? "false" : "true") . ")' 
                                                        data-bs-toggle='modal' data-bs-target='#appointmentModal' style='text-decoration: none; color: inherit;'>
                                                        " . htmlspecialchars($row['fullName']) . "
                                                        </a>
                                                    </td>
                                                    <td>{$formattedDate}</td>
                                                    <td>{$row['timeSlot']}</td>
                                                    <td>{$row['serviceName']}</td>
                                                    <td>
                                                        <div class='d-flex gap-2'>
                                                            <button type='button' class='btn btn-sm btn-success' 
                                                                onclick='updateStatus({$row["appointmentID"]}, \"Upcoming\")'>
                                                                <i class='fas fa-check'></i>
                                                            </button>
                                                            <button type='button' class='btn btn-sm btn-danger' 
                                                                onclick='deleteAppointment({$row["appointmentID"]})'>
                                                                <i class='fas fa-times'></i>
                                                            </button>
                                                        </div>
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

        <!-- Upcoming Customers -->
        <div class="row ms-5">
            <div class="col-12">
                <div class="card border-0 rounded-4">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <h2 class="fw-bold">Upcoming Customers</h2>
                        <div class="d-flex align-items-center gap-3">
                            <!-- Barber Filter Dropdown -->
                            <div class="mb-0">
                                <select id="barberFilter" class="form-select bg-white text-dark form-control" onchange="filterAppointments()">
                                    <option value="">All Barbers</option>
                                    <?php
                                    // Fetch all barbers
                                    $barberListQuery = "SELECT barberID, firstName, lastName FROM barbers_tbl";
                                    $barberListResult = mysqli_query($conn, $barberListQuery);
                                    while ($barber = mysqli_fetch_assoc($barberListResult)) {
                                        $selected = isset($_GET['barber']) && $_GET['barber'] == $barber['barberID'] ? 'selected' : '';
                                        echo "<option value='{$barber['barberID']}' {$selected}>{$barber['firstName']} {$barber['lastName']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- Date Picker for Filtering -->
                            <div class="mb-0">
                                <input type="date" id="appointmentDate" class="form-control" 
                                    value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>" 
                                    onchange="filterAppointments()">
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
                                        $selectedBarber = isset($_GET['barber']) ? $_GET['barber'] : null;
                                        
                                        $upcomingQuery = "
                                        SELECT 
                                            a.appointmentID,
                                            a.date,
                                            a.timeSlot,
                                            a.status,
                                            c.customerID,
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
                                            a.status = 'Upcoming'";

                                    // Add date filtering if a date is selected
                                    if (!empty($selectedDate)) {
                                        $upcomingQuery .= " AND a.date = '" . mysqli_real_escape_string($conn, $selectedDate) . "'";
                                    }

                                    // Add barber filtering if a barber is selected
                                    if (!empty($selectedBarber)) {
                                        $upcomingQuery .= " AND ba.barberID = '" . mysqli_real_escape_string($conn, $selectedBarber) . "'";
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
                                            $formattedDate = date("F d, Y", strtotime($row['date']));
                                            echo "<tr>
                                                    <td>{$counter}</td>
                                                    <td>
                                                        <a href='#' onclick='showAppointmentDetails({$row['appointmentID']}, " . ($row['customerID'] ? "false" : "true") . ")' 
                                                        data-bs-toggle='modal' data-bs-target='#appointmentModal' style='text-decoration: none; color: inherit;'>
                                                        " . htmlspecialchars($row['fullName']) . "
                                                        </a>
                                                    </td>
                                                    <td>{$formattedDate}</td>
                                                    <td>{$row['timeSlot']}</td>
                                                    <td>{$row['serviceName']}</td>
                                                    <td>
                                                        <form action='assign_barber.php' method='POST' class='assign-barber-form'>
                                                            <input type='hidden' name='appointmentID' value='{$row['appointmentID']}'>
                                                            <select name='barberID' class='form-select' onchange='handleBarberAssignment(this.form)'>
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
                                                        <div class='d-flex gap-2'>
                                                            <button type='button' class='btn btn-sm btn-success' 
                                                                onclick='confirmComplete({$row['appointmentID']})' 
                                                                " . (date('F d, Y') !== $formattedDate ? "disabled title='Can only complete appointments scheduled for today'" : "") . ">
                                                                <i class='fas fa-check'></i>
                                                            </button>
                                                            <button type='button' class='btn btn-sm btn-danger' 
                                                                onclick='confirmCancel({$row['appointmentID']})'>
                                                                <i class='fas fa-times'></i>
                                                            </button>";
                                                            
                                                            // Only show reminder button for non-walk-in customers
                                                            if ($row['customerID']) {
                                                                echo "<button type='button' class='btn btn-sm btn-warning' 
                                                                        onclick='confirmReminder({$row['appointmentID']})'>
                                                                        <i class='fas fa-bell'></i>
                                                                    </button>";
                                                            }

                                                            echo "</div>
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
                <?php if ($notificationCount > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?php echo $notificationCount; ?></span>
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

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/calendar.js"></script>

<script>
    function updateStatus(appointmentID, status) {
        if (confirm("Are you sure you want to update this appointment's status?")) {
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `appointmentID=${appointmentID}&status=${status}`
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
    }

    function deleteAppointment(appointmentID) {
        if (confirm("Are you sure you want to delete this appointment?")) {
            fetch('delete_appointment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `appointmentID=${appointmentID}`
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
    }

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
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        let selectedDate = document.getElementById("appointmentDate").value;
        let selectedBarber = document.getElementById("barberFilter").value;
        
        let url = "appointments.php?";
        let params = [];
        
        if (selectedDate) {
            params.push("date=" + selectedDate);
        }
        
        if (selectedBarber) {
            params.push("barber=" + selectedBarber);
        }
        
        url += params.join("&");
        window.location.href = url;
    }, 800);
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
    function updateStatus(appointmentID, status) {
        document.getElementById('updateAppointmentID').value = appointmentID;
        document.getElementById('updateStatusValue').value = status;
        var updateModal = new bootstrap.Modal(document.getElementById('updateConfirmModal'));
        updateModal.show();
    }

    function deleteAppointment(appointmentID) {
        document.getElementById('deleteAppointmentID').value = appointmentID;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteModal.show();
    }

</script>

<!-- Update Status Confirmation Modal -->
<div class="modal fade" id="updateConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Confirm Status Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to update this appointment's status?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="updateForm" action="update_status.php" method="POST" style="display: inline;">
                    <input type="hidden" id="updateAppointmentID" name="appointmentID">
                    <input type="hidden" id="updateStatusValue" name="status">
                    <button type="submit" class="btn btn-success">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to decline this appointment?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="delete_appointment.php" method="POST" style="display: inline;">
                    <input type="hidden" id="deleteAppointmentID" name="appointmentID">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for additional details -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border: none;">
            <div class="modal-header" style="border: none;">
                <h5 class="modal-title" id="appointmentModalLabel">Other Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="border: none;">
                <p><strong>Add-on Name:</strong> <span id="addonName"></span></p>
                <p><strong>Haircut Name:</strong> <span id="hcName"></span></p>
                <p><strong>Remarks:</strong> <span id="remarks"></span></p>
                <hr>
                <p><strong>Payment Status:</strong> <span id="paymentStatus"></span></p>
                <p><strong>Payment Amount:</strong> <span id="paymentAmount"></span></p>
                <p><strong>GCash Reference:</strong> <span id="gcashReference"></span></p>
                <p><strong>Payment Proof:</strong></p>
                <div id="paymentProofContainer" class="text-center">
                    <!-- Payment proof image will be displayed here -->
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Complete Confirmation Modal -->
<div class="modal fade" id="completeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Confirm Completion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to mark this appointment as complete?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="completeForm" action="update_status.php" method="POST" style="display: inline;">
                    <input type="hidden" id="completeAppointmentID" name="appointmentID">
                    <input type="hidden" name="status" value="Completed">
                    <button type="submit" class="btn btn-success">Complete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reminder Confirmation Modal -->
<div class="modal fade" id="reminderConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Send Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to send a reminder for this appointment?
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="sendReminder()">Send Reminder</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 justify-content-center position-relative">
                <h5 class="modal-title fs-4 fw-bold" id="messageModalLabel">Status</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center border-0">
                <p id="statusMessage" class="mb-0"></p>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Reason Modal -->
<div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 justify-content-center position-relative">
                <h5 class="modal-title fs-4 fw-bold" id="cancelReasonModalLabel">Cancel Appointment</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center border-0">
                <form id="cancelForm" action="update_status.php" method="POST">
                    <input type="hidden" id="cancelAppointmentID" name="appointmentID">
                    <input type="hidden" name="status" value="Cancelled">
                    <label for="cancelReason" class="form-label">Reason for Cancellation (Required)</label>
                    <textarea id="cancelReason" name="reason" class="form-control" rows="3" required></textarea>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmComplete(appointmentId) {
    fetch(`check_barber.php?appointmentID=${appointmentId}`)
    .then(response => response.json())
    .then(data => {
        if (!data.hasBarber) {
            document.getElementById('statusMessage').innerText = 'Please assign a barber before marking the appointment as done.';
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
            return;
        }
        
        document.getElementById('completeAppointmentID').value = appointmentId;
        const modal = new bootstrap.Modal(document.getElementById('completeConfirmModal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error checking barber:', error);
        document.getElementById('statusMessage').innerText = 'An error occurred while checking barber assignment.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    });
}

function confirmCancel(appointmentId) {
    document.getElementById('cancelAppointmentID').value = appointmentId;
    const modal = new bootstrap.Modal(document.getElementById('cancelReasonModal'));
    modal.show();
}

function confirmReminder(appointmentId) {
    document.getElementById('appointmentID').value = appointmentId;
    const modal = new bootstrap.Modal(document.getElementById('reminderConfirmModal'));
    modal.show();
}

// Update the existing sendReminder function to use the new modal
function sendReminder() {
    let appointmentID = document.getElementById("appointmentID").value;
    
    const reminderModal = bootstrap.Modal.getInstance(document.getElementById('reminderConfirmModal'));
    if (reminderModal) {
        reminderModal.hide();
    }

    fetch('update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `appointmentID=${appointmentID}&status=Reminder`
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('statusMessage').innerText = data.success ? 
            "Reminder email sent successfully!" : "Error: " + data.message;
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    })
    .catch(error => {
        console.error("Error:", error);
        document.getElementById('statusMessage').innerText = "An error occurred while sending the reminder.";
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    });
}
</script>

<script>
function openStatusModal(appointmentId, customerId) {
    document.getElementById('appointmentID').value = appointmentId;

    // Get the appointment date from the row
    const appointmentRow = document.querySelector(`[onclick*="${appointmentId}"]`).closest('tr');
    const appointmentDateCell = appointmentRow.children[2].textContent; // Get the date from the third column
    
    // Get current date and format it similarly
    const today = new Date();
    const formattedToday = today.toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric', 
        year: 'numeric' 
    });

    // Get the complete button
    const completeButton = document.querySelector('#statusForm button[type="submit"]');
    
    // Compare dates and disable button if not today
    if (appointmentDateCell !== formattedToday) {
        completeButton.disabled = true;
        completeButton.title = 'Can only complete appointments scheduled for today';
        completeButton.classList.add('btn-secondary');
        completeButton.classList.remove('btn-success');
    } else {
        completeButton.disabled = false;
        completeButton.title = '';
        completeButton.classList.add('btn-success');
        completeButton.classList.remove('btn-secondary');
    }

    const reminderButton = document.getElementById('sendReminderButton');
    
    // Ensure proper NULL checking
    if (!customerId || customerId === "null" || customerId === "undefined") {
        reminderButton.style.display = 'none'; // Hide if customerID is NULL
    } else {
        reminderButton.style.display = 'block'; // Show otherwise
    }

    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}
</script>

<script>
    function showAppointmentDetails(appointmentID, isWalkIn) {
        fetch('fetch_appointment_details.php?appointmentID=' + appointmentID)
            .then(response => {
                if (!response.ok) {
                    throw new Error("HTTP error " + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Received data:", data);

                if (data.error) {
                    console.error("Server error:", data.error);
                    console.error("Error details:", data.details);
                    alert("Error: " + data.error);
                    return;
                }

                document.getElementById('addonName').innerText = data.addonName || 'N/A';
                document.getElementById('hcName').innerText = data.hcName || 'N/A';
                document.getElementById('remarks').innerText = data.remarks || 'N/A';

                // Get payment details elements
                const paymentDetailsElements = [
                    document.getElementById('paymentStatus').parentElement,
                    document.getElementById('paymentAmount').parentElement,
                    document.getElementById('gcashReference').parentElement,
                    document.getElementById('paymentProofContainer').previousElementSibling, // "Payment Proof:" label
                    document.getElementById('paymentProofContainer')
                ];

                // Show/hide payment details based on isWalkIn
                paymentDetailsElements.forEach(element => {
                    if (element) {
                        element.style.display = isWalkIn ? 'none' : 'block';
                    }
                });

                // Only populate payment details if not a walk-in
                if (!isWalkIn) {
                    document.getElementById('paymentStatus').innerText = data.paymentStatus || 'N/A';
                    document.getElementById('paymentAmount').innerText = "₱" + (data.paymentAmount || "0.00");
                    document.getElementById('gcashReference').innerText = data.gcashReference || 'N/A';

                    const proofContainer = document.getElementById('paymentProofContainer');
                    proofContainer.innerHTML = '';

                    // Fetch payment proof image only for non-walk-in appointments
                    fetch('fetch_payment_proof.php?appointmentID=' + appointmentID)
                        .then(response => response.json())
                        .then(imageData => {
                            if (imageData.success && imageData.image) {
                                const img = document.createElement('img');
                                img.style.width = '100%';
                                img.style.maxHeight = '300px';
                                img.style.objectFit = 'contain';
                                img.classList.add('img-fluid', 'mb-3');

                                img.onload = function() {
                                    console.log("Image loaded successfully");
                                };

                                img.onerror = function() {
                                    console.error("Error loading image");
                                    proofContainer.innerHTML = '<p class="text-danger">Error loading payment proof image</p>';
                                };

                                img.src = imageData.image;
                                proofContainer.appendChild(img);
                            } else {
                                proofContainer.innerHTML = '<p class="text-muted">No payment proof available</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching payment proof:', error);
                            proofContainer.innerHTML = '<p class="text-danger">Error loading payment proof image</p>';
                        });
                }

                // Show/hide haircut name and remarks based on isWalkIn
                if (!isWalkIn) {
                    document.getElementById('hcName').parentElement.style.display = 'block';
                    document.getElementById('remarks').parentElement.style.display = 'block';
                } else {
                    document.getElementById('hcName').parentElement.style.display = 'none';
                    document.getElementById('remarks').parentElement.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching details:', error);
                console.error('Error stack:', error.stack);
                alert('Error fetching appointment details. Please check the console for more information.');
            });
    }
</script>

<script>
// Add form submission handler for Complete action
document.getElementById('completeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Close the complete confirmation modal
        const completeModal = bootstrap.Modal.getInstance(document.getElementById('completeConfirmModal'));
        completeModal.hide();
        
        // Show the message modal
        document.getElementById('statusMessage').innerText = data.message;
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        
        // Reload the page after closing the message modal
        document.getElementById('messageModal').addEventListener('hidden.bs.modal', function () {
            window.location.reload();
        }, { once: true });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('statusMessage').innerText = 'An error occurred while updating the appointment status.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    });
});

// Add form submission handler for Cancel action
document.getElementById('cancelForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const reasonField = document.getElementById('cancelReason');
    
    // Check if the reason field is empty
    if (!reasonField.value.trim()) {
        document.getElementById('statusMessage').innerText = 'Please provide a reason for cancellation.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('update_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Close the cancel reason modal
        const cancelModal = bootstrap.Modal.getInstance(document.getElementById('cancelReasonModal'));
        cancelModal.hide();
        
        // Show the message modal
        document.getElementById('statusMessage').innerText = data.message;
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        
        // Reload the page after closing the message modal
        document.getElementById('messageModal').addEventListener('hidden.bs.modal', function () {
            window.location.reload();
        }, { once: true });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('statusMessage').innerText = 'An error occurred while cancelling the appointment.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    });
});

// Add hidden input for reminder action
document.body.insertAdjacentHTML('beforeend', '<input type="hidden" id="appointmentID">');
</script>

<script>
// Add this new function to handle barber assignment response
function handleBarberAssignment(form) {
    event.preventDefault(); // Prevent any default behavior
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('Server response: ' + text);
            });
        }
        return response.json();
    })
    .then(data => {
        if (!data || typeof data !== 'object') {
            throw new Error('Invalid response format');
        }

        // Show message modal with response
        document.getElementById('statusMessage').innerText = data.message || 'Barber assigned successfully.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        
        // Remove any existing event listeners
        const modalElement = document.getElementById('messageModal');
        const newModalElement = modalElement.cloneNode(true);
        modalElement.parentNode.replaceChild(newModalElement, modalElement);
        
        // Add new event listener for modal hidden
        if (data.success) {
            newModalElement.addEventListener('hidden.bs.modal', function () {
                window.location.reload();
            }, { once: true });
        }
        
        // Show the modal
        const newModal = new bootstrap.Modal(newModalElement);
        newModal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error in modal without page reload
        document.getElementById('statusMessage').innerText = 'An error occurred while assigning the barber. Please try again.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    });
}
</script>
</body>
</html>
