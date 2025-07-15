<?php
$pageTitle = "قیمت دستوری";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../app/controller/attendance/mobileRegisterController.php';
?>

<!-- Full viewport height container to center content -->
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-10">
    <div class="bg-white shadow-lg rounded-xl w-full max-w-md p-8 space-y-8">
        <h2 class="text-3xl font-extrabold text-gray-800 text-center">ثبت دستگاه کاربر</h2>

        <div class="text-gray-700 space-y-3 text-base">
            <p><span class="font-semibold">نام:</span> <?= htmlspecialchars($user['name']) ?></p>
            <p><span class="font-semibold">نام کاربری:</span> <?= htmlspecialchars($user['username']) ?></p>
        </div>

        <?php if ($user['access_token']): ?>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">توکن ثبت شده:</label>
                <div class="bg-gray-100 border border-gray-300 rounded p-3 text-sm break-words text-gray-900 select-all">
                    <?= htmlspecialchars($user['access_token']) ?>
                </div>
                <script>
                    localStorage.setItem("attend_token", "<?= htmlspecialchars($user['access_token']) ?>");
                    console.log("توکن در مرورگر ذخیره شد.");
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

        <?php if (isset($message)): ?>
            <div class="text-green-600 text-center text-sm mt-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once './components/footer.php'; ?>
