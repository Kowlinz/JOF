<?php
require_once('TCPDF-main/tcpdf.php');

// Create a new PDF instance
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Jack of Fades');
$pdf->SetAuthor('Jack of Fades');
$pdf->SetTitle('Monthly Earnings Report');
$pdf->SetSubject('Monthly Earnings Summary');
$pdf->SetKeywords('Earnings, Report, Barber, Income, Monthly');

// Remove default header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Add logo
$logo = 'logo.png'; // Ensure the correct path
$pdf->Image($logo, 90, 10, 30, 30, 'PNG');

// Add title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 40, 'Jack of Fades Barbershop', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 5, 'Monthly Earnings Report', 0, 1, 'C');

$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);

// Get selected month
$selectedMonth = $_GET['month'] ?? date('Y-m'); // Default to current month
$pdf->Cell(0, 10, '' . date('F Y', strtotime($selectedMonth . '-01')), 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Earnings Summary:', 0, 1, 'C');

// Define column widths
$col1 = 80;  // Barber Name & Earnings
$col2 = 40;  // Barber Earnings
$col3 = 40;  // Total Earnings
$tableWidth = $col1 + $col2 + $col3;
$pageWidth = $pdf->GetPageWidth();
$startX = ($pageWidth - $tableWidth) / 2;

// Move to starting X position
$pdf->SetX($startX);

// Table Header
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col1, 10, 'Barber Name & Earnings', 1, 0, 'C');
$pdf->Cell($col2, 10, 'My Earnings', 1, 0, 'C');
$pdf->Cell($col3, 10, 'Total', 1, 1, 'C');

// Fetch earnings data from the database
require 'db_connect.php'; // Ensure the correct database connection file
mysqli_query($conn, "SET time_zone = '+08:00'");
date_default_timezone_set('Asia/Manila'); 

$query = "SELECT 
            CONCAT(b.firstName COLLATE utf8mb4_unicode_ci, ' ', 
                   b.lastName COLLATE utf8mb4_unicode_ci) AS barberFullName, 
            SUM(e.adminEarnings) AS totalAdminEarnings, 
            SUM(e.barberEarnings) AS totalBarberEarnings 
          FROM earnings_tbl e
          JOIN barbers_tbl b ON e.barberID = b.barberID
          JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
          WHERE DATE_FORMAT(a.date, '%Y-%m') = ?
          GROUP BY b.barberID";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedMonth);
$stmt->execute();
$result = $stmt->get_result();

$pdf->SetFont('helvetica', '', 11);

$totalAdminEarnings = 0;
$totalBarberEarnings = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total = $row['totalAdminEarnings'] + $row['totalBarberEarnings'];
        $barberNameWithEarnings = $row['barberFullName'] . " (P" . number_format($row['totalBarberEarnings'], 2) . ")";
        
        $pdf->SetX($startX); // Move row to center
        $pdf->Cell($col1, 10, $barberNameWithEarnings, 1, 0, 'C');
        $pdf->Cell($col2, 10, 'P' . number_format($row['totalAdminEarnings'], 2), 1, 0, 'C');
        $pdf->Cell($col3, 10, 'P' . number_format($total, 2), 1, 1, 'C');

        // Sum earnings for overall total
        $totalAdminEarnings += $row['totalAdminEarnings'];
        $totalBarberEarnings += $row['totalBarberEarnings'];        
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
    $pdf->Cell($tableWidth, 10, 'No earnings data available for this month', 1, 1, 'C');
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
$pdf->Output('Monthly_Earnings_Report_' . $selectedMonth . '.pdf', 'D');

exit;
?>