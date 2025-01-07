<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (!isset($_POST['serviceID']) || !isset($_POST['serviceName']) || !isset($_POST['serviceDesc']) || !isset($_POST['servicePrice'])) {
        die(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }

    $serviceID = trim($_POST['serviceID']);
    $serviceName = trim($_POST['serviceName']);
    $serviceDesc = trim($_POST['serviceDesc']);
    $servicePrice = trim($_POST['servicePrice']);

    // Validate price is numeric and positive
    if (!is_numeric($servicePrice) || $servicePrice <= 0) {
        die(json_encode(['success' => false, 'message' => 'Price must be a positive number']));
    }

    // Update the service
    $stmt = $conn->prepare("UPDATE service_tbl SET serviceName = ?, serviceDesc = ?, servicePrice = ? WHERE serviceID = ?");
    $stmt->bind_param("ssdi", $serviceName, $serviceDesc, $servicePrice, $serviceID);

    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update service']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?> 