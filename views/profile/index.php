<?php
$pageTitle = "گزارش حضور و غیاب من";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/profile/ProfileController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

$currentUserId = $_SESSION['id']; // فقط کاربر فعلی
// همه ماه‌های فارسی
$persianMonths = [
    1 => 'فروردین',
    2 => 'اردیبهشت',
    3 => 'خرداد',
    4 => 'تیر',
    5 => 'مرداد',
    6 => 'شهریور',
    7 => 'مهر',
    8 => 'آبان',
    9 => 'آذر',
    10 => 'دی',
    11 => 'بهمن',
    12 => 'اسفند'
];

// Current Persian year
$currentYear = jdate('Y');

// Selected month (default = current month)
$selectedMonth = convertPersianToEnglish($_GET['month'] ?? jdate('n'));

// Get first and last day of selected Persian month
$firstDay = 1;
$daysInMonth = convertPersianToEnglish(jdate('t', jmktime(0, 0, 0, $selectedMonth, 1, $currentYear)));


// Convert Persian first/last day to Gregorian
list($startY, $startM, $startD) = jalali_to_gregorian($currentYear, $selectedMonth, $firstDay);
list($endY, $endM, $endD) = jalali_to_gregorian($currentYear, $selectedMonth, $daysInMonth);

$startGregorian = "$startY-$startM-$startD";
$endGregorian   = "$endY-$endM-$endD";

// Get user info
$user = getUserById($currentUserId);
if (!$user) die("کاربر یافت نشد.");

// Prepare dates array for table display
$dates = [];
for ($i = 0; $i < $daysInMonth; $i++) {
    $dateJalali = [$currentYear, $selectedMonth, $i + 1];
    list($gY, $gM, $gD) = jalali_to_gregorian(...$dateJalali);
    $gregorianDate = "$gY-$gM-$gD";
    $dates[] = [
        'jalali' => jdate('l Y/m/d', strtotime($gregorianDate)),
        'gregorian' => $gregorianDate
    ];
}

// Fetch attendance records for the month
$attendanceRecords = getUserAttendanceLogsMonth($currentUserId, $startGregorian, $endGregorian);
$attendanceRule = getUserAttendanceRule($currentUserId);
?>
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

<div class="bg-gray-100 p-3">
    <div class="container mx-auto bg-white p-6 rounded shadow">

        <h2 class="text-lg font-bold mb-4">انتخاب ماه</h2>
        <div class="mb-4">
            <?php foreach ($persianMonths as $num => $name): ?>
                <a href="?month=<?= $num ?>" class="inline-block px-4 py-2 mr-2 mb-2 rounded <?= ($num == $selectedMonth) ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                    <?= $name ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="overflow-auto">
            <table class="table-auto border border-gray-300 w-full text-sm">
                <thead>
                    <tr class="bg-amber-700 text-white border-4 border-black">
                        <th class="px-4 py-2"><?= $user['name'] . ' ' . $user['family'] ?></th>
                        <th class="px-4 py-2">وضعیت</th>
                        <th class="px-4 py-2">اضافه کار</th>
                        <th class="px-4 py-2">تاخیر/ تعجیل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $todayDate = date('Y-m-d'); ?>
                    <?php foreach ($dates as $d):
                        $reportDate = $d['gregorian'];
                        $totalDelay = 0;
                        $totalExtra = 0;

                        // Skip Fridays
                        if (jdate('l', strtotime($reportDate)) === 'جمعه') continue;

                        $attendanceRecords = getUserAttendanceLogs($currentUserId, $reportDate);
                        $leaveRecords      = getUserLeaveReport($currentUserId, $reportDate);
                        $rule              = getUserAttendanceRule($currentUserId);

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
                        <tr class="last:border-b-4 border-black border-b-2 even:bg-sky-100">
                            <td class="border-r-4 border-black p-2 font-semibold text-gray-700"><?= $d['jalali'] ?></td>
                            <td class="border p-2 font-semibold text-gray-700">
                                <?= $displayText ?: '-' ?>
                            </td>
                            <td class="border p-2 font-semibold text-gray-700"><?= $dayExtra ? round($dayExtra) . ' دقیقه' : '-' ?></td>
                            <td class="border-l-4 border-black p-2 font-semibold text-gray-700"><?= $dayDelay ? round($dayDelay) . ' دقیقه' : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
<?php
require_once './components/footer.php';
