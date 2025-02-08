<?php
include 'db_connect.php';

header('Content-Type: application/json');

$response = ["success" => false, "message" => "Invalid request"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['barberID'])) {
    $barberID = intval($_POST['barberID']);

    // First, delete related records from barb_apps_tbl
    $deleteAppointments = "DELETE FROM barb_apps_tbl WHERE barberID = ?";
    $stmt1 = $conn->prepare($deleteAppointments);
    
    if ($stmt1) {
        $stmt1->bind_param("i", $barberID);
        $stmt1->execute();
        $stmt1->close();
    } else {
        $response = ["success" => false, "message" => "Failed to delete related records: " . $conn->error];
        echo json_encode($response);
        exit;
    }

    // Now, delete barber from barbers_tbl
    $deleteBarber = "DELETE FROM barbers_tbl WHERE barberID = ?";
    $stmt2 = $conn->prepare($deleteBarber);
    
    if ($stmt2) {
        $stmt2->bind_param("i", $barberID);
        if ($stmt2->execute()) {
            if ($stmt2->affected_rows > 0) {
                $response = ["success" => true, "message" => "Barber deleted successfully"];
            } else {
                $response = ["success" => false, "message" => "No barber found with this ID"];
            }
        } else {
            $response = ["success" => false, "message" => "Error deleting barber: " . $stmt2->error];
        }
        $stmt2->close();
    } else {
        $response = ["success" => false, "message" => "Failed to prepare barber delete statement: " . $conn->error];
    }

    $conn->close();
}

echo json_encode($response);
?>
