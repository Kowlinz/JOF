<?php
session_start();
include 'db_connect.php';

$response = array();

if (isset($_GET['table'])) {
    $table = $_GET['table'];
    
    try {
        // Start transaction
        $conn->begin_transaction();

        if ($table === 'previous_customer' || $table === 'cancelled') {
            $status = ($table === 'previous_customer') ? 'Completed' : 'Cancelled';
            
            // First, check if there are any records to delete
            $checkQuery = "SELECT COUNT(*) as count FROM appointment_tbl WHERE status = ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];

            if ($count === 0) {
                throw new Exception("No records found to delete.");
            }

            // Get all appointmentIDs that will be deleted
            $appointmentQuery = "SELECT appointmentID FROM appointment_tbl WHERE status = ?";
            $stmt = $conn->prepare($appointmentQuery);
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Get all appointmentIDs
            $appointmentIDs = [];
            while ($row = $result->fetch_assoc()) {
                $appointmentIDs[] = $row['appointmentID'];
            }

            if (!empty($appointmentIDs)) {
                // Convert array to string for IN clause
                $appointmentIDsString = implode(',', $appointmentIDs);

                // Delete from barb_apps_tbl first (child table)
                $deleteBarberApps = "DELETE FROM barb_apps_tbl WHERE appointmentID IN ($appointmentIDsString)";
                if (!$conn->query($deleteBarberApps)) {
                    throw new Exception("Error deleting from barb_apps_tbl: " . $conn->error);
                }

                // Delete from earnings_tbl (if it exists and has foreign key)
                $deleteEarnings = "DELETE FROM earnings_tbl WHERE appointmentID IN ($appointmentIDsString)";
                if (!$conn->query($deleteEarnings)) {
                    throw new Exception("Error deleting from earnings_tbl: " . $conn->error);
                }

                // Finally delete from appointment_tbl (parent table)
                $deleteAppointments = "DELETE FROM appointment_tbl WHERE appointmentID IN ($appointmentIDsString)";
                if (!$conn->query($deleteAppointments)) {
                    throw new Exception("Error deleting from appointment_tbl: " . $conn->error);
                }
            }

            // Commit transaction
            $conn->commit();
            
            $response['success'] = true;
            $response['message'] = 'Data deleted successfully';
        } else {
            throw new Exception("Invalid table specified");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'No table specified';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
