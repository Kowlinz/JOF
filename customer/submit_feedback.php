<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['appointmentId']) || !isset($data['comment'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$appointmentId = $data['appointmentId'];
$comment = $data['comment'];
$customerId = $_SESSION['customerID'];

// Update feedback in appointment_tbl
$sql = "UPDATE appointment_tbl SET feedback = ? WHERE appointmentID = ? AND customerID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $comment, $appointmentId, $customerId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}

$stmt->close();
$conn->close(); 