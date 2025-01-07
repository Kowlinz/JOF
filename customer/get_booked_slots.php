<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$sql = "SELECT timeSlot FROM appointment_tbl WHERE date = ? AND status != 'Cancelled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$bookedSlots = array();
while($row = $result->fetch_assoc()) {
    $bookedSlots[] = $row['timeSlot'];
}

echo json_encode($bookedSlots);
$conn->close(); 