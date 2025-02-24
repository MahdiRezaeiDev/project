<?php
$pageTitle = "مدیریت حضور و غیاب";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/UsersController.php';
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
            مدیریت حضور و غیاب
        </h2>
        <a href="./createUserProfile.php" class="bg-gray-600 text-white py-2 px-3 rounded-sm text-sm">ثبت کاربر جدید</a>
    </div>
    <div class="bg-white rounded-lg p-5 shadow-md hover:shadow-xl">
        <div class="border border-dashed border-gray-800 flex flex-col items-center h-full rounded-lg">
            <div class="overflow-x-auto shadow-md sm:rounded-lg w-full h-full">
                <table class="w-full text-sm text-left rtl:text-right text-gray-800 h-full">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                شماره
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                کاربر
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                ساعت ورود
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                ساعت خروج
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                عملیات
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $index => $factor) : ?>
                            <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                                <th class="px-6 py-3  font-semibold text-gray-800 text-right">
                                    <?= $index + 1; ?>
                                </th>
                                <th class="px-6 py-3  font-semibold text-gray-800 text-right">
                                    <?= $factor['name'] . ' ' . $factor['family'] ?>
                                </th>
                                <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                    ۹:۰۰ صبح
                                </td>
                                <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                    ۱۸:۰۰ عصر
                                </td>
                                <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                    <a href="./editUserProfile.php?id=<?= $factor['id'] ?>" class="text-blue-500 hover:text-blue-700">ویرایش</a>
                                    <a href="./deleteUserProfile.php?id=<?= $factor['id'] ?>" class="text-red-500 hover:text-red-700">حذف</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="./assets/js/usersManagement.js"></scrip>
<?php
require_once './components/footer.php';
