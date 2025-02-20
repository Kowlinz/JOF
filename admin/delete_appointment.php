<?php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit();
}

include 'db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointmentID = $_POST['appointmentID'];

    // Fetch customer details before deletion
    $query = "SELECT c.email, c.firstName, c.lastName, a.date, a.timeSlot 
              FROM appointment_tbl a 
              LEFT JOIN customer_tbl c ON a.customerID = c.customerID 
              WHERE a.appointmentID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $appointmentID);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();

    if (!$customer) {
        echo json_encode(["success" => false, "message" => "Customer not found."]);
        exit();
    }

    $email = $customer['email'];
    $customerName = htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName'], ENT_QUOTES, 'UTF-8');
    $appointmentDate = $customer['date'];
    $appointmentTime = $customer['timeSlot'];

    // Update appointment status to 'Cancelled'
    $updateQuery = "UPDATE appointment_tbl SET status = 'Cancelled' WHERE appointmentID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $appointmentID);

    if ($stmt->execute()) {
        // Send email notification
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
            $mail->Subject = "Appointment Deletion Notice";
            $mail->Body = "Dear $customerName,<br><br>
                           Your appointment on <strong>$appointmentDate</strong> at <strong>$appointmentTime</strong> has been deleted.<br>
                           If this was a mistake, please contact us.<br><br>
                           Best regards,<br>
                           Jack of Fades Team";

            $mail->send();
            echo json_encode(["success" => true, "message" => "Appointment declined. Email notification sent."]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => "Appointment deleted, but email could not be sent."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Error deleting appointment."]);
    }
}
?>