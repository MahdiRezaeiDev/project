<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}
// Get the file name
$fileName = basename($_SERVER['REQUEST_URI']);

// Get the directory path
$directory = dirname($_SERVER['REQUEST_URI']);

// Split the directory path into parts
$directoryParts = explode('/', trim($directory, '/'));

// Get the parent directory (second last element)
$parentDirectory = $directoryParts[count($directoryParts) - 1];

// Determine the correct append path
$append = ($parentDirectory === 'callcenter') ? './' : '../callcenter/';
?>

<nav id="main_nav" class="fixed top-0 left-0 right-0 z-50 p-2 flex justify-between bg-white shadow-md">
    <ul class="flex items-center">
        <li class="mx-1 bg-gray-200 text-sm font-bold">
            <img id="close" onclick="toggleSidebar()" class="cursor-pointer p-2" id="open_aside_icon" src="../../public/icons/menu.svg" alt="menu icon">
        </li>
        <li class="mx-1 hidden sm:block <?= $fileName == 'purchase.php' ? 'bg-gray-400' : 'bg-gray-200' ?> text-sm font-bold">
            <a class="p-2 flex items-center gap-3" href="<?= $append ?>cartable.php">
                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/cartable.svg" alt="telegram icon">
                کارتابل
            </a>
        </li>
        <li class="mx-1 cursor-pointer <?= in_array($fileName, ['generalCall.php', 'callToBazar.php', 'last-calling-time.php']) ? 'bg-gray-400' : 'bg-gray-200' ?> text-sm font-bold hidden sm:flex items-center gap-3">
            <div class="dropdown">
                <a class="flex p-2 items-center gap-2 cursor-pointer" href="<?= $append ?>callToBazar.php">
                    <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/telephone.svg" alt="telegram icon">
                    تماس
                    <img src="../inventory/assets/icons/down_arrow.svg" alt="down arrow">
                </a>
                <div class="dropdown_container ">
                    <ul class="dropdown_menu bg-gray-800 border border-gray-800">
                        <li class="hover:bg-gray-900 text-white text-sm font-bold">
                            <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>generalCall.php">
                                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/handshake.svg" alt="add icon">
                                تماس عمومی
                            </a>
                        </li>
                        <li class="hover:bg-gray-900 text-white text-sm font-bold">
                            <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>callToBazar.php">
                                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/auto.svg" alt="add icon">
                                تماس بازار
                            </a>
                        </li>
                        <li class="hover:bg-gray-900 text-white text-sm font-bold">
                            <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>saveBazarPrice.php">
                                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/auto.svg" alt="add icon">
                                ثبت قیمت بازار
                            </a>
                        </li>
                        <li class="hover:bg-gray-900 text-white text-sm font-bold">
                            <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>last-calling-time.php">
                                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/latest.svg" alt="add icon">
                                آخرین تماس ها
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </li>
        <li class="dropdown mx-1 cursor-pointer <?= in_array($fileName, ['telegramPartner.php', 'telegramProcess.php', 'dashboard.php']) ? 'bg-gray-400' : 'bg-gray-200' ?> text-sm font-bold flex items-center gap-3 pl-2">
            <a class="p-2 flex items-center gap-2 cursor-pointer" href="<?= $append ?>telegramPartner.php">
                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/telegram.svg" alt="telegram icon">
                تلگرام
                <img src="../inventory/assets/icons/down_arrow.svg" alt="down arrow">
            </a>
            <div class="dropdown_container ">
                <ul class="dropdown_menu bg-gray-800 border border-gray-800">
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>telegramPartner.php">
                            <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/handshake.svg" alt="add icon">
                            همکار تلگرام
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="../telegram/dashboard.php">
                            <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/auto.svg" alt="add icon">
                            پیام خودکار
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>telegramProcess.php">
                            <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/attention.svg" alt="add icon">
                            قیمت های تلگرام
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="mx-1 hidden sm:block <?= $fileName == 'askedPrices.php' ? 'bg-gray-400' : 'bg-gray-200' ?> text-sm font-bold ">
            <a class="p-2 flex items-center gap-2" href="<?= $append ?>askedPrices.php">
                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/price.svg" alt="telegram icon">
                قیمت گرفته شده
            </a>
        </li>
        <li class="dropdown mx-1 cursor-pointer <?= in_array($fileName, ['factor.php']) ? 'bg-gray-400' : 'bg-gray-200' ?> text-sm font-bold flex items-center gap-3 pl-2">
            <a class="p-2 flex items-center gap-2 cursor-pointer" href="<?= $append ?>factor.php">
                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/bill.svg" alt="telegram icon">
                فاکتور
                <img src="../inventory/assets/icons/down_arrow.svg" alt="down arrow">
            </a>
            <div class="dropdown_container ">
                <ul class="dropdown_menu bg-gray-800 border border-gray-800">
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>factor.php">
                            <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/list.svg" alt="add icon">
                            شماره فاکتور
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="../factor/index.php">
                            <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/authority.svg" alt="add icon">
                            مدیریت فاکتور
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="../factor/payments.php">
                            <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/authority.svg" alt="add icon">
                            واریزی ها
                        </a>
                    </li>
                    <li class="hover:bg-gray-900 text-white text-sm font-bold">
                        <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="../factor/deliveries.php">
                            <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/delivery.svg" alt="add icon">
                            مرسولات
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="mx-1 <?= $fileName == 'givenPrice.php' ? 'bg-gray-400' : 'bg-gray-200' ?> text-sm font-bold">
            <a target="_self" class="p-2 flex items-center gap-2" href="<?= $append ?>givenPrice.php">
                <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/approve.svg" alt="telegram icon">
                قیمت دستوری
            </a>
        </li>
        <li class="mx-1 hidden sm:block <?= $fileName == 'pricesHistory.php' ? 'bg-gray-400' : 'bg-gray-200' ?> text-sm font-bold">
            <div class="dropdown">
                <a class="p-2 flex items-center gap-2" target="_blank" href="<?= $append ?>pricesHistory.php">
                    <img class="hidden sm:inline-block" src="../../layouts/callcenter/icons/history.svg" alt="telegram icon">
                    تاریخچه
                    <img src="../inventory/assets/icons/down_arrow.svg" alt="down arrow">
                </a>
                <div class="dropdown_container ">
                    <ul class="dropdown_menu bg-gray-800 border border-gray-800">
                        <li class="hover:bg-gray-900 text-white text-sm font-bold">
                            <a class="p-3 hover:bg-gray-900 flex items-center gap-2" href="<?= $append ?>priceSearch.php">
                                تاریخچه قیمت دستوری
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
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
                        <a href="/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">🚪 خروج</a>
                    </li>
                </ul>
            </div>
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
</nav>