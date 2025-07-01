<?php
session_start();
// Include the database connection
include 'config.php';
// Fetch the date range and filter option from GET parameters
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$reportRange = $_GET['report_range'] ?? 'weekly';
$filterOption = $_GET['filter_option'] ?? null;

// Initialize variables for records and total amount
$records = [];
$totalAmount = 0;

// Prepare the SQL query based on the selected date range and filter option
if ($startDate && $endDate) {
    // Ensure that start_date is earlier than end_date
    if ($startDate > $endDate) {
        echo "Start date cannot be later than end date.";
        exit;
    }

    // Basic SQL query to fetch data within the date range
    $sql = "SELECT date_issuance, patient_name, representative_name, options, amount_approved, expiry_date
            FROM assistance
            WHERE date_issuance BETWEEN '$startDate' AND '$endDate'";

    // Add filter condition if a filter option is selected
    if ($filterOption) {
        $sql .= " AND options = '$filterOption'";
    }

    // Execute the query
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Store results in an array and calculate the total amount
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
            $totalAmount += $row['amount_approved'];
        }
    } else {
        $records = [];
        $totalAmount = 0;
    }
}

// Format the total amount to 2 decimal places
$totalAmountFormatted = number_format($totalAmount, 2);

// Include PhpSpreadsheet for exporting to Excel
require '../vendor/autoload.php'; // Ensure you have PhpSpreadsheet installed using Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers for the Excel file
$sheet->setCellValue('A1', 'Date of Issuance');
$sheet->setCellValue('B1', 'Patient\'s Name');
$sheet->setCellValue('C1', 'Name of Representative');
$sheet->setCellValue('D1', 'Kind of Assistance');
$sheet->setCellValue('E1', 'Amount Approved');
$sheet->setCellValue('F1', 'Expiry Date');
$sheet->setCellValue('G2', 'Total Amount');

// Write the data to the Excel file
$row = 2;
foreach ($records as $record) {
    $sheet->setCellValue("A$row", $record['date_issuance']);
    $sheet->setCellValue("B$row", $record['patient_name']);
    $sheet->setCellValue("C$row", $record['representative_name']);
    $sheet->setCellValue("D$row", $record['options']);
    $sheet->setCellValue("E$row", '₱' . $record['amount_approved']);
    $sheet->setCellValue("F$row", $record['expiry_date']);
    $sheet->setCellValue("G$row", '₱' . $totalAmountFormatted);
    $row++;
}

// Write total amount at the bottom

// Create the writer and output the Excel file
$writer = new Xlsx($spreadsheet);
$fileName = 'Assistance_Report.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;

// Close the database connection
$conn->close();
?>
