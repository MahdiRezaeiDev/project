<?php
$pageTitle = "حضور و غیاب";
$iconUrl = 'attendance.svg';
require_once './components/header.php';

if (!isset($_GET['user'])) {
    showAlertAndExit('لینک شما اشتباه است.');
}

function isMobile()
{
    return preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/', $_SERVER['HTTP_USER_AGENT']);
}

if (!isMobile()): ?>

    <div class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 text-center space-y-6 border border-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-20 h-20 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 17h2" />
            </svg>
            <h1 class="text-3xl font-bold text-gray-800">دسترسی فقط از طریق موبایل</h1>
            <p class="text-gray-600 text-base">
                لطفاً برای استفاده از این صفحه، با استفاده از گوشی موبایل خود وارد شوید.
            </p>
        </div>
    </div>
<?php
    exit;
endif;

function getUserInfo($username)
{
    $stmt = PDO_CONNECTION->prepare("SELECT id, access_token FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$userInfo = getUserInfo($_GET['user']);
$userId = $userInfo['id'];
$userAccessToken = $userInfo['access_token'];

$myAttendanceReportStart = getUserAttendanceReportTooltip('start', $userId);
$myAttendanceReportEnd = getUserAttendanceReportTooltip('leave', $userId);

function getUserAttendanceReportTooltip($action, $user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs WHERE user_id = :user_id AND DATE(created_at) = CURDATE() AND action = :action";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$presentCount = count($myAttendanceReportStart);
$absentCount = count($myAttendanceReportEnd);
$isPresent = ($presentCount > $absentCount);
$isAbsent = ($presentCount == $absentCount);

$showStartCard = $isAbsent;
$showLeaveCard = $isPresent && date('H:i') >= '17:50';
?>

<?php if ($showStartCard || $showLeaveCard): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-6 hide_while_print bg-black/30 backdrop-blur-sm animate-fadeIn">
        <div id="attendanceCard"
            class="w-full max-w-lg bg-white rounded-3xl shadow-2xl p-10 text-center space-y-6 cursor-pointer transition-all hover:shadow-3xl hover:scale-105 duration-300 relative"
            onclick="handleCardClick()">

            <div id="profile" class="hidden space-y-2">
                <img id="profilePic" class="w-20 h-20 rounded-full mx-auto" src="default-avatar.png" alt="User image">
                <div id="name" class="text-lg font-semibold text-gray-800"></div>
                <div id="username" class="text-sm text-gray-500"></div>
            </div>

            <div id="status" class="text-red-600 font-medium"></div>

            <div class="flex justify-center">
                <img class="w-20 h-20" src="<?= $showStartCard ? '../../public/icons/start.svg' : '../../public/icons/leave.svg' ?>" alt="">
            </div>

            <div class="space-y-2">
                <h2 class="text-2xl font-bold <?= $showStartCard ? 'text-green-700' : 'text-rose-700' ?>">
                    <?= $showStartCard ? 'ثبت ساعت ورود' : 'ثبت ساعت خروج' ?>
                </h2>
                <p class="text-base text-gray-600">
                    <?= $showStartCard ? 'برای شروع روز کاری، ساعت ورود خود را ثبت کنید.' : 'پایان روز کاری خود را ثبت کنید.' ?>
                </p>
                <span class="text-xl font-extrabold clock text-gray-900"></span>
            </div>
        </div>
    </div>
<?php endif; ?>
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }

    @media (min-width: 640px) {
        .hide_while_print .fixed.inset-0 {
            justify-content: flex-end;
            align-items: flex-end;
            padding: 1rem;
        }
    }
</style>

<script>
    const ENDPOINT = '../../app/api/callcenter/AttendanceApi.php';
    const USERNAME = <?= json_encode($_GET['user']) ?>;
    const PRESET_ACTION = <?= json_encode($showStartCard ? 'start' : 'leave') ?>;
    const token = localStorage.getItem("attend_token");
    const USER_ID = <?= $userId; ?>
    let canSubmit = false;

    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.querySelectorAll('.clock').forEach(el => {
            el.textContent = `${hours}:${minutes}:${seconds}`;
        });
    }
    setInterval(updateClock, 1000);
    updateClock();

    function handleCardClick() {
        if (!canSubmit) {
            alert("توکن نامعتبر یا یافت نشد.");
            return;
        }
        setWorkingHour(PRESET_ACTION);
    }

    function setWorkingHour(action) {
        const params = new URLSearchParams();
        params.append('action', 'setWorkingHour');
        params.append('user', USERNAME);
        params.append('token', token);
        params.append('preform', action);
        params.append('user_id', USER_ID);

        axios.post(ENDPOINT, params).then((response) => {
            alert(response.data.message);
            if (response.data.status === 'success') {
                location.reload();
            }
        }).catch((error) => {
            console.error(error);
        });
    }

    if (token) {
        fetch("verify.php?token=" + token)
            .then(res => res.json())
            .then(data => {
                if (data.status === "ok" && data.username === USERNAME) {
                    canSubmit = true;
                    document.getElementById("profile").classList.remove("hidden");
                    document.getElementById("status").classList.add("hidden");
                    document.getElementById("name").textContent = data.name + ' ' + data.family;
                    document.getElementById("username").textContent = "نام کاربری: " + data.username;
                    document.getElementById("profilePic").src =
                        data.id ? '../../public/userimg/' + data.id + '.jpg' : "default-avatar.png";
                } else {
                    document.getElementById("status").textContent = "شما اجازه ثبت ورود و خروج را برای این کاربر ندارید.";
                }
            }).catch(err => {
                console.error("خطا در بررسی توکن:", err);
                document.getElementById("status").textContent = "خطا در اتصال به سرور.";
            });
    } else {
        document.getElementById("status").textContent = "توکن یافت نشد. لطفاً ثبت‌نام کنید.";
    }
</script>
<?php require_once './components/footer.php'; ?>