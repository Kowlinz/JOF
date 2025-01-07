<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (!isset($_POST['serviceName']) || !isset($_POST['serviceDesc']) || !isset($_POST['servicePrice'])) {
        die(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }

    $serviceName = trim($_POST['serviceName']);
    $serviceDesc = trim($_POST['serviceDesc']);
    $servicePrice = trim($_POST['servicePrice']);

    // Validate price is numeric and positive
    if (!is_numeric($servicePrice) || $servicePrice <= 0) {
        die(json_encode(['success' => false, 'message' => 'Price must be a positive number']));
    }

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO service_tbl (serviceName, serviceDesc, servicePrice) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $serviceName, $serviceDesc, $servicePrice);

    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add service']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?> 