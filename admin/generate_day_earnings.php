<?php
require_once('TCPDF-main/tcpdf.php');
require 'db_connect.php'; // Ensure the correct database connection file

// Ensure time zone is set to avoid issues
mysqli_query($conn, "SET time_zone = '+08:00'");
date_default_timezone_set('Asia/Manila'); 

// Create a new PDF instance
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Jack of Fades');
$pdf->SetAuthor('Jack of Fades');
$pdf->SetTitle('Earnings Report');
$pdf->SetSubject('Earnings Summary');
$pdf->SetKeywords('Earnings, Report, Barber, Income');

// Remove default header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Add logo (Adjust the path to your actual logo image)
$logo = '.png'; // Change to the correct path
$pdf->Image($logo, 90, 10, 30, 30, 'PNG');

// Add title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 40, 'Jack of Fades Barbershop', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 5, 'Earnings Report', 0, 1, 'C');

// Get selected date from GET request or default to today
$selectedDate = $_GET['date'] ?? date('Y-m-d');

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Date of Earnings Report: ' . date('F j, Y', strtotime($selectedDate)), 0, 1, 'C');
$pdf->Ln(10);

// Define column widths
$col1 = 80;  // Barber Name & Earnings
$col2 = 40;  // My Earnings
$col3 = 40;  // Total
$tableWidth = $col1 + $col2 + $col3; // Calculate total table width
$pageWidth = $pdf->GetPageWidth(); // Get page width
$startX = ($pageWidth - $tableWidth) / 2; // Calculate center position

// Move to starting X position
$pdf->SetX($startX);

// Table Header
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col1, 10, 'Barber Name & Earnings', 1, 0, 'C');
$pdf->Cell($col2, 10, 'My Earnings', 1, 0, 'C');
$pdf->Cell($col3, 10, 'Total', 1, 1, 'C');

$selectedDate = $_GET['date'] ?? date('Y-m-d');

$query = "SELECT CONCAT(b.firstName, ' ', b.lastName) AS barberFullName, 
                 e.adminEarnings, e.barberEarnings 
          FROM earnings_tbl e
          JOIN barbers_tbl b ON e.barberID = b.barberID
          JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
          WHERE DATE(a.date) = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

$pdf->SetFont('helvetica', '', 11);

$totalAdminEarnings = 0;
$totalBarberEarnings = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total = $row['adminEarnings'] + $row['barberEarnings'];
        $barberNameWithEarnings = $row['barberFullName'] . " (P" . number_format($row['barberEarnings'], 2) . ")";
        
        $pdf->SetX($startX); // Move each row to center
        $pdf->Cell($col1, 10, $barberNameWithEarnings, 1, 0, 'C');
        $pdf->Cell($col2, 10, 'P' . number_format($row['adminEarnings'], 2), 1, 0, 'C');
        $pdf->Cell($col3, 10, 'P' . number_format($total, 2), 1, 1, 'C');

        // Sum earnings for overall total
        $totalAdminEarnings += $row['adminEarnings'];
        $totalBarberEarnings += $row['barberEarnings'];

    }
        // Display Overall Earnings only under "Total" column
        $overallTotal = $totalAdminEarnings + $totalBarberEarnings;

        $pdf->Ln(15); // Add spacing before total
        $pdf->SetFont('helvetica', 'B', 14); // Make text bold and slightly larger
        $pdf->SetTextColor(0, 0, 0); // Black text
        
        $pdf->SetX($startX + $col1 + $col2);
        $pdf->Cell($col3, 10, 'Overall Total Earnings: P' . number_format($overallTotal, 2), 0, 1, 'R'); // Right-aligned

} else {
    $pdf->SetX($startX);
    $pdf->Cell($tableWidth, 10, 'No earnings data available', 1, 1, 'C');
}

// Leave space for signature
$pdf->Ln(20);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, '_____________________________________', 0, 1, 'C');
$pdf->Cell(0, 10, 'Signature over Printed Name', 0, 1, 'C');

// Date and Time Generated
$pdf->SetFont('helvetica', '', 10);
$pdf->Ln(20);
$pdf->Cell(0, 10, 'Date and Time generated: ' . date('F j, Y - h:i A'), 0, 1, 'R');

// Output PDF
$pdf->Output('Earnings_Report_' . $selectedDate . '.pdf', 'D');

exit;
?>