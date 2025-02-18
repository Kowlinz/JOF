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

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentID = $_POST['appointmentID'] ?? null;
    $status = $_POST['status'] ?? null;
    $reason = $_POST['reason'] ?? '';

    if (!$appointmentID || !$status) {
        $response["message"] = "Invalid input: Appointment ID or Status is missing.";
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

        $sendEmail = $customer && !empty($customer['customerID']);

        if ($sendEmail) {
            $email = $customer['email'];
            $customerName = htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName'], ENT_QUOTES, 'UTF-8');
            $appointmentDate = $customer['date'];
            $appointmentTime = $customer['timeSlot'];
        }

        // Update appointment status
        $updateQuery = "UPDATE appointment_tbl SET status = ?, payment_status = ?, reason = ? WHERE appointmentID = ?";
        $paymentStatus = ($status === 'Completed') ? 'paid' : (($status === 'Cancelled') ? 'cancelled' : NULL);
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $status, $paymentStatus, $reason, $appointmentID);
        $stmt->execute();
        $stmt->close();

        // Earnings Calculation for Completed Appointments
        if ($status === 'Completed') {
            $fetchQuery = "SELECT b.barberID, s.servicePrice
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
                if (!empty($row['barberID']) && !empty($row['servicePrice'])) {
                    $adminEarnings = $row['servicePrice'] * 0.6;
                    $barberEarnings = $row['servicePrice'] * 0.4;
                    $adminID = 1;
                    
                    $insertEarningsQuery = "INSERT INTO earnings_tbl (adminID, appointmentID, barberID, adminEarnings, barberEarnings)
                                            VALUES (?, ?, ?, ?, ?)";
                    $stmtInsert = $conn->prepare($insertEarningsQuery);
                    $stmtInsert->bind_param("iiidd", $adminID, $appointmentID, $row['barberID'], $adminEarnings, $barberEarnings);
                    $stmtInsert->execute();
                    $stmtInsert->close();
                } else {
                    throw new Exception("Barber or service price missing for this appointment.");
                }
            } else {
                throw new Exception("Error fetching appointment details.");
            }
        }

        $conn->commit();

        // Email Notification
        if ($sendEmail) {
            $mail = new PHPMailer(true);
            try {
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

                switch ($status) {
                    case 'Completed':
                        $mail->Subject = "Thank You for Your Visit!";
                        $mail->Body = "Dear $customerName,<br><br>
                                       Thank you for choosing Jack of Fades! We look forward to seeing you again.<br><br>
                                       Best regards,<br>
                                       Jack of Fades Team";
                        break;
                    case 'Cancelled':
                        $mail->Subject = "Appointment Cancellation Notice";
                        $mail->Body = "Dear $customerName,<br><br>
                                       Your appointment on <strong>$appointmentDate</strong> at <strong>$appointmentTime</strong> has been canceled.<br>
                                       <strong>Reason:</strong> $reason.<br><br>
                                       We apologize for the inconvenience.<br><br>
                                       Jack of Fades Team";
                        break;
                    case 'Reminder':
                        $mail->Subject = "Appointment Reminder";
                        $mail->Body = "Dear $customerName,<br><br>
                                       This is a reminder for your upcoming appointment on <strong>$appointmentDate</strong> at <strong>$appointmentTime</strong>. We look forward to serving you!<br><br>
                                       Jack of Fades Team";
                        break;
                    case 'Upcoming':
                        $mail->Subject = "Payment Confirmation";
                        $mail->Body = "Dear $customerName,<br><br>
                                       Your payment for the appointment on <strong>$appointmentDate</strong> at <strong>$appointmentTime</strong> has been successfully received.<br><br>
                                       Thank you for choosing Jack of Fades!<br><br>
                                       Best regards,<br>
                                       Jack of Fades Team";
                        break;
                }

                $mail->send();
                $response['message'] .= " Email notification sent.";
            } catch (Exception $e) {
                error_log("Email sending failed: " . $mail->ErrorInfo);
            }
        }

        $response["success"] = true;
        $response["message"] = "Appointment has been " . strtolower($status) . " successfully.";
    } catch (Exception $e) {
        error_log("Transaction failed: " . $e->getMessage());
        $conn->rollback();
        $response["message"] = "Error: " . $e->getMessage();
    }
} else {
    $response["message"] = "Invalid request method.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
