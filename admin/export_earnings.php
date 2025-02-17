<?php
require 'db_connect.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
    exit;
}

$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Fetch earnings data
$query = "SELECT e.adminEarnings, e.barberEarnings, CONCAT(b.firstName, ' ', b.lastName) AS barberFullName, a.date, a.timeSlot
          FROM earnings_tbl e 
          JOIN barbers_tbl b ON e.barberID = b.barberID
          JOIN appointment_tbl a ON e.appointmentID = a.appointmentID
          WHERE DATE(a.date) = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Earnings Report");

// Set Headers
$sheet->setCellValue('A1', 'Barber Name')
      ->setCellValue('B1', 'Barber Earnings')
      ->setCellValue('C1', 'My Earnings')
      ->setCellValue('D1', 'Total')
      ->setCellValue('E1', 'Time');

// Fill Data
$row = 2;
while ($data = $result->fetch_assoc()) {
    $total = $data['adminEarnings'] + $data['barberEarnings'];
    
    $sheet->setCellValue('A' . $row, $data['barberFullName'])
          ->setCellValue('B' . $row, $data['barberEarnings'])
          ->setCellValue('C' . $row, $data['adminEarnings'])
          ->setCellValue('D' . $row, $total)
          ->setCellValue('E' . $row, $data['timeSlot']);
    $row++;
}

// Set Auto Column Width
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Generate Excel file
$filename = "Earnings_Report_{$selectedDate}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
