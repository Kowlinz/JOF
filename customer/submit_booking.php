<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

// Include the database connection file
include 'db_connect.php';

// Set the adminID to 1 (as per your request)
$adminID = 1; // Static adminID as 1 (assumed to be the admin)

// Verify if the admin exists (optional check)
$sql_admin = "SELECT adminID FROM admin_tbl WHERE adminID = '$adminID'";
$result_admin = $conn->query($sql_admin);

// Check if form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $timeSlot = isset($_POST['timeSlot']) ? $_POST['timeSlot'] : null;
    $serviceID = isset($_POST['service']) ? $_POST['service'] : null;
    $addonID = isset($_POST['addon']) && !empty($_POST['addon']) ? $_POST['addon'] : null;
    $hcID = isset($_POST['haircut']) && !empty($_POST['haircut']) ? $_POST['haircut'] : null;
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;

    // Check if the selected time slot for the selected date is already booked
    $sql_check = "SELECT * FROM appointment_tbl WHERE date = '$date' AND timeSlot = '$timeSlot' AND status != 'Cancelled'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        // Redirect back to walk-in.php with error message
        header("Location: walk-in.php?error=" . urlencode("Error: This time slot is already booked."));
        exit();
    }

    // Validate required fields (Date, Time, Service)
    if (!$date || !$timeSlot || !$serviceID) {
        // Redirect back to walk-in.php with error message
        header("Location: walk-in.php?error=" . urlencode("Please fill in all required fields (Date, Time Slot, and Service)."));
        exit();
    }

    // Default status for the appointment is "Pending"
    $status = "Pending";

    // Insert into the appointment_tbl (excluding adminID)
    $sql = "INSERT INTO appointment_tbl (adminID, date, timeSlot, serviceID, addonID, hcID, remarks, status) 
    VALUES ('$adminID', '$date', '$timeSlot', '$serviceID', " . ($addonID !== null ? "'$addonID'" : "NULL") . ", " . ($hcID !== null ? "'$hcID'" : "NULL") . ", '$remarks', '$status')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to appointment.php after successful booking
        header("Location: appointments.php");
        exit();
    } else {
        echo "<script>alert('Error: Unable to book appointment.'); window.history.back();</script>";
        exit();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- existing head content -->
</head>
<body>
    <!-- existing body content -->

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- existing scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
