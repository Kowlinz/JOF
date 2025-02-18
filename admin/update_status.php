<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

include 'db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointmentID = $_POST['appointmentID'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE appointment_tbl SET status = '$status' WHERE appointmentID = '$appointmentID'";
    
    if (mysqli_query($conn, $updateQuery)) {
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit();
    } else {
        echo "<script>alert('Error updating status: " . mysqli_error($conn) . "'); window.location.href = document.referrer;</script>";
    }
}

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Received appointmentID: " . ($_POST['appointmentID'] ?? 'Not Set'));
    error_log("Received status: " . ($_POST['status'] ?? 'Not Set'));

    $appointmentID = $_POST['appointmentID'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$appointmentID || !$status) {
        error_log("Missing appointmentID or status in request.");
        $response['success'] = false;
        $response['message'] = "Invalid input. Appointment ID or Status is missing.";
        echo json_encode($response);
        exit();
    }

    try {
        $conn->begin_transaction();
        
        // Fetch customer details
        $query = "SELECT c.email, c.firstName, c.lastName, a.date, a.timeSlot, a.serviceID, a.customerID
                  FROM appointment_tbl a 
                  LEFT JOIN customer_tbl c ON a.customerID = c.customerID 
                  WHERE a.appointmentID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $appointmentID);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $stmt->close();

        // If customerID is NULL, skip sending an email but still update status
        if (!$customer || empty($customer['customerID'])) {
            error_log("Skipping email notification: No valid customer for appointment ID: $appointmentID");
            $sendEmail = false;
        } else {
            $sendEmail = true;
            $email = $customer['email'];
            $customerName = htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName'], ENT_QUOTES, 'UTF-8');
            $appointmentDate = $customer['date'];
            $appointmentTime = $customer['timeSlot'];
        }

        // Update appointment status
        if ($status !== 'Reminder') { 
            if ($status === 'Cancelled') {
                $reason = htmlspecialchars($_POST['reason'] ?? '', ENT_QUOTES, 'UTF-8');
                $updateQuery = "UPDATE appointment_tbl SET status = ?, reason = ?, payment_status = 'cancelled' WHERE appointmentID = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ssi", $status, $reason, $appointmentID);
            } elseif ($status === 'Completed') {
                $updateQuery = "UPDATE appointment_tbl SET status = ?, payment_status = 'paid' WHERE appointmentID = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("si", $status, $appointmentID);
            } else {
                $updateQuery = "UPDATE appointment_tbl SET status = ? WHERE appointmentID = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("si", $status, $appointmentID);
            }
            $stmt->execute();
            $stmt->close();
        }

        // Earnings Calculation for Completed Appointments
        if ($status === 'Completed') {
            $fetchQuery = "
                SELECT b.barberID, s.servicePrice
                FROM appointment_tbl a
                LEFT JOIN barb_apps_tbl b ON a.appointmentID = b.appointmentID
                LEFT JOIN service_tbl s ON a.serviceID = s.serviceID
                WHERE a.appointmentID = ?";
            $stmtFetch = $conn->prepare($fetchQuery);
            $stmtFetch->bind_param("i", $appointmentID);
            $stmtFetch->execute();
            $result = $stmtFetch->get_result();
            $stmtFetch->close();

            if ($result && $row = $result->fetch_assoc()) {
                $servicePrice = $row['servicePrice'];
                $barberID = $row['barberID'];
                
                if ($servicePrice && $barberID) {
                    $adminEarnings = $servicePrice * 0.6;
                    $barberEarnings = $servicePrice * 0.4;
                    $adminID = 1;
                    
                    $insertEarningsQuery = "INSERT INTO earnings_tbl (adminID, appointmentID, barberID, adminEarnings, barberEarnings) VALUES (?, ?, ?, ?, ?)";
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

        $conn->commit();

        // Only send email if customerID is valid
        if ($sendEmail) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jackoffadeswebsite@gmail.com'; 
            $mail->Password = 'edol rcjc oakv imen'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('jackoffadeswebsite@gmail.com', 'Jack of Fades');
            $mail->addAddress($email, $customerName);
            $mail->isHTML(true);

        if ($status == 'Completed') {
            $mail->Subject = "Thank You for Your Visit!";
            $mail->Body = "Dear $customerName,<br><br>Thank you for choosing Jack of Fades! We look forward to seeing you again.<br><br>Best regards,<br>Jack of Fades Team";
        } elseif ($status == 'Cancelled') {
            $mail->Subject = "Appointment Cancellation Notice";
            $mail->Body = "Dear $customerName,<br><br>Your appointment on $appointmentDate at $appointmentTime has been canceled.<br>Reason: $reason.<br><br>We apologize for the inconvenience.<br><br>Jack of Fades Team";
        } elseif ($status == 'Reminder') {
            $mail->Subject = "Appointment Reminder";
            $mail->Body = "Dear $customerName,<br><br>This is a reminder for your upcoming appointment on <strong>$appointmentDate</strong> at <strong>$appointmentTime</strong>. We look forward to serving you!<br><br>Jack of Fades Team";
        }

            $mail->send();
        }

        $response['success'] = true;
        $response['message'] = "Appointment has been " . strtolower($status) . " successfully." . ($sendEmail ? " Email notification sent." : "");
    } catch (Exception $e) {
        error_log("Transaction failed: " . $e->getMessage());
        $conn->rollback();
        $response['success'] = false;
        $response['message'] = "Error: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid request method.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>