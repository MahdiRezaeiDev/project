<?php
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require '../../../vendor/autoload.php';
require '../../../utilities/jdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$users = [];
$date = null;
$userId = 0;

if ($_POST['date']) {
    $givenDate  = $_POST['date'];
    $userId = $_POST['user'];
}

$users = getUsers($userId);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$today = date('Y-m-d');

// Convert both dates to DateTime objects for easy manipulation
$givenDateObj = new DateTime($givenDate);
$todayObj = new DateTime($today);

// Calculate the difference
$interval = $givenDateObj->diff($todayObj);

// Get the number of days
$dayDifference = $interval->days + 1;

// Get Days from GET Request (Default to 7 days if not set)
$daysAmount = isset($dayDifference) && is_numeric($dayDifference) ? (int)$dayDifference : 7;

if ($daysAmount == 0) {
    $daysAmount = 1;
}

// Calculate Start Date (Last N days up to today)
$startDate = strtotime("-" . ($daysAmount - 1) . " days", strtotime($today));

// Set Headers
$headers = ['نام نام خانوادگی'];
$subHeaders = [];

for ($index = 0; $index < $daysAmount; $index++) {
    $date = strtotime("+$index days", $startDate);
    $persianDate = jdate('l', $date) . ' ' . jdate('Y/m/d', $date);
    $startColumn = Coordinate::stringFromColumnIndex($index * 4 + 2);
    $endColumn = Coordinate::stringFromColumnIndex($index * 4 + 5);

    // Merge and Set Headers
    $sheet->mergeCells("$startColumn" . "1:$endColumn" . "1");
    $sheet->setCellValue("$startColumn" . "1", $persianDate);
    $sheet->getStyle("$startColumn" . "1")->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(1)->setRowHeight(30);  // Adjust header row height

    array_push($subHeaders, 'ورود', 'تاخیر', 'خروج', 'اضافه کار');
}

// Set the column width for "نام نام خانوادگی" to make it wider and center it
$sheet->getColumnDimension('A')->setWidth(25); // Adjust the width (for example, 25)
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center'); // Center "نام نام خانوادگی"

$sheet->fromArray([$headers], NULL, 'A1');
$sheet->fromArray([$subHeaders], NULL, 'B2');
$row = 3;

foreach ($users as $user) {
    $usersData = [$user['name'] . ' ' . $user['family']];
    
    for ($counter = 0; $counter < $daysAmount; $counter++) {
        $date = strtotime("+$counter days", $startDate);
        $reportDate = date("Y-m-d", $date);

        // Get all start and leave records for the user on this specific date
        $startRecords = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
        $leaveRecords = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);
        $Rule = getUserAttendanceRule($user['selectedUser']);
        $startTime = $Rule['start_hour'];
        $endTime = $Rule['end_hour'];

        // Initialize strings to hold multiple records
        $entryText = '';
        $delayText = '';
        $exitText = '';
        $extraText = '';

        // Display all start records (append to the text variable with new line)
        if (count($startRecords) > 0) {
            foreach ($startRecords as $start) {
                $entryText .= date('H:i', strtotime($start['timestamp'])) . "\n";
                $delayText .= (strtotime($start['timestamp']) > strtotime($startTime)) ? round((strtotime($start['timestamp']) - strtotime($startTime)) / 60) . ' دقیقه' : '-' . "\n";
            }
        } else if (strtotime($reportDate) > strtotime($today)) {
            $entryText .= 'ثبت نشده' . "\n";
            $delayText .= '-' . "\n";
        } else {
            $entryText .= 'غایب' . "\n";
            $delayText .= '-' . "\n";
        }

        // Display all leave records (append to the text variable with new line)
        if (count($leaveRecords) > 0) {
            foreach ($leaveRecords as $leave) {
                $exitText .= date('H:i', strtotime($leave['timestamp'])) . "\n";
                $extraText .= (strtotime($leave['timestamp']) > strtotime($endTime)) ? round((strtotime($leave['timestamp']) - strtotime($endTime)) / 60) . ' دقیقه' : '-' . "\n";
            }
        }

        // Add the records in the same row with new lines in the same cell
        $usersData = [
            $user['name'] . ' ' . $user['family'],
            rtrim($entryText, "\n"),
            rtrim($delayText, "\n"),
            rtrim($exitText, "\n"),
            rtrim($extraText, "\n")
        ];
        
        $sheet->fromArray([$usersData], NULL, "A{$row}");

        // Apply wrap text for multi-line cells and center the content
        $sheet->getStyle("A{$row}:E{$row}")
              ->getAlignment()->setHorizontal('center')
              ->setVertical('center');
        $sheet->getStyle("A{$row}:E{$row}")
              ->getAlignment()->setWrapText(true);  // Allow line breaks in the cell

        $sheet->getRowDimension($row)->setRowHeight(40);  // Adjust row height for readability
        $row++;
    }
}

// Center the subheader cells (ورود تاخیر خروج اضافه کار) text
$sheet->getStyle('B2:E2')->getAlignment()->setHorizontal('center')->setVertical('center');

// Clear any previous output (important to prevent headers from being sent early)
ob_clean();

// Set file download headers
$filename = "attendance_report_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=$filename");
header('Cache-Control: max-age=0');

// Output the file to the browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Function definitions (unchanged)
function getUsers($id = null)
{
    $sql = "SELECT users.id, name, family, settings.user_id AS selectedUser FROM yadakshop.users AS users 
            INNER JOIN yadakshop.attendance_settings AS settings ON settings.user_id = users.id
            WHERE users.password IS NOT NULL AND users.password != '' AND username != 'tv'";

    if (!empty($id)) {
        $sql .= " AND user_id = :id";
    }

    $stmt = PDO_CONNECTION->prepare($sql);

    if (!empty($id)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserAttendanceReport($action, $user_id, $date)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs WHERE user_id = :user_id AND DATE(created_at) = :date AND action = :action";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserAttendanceRule($user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_settings WHERE user_id = :user_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
