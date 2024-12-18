<?php
session_start();

// Check if the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    header("Location: ../login.php");
    exit();
}

// Include the database connection file
include 'db_connect.php';

// Get the logged-in customer's ID from the session
$customerID = $_SESSION["customerID"];

// Verify if the customer exists (optional check)
$sql_customer = "SELECT customerID FROM customer_tbl WHERE customerID = '$customerID'";
$result_customer = $conn->query($sql_customer);

// Check if form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $timeSlot = isset($_POST['timeSlot']) ? $_POST['timeSlot'] : null;
    $serviceID = isset($_POST['service']) ? $_POST['service'] : null;
    $addonID = isset($_POST['addon']) && !empty($_POST['addon']) ? $_POST['addon'] : null;
    $hcID = isset($_POST['haircut']) && !empty($_POST['haircut']) ? $_POST['haircut'] : null;
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;

    // Validate required fields (Date, Time, Service)
    if (!$date || !$timeSlot || !$serviceID) {
        echo "<script>alert('Error: Missing required fields.'); window.history.back();</script>";
        exit();
    }

    // Check if the selected time slot for the selected date is already booked and not canceled
    $sql_check = "SELECT * FROM appointment_tbl WHERE date = '$date' AND timeSlot = '$timeSlot' AND status != 'Cancelled'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Error: This time slot is already booked.'); window.history.back();</script>";
        exit();
    }

    // Default status for the appointment is "Pending"
    $status = "Pending";

    // Insert into the appointment_tbl
    $sql = "INSERT INTO appointment_tbl (customerID, date, timeSlot, serviceID, addonID, hcID, remarks, status) 
    VALUES ('$customerID', '$date', '$timeSlot', '$serviceID', " . ($addonID !== null ? "'$addonID'" : "NULL") . ", " . ($hcID !== null ? "'$hcID'" : "NULL") . ", '$remarks', '$status')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to appointment.php after successful booking
        header("Location: appointment.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
