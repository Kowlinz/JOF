<?php
session_start(); // Start the session
if (isset($_SESSION['appointmentID'], $_SESSION['paymentAmount'])) {
    $appointmentID = $_SESSION['appointmentID'];
    $paymentAmount = $_SESSION['paymentAmount'];

    // Unset session variables after use (optional for security)
    unset($_SESSION['appointmentID'], $_SESSION['paymentAmount']);
} else {
    die("Invalid access.");
}

include 'db_connect.php';

// Fetch appointment details including service and addon prices
$sql = "SELECT 
            a.serviceID, a.addonID, a.payment_status, 
            s.servicePrice, 
            COALESCE(ad.addonPrice, 0) AS addonPrice 
        FROM appointment_tbl a
        LEFT JOIN service_tbl s ON a.serviceID = s.serviceID
        LEFT JOIN addon_tbl ad ON a.addonID = ad.addonID
        WHERE a.appointmentID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointmentID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// If no result found, show error and exit
if (!$row) {
    echo "<script>alert('Error: Appointment not found.'); window.location.href = 'appointments.php';</script>";
    exit();
}

// Get service and addon prices
$servicePrice = $row['servicePrice'] ?? 0;
$addonPrice = $row['addonPrice'] ?? 0;
$fullPrice = $servicePrice + $addonPrice;

// Determine the payment amount based on selected payment option
$paymentStatus = $row['payment_status']; // Should be 'downpayment' or 'full'
$paymentAmount = ($paymentStatus === 'partial') ? $fullPrice * 0.5 : $fullPrice;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GCash Payment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container text-center mt-5">
        <h2>Pay via GCash</h2>
        <p>Scan the QR Code</p>
        <img src="../customer/css/images/gcash_qr.JPG" alt="GCash QR Code">
        <br><br>
        <h4>Service Price: ₱<?php echo number_format($servicePrice, 2); ?></h4>
        <h4>Addon Price: ₱<?php echo number_format($addonPrice, 2); ?></h4>
        <h3>
            <strong>
                Total Amount to Pay:
                ₱<?php echo number_format($paymentAmount, 2); ?>
            </strong>
        </h3>
        <br><br>
        <form action="verify_payment.php" method="POST">
            <input type="hidden" name="appointmentID" value="<?php echo $appointmentID; ?>">
            <input type="hidden" name="amount" value="<?php echo $paymentAmount; ?>">
            <label>Enter GCash Reference Number:</label>
            <input type="text" name="gcashRef" required class="form-control">
            <button type="submit" class="btn btn-primary mt-3">Confirm Payment</button>
        </form>
    </div>
</body>
</html>
?>