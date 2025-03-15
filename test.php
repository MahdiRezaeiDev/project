<?php
require_once './config/constants.php';
require_once './database/db_connect.php';
require './vendor/autoload.php';
require './utilities/jdf.php';

$users = [];
$date = null;
$userId = 0;
$givenDate  = date('Y/m/d', strtotime('2025/03/12'));
$today  = date('Y/m/d', strtotime('2025/03/13'));
$userId = 5;

$users = getUsers($userId);

$givenDateObj = new DateTime($givenDate);
$todayObj = new DateTime($today);
$interval = $givenDateObj->diff($todayObj);
$dayDifference = $interval->days + 1;
$daysAmount = isset($dayDifference) && is_numeric($dayDifference) ? (int)$dayDifference : 7;

if ($daysAmount == 0) {
    $daysAmount = 1;
}

$startDate = strtotime("-" . ($daysAmount - 1) . " days", strtotime($today));

foreach ($users as $user) {
    echo $user['name'];
    for ($counter = 0; $counter < $daysAmount; $counter++) {
        $date = strtotime("+$counter days", $startDate);
        $reportDate = date("Y-m-d", $date);

        $startColumnIndex = 2 + ($counter * 4);

        $startRecords = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
        $leaveRecords = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);

        $Rule = getUserAttendanceRule($user['selectedUser']);
        $startTime = $Rule['start_hour'];
        $endTime = $Rule['end_hour'];
        $endWeek = $Rule['end_week'];

        $day = jdate("l", $date);
        if ($day == 'پنجشنبه') {
            $endTime = $endWeek;
        }

        $entryTimes = [];
        $exitTimes = [];
        $delayMinutes = 0;
        $extraMinutes = 0;

        foreach ($startRecords as $start) {
            $entryTimes[] = date('H:i', strtotime($start['timestamp']));
            if (strtotime($start['timestamp']) > strtotime($startTime)) {
                $delayMinutes += floor((strtotime($start['timestamp']) - strtotime($startTime)) / 60);
            } else {
                $extraMinutes += floor(abs(strtotime($start['timestamp']) - strtotime($startTime)) / 60);
            }
        }

        foreach ($leaveRecords as $leave) {
            $exitTimes[] = date('H:i', strtotime($leave['timestamp']));
            if (strtotime($leave['timestamp']) > strtotime($endTime)) {
                $extraMinutes += floor((strtotime($leave['timestamp']) - strtotime($endTime)) / 60);
            } else {
                $delayMinutes += floor((strtotime($leave['timestamp']) - strtotime($endTime)) / 60);
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
    }
}

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
