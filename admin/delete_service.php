<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['serviceID'])) {
        die(json_encode(['success' => false, 'message' => 'No service ID provided']));
    }

    $serviceID = $_POST['serviceID'];

    // Check if service is being used in appointments
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment_services WHERE serviceID = ?");
    $check_stmt->bind_param("i", $serviceID);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $check_stmt->close();
        die(json_encode([
            'success' => false, 
            'message' => 'This service cannot be deleted because it is being used in existing appointments.'
        ]));
    }

    $check_stmt->close();

    // Delete the service
    $stmt = $conn->prepare("DELETE FROM service_tbl WHERE serviceID = ?");
    $stmt->bind_param("i", $serviceID);

    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete service']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?> 