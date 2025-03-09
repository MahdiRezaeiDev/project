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

$givenDateObj = new DateTime($givenDate);
$todayObj = new DateTime($today);
$interval = $givenDateObj->diff($todayObj);
$dayDifference = $interval->days + 1;
$daysAmount = isset($dayDifference) && is_numeric($dayDifference) ? (int)$dayDifference : 7;

if ($daysAmount == 0) {
    $daysAmount = 1;
}

$startDate = strtotime("-" . ($daysAmount - 1) . " days", strtotime($today));

$headers = ['نام نام خانوادگی'];
$subHeaders = [];

for ($index = 0; $index < $daysAmount; $index++) {
    $date = strtotime("+$index days", $startDate);
    $persianDate = jdate('l', $date) . ' ' . jdate('Y/m/d', $date);
    $startColumn = Coordinate::stringFromColumnIndex($index * 4 + 2);
    $endColumn = Coordinate::stringFromColumnIndex($index * 4 + 5);

    $sheet->mergeCells("$startColumn" . "1:$endColumn" . "1");
    $sheet->setCellValue("$startColumn" . "1", $persianDate);
    $sheet->getStyle("$startColumn" . "1")->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(1)->setRowHeight(30);

    array_push($subHeaders, 'ورود', 'تاخیر', 'خروج', 'اضافه کار');
}

$sheet->getColumnDimension('A')->setWidth(25);
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');

$sheet->fromArray([$headers], NULL, 'A1');
$sheet->fromArray([$subHeaders], NULL, 'B2');
$row = 3;

// Set default column widths for better readability
foreach (range('B', $sheet->getHighestColumn()) as $col) {
    $sheet->getColumnDimension($col)->setWidth(15); // Adjust width as needed
}


foreach ($users as $user) {
    $userRow = $row;

    $sheet->setCellValue("A{$userRow}", $user['name'] . ' ' . $user['family']);

    for ($counter = 0; $counter < $daysAmount; $counter++) {
        $date = strtotime("+$counter days", $startDate);
        $reportDate = date("Y-m-d", $date);

        $startColumnIndex = 2 + ($counter * 4);
        $entryColumn = Coordinate::stringFromColumnIndex($startColumnIndex);
        $delayColumn = Coordinate::stringFromColumnIndex($startColumnIndex + 1);
        $exitColumn = Coordinate::stringFromColumnIndex($startColumnIndex + 2);
        $extraColumn = Coordinate::stringFromColumnIndex($startColumnIndex + 3);

        $startRecords = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
        $leaveRecords = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);

        $Rule = getUserAttendanceRule($user['selectedUser']);
        $startTime = $Rule['start_hour'];
        $endTime = $Rule['end_hour'];

        $entryTimes = [];
        $exitTimes = [];
        $delayMinutes = 0;
        $extraMinutes = 0;

        foreach ($startRecords as $start) {
            $entryTimes[] = date('H:i', strtotime($start['timestamp']));
            if (strtotime($start['timestamp']) > strtotime($startTime)) {
                $delayMinutes += round((strtotime($start['timestamp']) - strtotime($startTime)) / 60);
            }
        }

        foreach ($leaveRecords as $leave) {
            $exitTimes[] = date('H:i', strtotime($leave['timestamp']));
            if (strtotime($leave['timestamp']) > strtotime($endTime)) {
                $extraMinutes += round((strtotime($leave['timestamp']) - strtotime($endTime)) / 60);
            }
        }

        $entryTime = !empty($entryTimes) ? implode("\n", $entryTimes) : '-';
        $exitTime = !empty($exitTimes) ? implode("\n", $exitTimes) : '-';
        $delayTime = $delayMinutes > 0 ? $delayMinutes . ' دقیقه' : '-';
        $extraTime = $extraMinutes > 0 ? $extraMinutes . ' دقیقه' : '-';

        if (strtotime($reportDate) > strtotime($today)) {
            $entryTime = 'ثبت نشده';
        } elseif (empty($startRecords)) {
            $entryTime = 'غایب';
        }

        $sheet->setCellValue("{$entryColumn}{$userRow}", $entryTime);
        $sheet->setCellValue("{$delayColumn}{$userRow}", $delayTime);
        $sheet->setCellValue("{$exitColumn}{$userRow}", $exitTime);
        $sheet->setCellValue("{$extraColumn}{$userRow}", $extraTime);

        // Enable text wrapping for multi-line display
        $sheet->getStyle("{$entryColumn}{$userRow}:{$extraColumn}{$userRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A{$userRow}:{$extraColumn}{$userRow}")->getAlignment()->setHorizontal('center')->setVertical('center');
    }

    $sheet->getRowDimension($userRow)->setRowHeight(-1);
    $row++;
}

$sheet->getStyle('B2:E2')->getAlignment()->setHorizontal('center')->setVertical('center');

ob_clean();
$filename = "attendance_report_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=$filename");
header('Cache-Control: max-age=0');

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
