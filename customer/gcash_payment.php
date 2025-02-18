<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    header("Location: ../login.php"); // Redirect to login if not logged in or not a customer
    exit();
}

// Get the logged-in customer's ID from the session
$customerID = $_SESSION["customerID"];

// Ensure $bookingData is set before accessing keys
$bookingData = $_POST ?? []; // Get data from form submission

$appointmentID = isset($bookingData['appointmentID']) ? intval($bookingData['appointmentID']) : 0;
$date = isset($bookingData['date']) ? htmlspecialchars($bookingData['date']) : 'Not Set';
$timeslot = isset($bookingData['timeslot']) ? htmlspecialchars($bookingData['timeslot']) : 'Not Set';
$serviceID = isset($bookingData['service']) ? intval($bookingData['service']) : 0;
$addonID = isset($bookingData['addon']) ? intval($bookingData['addon']) : 0;
$remarks = isset($bookingData['remarks']) ? htmlspecialchars($bookingData['remarks']) : 'No remarks';
$paymentOption = isset($bookingData['paymentOption']) ? htmlspecialchars($bookingData['paymentOption']) : 'full';

// Fetch service and addon details
$sql = "SELECT 
            s.serviceName, CAST(s.servicePrice AS DECIMAL(10,2)) AS servicePrice, 
            COALESCE(ad.addonName, 'None') AS addonName, 
            COALESCE(CAST(ad.addonPrice AS DECIMAL(10,2)), 0) AS addonPrice 
        FROM service_tbl s
        LEFT JOIN addon_tbl ad ON ad.addonID = ?
        WHERE s.serviceID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $addonID, $serviceID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$serviceName = $row['serviceName'] ?? 'Unknown Service';
$servicePrice = $row['servicePrice'] ?? 0.00;
$addonName = $row['addonName'] ?? 'None';
$addonPrice = $row['addonPrice'] ?? 0.00;

// Calculate total price
$fullPrice = $servicePrice + $addonPrice;

// Apply downpayment logic (50%) or full price
if ($paymentOption === 'downpayment') {
    $paymentAmount = $fullPrice * 0.5; // 50% of total
    $finalPaymentStatus = 'partial';   // Set status to partial
} else {
    $paymentAmount = $fullPrice;       // Full payment
    $finalPaymentStatus = 'paid';      // Set status to paid
}

// Close the statement
$stmt->close();
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
            max-width: 70%;
            height: auto;
            margin: 0 auto;
            display: block;
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

        /* Add these responsive styles for mobile */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .payment-container {
                padding: 1rem;
                margin-top: 1rem;
            }

            .qr-code-container {
                padding: 1rem;
                margin: 1rem 0;
            }

            .qr-code-container img {
                max-width: 100%;
                width: auto;
                height: auto;
                object-fit: contain;
            }

            /* Adjust other elements for mobile */
            .price-details {
                padding: 1rem;
                margin: 1rem 0;
            }

            .total-amount {
                padding: 0.8rem;
                margin-top: 1rem;
            }

            .form-container {
                padding: 0;
            }

            .upload-container {
                padding: 0.8rem;
            }

            /* Adjust button size for mobile */
            .btn-confirm {
                width: 100%;
                padding: 0.8rem;
            }
        }

        /* Add this to ensure the QR code image stays within its container */
        .qr-code-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            overflow: hidden;
        }

        .qr-code-container img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }

        .modal-dialog {
            color: black !important;
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
                <img src="../customer/css/images/gcash_qr.jpg" alt="GCash QR Code" class="img-fluid">
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
                <input type="hidden" name="serviceID" value="<?php echo $serviceID; ?>">
                <input type="hidden" name="addonID" value="<?php echo $addonID; ?>">
                <input type="hidden" name="date" value="<?php echo $bookingData['date']; ?>">
                <input type="hidden" name="timeSlot" value="<?php echo $bookingData['timeSlot']; ?>">
                <input type="hidden" name="remarks" value="<?php echo htmlspecialchars($bookingData['remarks']); ?>">
                <input type="hidden" name="paymentOption" value="<?php echo htmlspecialchars($bookingData['paymentOption']); ?>">

                <div class="mb-4">
                    <span style="color: red;">* </span>
                    <label class="form-label">Attach Payment Screenshot:</label>
                    <div class="upload-container">
                        <input type="file" name="paymentProof" class="form-control" accept="image/*">
                        <small class="text-muted d-block mt-2">Accepted formats: JPG, PNG, JPEG</small>
                    </div>
                </div>

                <div class="mb-4">
                    <span style="color: red;">* </span>
                    <label class="form-label">Enter GCash Reference Number:</label>
                    <input type="text" id="gcashRef" name="gcashRef" required class="form-control"
                        placeholder="XXXX XXX XXXXXX" maxlength="17" 
                        oninput="formatReferenceNumber(this)" pattern="\d{4} \d{3} \d{6}" 
                        title="Reference number must be in the format XXXX XXX XXXXXX (13 digits)">
                </div>

                <!-- Terms and Conditions Checkbox -->
                <div class="mb-4 text-center">
                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                    <label for="agreeTerms">
                        I have read and agree to the 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>.
                    </label>
                </div>

                <button type="submit" class="btn btn-confirm">Confirm Payment</button>
            </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Welcome to Jack of Fades! Please read these terms and conditions carefully before making a payment.</p>

                    <h4>1. Appointment Policy</h4>
                    <p>All appointments must be booked in advance. Walk-ins are accepted based on availability.</p>

                    <h4>2. Payment Policy</h4>
                    <p>A downpayment is required to secure your appointment. The remaining balance will be paid upon service completion.</p>
                    <p><strong>Note:</strong> The downpayment is <span class="text-danger">non-refundable</span>.</p>

                    <h4>3. Rescheduling & Cancellations</h4>
                    <p>Clients may reschedule their appointment at least 24 hours in advance. Cancellations made less than 24 hours before the appointment are subject to forfeiture of the downpayment.</p>

                    <h4>4. Refund Policy</h4>
                    <p>Payments are non-refundable except in cases where Jack of Fades is unable to provide the service due to unforeseen circumstances.</p>

                    <h4>5. Proof of Payment</h4>
                    <p>Clients must upload a valid screenshot of the payment and provide the correct GCash reference number. Failure to do so may result in payment verification delays.</p>

                    <h4>6. Acceptance of Terms</h4>
                    <p>By proceeding with the payment, you confirm that you have read, understood, and agreed to these terms and conditions.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script>
        document.getElementById("agreeTerms").addEventListener("change", function () {
            document.getElementById("confirmPaymentBtn").disabled = !this.checked;
        });
    </script>

    <script>
    function formatReferenceNumber(input) {
        let value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
        let formattedValue = '';

        if (value.length > 13) {
            value = value.substring(0, 13); // Ensure max of 13 digits
        }

        // Format as XXXX XXX XXXXXX
        if (value.length > 4) {
            formattedValue = value.substring(0, 4) + ' ';
            if (value.length > 7) {
                formattedValue += value.substring(4, 7) + ' ' + value.substring(7);
            } else {
                formattedValue += value.substring(4);
            }
        } else {
            formattedValue = value;
        }

        input.value = formattedValue;
    }
    </script>
</body>
</html>