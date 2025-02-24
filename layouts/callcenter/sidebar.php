<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
} ?>
<aside id="side_bar">
    <ul>
        <li class="flex justify-end">
            <img src="../../public/icons/close.svg" class="cursor-pointer ml-3 mt-4" alt="close menu icon" onclick="toggleSidebar()">
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'index.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>index.php">
                <img src="../../layouts/callcenter/icons/dashboard.svg" alt="dashboard icon">
                صفحه اصلی
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold items-center gap-2" href="../inventory/index.php">
                <img src="../../layouts/callcenter/icons/system.svg" alt="dashboard icon">
                سامانه قیمت
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'customersList.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>customersList.php">
                <img src="../../layouts/callcenter/icons/client.svg" alt="dashboard icon">
                لیست مشتریان
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'searchGoods.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>searchGoods.php">
                <img src="../inventory/assets/icons/search.svg" alt="dashboard icon">
                جستجوی اجناس
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'goodsList.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>goodsList.php">
                <img src="../inventory/assets/icons/chart.svg" alt="dashboard icon">
                لیست اجناس
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'priceRates.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>priceRates.php">
                <img src="../../layouts/callcenter/icons/rate.svg" alt="dashboard icon">
                نرخ های ارز
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'relationships.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>relationships.php">
                <img src="../../layouts/callcenter/icons/web.svg" alt="dashboard icon">
                تعریف رابطه اجناس
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'usersManagement.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>usersManagement.php">
                <img src="../inventory/assets/icons/manage.svg" alt="dashboard icon">
                مدیریت کاربران
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'attendance.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>attendance.php">
                <img src="../../layouts/callcenter/icons/attendance.svg" alt="dashboard icon">
                مدیریت حضور و غیاب
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'defineExchangeRate.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>defineExchangeRate.php">
                <img src="../../layouts/callcenter/icons/dollar.svg" alt="dashboard icon">
                تعریف دلار جدید
            </a>
        </li>
        <li>
            <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'price_check.php' ? 'bg-gray-400' : '' ?> items-center gap-2" href="<?= $append ?>price_check.php">
                <img src="../inventory/assets/icons/explore.svg" alt="dashboard icon">
                بررسی قیمت کدفنی
            </a>
        </li>
    </ul>
    <!-- Authentication -->
    <a class="flex justify-start p-4 hover:bg-gray-200 text-sm font-semibold items-center gap-2" href="../auth/logout.php">
        <img src="../../layouts/callcenter/icons/exit.svg" alt="dashboard icon">
        خروج از حساب
    </a>
</aside>