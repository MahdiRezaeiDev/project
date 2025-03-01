<?php
$pageTitle = "مدیریت کاربران";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/ManageUsersController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$users = getUsers();
?>
<style>
    table {
        border-collapse: collapse;
    }

    tr {
        transition: filter 0.3s;
    }

    tr:hover {
        filter: none;
    }
</style>
<div class="bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between p-2">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="material-icons font-semibold text-orange-400">security</i>
            مدیریت دسترسی کاربران
        </h2>
        <a href="./createUserProfile.php" class="bg-gray-600 text-white py-2 px-3 rounded-sm text-sm">ثبت کاربر جدید</a>
    </div>
    <div class="table-wrapper">
        <table class="table-fixed min-w-full text-sm font-light">
            <thead id="blur" class="font-medium sticky top-12" style="z-index: 99;">
                <tr class="bg-gray-600" style="filter: none;">
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        #
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        نام
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        نام کاربری
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        مدیریت کاربران
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        ثبت خروج کالا
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        ثبت ورود کالا
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        گزارش خروج
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        گزارش ورود
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        انتقال به انبار
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        گزارش انتقالات
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        نیاز به انتقال
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        گزارش کسرات
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        انبار گردانی
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        تلگرام
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        قیمت دستوری
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        نرخ ارز
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        رابطه اجناس
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        دلار جدید
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        تلگرام بازار
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        #
                    </th>
                </tr>
            </thead>
            <tbody id="results" class="divide-y divide-gray-300">
                <?php
                foreach ($users as $index => $user) :
                    $auth = json_decode($user['auth'], true); ?>
                    <tr class="even:bg-gray-200">
                        <td class='p-2'>
                            <?= ++$index ?>
                        </td>
                        <td class='p-2'>
                            <?= $user['name'] . ' ' . $user['family'] ?>
                        </td>
                        <td class='p-2 '>
                            <?= $user['username'] ?>
                        </td>

                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['usersManagement'] ? 'checked' : '' ?> data-authority="usersManagement" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['sell'] ? 'checked' : '' ?> data-authority="sell" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['purchase'] ? 'checked' : '' ?> data-authority="purchase" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['sellsReport'] ? 'checked' : '' ?> data-authority="sellsReport" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['purchaseReport'] ? 'checked' : '' ?> data-authority="purchaseReport" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['transferGoods'] ? 'checked' : '' ?> data-authority="transferGoods" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['transferReport'] ? 'checked' : '' ?> data-authority="transferReport" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['requiredGoods'] ? 'checked' : '' ?> data-authority="requiredGoods" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['generalRequiredGoods'] ? 'checked' : '' ?> data-authority="generalRequiredGoods" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['stockAdjustment'] ? 'checked' : '' ?> data-authority="stockAdjustment" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['telegramProcess'] ? 'checked' : '' ?> data-authority="telegramProcess" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['givePrice'] ? 'checked' : '' ?> data-authority="givePrice" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['priceRates'] ? 'checked' : '' ?> data-authority="priceRates" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['relationships'] ? 'checked' : '' ?> data-authority="relationships" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['defineExchangeRate'] ? 'checked' : '' ?> data-authority="defineExchangeRate" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <input class="user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= (isset($auth['hamkarTelegram']) ? $auth['hamkarTelegram'] : '') ? 'checked' : '' ?> data-authority="hamkarTelegram" data-user='<?= $user['id'] ?>'>
                        </td>
                        <td class='p-2'>
                            <a href="./updateUserProfile.php?user=<?= $user['id'] ?>">
                                <i data-user="<?= $user['id'] ?>" class="material-icons cursor-pointer text-indigo-600 hover:text-indigo-800">edit</i>
                            </a>
                            <i onclick="deleteUser(this)" data-user="<?= $user['id'] ?>" class="material-icons cursor-pointer text-red-600 hover:text-red-800">do_not_disturb_on</i>
                        </td>
                    </tr>
                <?php
                endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    const rows = document.querySelectorAll('tbody tr'); // Select only <tr> elements in the tbody

    rows.forEach(row => {
        row.addEventListener('mouseenter', () => {
            rows.forEach(otherRow => {
                if (otherRow !== row) {
                    otherRow.style.filter = 'blur(10px)';
                }
            });
        });
        row.addEventListener('mouseleave', () => {
            rows.forEach(otherRow => {
                otherRow.style.filter = 'none';
            });
        });
    });
</script>
<script src="./assets/js/usersManagement.js"></script>
<?php
require_once './components/footer.php';
