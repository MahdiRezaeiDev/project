<?php
$pageTitle = "مدیریت حضور و غیاب";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

if (!($_POST['start'] && $_POST['end'])) {
    die("تاریخ شروع و پایان ارسال نشده‌اند.");
}

$givenDate = $_POST['start'];
$today     = $_POST['end'];
$userId    = $_POST['user'] ?? 0;

$users = getUsers($userId);

// Prepare dates
$startObj = new DateTime($givenDate);
$endObj   = new DateTime($today);
$interval = $startObj->diff($endObj);
$daysAmount = $interval->days + 1;

$dates = [];
for ($i = 0; $i < $daysAmount; $i++) {
    $dateObj = clone $startObj;
    $dateObj->modify("+$i days");
    $dates[] = [
        'date' => $dateObj->format('Y-m-d'),
        'label' => jdate('l', strtotime($dateObj->format('Y-m-d'))) . ' ' . jdate('Y/m/d', strtotime($dateObj->format('Y-m-d')))
    ];
}
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

        .attendance {
            background-color: #d1fae5;
            padding: 2px;
            margin-bottom: 1px;
            border-radius: 2px;
        }

        .leave {
            background-color: #fee2e2;
            padding: 2px;
            margin-bottom: 1px;
            border-radius: 2px;
        }
    </style>
</head>

<body class="bg-gray-100 p-3">
    <div class="container mx-auto bg-white p-6 rounded shadow">
        <div class="overflow-auto">
            <table class="table-auto border border-gray-300 w-full text-sm">
                <thead>
                    <tr class="bg-amber-700 text-white border-4 border-black">
                        <th rowspan="2" class="px-4 py-2">نام نام‌خانوادگی</th>
                        <?php foreach ($dates as $d):
                            // Skip Fridays
                            if (jdate('l', strtotime($d['date'])) === 'جمعه') continue;
                        ?>
                            <th colspan="3" class="border-4 border-black px-4 py-2"><?= $d['label'] ?></th>
                        <?php endforeach; ?>
                        <th colspan="2">مجموع</th>
                    </tr>
                    <tr class="bg-amber-700 text-white border-4 border-black">
                        <?php foreach ($dates as $d):
                            if (jdate('l', strtotime($d['date'])) === 'جمعه') continue;
                        ?>
                            <th class="border-r-4 border-black px-2 py-1">وضعیت</th>
                            <th class="px-2 py-1">تاخیر</th>
                            <th class="border-l-4 border-black px-2 py-1">اضافه‌</th>
                        <?php endforeach; ?>
                        <th class="border-r-4 border-black px-2 py-1">تاخیر</th>
                        <th class="border-l-4 border-black px-2 py-1">اضافه‌</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $todayDate = date('Y-m-d'); // current date
                    foreach ($users as $user):
                        $totalDelay = 0;
                        $totalExtra = 0;
                    ?>
                        <tr class="last:border-b-4 border-black border-b-2 even:bg-sky-100">
                            <td class="border-r-4 border-black px-4 py-2 font-semibold"><?= $user['name'] . ' ' . $user['family'] ?></td>

                            <?php foreach ($dates as $d):
                                $reportDate = $d['date'];

                                // Skip Fridays
                                if (jdate('l', strtotime($reportDate)) === 'جمعه') continue;

                                $attendanceRecords = getUserAttendanceLogs($user['selectedUser'], $reportDate);
                                $leaveRecords      = getUserLeaveReport($user['selectedUser'], $reportDate);
                                $rule              = getUserAttendanceRule($user['selectedUser']);

                                $scheduleStart = strtotime($rule['start_hour']);
                                $scheduleEnd   = jdate('l', strtotime($reportDate)) === 'پنجشنبه' ? strtotime($rule['end_week']) : strtotime($rule['end_hour']);

                                $displayText = '';
                                $dayDelay = 0;
                                $dayExtra = 0;

                                if (empty($attendanceRecords) && empty($leaveRecords)) {
                                    $displayText = 'ثبت نشده';
                                } else {
                                    $now = time(); // current time for ongoing attendance
                                    foreach ($attendanceRecords as $att) {
                                        $start = strtotime($att['timestamp']);
                                        $end   = strtotime($att['end_time']) ?: $now;

                                        if ($reportDate === $todayDate) {
                                            // For today, show attendance but DO NOT count in delay/extra
                                            if (strtotime($att['end_time'])) {
                                                $displayText .= "<div class='attendance'>" . date('H:i', $start) . " - " . date('H:i', $end) . " حضور</div>";
                                            } else {
                                                $displayText .= "<div class='attendance'>" . date('H:i', $start) . " - ..." . " حضور</div>";
                                            }
                                            continue; // skip calculations for today
                                        }

                                        if (strtotime($att['end_time'])) {
                                            $displayText .= "<div class='attendance'>" . date('H:i', $start) . " - " . date('H:i', $end) . " حضور</div>";
                                        } else {
                                            $displayText .= "<div class='attendance'>" . date('H:i', $start) . " - ..." . " حضور</div>";
                                        }
                                    }

                                    foreach ($leaveRecords as $l) {
                                        $start = strtotime($reportDate . ' ' . $l['start_time']);
                                        $end   = strtotime($reportDate . ' ' . $l['end_time']);
                                        $displayText .= "<div class='leave'>" . date('H:i', $start) . " - " . date('H:i', $end) . " مرخصی</div>";
                                    }

                                    if ($reportDate !== $todayDate) {
                                        // --- Required minutes = schedule - official leaves ---
                                        $totalScheduled = ($scheduleEnd - $scheduleStart) / 60;
                                        foreach ($leaveRecords as $l) {
                                            $leaveStart = strtotime($reportDate . ' ' . $l['start_time']);
                                            $leaveEnd   = strtotime($reportDate . ' ' . $l['end_time']);
                                            $totalScheduled -= ($leaveEnd - $leaveStart) / 60;
                                        }

                                        // --- Attended minutes ---
                                        $attended = 0;
                                        $lastEnd = 0;
                                        foreach ($attendanceRecords as $att) {
                                            $start = strtotime($att['timestamp']);
                                            $end   = strtotime($att['end_time']);
                                            if (!$end) continue;

                                            // Clip to schedule
                                            if ($end > $scheduleStart && $start < $scheduleEnd) {
                                                $clipStart = max($start, $scheduleStart);
                                                $clipEnd   = min($end, $scheduleEnd);
                                                if ($clipEnd > $clipStart) {
                                                    $attended += ($clipEnd - $clipStart) / 60;
                                                }
                                            }
                                            $lastEnd = max($lastEnd, $end);
                                        }

                                        // --- Delay & Extra ---
                                        if ($attended < $totalScheduled) {
                                            $dayDelay = max(0, $totalScheduled - $attended);
                                            $dayExtra = 0;
                                        } else {
                                            $dayExtra = $attended - $totalScheduled;
                                        }

                                        // Extra beyond schedule end only if lastEnd > scheduleEnd
                                        if ($lastEnd > $scheduleEnd) {
                                            $dayExtra += ($lastEnd - $scheduleEnd) / 60;
                                        }

                                        $totalDelay += $dayDelay;
                                        $totalExtra += $dayExtra;
                                    }
                                }
                            ?>
                                <td class="border-r-4 border-black p-2 text-xs font-semibold text-gray-700"><?= $displayText ?></td>
                                <td class="border p-2 text-xs font-semibold text-gray-700"><?= $dayDelay ? round($dayDelay) . ' دقیقه' : '-' ?></td>
                                <td class="border-l-4 border-black p-2 text-xs font-semibold text-gray-700"><?= $dayExtra ? round($dayExtra) . ' دقیقه' : '-' ?></td>
                            <?php endforeach; ?>
                            <td class="border-black border px-4 py-2 text-xs font-semibold"><?= round($totalDelay) ?> دقیقه</td>
                            <td class="border-l-4 border border-black px-4 py-2 text-xs font-semibold"><?= round($totalExtra) ?> دقیقه</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>
</body>

</html>

<?php
function getUsers($id = null)
{
    $sql = "SELECT users.id,name,family,settings.user_id AS selectedUser 
            FROM yadakshop.users AS users 
            INNER JOIN yadakshop.attendance_settings AS settings ON settings.user_id=users.id
            WHERE users.password IS NOT NULL AND users.password!='' AND username!='tv' AND settings.is_active!=0";
    if ($id) $sql .= " AND user_id=:id";
    $stmt = PDO_CONNECTION->prepare($sql);
    if ($id) $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// Fetch attendance logs with START + its matching LEAVE
function getUserAttendanceLogs($user_id, $date)
{
    $sql = "SELECT t1.timestamp, t2.timestamp AS end_time 
            FROM yadakshop.attendance_logs t1
            LEFT JOIN yadakshop.attendance_logs t2 
                ON t2.user_id=t1.user_id 
                AND t2.action='LEAVE' 
                AND t2.id=(SELECT MIN(id) FROM yadakshop.attendance_logs WHERE user_id=t1.user_id AND action='LEAVE' AND id>t1.id)
            WHERE t1.user_id=:user_id AND DATE(t1.created_at)=:date AND t1.action='START'
            ORDER BY t1.timestamp ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getUserLeaveReport($user_id, $date)
{
    $sql = "SELECT * FROM yadakshop.leaves WHERE user_id=:user_id AND date=:date ORDER BY start_time ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getUserAttendanceRule($user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_settings WHERE user_id=:user_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['start_hour' => '09:00', 'end_hour' => '18:00', 'end_week' => '14:00'];
}
?>