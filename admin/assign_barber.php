<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

// Include database connection
include 'db_connect.php';

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointmentID']) && isset($_POST['barberID'])) {
    $appointmentID = mysqli_real_escape_string($conn, $_POST['appointmentID']);
    $barberID = mysqli_real_escape_string($conn, $_POST['barberID']);

    // Step 1: Get the appointment details (date and time)
    $appointmentQuery = "SELECT date, timeSlot FROM appointment_tbl WHERE appointmentID = '$appointmentID'";
    $appointmentResult = mysqli_query($conn, $appointmentQuery);
    
    if (!$appointmentResult || mysqli_num_rows($appointmentResult) == 0) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
        exit();
    }

    $appointment = mysqli_fetch_assoc($appointmentResult);
    $appointmentDate = $appointment['date'];
    $appointmentTime = $appointment['timeSlot'];

    // Step 2: Check if the barber is already booked at the same date and time
    $checkBarberQuery = "SELECT * FROM barb_apps_tbl ba
                         JOIN appointment_tbl a ON ba.appointmentID = a.appointmentID
                         WHERE ba.barberID = '$barberID' 
                         AND a.date = '$appointmentDate' 
                         AND a.timeSlot = '$appointmentTime'
                         AND a.appointmentID != '$appointmentID'"; // Exclude current appointment
                         
    $checkBarberResult = mysqli_query($conn, $checkBarberQuery);

    if (mysqli_num_rows($checkBarberResult) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: The selected barber is already booked for this timeslot.'
        ]);
        exit();
    }

    // Step 3: Check if there's an existing assignment
    $checkQuery = "SELECT * FROM barb_apps_tbl WHERE appointmentID = '$appointmentID'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        // Update existing assignment
        $query = "UPDATE barb_apps_tbl SET barberID = '$barberID' WHERE appointmentID = '$appointmentID'";
    } else {
        // Create new assignment
        $query = "INSERT INTO barb_apps_tbl (appointmentID, barberID) VALUES ('$appointmentID', '$barberID')";
    }

    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Barber assigned successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error assigning barber: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request parameters.'
    ]);
}
?>
