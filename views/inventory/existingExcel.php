<?php
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
require '../../vendor/autoload.php'; // Include the Composer auto loader
require '../../utilities/jdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

global $stock;
// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
// SQL query to retrieve data from the database

$purchasedGoods = getPurchaseReport();
$existingGoods = [];
$deficits = [];

function getPurchaseReport()
{
    global $stock;
    $sql = "SELECT nisha.partnumber, nisha.partName, nisha.id, qtybank.id as qid, stock.name AS stckname, nisha.price AS nprice,
                seller.name, brand.name AS brand_name, qtybank.qty, qtybank.pos1, qtybank.pos2,
                qtybank.des, qtybank.id AS qtyid, qtybank.qty AS entqty, qtybank.is_transfered
            FROM $stock.qtybank
            LEFT JOIN nisha ON qtybank.codeid = nisha.id
            LEFT JOIN seller ON qtybank.seller = seller.id
            LEFT JOIN brand ON qtybank.brand = brand.id
            LEFT JOIN stock ON qtybank.stock_id = stock.id
            ORDER BY nisha.partnumber DESC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function checkSoldGoods($qtyId)
{
    global $stock;
    $sql = "SELECT SUM(qty) FROM $stock.exitrecord WHERE qtyid = :qtyId";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute([':qtyId' => $qtyId]);
    return $stmt->fetchColumn();
}

foreach ($purchasedGoods as $purchase) {
    $uniqueKey = $purchase['id'] . '-' . $purchase['brand_name']; // Unique identifier for nisha.id and brand.id combination
    $soldQty = checkSoldGoods($purchase['qtyid']);
    $remainingQty = $purchase['entqty'] - $soldQty;

    // Adjust remainingQty based on existing deficits
    if (isset($deficits[$uniqueKey])) {
        $remainingQty += $deficits[$uniqueKey];
        if ($remainingQty < 0) {
            $deficits[$uniqueKey] = $remainingQty;
            continue;
        } else {
            unset($deficits[$uniqueKey]);
        }
    }

    // If remainingQty is less than zero, track it as a deficit for future purchases of the same item
    if ($remainingQty < 0) {
        $deficits[$uniqueKey] = $remainingQty;
    } else {
        if ($remainingQty > 0) {
            $purchase['remaining_qty'] = $remainingQty; // Add the remaining quantity to the record
            $existingGoods[] = $purchase;
        }
    }
}

// Adjust remaining_qty in existingGoods based on any remaining deficits
foreach ($existingGoods as &$item) {
    $uniqueKey = $item['id'] . '-' . $item['brand_name']; // Unique identifier for nisha.id and brand.id combination
    if (isset($deficits[$uniqueKey])) {
        $item['remaining_qty'] += $deficits[$uniqueKey];
    }
}

$sanitizedData = [];

foreach ($existingGoods as $row) {
    $sanitizedData[] = [
        'partnumber' => strtoupper($row["partnumber"]),
        'partName' => $row["partName"],
        'brand' => $row["brand_name"],
        'quantity' => $row["remaining_qty"],
        'seller' => $row["name"],
        'pos1' => $row["pos1"],
        'pos2' => $row["pos2"],
        'description' => $row["des"],
        'stock' => $row["stckname"]
    ];
}

// Set the active sheet to the first sheet
$sheet = $spreadsheet->getActiveSheet();

// Set custom column headers
$customHeaders = [
    'شماره فنی','قطعه' ,'برند', 'تعداد', 'فروشنده', 'راهرو', 'قفسه', 'توضیحات', 'انبار'
];

$col = 1;
foreach ($customHeaders as $header) {
    $sheet->setCellValueByColumnAndRow($col, 1, $header);
    $col++;
}

// Set data from the database query result
$row = 2;
foreach ($sanitizedData as $row_data) {
    $col = 1;
    foreach ($row_data as $key => $value) {
        // Set the cell format to Text for the "partnumber" column
        if ($key === 'partnumber') {
            $sheet->getCellByColumnAndRow($col, $row)->setValueExplicit($value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        } else {
            $sheet->setCellValueByColumnAndRow($col, $row, $value);
        }
        $col++;
    }
    $row++;
}

// Freeze the header row
$sheet->freezePane('A2');

// Set the header for the Excel file with today's date and time
$timestamp = date('Y-m-d');
$filename = "existing_goods_report_{$timestamp}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Clean the output buffer
ob_clean();

// Create Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
