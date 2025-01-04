<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
}

// Include database connection
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the data from the form
    $appointmentID = $_POST['appointmentID'];
    $barberID = $_POST['barberID'];

    try {
        // Check if this appointment already has a barber assigned
        $checkQuery = "SELECT * FROM barb_apps_tbl WHERE appointmentID = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $appointmentID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing record
            $updateQuery = "UPDATE barb_apps_tbl SET barberID = ?, adminID = ? WHERE appointmentID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("iii", $barberID, $adminID, $appointmentID);
            $updateStmt->execute();

            if ($updateStmt->affected_rows > 0) {
                header("Location: appointments.php?status=success&message=Barber+assignment+updated+successfully");
                exit();
            } else {
                header("Location: appointments.php?status=error&message=No+changes+were+made");
                exit();
            }
        } else {
            // Insert a new record
            $insertQuery = "INSERT INTO barb_apps_tbl (appointmentID, barberID, adminID) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iii", $appointmentID, $barberID, $adminID);
            $insertStmt->execute();

            if ($insertStmt->affected_rows > 0) {
                header("Location: appointments.php?status=success&message=Barber+assigned+successfully");
                exit();
            } else {
                header("Location: appointments.php?status=error&message=Failed+to+assign+barber");
                exit();
            }
        }
    } catch (Exception $e) {
        header("Location: appointments.php?status=error&message=" . urlencode("Error: " . $e->getMessage()));
        exit();
    }
}
?>
