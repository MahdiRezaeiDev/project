<?php
$pageTitle = "مدیریت حضور و غیاب";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

$date = null;
$userId = 0;

if ($_POST['start']) {
    $givenDate  = $_POST['start'];
    $today  = $_POST['end'];
    $userId = $_POST['user'];
} else {
    die("تاریخ شروع و پایان ارسال نشده‌اند.");
}

$users = getUsers($userId);

$givenDateObj = new DateTime($givenDate);
$todayObj = new DateTime($today);
$interval = $givenDateObj->diff($todayObj);
$dayDifference = $interval->days + 1;
$daysAmount = $dayDifference ?: 7;

$startDate = strtotime("-" . ($daysAmount - 1) . " days", strtotime($today));

// Collect headers
$dates = [];
for ($index = 0; $index < $daysAmount; $index++) {
    $date = strtotime("+$index days", $startDate);
    $dates[] = [
        'timestamp' => $date,
        'label' => jdate('l', $date) . ' ' . jdate('Y/m/d', $date)
    ];
}

// Start HTML output
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>گزارش حضور و غیاب</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        th,
        td {
            text-align: center;
            vertical-align: middle;
            white-space: pre-wrap;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <div class="container mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-center">گزارش حضور و غیاب</h1>

        <div class="overflow-auto">
            <table class="table-auto border border-gray-300 w-full text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th rowspan="2" class="border px-4 py-2">نام نام‌خانوادگی</th>
                        <?php foreach ($dates as $d): ?>
                            <th colspan="4" class="border px-4 py-2"><?= $d['label'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr class="bg-gray-100">
                        <?php foreach ($dates as $d): ?>
                            <th class="border px-2 py-1">ورود</th>
                            <th class="border px-2 py-1">تاخیر</th>
                            <th class="border px-2 py-1">خروج</th>
                            <th class="border px-2 py-1">اضافه‌کار</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="border px-4 py-2 font-medium"><?= $user['name'] . ' ' . $user['family'] ?></td>
                            <?php foreach ($dates as $d): ?>
                                <?php
                                $reportDate = date("Y-m-d", $d['timestamp']);
                                $startRecords = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
                                $leaveRecords = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);
                                $Rule = getUserAttendanceRule($user['selectedUser']);

                                $startTime = $Rule['start_hour'];
                                $endTime = jdate('l', $d['timestamp']) === 'پنجشنبه' ? $Rule['end_week'] : $Rule['end_hour'];

                                $entryTimes = [];
                                $exitTimes = [];
                                $delayMinutes = 0;
                                $extraMinutes = 0;

                                foreach ($startRecords as $start) {
                                    $entry = date('H:i', strtotime($start['timestamp']));
                                    $entryTimes[] = $entry;
                                    $time = strtotime($start['timestamp']);
                                    if ($time > strtotime($startTime)) {
                                        $delayMinutes += floor(($time - strtotime($startTime)) / 60);
                                    } else {
                                        $extraMinutes += floor((strtotime($startTime) - $time) / 60);
                                    }
                                }

                                foreach ($leaveRecords as $leave) {
                                    $exit = date('H:i', strtotime($leave['timestamp']));
                                    $exitTimes[] = $exit;
                                    $time = strtotime($leave['timestamp']);
                                    if ($time > strtotime($endTime)) {
                                        $extraMinutes += floor(($time - strtotime($endTime)) / 60);
                                    } else {
                                        $delayMinutes += floor((strtotime($endTime) - $time) / 60);
                                    }
                                }

                                $entryTime = $entryTimes ? implode("\n", $entryTimes) : '-';
                                $exitTime = $exitTimes ? implode("\n", $exitTimes) : '-';
                                $delayTime = $delayMinutes ? "$delayMinutes دقیقه" : '-';
                                $extraTime = $extraMinutes ? "$extraMinutes دقیقه" : '-';

                                if (strtotime($reportDate) > strtotime($today)) {
                                    $entryTime = 'ثبت نشده';
                                } elseif (empty($startRecords)) {
                                    $entryTime = 'غایب';
                                }
                                ?>
                                <td class="border px-2 py-2"><?= $entryTime ?></td>
                                <td class="border px-2 py-2"><?= $delayTime ?></td>
                                <td class="border px-2 py-2"><?= $exitTime ?></td>
                                <td class="border px-2 py-2"><?= $extraTime ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>

<?php
// Same helper functions from your code
function getUsers($id = null)
{
    $sql = "SELECT users.id, name, family, settings.user_id AS selectedUser FROM yadakshop.users AS users 
            INNER JOIN yadakshop.attendance_settings AS settings ON settings.user_id = users.id
            WHERE users.password IS NOT NULL AND users.password != '' AND username != 'tv' AND settings.is_active != 0";
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
?>