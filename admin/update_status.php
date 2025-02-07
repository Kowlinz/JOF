<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

include 'db_connect.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentID = $_POST['appointmentID'];
    $status = $_POST['status'];

    try {
        if ($status === 'Cancelled') {
            $reason = mysqli_real_escape_string($conn, $_POST['reason']);
            $query = "UPDATE appointment_tbl SET status = '$status', reason = '$reason' WHERE appointmentID = $appointmentID";
        } else {
            $query = "UPDATE appointment_tbl SET status = '$status' WHERE appointmentID = $appointmentID";
        }
        
        if (mysqli_query($conn, $query)) {
            $response['success'] = true;
            $response['message'] = "Appointment has been " . strtolower($status) . " successfully.";
        } else {
            throw new Exception(mysqli_error($conn));
        }
    }

    catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "Error updating appointment status: " . $e->getMessage();

    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;

    // Validate inputs
    if (!empty($appointmentID) && !empty($status)) {
        $conn->begin_transaction(); // Begin transaction for atomicity
        try {
            // 1. Update the appointment_tbl
            $updateQuery = "UPDATE appointment_tbl SET status = ? WHERE appointmentID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $status, $appointmentID);
            $stmt->execute();
            $stmt->close();

            // 2. Check if status is 'Completed' to calculate earnings
            if ($status === 'Completed') {
                // Fetch service price and barberID
                $fetchQuery = "
                    SELECT a.serviceID, b.barberID, s.servicePrice
                    FROM appointment_tbl a
                    LEFT JOIN barb_apps_tbl b ON a.appointmentID = b.appointmentID
                    LEFT JOIN service_tbl s ON a.serviceID = s.serviceID
                    WHERE a.appointmentID = ?
                ";
                $stmtFetch = $conn->prepare($fetchQuery);
                $stmtFetch->bind_param("i", $appointmentID);
                $stmtFetch->execute();
                $result = $stmtFetch->get_result();
                $stmtFetch->close();

                if ($result && $row = $result->fetch_assoc()) {
                    $servicePrice = $row['servicePrice'];
                    $barberID = $row['barberID'];

                    if ($servicePrice && $barberID) {
                        // Split earnings into half
                        $adminEarnings = $servicePrice * 0.6;
                        $barberEarnings = $servicePrice * 0.4;

                        // Fetch the adminID from session
                        $adminID = 1;

                        // Insert earnings into earnings_tbl
                        $insertEarningsQuery = "
                            INSERT INTO earnings_tbl (adminID, appointmentID, barberID, adminEarnings, barberEarnings)
                            VALUES (?, ?, ?, ?, ?)
                        ";
                        $stmtInsert = $conn->prepare($insertEarningsQuery);
                        $stmtInsert->bind_param("iiidd", $adminID, $appointmentID, $barberID, $adminEarnings, $barberEarnings);
                        $stmtInsert->execute();
                        $stmtInsert->close();
                    } else {
                        throw new Exception("Missing barber for this appointment.");
                    }
                } else {
                    throw new Exception("Error fetching appointment details.");
                }
            }

            // Commit transaction if all steps succeed
            $conn->commit();
            header('Location: appointments.php?status=success&message=Appointment status updated successfully.');
            exit();
        } catch (Exception $e) {
            // Rollback transaction on failure
            $conn->rollback();
            header('Location: appointments.php?status=error&message=' . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header('Location: appointments.php?status=error&message=Invalid input.');
        exit();
    }
} else {
    header('Location: appointments.php?status=error&message=Invalid request method.');
    exit();
}

$conn->close();
?>