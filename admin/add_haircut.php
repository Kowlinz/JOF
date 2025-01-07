<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (!isset($_POST['haircutName']) || !isset($_FILES['haircutPhoto']) || !isset($_POST['haircutCategory'])) {
        die(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }

    $haircutName = trim($_POST['haircutName']);
    $haircutCategory = trim($_POST['haircutCategory']);
    
    // Validate file
    $file = $_FILES['haircutPhoto'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['success' => false, 'message' => 'Error uploading file']));
    }

    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        die(json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed']));
    }

    // Read the image file
    $imageData = file_get_contents($file['tmp_name']);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO haircut_tbl (hcName, hcImage, hcCategory) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $haircutName, $imageData, $haircutCategory);

    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Haircut added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add haircut']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?> 