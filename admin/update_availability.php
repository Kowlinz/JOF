<?php
session_start();
    if (!isset($_SESSION["user"])) {
        header("Location: ../login-staff.php");
    }
    
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get barber ID and availability from form
    $barberID = $_POST['barberID'];
    $availability = $_POST['availability'];

    // Validate inputs
    if (!in_array($availability, ['Available', 'Unavailable'])) {
        die("Invalid availability value.");
    }

    // Update query
    $sql = "UPDATE barbers_tbl SET availability = ? WHERE barberID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $availability, $barberID);

        if ($stmt->execute()) {
            header("Location: barbers.php?success=1"); // Redirect back on success
            exit;
        } else {
            echo "Error updating availability: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
}
$conn->close();
?>
