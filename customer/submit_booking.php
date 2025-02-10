<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    header("Location: ../login.php");
    exit();
}

// Get the logged-in customer's ID from the session
$customerID = $_SESSION["customerID"];

// Verify if the customer exists
$sql_customer = "SELECT customerID FROM customer_tbl WHERE customerID = ?";
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
    $paymentOption = $_POST['paymentOption'] ?? null; // Default to full payment

    // Validate required fields (Date, Time, Service)
    if (!$date || !$timeSlot || !$serviceID || !$paymentOption) {
        echo "<script>alert('Error: Missing required fields.'); window.history.back();</script>";
        exit();
    }

    // Fetch service price
    $sql_price = "SELECT servicePrice FROM service_tbl WHERE serviceID = ?";
    $stmt_price = $conn->prepare($sql_price);
    $stmt_price->bind_param("i", $serviceID);
    $stmt_price->execute();
    $result_price = $stmt_price->get_result();
    $row_price = $result_price->fetch_assoc();
    $totalPrice = $row_price['servicePrice'] ?? 0;
    $stmt_price->close();

    // Determine payment amount
    $paymentAmount = ($paymentOption === "downpayment") ? $totalPrice * 0.5 : $totalPrice;
    $paymentStatus = ($paymentOption === "downpayment") ? "partial" : "paid";

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

    // Insert the appointment with payment details
    $sql_insert = "INSERT INTO appointment_tbl (customerID, date, timeSlot, serviceID, addonID, hcID, remarks, status, payment_status, payment_amount) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)";
    
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issiiissd", 
        $customerID, $date, $timeSlot, $serviceID, 
        $addonID, $hcID, $remarks, $paymentStatus, $paymentAmount
    );

    if ($stmt_insert->execute()) {
        $appointmentID = $stmt_insert->insert_id;

        // Redirect to GCash payment page
        header("Location: gcash_payment.php?appointmentID=$appointmentID&amount=$paymentAmount");
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
