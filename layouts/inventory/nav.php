<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$fileName = basename($_SERVER['PHP_SELF']);
?>
<nav id="main_nav" class="fixed top-0 left-0 right-0 z-50 p-2 flex justify-between overflow-visible bg-white shadow-md">
    <ul class="flex items-center">
        <li onclick="toggleSidebar()" class="mx-1 hover:bg-gray-400 text-sm font-bold cursor-pointer">
            <img id="open_aside_icon" style="max-width: 20px !important;" class="w-5 h-5" src="../../public/icons/menu.svg" alt="menu icon">
        </li>
        <li class="mx-1 <?= $fileName == 'purchase.php' ? 'bg-gray-400' : 'bg-gray-200' ?> hover:bg-gray-400 text-sm font-bold">
            <a class="p-2 menu_item flex items-center gap-2" href="./purchase.php">
                <img class="hidden sm:inline-block" src="./assets/icons/add.svg" alt="add icon">
                ورود کالا
            </a>
        </li>
        <li class="mx-1 <?= $fileName == 'sell.php' ? 'bg-gray-400' : 'bg-gray-200' ?> hover:bg-gray-400 text-sm font-bold">
            <a class="p-2 menu_item flex items-center gap-2" href="sell.php">
                <img class="hidden sm:inline-block" src="./assets/icons/subtract.svg" alt="add icon">
                خروج کالا
            </a>
        </li>
        <li class="mx-1 <?= $fileName == 'pendingSells.php' ? 'bg-gray-400' : 'bg-rose-400' ?> hover:bg-gray-400 text-sm font-bold">
            <a class="p-2 menu_item flex items-center gap-2" href="pendingSells.php">
                <img class="hidden sm:inline-block" src="./assets/icons/subtract.svg" alt="add icon">
                منتظر خروج
            </a>
        </li>
        <li class="mx-1 <?= $fileName == 'newSell.php' ? 'bg-gray-400' : 'bg-rose-400' ?> hover:bg-gray-400 text-sm font-bold">
            <a class="p-2 menu_item flex items-center gap-2" href="newSell.php">
                <img class="hidden sm:inline-block" src="./assets/icons/subtract.svg" alt="add icon">
                خروج کالا جدید
            </a>
        </li>
        <li class="mx-1 <?= $fileName == 'purchaseReport.php' ? 'bg-gray-400' : 'bg-gray-200' ?> hover:bg-gray-400 text-sm font-bold hidden sm:flex gap-2">
            <div class="dropdown">
                <a class="p-2 menu_item flex items-center gap-2">
                    <img class="hidden sm:inline-block" src="./assets/icons/chart.svg" alt="add icon">
                    گزارش ورود
                    <img src="./assets/icons/down_arrow.svg" alt="down arrow" srcset="">
                </a>
                <div class="dropdown_container">
                    <ul class="dropdown_menu p-0 bg-gray-800 border border-gray-800">
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
            </div>
        </li>
        <li class="mx-1 <?= $fileName == 'sellsReport.php' ? 'bg-gray-400' : 'bg-gray-200' ?> hover:bg-gray-400 text-sm font-bold hidden sm:flex gap-2">
            <div class="dropdown">
                <a class="p-2 menu_item flex items-center gap-2">
                    <img class="hidden sm:inline-block" src="./assets/icons/chart_report.svg" alt="add icon">
                    گزارش خروج
                    <img src="./assets/icons/down_arrow.svg" alt="down arrow" srcset="">
                </a>
                <div class="dropdown_container">
                    <ul class="dropdown_menu bg-gray-800 border border-gray-800">
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
            </div>
        </li>
        <li class="mx-1 <?= $fileName == 'existingReport.php' ? 'bg-gray-400' : 'bg-gray-200' ?> hover:bg-gray-400 text-sm font-bold">
            <a class="p-2 menu_item flex items-center gap-2" href="existingReport.php">
                <img class="hidden sm:inline-block" src="./assets/icons/stock.svg" alt="add icon">
                موجودی کالا
            </a>
        </li>
        <li class="mx-1 <?= $fileName == 'callcenter.php' ? 'bg-gray-400' : 'bg-gray-200' ?> hover:bg-gray-400 text-sm font-bold">
            <a class="p-2 menu_item flex items-center gap-2" target="_blank" href="../callcenter">
                <img class="hidden sm:inline-block" src="./assets/icons/call_center.svg" alt="add icon">
                مرکز تماس
            </a>
        </li>
        <?php if ($_SESSION['financialYear'] != jdate('Y', '', '', '', 'en')) : ?>
            <li class="mx-1 text-sm font-bold">
                <a class="px-4 py-2 bg-rose-600 ml-2 text-white text-xs">
                    سال مالی <?= $_SESSION['financialYear'] ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="relative hidden sm:flex items-center">
        <!-- TV Button -->
        <img src="../../public/icons/tv.svg"
            class="cursor-pointer ml-2"
            alt="close menu icon"
            onclick="toggleTV()">

        <?php
        $profile = '../../public/userimg/default.png';
        if (file_exists("../../public/userimg/" . $_SESSION['id'] . ".jpg")) {
            $profile = "../../public/userimg/" . $_SESSION['id'] . ".jpg";
        }
        ?>

        <!-- Profile picture -->
        <div class="relative">
            <img id="profileBtn"
                class="w-9 h-9 rounded-full border-2 border-gray-900 cursor-pointer"
                src="<?= $profile ?>"
                title="<?= $_SESSION['username'] ?>"
                alt="user image"
                onclick="toggleProfileDropdown()" />

            <!-- Dropdown (aligned to the right of profile picture) -->
            <div id="profileDropdown"
                class="absolute top-full left-full ml-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
                <ul class="py-2 text-sm text-gray-700">
                    <li>
                        <a href="../profile/index.php" class="block px-4 py-2 hover:bg-gray-100">👤 پروفایل کاربری</a>
                    </li>
                    <li>
                        <a href="/settings.php" class="block px-4 py-2 hover:bg-gray-100">⚙️ تنظیمات</a>
                    </li>
                    <li>
                        <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">🚪 خروج</a>
                    </li>
                </ul>
            </div>
        </div>
        <script>
            function toggleProfileDropdown() {
                const dropdown = document.getElementById("profileDropdown");
                dropdown.classList.toggle("hidden");

                // Close if clicked outside
                document.addEventListener("click", function(event) {
                    const button = document.getElementById("profileBtn");
                    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                        dropdown.classList.add("hidden");
                    }
                }, {
                    once: true
                });
            }
        </script>
    </div>
</nav>