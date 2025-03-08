<?php

$myAttendanceReportStart = getUserAttendanceReport('start', $_SESSION['id']);
$myAttendanceReportEnd = getUserAttendanceReport('leave', $_SESSION['id']);

function getUserAttendanceReport($action, $user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs WHERE user_id = :user_id AND DATE(created_at) = CURDATE() AND action = :action";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    $attendance_report = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $attendance_report;
}

$presentCount = count($myAttendanceReportStart);
$absentCount = count($myAttendanceReportEnd);
$isPresent = ($presentCount > $absentCount);
$isAbsent = ($presentCount == $absentCount);

if ($isAbsent): ?>
    <div
        <?php if ($isAbsent) : ?>
        onclick="setWorkingHour('start')"
        <?php endif; ?>
        class="p-2 transition-shadow bg-green-700 text-white rounded-lg shadow-sm hover:shadow-lg cursor-pointer fixed bottom-3 left-2 z-50">
        <?php if ($isAbsent) : ?>
            <div class="flex items-start justify-between gap-5">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-sm">ثبت ساعت ورود</span>
                    <span class="text-sm font-semibold clock"></span>
                </div>
                <img class="rounded-md w-8 h-8" src="<?= ('../../public/icons/start.svg') ?>" alt="">
            </div>
        <?php else : ?>
            <?php
            $startTime = strtotime($myAttendanceReportStart[$presentCount - 1]['timestamp']);
            $hour = date('H', $startTime);
            $time = date('H:i', $startTime);
            ?>
            <div class="flex items-start justify-between gap-5">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-sm">
                        ساعت ورود شما ثبت شده است.
                    </span>
                    <span class="text-sm font-semibold">
                        <?= $time . ' ' . getPeriod($hour) ?>
                    </span>
                </div>
                <img class="rounded-md w-8 h-8" src="<?= ('../../public/icons/start.svg') ?>" alt="">
            </div>
        <?php endif; ?>
    </div>
<?php endif;
if ($isPresent && date('H:i') >= '17:50') : ?>
    <div
        <?php if ($isPresent) : ?>
        onclick="setWorkingHour('leave')"
        <?php endif; ?>
        class="p-2 transition-shadow bg-rose-700 rounded-lg shadow-sm hover:shadow-lg text-white cursor-pointer fixed bottom-3 left-2 z-50">
        <?php if ($isPresent || $absentCount == 0) : ?>
            <div class="flex items-start justify-between gap-5">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-sm">ثبت ساعت خروج</span>
                    <span class="text-sm font-semibold clock"></span>
                </div>
                <img class="rounded-md w-8 h-8" src="<?= ('../../public/icons/leave.svg') ?>" alt="">
            </div>
        <?php else : ?>
            <?php
            $endTime = strtotime($myAttendanceReportEnd[$absentCount - 1]['timestamp']);
            $hour = date('H', $endTime);
            $time = date('H:i', $endTime);
            ?>
            <div class="flex items-start justify-between gap-5">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-sm">
                        ساعت خروج شما ثبت شده است.
                    </span>
                    <span class="text-sm font-semibold">
                        <?= $time . ' ' . getPeriod($hour) ?>
                    </span>
                </div>
                <img class="rounded-md w-8 h-8" src="<?= ('../../public/icons/leave.svg') ?>" alt="">
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<script>
    const ENDPOINT = '../../app/api/callcenter/AttendanceApi.php';

    function setWorkingHour(action) {
        const user_id = '<?= $_SESSION['id'] ?>';
        const params = new URLSearchParams();
        params.append('action', 'setWorkingHour');
        params.append('user_id', user_id);
        params.append('preform', action);

        axios.post(ENDPOINT, params).then((response) => {
            if (response.data.status === 'success') {
                alert(response.data.message);
                location.reload();
            } else {
                alert(response.data.message);
            }
        }).catch((error) => {
            console.error(error);
        });
    }

    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        // Convert HTMLCollection to an array using Array.from()
        Array.from(document.getElementsByClassName('clock')).forEach((item) => {
            item.textContent = `${hours}:${minutes}:${seconds}`;
        });
    }

    setInterval(updateClock, 1000);
    updateClock(); // Initialize clock immediately
</script>