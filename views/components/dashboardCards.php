<div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-2 lg:grid-cols-4">
    <div class="p-4 transition-shadow bg-white rounded-lg shadow-sm hover:shadow-lg">
        <div class="flex items-start justify-between">
            <div class="flex flex-col space-y-2">
                <span class="text-gray-800 font-semibold">مجموع کاربران</span>
                <span class="text-lg font-semibold"><?= $totalUsers ?></span>
            </div>
            <img class="rounded-md w-16 h-16" src="<?= ('../../public/icons/user.svg') ?>" alt="">
        </div>
        <div>
            <span class="inline-block px-2 text-sm text-white bg-green-500 ml-1 rounded"><?= rand(1, 100) ?>%</span>
            <a href="<?= ($_SESSION['username'] == 'niayesh') ? '../callcenter/usersManagement.php' : '#' ?>" class="text-blue-500 underline">مدیریت کاربران</a>
        </div>
    </div>
    <div class="p-4 transition-shadow bg-white rounded-lg shadow-sm hover:shadow-lg">
        <div class="flex items-start justify-between">
            <div class="flex flex-col space-y-2">
                <span class="text-gray-800 font-semibold">مجموع فاکتور های ثبت شده</span>
                <span class="text-lg font-semibold"><?= $totalFactors ?></span>
            </div>
            <img class="rounded-md w-16 h-16" src="<?= ('../../public/icons/invoice.svg') ?>" alt="">
        </div>
        <div>
            <span class="inline-block px-2 text-sm text-white bg-green-500 ml-1 rounded"><?= rand(1, 100) ?>%</span>
            <a href="../callcenter/factor.php" class="text-blue-500 underline">ثبت فاکتور جدید</a>
        </div>
    </div>
    <?php
    $presentCount = count($myAttendanceReportStart);
    $absentCount = count($myAttendanceReportEnd);
    $isPresent = ($presentCount > $absentCount);
    $isAbsent = ($presentCount == $absentCount);
    ?>
    <div
        <?php if ($isAbsent) : ?>
        onclick="setWorkingHour('start')"
        <?php endif; ?>
        class="p-4 transition-shadow bg-green-700 text-white rounded-lg shadow-sm hover:shadow-lg cursor-pointer">
        <?php if ($isAbsent) : ?>
            <div class="flex items-start justify-between">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-xl">ثبت ساعت ورود</span>
                    <span class="text-lg font-semibold clock"></span>
                </div>
                <img class="rounded-md w-16 h-16" src="<?= ('../../public/icons/start.svg') ?>" alt="">
            </div>
            <div>
                <p class="text-xs">برای ثبت ساعت ورود خود اینجا کلیک نمایید.</p>
            </div>
        <?php else : ?>
            <div class="flex items-start justify-between">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-xl">
                        ساعت ورود شما ثبت شده است.
                    </span>
                    <span class="text-lg font-semibold">
                        <?= date('h:i A', strtotime($myAttendanceReportStart[$presentCount - 1]['timestamp'])) ?>
                    </span>
                </div>
                <img class="rounded-md w-16 h-16" src="<?= ('../../public/icons/start.svg') ?>" alt="">
            </div>
            <div>
                <p class="text-xs">ساعت ورود شما ثبت شده است.</p>
            </div>
        <?php endif; ?>
    </div>
    <div
        <?php if ($isPresent) : ?>
        onclick="setWorkingHour('leave')"
        <?php endif; ?>
        class="p-4 transition-shadow bg-rose-700 rounded-lg shadow-sm hover:shadow-lg text-white cursor-pointer">
        <?php if ($isPresent) : ?>
            <div class="flex items-start justify-between">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-xl">ثبت ساعت خروج</span>
                    <span class="text-lg font-semibold clock"></span>
                </div>
                <img class="rounded-md w-16 h-16" src="<?= ('../../public/icons/leave.svg') ?>" alt="">
            </div>
            <div>
                <p class="text-xs">برای ثبت ساعت خروج خود اینجا کلیک نمایید.</p>
            </div>
        <?php else : ?>
            <div class="flex items-start justify-between">
                <div class="flex flex-col space-y-2">
                    <span class="font-semibold text-xl">
                        ساعت خروج شما ثبت شده است.
                    </span>
                    <span class="text-lg font-semibold">
                        <?= date('h:i A', strtotime($myAttendanceReportEnd[$absentCount - 1]['timestamp'])) ?>
                    </span>
                </div>
                <img class="rounded-md w-16 h-16" src="<?= ('../../public/icons/leave.svg') ?>" alt="">
            </div>
            <div>
                <p class="text-xs">ساعت خروج شما ثبت شده است.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
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