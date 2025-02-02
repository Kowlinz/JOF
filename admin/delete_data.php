<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

if (isset($_GET['table'])) {
    $table = $_GET['table'];

    // Whitelist of valid table parameters
    $allowedTables = ['cancelled', 'previous_customer'];

    if (!in_array($table, $allowedTables)) {
        die("Invalid table name: " . htmlspecialchars($table)); // Prevent SQL injection
    }

    // Determine actual table name and condition
    $tableName = "appointment_tbl"; // Fixed table name based on your database
    $condition = "";

    if ($table === "cancelled") {
        $condition = "status = 'Cancelled'";
    } elseif ($table === "previous_customer") {
        $condition = "status = 'Completed'";
    }

    // Ensure the query is valid before execution
    if (!empty($tableName) && !empty($condition)) {
        // Step 1: Delete the dependent rows in barb_apps_tbl first
        $deleteBarbAppsQuery = "DELETE FROM barb_apps_tbl WHERE appointmentID IN (SELECT appointmentID FROM $tableName WHERE $condition)";
        
        // Debugging output
        echo "Executing Query for barb_apps_tbl: $deleteBarbAppsQuery <br>";
        
        if (!mysqli_query($conn, $deleteBarbAppsQuery)) {
            die("Error deleting dependent records from barb_apps_tbl: " . mysqli_error($conn)); // Display MySQL error
        }

        // Step 2: Delete the dependent rows in earnings_tbl first
        $deleteEarningsQuery = "DELETE FROM earnings_tbl WHERE appointmentID IN (SELECT appointmentID FROM $tableName WHERE $condition)";
        
        // Debugging output
        echo "Executing Query for earnings_tbl: $deleteEarningsQuery <br>";
        
        if (!mysqli_query($conn, $deleteEarningsQuery)) {
            die("Error deleting dependent records from earnings_tbl: " . mysqli_error($conn)); // Display MySQL error
        }

        // Step 3: Now delete from appointment_tbl
        $deleteAppointmentQuery = "DELETE FROM $tableName WHERE $condition";
        
        echo "Executing Query for appointment_tbl: $deleteAppointmentQuery <br>"; // Debugging output

        if (mysqli_query($conn, $deleteAppointmentQuery)) {
            echo "<script>alert('Data deleted successfully!'); window.location.href='a_history.php';</script>";
        } else {
            die("Error deleting data from appointment_tbl: " . mysqli_error($conn)); // Display MySQL error
        }
    } else {
        die("Error: Table name or condition is empty.");
    }
} else {
    die("Invalid request: Table parameter missing.");
}

mysqli_close($conn);
?>
