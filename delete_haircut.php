<?php
session_start();
if (!isset($_SESSION["user"])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

include 'db_connect.php';

if (!isset($_POST['haircut_id'])) {
    die(json_encode(['success' => false, 'message' => 'No haircut ID provided']));
}

$haircut_id = $_POST['haircut_id'];

$check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment_tbl WHERE hcID = ?");
$check_stmt->bind_param("i", $haircut_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    $check_stmt->close();
    $conn->close();
    die(json_encode([
        'success' => false, 
        'message' => 'This haircut cannot be deleted because it is being used in existing appointments.'
    ]));
}

$check_stmt->close();

$delete_stmt = $conn->prepare("DELETE FROM haircut_tbl WHERE hcID = ?");
$delete_stmt->bind_param("i", $haircut_id);

try {
    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete haircut']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$delete_stmt->close();
$conn->close();
?> 