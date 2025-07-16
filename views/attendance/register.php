<?php
$pageTitle = "ثبت دستگاه کاربر";
$iconUrl = 'report.png';
require_once './components/header.php';

$message = null;

// --- Step 1: Check input token from URL
if (!isset($_GET['token'])) {
    showAlertAndExit("توکن ثبت‌ نام یافت نشد.");
}
$registrationToken = $_GET['token'];

// --- Step 2: Validate token from database
$stmt = PDO_CONNECTION->prepare("SELECT * FROM registration_tokens WHERE token = ? AND is_used = 0 AND expires_at >= NOW()");
$stmt->execute([$registrationToken]);
$reg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reg) {
    showAlertAndExit("توکن منقضی یا نامعتبر است.");
}

// --- Step 3: Fetch user info
$stmt = PDO_CONNECTION->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$reg['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    showAlertAndExit("کاربر یافت نشد.");
}

// --- Step 4: Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_token'])) {
        $newToken = bin2hex(random_bytes(16));

        // Set token in users table
        $stmt = PDO_CONNECTION->prepare("UPDATE users SET access_token = ? WHERE id = ?");
        $stmt->execute([$newToken, $user['id']]);
        $user['access_token'] = $newToken;

        // Mark registration token as used
        $stmt = PDO_CONNECTION->prepare("UPDATE registration_tokens SET is_used = 1 WHERE id = ?");
        $stmt->execute([$reg['id']]);

        $message = "توکن با موفقیت ثبت شد.";
    } elseif (isset($_POST['delete_token'])) {
        $stmt = PDO_CONNECTION->prepare("UPDATE users SET access_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        $user['access_token'] = null;
        $message = "توکن حذف شد.";
    }
}
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-10">
    <div id="attendanceCard" class="bg-white shadow-lg rounded-xl w-full max-w-md p-8 space-y-8">
        <h2 class="text-3xl font-extrabold text-gray-800 text-center">ثبت دستگاه کاربر</h2>

        <div class="text-gray-700 space-y-3 text-base">
            <p><span class="font-semibold">نام:</span> <?= htmlspecialchars($user['name']) ?></p>
            <p><span class="font-semibold">نام کاربری:</span> <?= htmlspecialchars($user['username']) ?></p>
        </div>

        <?php if ($user['access_token']): ?>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">توکن ثبت شده:</label>
                <p class="tex-xs text-gray-500">لطفا بروی ایکون ذیل کلیک نمایید تا لینک ورود و خروج شما برای استفاده بعدی کپی شود.</p>
                <div class="bg-gray-100 border border-gray-300 rounded p-3 text-sm break-words text-gray-900 select-all flex items-center justify-between">
                    <a
                        id="attendLink"
                        class="text-blue-700 hover:underline truncate"
                        href="http://192.168.9.14/YadakShop-APP/views/attendance/attend.php?user=<?= $user['username'] ?>"
                        target="_blank">
                        ثبت ورود و خروج
                    </a>
                    <button
                        type="button"
                        onclick="copyAttendLink()"
                        class="ml-3 text-sm text-blue-600 hover:text-blue-800 focus:outline-none"
                        title="کپی لینک">
                        📋
                    </button>
                </div>

                <script>
                    function copyAttendLink() {
                        const link = document.getElementById('attendLink').href;

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(link)
                                .then(() => alert("لینک ورود و خروج در کلیپ‌بورد کپی شد."))
                                .catch(err => {
                                    console.error("Clipboard error:", err);
                                    alert("مشکلی در کپی کردن لینک به وجود آمد.");
                                });
                        } else {
                            // Fallback for older browsers
                            const textarea = document.createElement('textarea');
                            textarea.value = link;
                            document.body.appendChild(textarea);
                            textarea.select();
                            try {
                                document.execCommand('copy');
                                alert("لینک کپی شد.");
                            } catch (err) {
                                alert("کپی نشد.");
                            }
                            document.body.removeChild(textarea);
                        }
                    }
                    localStorage.setItem("attend_token", "<?= htmlspecialchars($user['access_token']) ?>");
                    console.log("✅ توکن در localStorage ذخیره شد.");
                </script>
                <form method="post" class="mt-6 flex justify-center">
                    <button
                        type="submit"
                        name="delete_token"
                        onclick="return confirm('آیا مطمئن هستید؟')"
                        class="bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 text-white px-6 py-2 rounded-lg shadow transition">
                        حذف ثبت‌نام
                    </button>
                </form>
            </div>
        <?php else: ?>
            <form method="post" class="flex justify-center">
                <button
                    type="submit"
                    name="create_token"
                    class="bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 text-white px-6 py-3 rounded-lg shadow transition">
                    ثبت این دستگاه
                </button>
            </form>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="text-green-600 text-center text-sm mt-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once './components/footer.php'; ?>