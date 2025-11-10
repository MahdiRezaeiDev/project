<?php
$pageTitle = "تلگرام خرید";
$iconUrl = 'orange-telegram.png';
require_once './components/header.php';
require_once '../../app/controller/callcenter/TelegramPartnerController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<style>
    #message_content {
        direction: ltr;
    }

    .hidden {
        display: none;
    }

    #success_edit,
    #success_create {
        opacity: 0;
        transition: all 0.5s linear;
    }

    .sticky_nav {
        position: sticky;
        top: 100px;
        /* Set your desired background color */
        z-index: 100;
        /* Set a higher z-index to make sure it's on top of other elements */
    }
</style>

<div class="mx-4 bg-white rounded-lg shadow-lg">
    <div class="flex bg-gray-800  rounded-t-lg p-2">
        <button class="px-4 py-2 text-white ml-2 tab-header" data-tab="tab1" onclick="openTab(this)">
            ارسال پیام
        </button>
        <button class="px-4 py-2 text-white ml-2 tab-header" data-tab="tab3" onclick="openTab(this); displayLocalData();">
            لیست مخاطبین
        </button>
        <button class="px-4 py-2 text-white ml-2 tab-header" data-tab="tab2" onclick="openTab(this); getContacts();">
            بروزرسانی لیست مخاطبین
        </button>
        <button class="px-4 py-2 text-white ml-2 tab-header" data-tab="tab4" onclick="openTab(this); displayCategories();">
            مدیریت دسته بندی ها
        </button>
    </div>
    <div class="p-4">
        <div id="tab1" class="tab-content">
            <div class="bg-gray-100 p-5 rounded-lg">
                <h1 class="text-xl py-2">ارسال پیام به گروه مخاطبین</h1>
                <form action="post" id="message" class="flex flex-col">
                    <textarea style="direction: ltr !important;" required class="w-full border-2 border-gray-400 p-3" name="message_content" id="message_content" cols="20" rows="3" placeholder="متن پیام خود را وارد کنید..."></textarea>
                    <div class="flex flex-row-reverse justify-between">
                        <span class="cursor-pointer rounded-sm bg-blue-600 w-32 text-white px-3 py-2 my-2 text-center" onclick="sendMessage()">ارسال پیام</span>
                        <p id="success" class="hidden text-white bg-green-700 px-5 py-2 my-3">پیام موفقانه ارسال شد !!</p>
                        <p id="error" class="hidden text-white bg-red-700 px-5 py-2 my-3"> لطفا متن پیام و دریافت کنندگان پیام را مشخص کنید!</p>
                    </div>
                </form>
            </div>
            <div class="my-3 bg-gray-100 p-5 rounded-lg h-screen">
                <table class="table-fixed w-full">
                    <thead>
                        <tr class="bg-gray-800 text-white font-semibold">
                            <th class="py-3" colspan="<?= count($categories) ?>">انتخاب گروه دریافت گنندگان پیام</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?= count($categories) ?>" class="p-3 text-gray-600 w-1/12 align-top">
                                <label class="cursor-pointer pl-5" for="all">
                                    <input type="checkbox" class="category_identifier" onclick="clickAll(this)" name="all" id="all">
                                    همه موارد
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <!-- Dynamic Category Checkboxes -->
                            <?php foreach ($categories as $category) : ?>
                                <td class="p-3 text-gray-600 w-1/12 align-top">
                                    <label class="cursor-pointer pl-5" for="<?= htmlspecialchars($category['id']) ?>">
                                        <input type="checkbox" class="category_identifier" onclick="updateCategory(this)" name="<?= htmlspecialchars($category['id']) ?>" id="<?= htmlspecialchars($category['id']) ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </label>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($categories as $category) : ?>
                                <td class="w-1/12 align-top" id="<?= htmlspecialchars($category['id']) ?>_result"></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab2" class="tab-content hidden">
            <div class="flex justify-between">
                <h1 class="text-xl py-2">مخاطبین اخیر تلگرام</h1>
                <span class="flex items-center cursor-pointer text-white bg-gray-800 rounded-md px-3" onclick="hardRefresh()">
                    بروزرسانی
                    <i class="material-icons ">sync</i>
                </span>
            </div>
            <div class="my-3">
                <table class="table-fixed min-w-full text-sm font-light h-screen overflow-scroll">
                    <thead class="sticky_nav sticky bg-gray-800 border border-gray-600">
                        <tr>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                شماره
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                نام
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                نام کاربری
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                پروفایل
                            </th>
                            <?php
                            foreach ($categories as $category) : ?>
                                <th scope="col" class="text-white font-semibold p-3 text-center">
                                    <?= $category['name'] ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody id="contact" class="h-screen border border-dashed border-gray-600">
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab3" class="tab-content hidden">
            <h1 class="text-xl py-2">لیست مخاطبین موجود در سیستم</h1>
            <div class="my-3">
                <table class="table-fixed min-w-full">
                    <thead class="sticky_nav sticky bg-gray-800 border border-gray-600">
                        <tr>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                شماره
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                نام
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                نام کاربری
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                پروفایل
                            </th>
                            <?php foreach ($categories as $category) : ?>
                                <th scope="col" class="text-white font-semibold p-3 text-center">
                                    <?= $category['name'] ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody id="initial_data" class="border border-dashed border-gray-600">
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab4" class="tab-content hidden">
            <div class="flex justify-between px-1">
                <h1 class="text-xl py-2">دسته بندی های موجود</h1>
                <div>
                    <form action="#" id="save_category">
                        <input class="border-2 p-2 mx-2" type="text" name="category_name" id="category_name" placeholder="اسم کتگوری...">
                        <button class="text-white bg-green-600 py-2 px-4 rounded-md" onclick="createCategoryForm()">
                            افزودن
                        </button>

                    </form>
                    <form action="#" id="edit_category" class="hidden">
                        <input type="hidden" id="category_id" value="" />
                        <input class="border-2 p-2 mx-2" type="text" name="category_name" id="edit_category_name" placeholder="اسم کتگوری...">
                        <button class="text-white bg-green-600 py-2 px-4 rounded-md" onclick="editCategoryForm()">
                            ویرایش
                        </button>
                    </form>
                    <p id="success_create" class="text-green-500 text-xs p-2">دسته بندی با موفقیت ثبت شد.</p>
                    <p id="success_edit" class="text-green-500 text-xs p-2">دسته بندی با موفقیت ویرایش شد.</p>
                </div>
            </div>
            <div class="my-3">
                <table class="table-fixed min-w-full rounded-lg">
                    <thead class="font-medium sticky bg-gray-800 border border-gray-600">
                        <tr>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                شماره
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                اسم دسته بندی
                            </th>
                            <th scope="col" class="text-white font-semibold p-3 text-center">
                                عملیات
                            </th>
                        </tr>
                    </thead>
                    <tbody id="category_data" class="border border-dashed border-gray-600">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function clickAll(element) {
        // Get all checkboxes on the page
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');

        // Loop through checkboxes and check each one
        node = null;
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = element.checked;
            node = checkbox;
        });

        updateCategory(node);
    }

    const partners_json = null;

    function openTab(element) {
        const tabs = document.querySelectorAll('.tab-content');
        const headers = document.querySelectorAll('.tab-header');
        headers.forEach(function(header) {
            header.classList.remove('bg-gray-900');
        });
        element.classList.add('bg-gray-900');

        tabs.forEach(tab => {
            if (tab.id === element.getAttribute('data-tab')) {
                tab.classList.remove('hidden');
            } else {
                tab.classList.add('hidden');
            }
        });
    }
</script>
<script src="./assets/js/telegramPartner.js"></script>
<?php
require_once './components/footer.php';
