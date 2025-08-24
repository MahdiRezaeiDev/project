<?php
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
require '../../vendor/autoload.php';
require '../../utilities/jdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$users = [];

$date = null;
$userId = 0;

if (!empty($_POST['date'])) {
    $date = $_POST['date'];
    $user = $_POST['user'] ?? null;
}

$users = getUsers($userId);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$today = date('Y-m-d');

// Get Days from GET Request (Default to 7 days if not set)
$daysAmount = isset($_GET['days']) && is_numeric($_GET['days']) ? (int)$_GET['days'] : 7;

if ($daysAmount <= 0) {
    die("Invalid days amount");
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
    $sheet->getStyle("$startColumn" . "1")->getAlignment()->setHorizontal('center');

    array_push($subHeaders, 'ورود', 'تاخیر', 'خروج', 'اضافه کار');
}

$sheet->fromArray([$headers], NULL, 'A1');
$sheet->fromArray([$subHeaders], NULL, 'B2');
$row = 3;

foreach ($users as $user) {
    $usersData = [$user['name'] . ' ' . $user['family']];
    for ($counter = 0; $counter < $daysAmount; $counter++) {
        $date = strtotime("+$counter days", $startDate);
        $reportDate = date("Y-m-d", $date);
        $start = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
        $leave = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);
        $Rule = getUserAttendanceRule($user['selectedUser']);
        $startTime = $Rule['start_hour'];
        $endTime = $Rule['end_hour'];

        $entry = count($start) > 0 ? date('H:i', strtotime($start[0]['timestamp'])) : (strtotime($reportDate) > strtotime($today) ? 'ثبت نشده' : 'غایب');
        $exit = count($leave) > 0 ? date('H:i', strtotime($leave[0]['timestamp'])) : '';
        $delay = count($start) > 0 && strtotime($start[0]['timestamp']) > strtotime($startTime) ? round((strtotime($start[0]['timestamp']) - strtotime($startTime)) / 60) . ' دقیقه' : '-';
        $extra = count($leave) > 0 && strtotime($leave[0]['timestamp']) > strtotime($endTime) ? round((strtotime($leave[0]['timestamp']) - strtotime($endTime)) / 60) . ' دقیقه' : '-';

        array_push($usersData, $entry, $delay, $exit, $extra);
    }
    $sheet->fromArray([$usersData], NULL, "A{$row}");
    $row++;
}

$sheet->freezePane('A3');

// ---------------- FIXED EXPORT BLOCK ----------------
$filename = "attendance_report_" . date('Y-m-d') . ".xlsx";

// Clean (remove) any output buffering before sending headers
if (ob_get_length()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
// ----------------------------------------------------

/**
 * Get Users
 */
function getUsers($id = null)
{
    $sql = "SELECT users.id, name, family, settings.user_id AS selectedUser 
            FROM yadakshop.users AS users 
            INNER JOIN yadakshop.attendance_settings AS settings ON settings.user_id = users.id
            WHERE users.password IS NOT NULL 
              AND users.password != '' 
              AND username != 'tv'";

    if (!empty($id)) {
        $sql .= " AND selectedUser = :id";
    }

    $stmt = PDO_CONNECTION->prepare($sql);

    if (!empty($id)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get Attendance Logs
 */
function getUserAttendanceReport($action, $user_id, $date)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs 
            WHERE user_id = :user_id 
              AND DATE(created_at) = :date 
              AND action = :action";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get User Rule
 */
function getUserAttendanceRule($user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_settings WHERE user_id = :user_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
<?php
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
require '../../vendor/autoload.php';
require '../../utilities/jdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$users = [];

$date = null;
$userId = 0;

if (!empty($_POST['date'])) {
    $date = $_POST['date'];
    $user = $_POST['user'] ?? null;
}

$users = getUsers($userId);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$today = date('Y-m-d');

// Get Days from GET Request (Default to 7 days if not set)
$daysAmount = isset($_GET['days']) && is_numeric($_GET['days']) ? (int)$_GET['days'] : 7;

if ($daysAmount <= 0) {
    die("Invalid days amount");
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
    $sheet->getStyle("$startColumn" . "1")->getAlignment()->setHorizontal('center');

    array_push($subHeaders, 'ورود', 'تاخیر', 'خروج', 'اضافه کار');
}

$sheet->fromArray([$headers], NULL, 'A1');
$sheet->fromArray([$subHeaders], NULL, 'B2');
$row = 3;

foreach ($users as $user) {
    $usersData = [$user['name'] . ' ' . $user['family']];
    for ($counter = 0; $counter < $daysAmount; $counter++) {
        $date = strtotime("+$counter days", $startDate);
        $reportDate = date("Y-m-d", $date);
        $start = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
        $leave = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);
        $Rule = getUserAttendanceRule($user['selectedUser']);
        $startTime = $Rule['start_hour'];
        $endTime = $Rule['end_hour'];

        $entry = count($start) > 0 ? date('H:i', strtotime($start[0]['timestamp'])) : (strtotime($reportDate) > strtotime($today) ? 'ثبت نشده' : 'غایب');
        $exit = count($leave) > 0 ? date('H:i', strtotime($leave[0]['timestamp'])) : '';
        $delay = count($start) > 0 && strtotime($start[0]['timestamp']) > strtotime($startTime) ? round((strtotime($start[0]['timestamp']) - strtotime($startTime)) / 60) . ' دقیقه' : '-';
        $extra = count($leave) > 0 && strtotime($leave[0]['timestamp']) > strtotime($endTime) ? round((strtotime($leave[0]['timestamp']) - strtotime($endTime)) / 60) . ' دقیقه' : '-';

        array_push($usersData, $entry, $delay, $exit, $extra);
    }
    $sheet->fromArray([$usersData], NULL, "A{$row}");
    $row++;
}

$sheet->freezePane('A3');

// ---------------- FIXED EXPORT BLOCK ----------------
$filename = "attendance_report_" . date('Y-m-d') . ".xlsx";

// Clean (remove) any output buffering before sending headers
if (ob_get_length()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
// ----------------------------------------------------

/**
 * Get Users
 */
function getUsers($id = null)
{
    $sql = "SELECT users.id, name, family, settings.user_id AS selectedUser 
            FROM yadakshop.users AS users 
            INNER JOIN yadakshop.attendance_settings AS settings ON settings.user_id = users.id
            WHERE users.password IS NOT NULL 
              AND users.password != '' 
              AND username != 'tv'";

    if (!empty($id)) {
        $sql .= " AND selectedUser = :id";
    }

    $stmt = PDO_CONNECTION->prepare($sql);
    
    if (!empty($id)) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get Attendance Logs
 */
function getUserAttendanceReport($action, $user_id, $date)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs 
            WHERE user_id = :user_id 
              AND DATE(created_at) = :date 
              AND action = :action";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get User Rule
 */
function getUserAttendanceRule($user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_settings WHERE user_id = :user_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
