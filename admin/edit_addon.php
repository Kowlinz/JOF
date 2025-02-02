<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $addonID = $_POST['addonID'];
    $addonName = $_POST['addonName'];
    $addonDesc = $_POST['addonDesc'];
    $addonPrice = $_POST['addonPrice'];

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE addon_tbl SET addonName = ?, addonDesc = ?, addonPrice = ? WHERE addonID = ?");
    $stmt->bind_param("ssii", $addonName, $addonDesc, $addonPrice, $addonID);

    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update add-on']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
    exit();
}

// If not a POST request
echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit();
?> 