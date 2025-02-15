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
                                            a.status = 'Pending'";

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
                                                            <select name='barberID' class='form-select'>
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
                                                        <button type='button' class='btn btn-link' 
                                                            onclick='openStatusModal({$row['appointmentID']}, " . ($row['customerID'] ? $row['customerID'] : 'null') . ")'>
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
</script>

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

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 justify-content-center position-relative">
                <h5 class="modal-title fs-4 fw-bold" id="statusModalLabel">Update Appointment Status</h5>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center border-0">
                <form id="statusForm" action="update_status.php" method="POST" onsubmit="handleStatusSubmit(event)">
                    <input type="hidden" id="appointmentID" name="appointmentID">
                    <input type="hidden" name="status" value="Completed">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Mark as Complete
                        </button>
                        <button type="button" class="btn btn-danger" onclick="openCancelModal()">
                            <i class="fas fa-times me-2"></i>Cancel Appointment
                        </button>
                        <button type="button" class="btn btn-warning" id="sendReminderButton" onclick="sendReminder()">
                            <i class="fas fa-bell me-2"></i>Send Reminder
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
                        <button type="submit" class="btn btn-danger">Cancel</button>
                    </div>
                </form>
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
    function sendReminder() {
        let appointmentID = document.getElementById("appointmentID").value;

        if (!appointmentID) {
            alert("Invalid appointment ID.");
            return;
        }

        // Close the Status Modal before sending the reminder
        const statusModal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
        if (statusModal) {
            statusModal.hide();
        }

        // Sending an AJAX request to update_status.php with "Reminder" status
        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `appointmentID=${appointmentID}&status=Reminder`
        })
        .then(response => response.json())
        .then(data => {
            console.log("Response:", data);  // Debug response
            if (data.success) {
                // Show success message in the modal
                document.getElementById('statusMessage').innerText = "Reminder email sent successfully!";
                const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                messageModal.show();
            } else {
                // Show error message in the modal
                document.getElementById('statusMessage').innerText = "Error: " + data.message;
                const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                messageModal.show();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            // Show error message in the modal
            document.getElementById('statusMessage').innerText = "An error occurred while sending the reminder.";
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
        });
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

// Add this to handle the form submission
document.getElementById('cancelForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const reasonField = document.getElementById('cancelReason');
    
    // Check if the reason field is empty
    if (!reasonField.value.trim()) {
        // Show the toast notification
        const toast = new bootstrap.Toast(document.getElementById('fillFieldToast'));
        toast.show();
        return; // Prevent form submission
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
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('statusMessage').innerText = 'An error occurred while processing your request.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    });
});
</script>

<script>
// Handle barber assignment forms
document.querySelectorAll('.assign-barber-form select').forEach(select => {
    select.addEventListener('change', function() {
        const form = this.closest('form');
        const formData = new FormData(form);
        
        fetch('assign_barber.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Always show the message modal for both success and error
            document.getElementById('statusMessage').innerText = data.message || 'Barber assigned successfully.';
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
            
            // If successful, reload the page after the modal is closed
            if (data.success) {
                document.getElementById('messageModal').addEventListener('hidden.bs.modal', function () {
                    window.location.reload();
                }, { once: true }); // Use once: true to prevent multiple event listeners
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('statusMessage').innerText = 'An error occurred while assigning the barber.';
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
        });
    });
});
</script>

<script>
function handleStatusSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const appointmentID = form.querySelector('#appointmentID').value;
    
    // First check if a barber is assigned
    fetch(`check_barber.php?appointmentID=${appointmentID}`)
    .then(response => response.json())
    .then(data => {
        if (!data.hasBarber) {
            // Show error message if no barber is assigned
            const statusModal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
            statusModal.hide();
            
            document.getElementById('statusMessage').innerText = 'Please assign a barber before marking the appointment as done.';
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
            return;
        }
        
        // If barber is assigned, proceed with the status update
        const formData = new FormData(form);
        return fetch('update_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Response:", data);  // Debug response
            // Close the status modal
            const statusModal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
            statusModal.hide();
            
            // Show the message modal
            document.getElementById('statusMessage').innerText = data.message;
            const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
            
            // Reload the page after closing the message modal
            document.getElementById('messageModal').addEventListener('hidden.bs.modal', function () {
                window.location.reload();
            }, { once: true });
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('statusMessage').innerText = 'An error occurred while updating the appointment status.';
        const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
    });
}
</script>

<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="fillFieldToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Please fill out this field.
        </div>
    </div>
</div>

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
</body>
</html>
