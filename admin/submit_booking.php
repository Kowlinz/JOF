<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Get the logged-in customer's ID from the session
$customerID = $_SESSION["adminID"];

// Verify if the customer exists
$sql_customer = "SELECT adminID FROM admin_tbl WHERE adminID = ?";
$stmt_customer = $conn->prepare($sql_customer);
$stmt_customer->bind_param("i", $customerID);
$stmt_customer->execute();
$result_customer = $stmt_customer->get_result();

if ($result_customer->num_rows === 0) {
    echo "<script>alert('Error: Invalid customer.'); window.location.href = '../login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $date = $_POST['date'] ?? null;
    $timeSlot = $_POST['timeSlot'] ?? null;
    $serviceID = $_POST['service'] ?? null;
    $addonID = !empty($_POST['addon']) ? $_POST['addon'] : null;
    $hcID = !empty($_POST['haircut']) ? $_POST['haircut'] : null;
    $remarks = $_POST['remarks'] ?? null;

    // Validate required fields (Date, Time, Service)
    if (!$date || !$timeSlot || !$serviceID) {
        echo "<script>alert('Error: Missing required fields.'); window.history.back();</script>";
        exit();
    }

    // Count available barbers
    $sql_barbers = "SELECT COUNT(*) AS totalBarbers FROM barbers_tbl WHERE availability = 'available'";
    $result_barbers = $conn->query($sql_barbers);
    $row_barbers = $result_barbers->fetch_assoc();
    $totalBarbers = $row_barbers['totalBarbers'] ?? 0;

    // Count booked appointments for the selected date and time
    $sql_check = "SELECT COUNT(*) AS bookedSlots FROM appointment_tbl WHERE date = ? AND timeSlot = ? AND status != 'Cancelled'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $date, $timeSlot);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $bookedSlots = $row_check['bookedSlots'] ?? 0;

    // Validate if slots are still available
    if ($bookedSlots >= $totalBarbers) {
        echo "<script>alert('Error: This time slot is fully booked.'); window.history.back();</script>";
        exit();
    }

    // Default status for the appointment
    $status = "Upcoming";

    // Insert the appointment into the database
    $sql_insert = "INSERT INTO appointment_tbl (adminID, date, timeSlot, serviceID, addonID, hcID, remarks, status) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issiiiss", 
        $customerID, $date, $timeSlot, $serviceID, 
        $addonID, $hcID, $remarks, $status
    );

    if ($stmt_insert->execute()) {
        // Redirect after successful booking
        header("Location: appointments.php");
        exit();
    } else {
        echo "Error: " . $stmt_insert->error;
    }

    // Close all statements
    $stmt_customer->close();
    $stmt_check->close();
    $stmt_insert->close();
    $conn->close();
}
?>
