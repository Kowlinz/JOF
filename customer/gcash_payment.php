<?php
session_start(); // Start the session
if (isset($_SESSION['appointmentID'], $_SESSION['paymentAmount'])) {
    $appointmentID = $_SESSION['appointmentID'];
    $paymentAmount = $_SESSION['paymentAmount'];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | GCash Payment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="../css/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lexend', sans-serif;
            background-color: #1f1f1f;
            color: #ffffff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .payment-container {
            background-color: #2d2d2d;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .qr-code-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 10px;
            margin: 2rem 0;
        }

        .qr-code-container img {
            max-width: 300px;
            height: auto;
        }

        .price-details {
            background-color: #3d3d3d;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .total-amount {
            background-color: #FFDE59;
            color: #000000;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            font-weight: bold;
        }

        .form-control {
            background-color: #3d3d3d;
            border: none;
            color: #ffffff;
            padding: 0.8rem;
            margin: 1rem 0;
        }

        .form-control:focus {
            background-color: #4d4d4d;
            color: #ffffff;
            box-shadow: none;
            border: 1px solid #FFDE59;
        }

        .btn-confirm {
            background-color: #FFDE59;
            color: #000000;
            font-weight: bold;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-confirm:hover {
            background-color: #e6c84f;
            transform: translateY(-2px);
        }

        /* Animation classes */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            margin-bottom: 2rem;
        }

        /* Add animation delays */
        .logo { animation-delay: 0.2s; }
        .payment-container { animation-delay: 0.4s; }
        .form-container { animation-delay: 0.6s; }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7); /* White with some transparency */
        }

        /* Optional: Style the placeholder text when the input is focused */
        .form-control:focus::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .upload-container {
            background-color: #3d3d3d;
            border-radius: 10px;
            padding: 1rem;
        }

        .upload-container input[type="file"] {
            background-color: transparent;
            border: 2px dashed rgba(255, 222, 89, 0.3);
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-container input[type="file"]:hover {
            border-color: #FFDE59;
            background-color: rgba(255, 222, 89, 0.05);
        }

        .upload-container .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Style for the file input */
        .form-control[type="file"]::file-selector-button {
            background-color: #FFDE59;
            color: #000000;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }

        .form-control[type="file"]::file-selector-button:hover {
            background-color: #e6c84f;
        }

        /* Increase spacing between form elements */
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4 fade-in logo">
            <img src="../css/images/jof_logo_yellow.png" alt="Jack of Fades Logo" width="90" height="90">
        </div>
        
        <div class="payment-container fade-in">
            <h2 class="text-center mb-4" style="color: #FFDE59;">GCash Payment</h2>
            
            <div class="qr-code-container text-center">
                <img src="../customer/css/images/gcash_qr.JPG" alt="GCash QR Code" class="img-fluid">
                <div class="mt-3">
                    <small class="text-warning">Note: Downpayment is non-refundable</small>
                </div>
            </div>

            <div class="price-details">
                <div class="price-item">
                    <span>Service Price:</span>
                    <span>₱<?php echo number_format($servicePrice, 2); ?></span>
                </div>
                <div class="price-item">
                    <span>Addon Price:</span>
                    <span>₱<?php echo number_format($addonPrice, 2); ?></span>
                </div>
                <div class="total-amount text-center">
                    <span>Total Amount to Pay: ₱<?php echo number_format($paymentAmount, 2); ?></span>
                </div>
            </div>

            <div class="form-container fade-in">
                <form action="verify_payment.php" method="POST" class="text-center" enctype="multipart/form-data">
                    <input type="hidden" name="appointmentID" value="<?php echo $appointmentID; ?>">
                    <input type="hidden" name="amount" value="<?php echo $paymentAmount; ?>">
                    
                    <div class="mb-4">
                        <span style="color: red;">* </span>
                        <label class="form-label">Attach Payment Screenshot:</label>
                        <div class="upload-container">
                            <input type="file" name="paymentProof" class="form-control" 
                                   accept="image/*"
                                   placeholder="Upload screenshot of your payment">
                            <small class="text-muted d-block mt-2">Accepted formats: JPG, PNG, JPEG</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <span style="color: red;">* </span>
                        <label class="form-label">Enter GCash Reference Number:</label>
                        <input type="text" name="gcashRef" required class="form-control" 
                               placeholder="Enter GCash Transaction Reference Number">
                    </div>
                    
                    <button type="submit" class="btn btn-confirm">Confirm Payment</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>