<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (!isset($_POST['addonName']) || !isset($_POST['addonDesc']) || !isset($_POST['addonPrice'])) {
        die(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }

    $addonName = trim($_POST['addonName']);
    $addonDesc = trim($_POST['addonDesc']);
    $addonPrice = trim($_POST['addonPrice']);

    // Validate price is numeric and positive
    if (!is_numeric($addonPrice) || $addonPrice <= 0) {
        die(json_encode(['success' => false, 'message' => 'Price must be a positive number']));
    }

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO addon_tbl (addonName, addonDesc, addonPrice) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $addonName, $addonDesc, $addonPrice);

    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Add-on added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add add-on']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?> 