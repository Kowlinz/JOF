<?php
$conn = new mysqli("localhost", "root", "", "jof_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get selected date from request
$date = $_GET['date'] ?? '';

if (!$date) {
    echo json_encode([]);
    exit;
}

// Get total available barbers
$barbersQuery = "SELECT COUNT(*) AS total_barbers FROM barbers_tbl WHERE availability = 'available'";
$barbersResult = $conn->query($barbersQuery);
$barbersRow = $barbersResult->fetch_assoc();
$totalBarbers = (int) $barbersRow['total_barbers'];

// Get all booked time slots for that date
$bookedSlotsQuery = "SELECT timeSlot, COUNT(*) AS booked 
                     FROM appointment_tbl 
                     WHERE date = '$date' AND status != 'Cancelled'
                     GROUP BY timeSlot";
$bookedSlotsResult = $conn->query($bookedSlotsQuery);

$slots = [];
while ($row = $bookedSlotsResult->fetch_assoc()) {
    $slots[$row['timeSlot']] = (int) $row['booked']; // Ensure integer value
}

$conn->close();

// Return data as JSON
echo json_encode(["totalBarbers" => $totalBarbers, "bookedSlots" => $slots]);
?>
