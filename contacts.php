<?php
require_once './config/constants.php';
require_once './database/db_connect.php';
require './vendor/autoload.php'; // Include Composer autoloader
require './utilities/jdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();

// SQL query to retrieve distinct customers
$sql = "SELECT DISTINCT customer.phone, customer.name, customer.family
        FROM factor.bill
        INNER JOIN callcenter.customer ON bill.customer_id = customer.id
        WHERE bill.partner = 0";
$stmt = PDO_CONNECTION->prepare($sql);
$stmt->execute();

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set the active sheet to the first sheet
$sheet = $spreadsheet->getActiveSheet();

// Set column headers
$headers = ['نام', 'نام خانوادگی', 'شماره تماس'];
$col = 1;
foreach ($headers as $header) {
    $sheet->setCellValueByColumnAndRow($col, 1, $header);
    $col++;
}

// Fill in the data
$row = 2;
foreach ($customers as $customer) {
    $sheet->setCellValueExplicitByColumnAndRow(1, $row, $customer['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicitByColumnAndRow(2, $row, $customer['family'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicitByColumnAndRow(3, $row, $customer['phone'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $row++;
}

// Freeze header row
$sheet->freezePane('A2');

// Auto-size columns
foreach (range('A', 'C') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Prepare download
$timestamp = date('Y-m-d');
$filename = "customer_report_{$timestamp}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Output Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
