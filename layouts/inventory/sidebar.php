<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}
$fileName = basename($_SERVER['PHP_SELF']);
?>
<aside id="side_bar">
    <ul>
        <li style="display: flex; justify-content: end;">
            <img src="../../public/icons/close.svg" class="cursor-pointer ml-3 mt-4" alt="close menu icon" onclick="toggleSidebar()">
        </li>
        <li class="block sm:hidden">
            <div class="dropdown">
                <a class="flex items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold justify-between <?= $fileName == 'transfer_index.php' ? 'bg-gray-400' : '' ?>" href="#">
                    <span class="flex items-center gap-2">
                        <img src="./assets/icons/chart.svg" alt="save icon">
                        گزارش ورود
                    </span>
                    <img src="./assets/icons/left_arrow.svg" alt="left arrow">
                </a>
                <ul class="drop_down_menu_aside bg-gray-800 border border-gray-800">
                    <li class="text-white text-sm font-semibold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="purchaseReport.php?interval=3">
                            <img class="hidden sm:inline-block" src="./assets/icons/three.svg" alt="add icon">
                            3 روز اخیر
                        </a>
                    </li>
                    <li class="text-white text-sm font-semibold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="purchaseReport.php?interval=10">
                            <img class="hidden sm:inline-block" src="./assets/icons/ten.svg" alt="add icon">
                            10 روز اخیر</a>
                    </li>
                    <li class="text-white text-sm font-semibold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="purchaseReport.php?interval=30">
                            <img class="hidden sm:inline-block" src="./assets/icons/thirty.svg" alt="add icon">
                            30 روز اخیر</a>
                    </li>
                    <li class="text-white text-sm font-semibold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="purchaseReport.php?interval=60">
                            <img class="hidden sm:inline-block" src="./assets/icons/sixty.svg" alt="add icon">
                            60 روز اخیر</a>
                    </li>
                    <li class="text-white text-sm font-semibold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="purchaseReport.php?interval=120">
                            <img class="hidden sm:inline-block" src="./assets/icons/hundred.svg" alt="add icon">
                            120 روز اخیر</a>
                    </li>
                    <li class="text-white text-sm font-semibold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="purchaseReport.php">
                            <img class="hidden sm:inline-block" src="./assets/icons/complete.svg" alt="add icon">
                            گزارش کامل
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="block sm:hidden">
            <div class="dropdown">
                <a class="flex items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold justify-between <?= $fileName == 'transfer_index.php' ? 'bg-gray-400' : '' ?>" href="#">
                    <span class="flex items-center gap-2">
                        <img src="./assets/icons/chart.svg" alt="save icon">
                        گزارش خروج
                    </span>
                    <img src="./assets/icons/left_arrow.svg" alt="left arrow">
                </a>
                <ul class="drop_down_menu_aside bg-gray-800 border border-gray-800">
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="sellsReport.php?interval=3">
                            <img class="hidden sm:inline-block" src="./assets/icons/three.svg" alt="add icon">
                            3 روز اخیر
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="sellsReport.php?interval=10">
                            <img class="hidden sm:inline-block" src="./assets/icons/ten.svg" alt="add icon">
                            10 روز اخیر
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="sellsReport.php?interval=30">
                            <img class="hidden sm:inline-block" src="./assets/icons/thirty.svg" alt="add icon">
                            30 روز اخیر
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="sellsReport.php?interval=60">
                            <img class="hidden sm:inline-block" src="./assets/icons/sixty.svg" alt="add icon">
                            60 روز اخیر
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="sellsReport.php?interval=120">
                            <img class="hidden sm:inline-block" src="./assets/icons/hundred.svg" alt="add icon">
                            120 روز اخیر
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="sellsReport.php">
                            <img class="hidden sm:inline-block" src="./assets/icons/complete.svg" alt="add icon">
                            گزارش کامل
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li>
            <a class="flex justify-start items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == '404.php' ? 'bg-gray-400' : '' ?>" href="../callcenter/registerGoods.php">
                <img src="./assets/icons/save.svg" alt="save icon">
                ثبت کدفنی
            </a>
        </li>
        <li>
            <a class="flex justify-start items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == '404.php' ? 'bg-gray-400' : '' ?>" href="../callcenter/searchGoods.php">
                <img src="./assets/icons/search.svg" alt="save icon">
                جستجوی کدفنی
            </a>
        </li>
        <li>
            <a class="flex justify-start items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'excelFile.php' ? 'bg-gray-400' : '' ?>" href="./excelFile.php">
                <img src="./assets/icons/manage.svg" alt="save icon">
                مدیریت فایل
            </a>
        </li>
        <li>
            <a class="flex justify-start items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'goodsDetailsManagement.php' ? 'bg-gray-400' : '' ?>" href="./goodsDetailsManagement.php">
                <svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#000000">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path fill-rule="evenodd" d="M4.22182541,19.7781746 C3.29761276,18.8539619 3.03246659,17.4441845 3.32230899,15.5944173 C1.80937652,14.4913839 1,13.3070341 1,12 C1,10.6929659 1.80937652,9.50861611 3.32230899,8.40558269 C3.03246659,6.55581547 3.29761276,5.14603806 4.22182541,4.22182541 C5.14603806,3.29761276 6.55581547,3.03246659 8.40558269,3.32230899 C9.50861611,1.80937652 10.6929659,1 12,1 C13.3070341,1 14.4913839,1.80937652 15.5944173,3.32230899 C17.4441845,3.03246659 18.8539619,3.29761276 19.7781746,4.22182541 C20.7023872,5.14603806 20.9675334,6.55581547 20.677691,8.40558269 C22.1906235,9.50861611 23,10.6929659 23,12 C23,13.3070341 22.1906235,14.4913839 20.677691,15.5944173 C20.9675334,17.4441845 20.7023872,18.8539619 19.7781746,19.7781746 C18.8539619,20.7023872 17.4441845,20.9675334 15.5944173,20.677691 C14.4913839,22.1906235 13.3070341,23 12,23 C10.6929659,23 9.50861611,22.1906235 8.40558269,20.677691 C6.55581547,20.9675334 5.14603806,20.7023872 4.22182541,19.7781746 Z M8.65258332,18.5979847 C9.05851175,18.5110507 9.47593822,18.6839544 9.70150129,19.0324608 C10.582262,20.3932808 11.3676332,21 12,21 C12.6323668,21 13.417738,20.3932808 14.2984987,19.0324608 C14.5240618,18.6839544 14.9414883,18.5110507 15.3474167,18.5979847 C16.9324536,18.9374379 17.9168102,18.8111119 18.363961,18.363961 C18.8111119,17.9168102 18.9374379,16.9324536 18.5979847,15.3474167 C18.5110507,14.9414883 18.6839544,14.5240618 19.0324608,14.2984987 C20.3932808,13.417738 21,12.6323668 21,12 C21,11.3676332 20.3932808,10.582262 19.0324608,9.70150129 C18.6839544,9.47593822 18.5110507,9.05851175 18.5979847,8.65258332 C18.9374379,7.06754643 18.8111119,6.08318982 18.363961,5.63603897 C17.9168102,5.18888812 16.9324536,5.06256208 15.3474167,5.40201528 C14.9414883,5.48894934 14.5240618,5.31604564 14.2984987,4.96753923 C13.417738,3.60671924 12.6323668,3 12,3 C11.3676332,3 10.582262,3.60671924 9.70150129,4.96753923 C9.47593822,5.31604564 9.05851175,5.48894934 8.65258332,5.40201528 C7.06754643,5.06256208 6.08318982,5.18888812 5.63603897,5.63603897 C5.18888812,6.08318982 5.06256208,7.06754643 5.40201528,8.65258332 C5.48894934,9.05851175 5.31604564,9.47593822 4.96753923,9.70150129 C3.60671924,10.582262 3,11.3676332 3,12 C3,12.6323668 3.60671924,13.417738 4.96753923,14.2984987 C5.31604564,14.5240618 5.48894934,14.9414883 5.40201528,15.3474167 C5.06256208,16.9324536 5.18888812,17.9168102 5.63603897,18.363961 C6.08318982,18.8111119 7.06754643,18.9374379 8.65258332,18.5979847 Z M11,12.5857864 L15.2928932,8.29289322 L16.7071068,9.70710678 L11,15.4142136 L7.29289322,11.7071068 L8.70710678,10.2928932 L11,12.5857864 Z"></path>
                    </g>
                </svg>
                مدیریت برند و فروشندگان
            </a>
        </li>
        <li>
            <a class="flex justify-start items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'stockAdjustment.php' ? 'bg-gray-400' : '' ?>" href="./stockAdjustment.php">
                <img src="./assets/icons/inventory.svg" alt="save icon">
                انبارگردانی
            </a>
        </li>
        <li>
            <a class="flex justify-start items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'singleItemReport.php' ? 'bg-gray-400' : '' ?>" href="./singleItemReport.php">
                <img src="./assets/icons/explore.svg" alt="save icon">
                بررسی تک آیتم
            </a>
        </li>
        <li class="dropdown">
            <a class="flex justify-between items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold <?= $fileName == 'price.php' ? 'bg-gray-400' : '' ?>">
                <span class="flex items-center gap-2">
                    <img src="./assets/icons/price.svg" alt="save icon">
                    سامانه قیمت
                </span>
                <img src="./assets/icons/left_arrow.svg" alt="left arrow">
            </a>
            <ul class="drop_down_menu_aside bg-gray-800 border border-gray-800">
                <li>
                    <a class="text-sm p-3 text-white hover:bg-gray-900 flex items-center gap-2" target="_self" href="price.php">
                        <img src="./assets/icons/price_white.svg" alt="save icon">
                        سامانه قیمت
                    </a>
                </li>
                <li>
                    <a class="text-sm p-3 text-white hover:bg-gray-900 flex items-center gap-2" target="_blank" href="https://yadakinfo.com/projects/price/">
                        <img src="./assets/icons/original.svg" alt="save icon">
                        قیمت موبیز
                    </a>
                </li>
            </ul>
        </li>
        <li class="dropdown">
            <a class="flex items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold justify-between <?= $fileName == 'transfer_index.php' ? 'bg-gray-400' : '' ?>" href="./transfer_index.php">
                <span class="flex items-center gap-2">
                    <img src="./assets/icons/ship.svg" alt="save icon">
                    انتقال اجناس
                </span>
                <img src="./assets/icons/left_arrow.svg" alt="left arrow">
            </a>
            <ul class="drop_down_menu_aside bg-gray-800 border border-gray-800">
                <li>
                    <a class="p-3 text-white text-sm hover:bg-gray-900 flex items-center gap-2" href="./transferGoods.php">
                        <img src="./assets/icons/move.svg" alt="save icon">
                        انتقال به انبار
                    </a>
                </li>
                <li>
                    <a class="p-3 text-white text-sm hover:bg-gray-900 flex items-center gap-2" href="./transferReport.php">
                        <img src="./assets/icons/report.svg" alt="save icon">
                        گزارش انتقالات
                    </a>
                </li>
                <li>
                    <a class="p-3 text-white text-sm hover:bg-gray-900 flex items-center gap-2" href="./requiredGoods.php">
                        <img src="./assets/icons/need.svg" alt="save icon">
                        نیاز به انتقال
                    </a>
                </li>
                <li>
                    <a class="p-3 text-white text-sm hover:bg-gray-900 flex items-center gap-2" href="./generalRequiredGoods.php">
                        <img src="./assets/icons/ask.svg" alt="save icon">
                        گزارش کسرات
                    </a>
                </li>
            </ul>
        </li>
    </ul>
    <ul>
        <li>
            <a class="flex justify-start items-center gap-2 p-4 hover:bg-gray-200 text-sm font-semibold" href="../auth/logout.php">
                <img src="./assets/icons/power.svg" alt="save icon">
                خروج از حساب کاربری
            </a>
        </li>
    </ul>
</aside>