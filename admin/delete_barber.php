<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['barberID'])) {
    $barberID = intval($_POST['barberID']);

    // Delete query
    $sql = "DELETE FROM barbers_tbl WHERE barberID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $barberID);

    if ($stmt->execute()) {
        echo "<script>alert('Barber deleted successfully'); window.location.href='barbers.php';</script>";
    } else {
        echo "<script>alert('Error deleting barber'); window.location.href='barbers.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
