<?php
$pageTitle = "ایجاد حساب کاربری";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../app/controller/callcenter/ManageUsersController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<div class="bg-gray-200 shadow-md w-1/2 mx-auto overflow-hidden">
    <div class="flex items-center justify-between p-5 bg-gray-800">
        <h2 class="text-xl font-semibold text-white flex items-center gap-2">
            <i class="material-icons font-semibold text-cyan-600">person_add</i>
            ایجاد حساب کاربری
        </h2>
    </div>
    <div class="p-5">
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">

            <div class="grid grid-cols-2 gap-3">
                <input type="hidden" name="createUser" value="createUser" hidden>
                <div class="mb-3">
                    <label class="block font-medium text-sm text-gray-700">
                        نام
                    </label>
                    <input name="name" class="border-2 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="serial" type="text" />
                    <p class="mt-2"></p>
                </div>
                <!-- Price -->
                <div class="mb-3">
                    <label class="block font-medium text-sm text-gray-700">
                        نام خانوادگی
                    </label>
                    <input name="family" class="border-2 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="price" type="text" />
                    <p class="mt-2"> </p>
                </div>
                <!-- Weight -->
                <div class="mb-3">
                    <label class="block font-medium text-sm text-gray-700">
                        نام کاربری
                        <span class="text-red-500">*</span>
                        <p class="mt-2">
                            <?= $username_error ? '<p class="text-red-600 text-xs font-semibold"> نام کاربری تکراری است ! </p>' : '' ?>
                        </p>
                    </label>
                    <input style="direction: ltr !important;" required name="username" class="border-2 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="weight" type="text" />

                </div>

                <div class="mb-3">
                    <label class="block font-medium text-sm text-gray-700">
                        پروفایل
                        <p class="mt-2"> <?= $type_error ? '<P class="text-red-600 text-xs font-semibold"> تنها فایل های jpg قابل آپلود می باشد</P>' : '' ?></p>
                        <p class="mt-2"> <?= $exist_file_error ? '<P class="text-red-600 text-xs font-semibold">فایلی با این اسم از قبل موجود است</P>' : '' ?></p>
                    </label>
                    <input name="profile" class="border-2 mt-1 text-sm block w-full bg-white border-gray-300 shadow-sm px-3 py-2" id="profile" type="file" />
                </div>
                <!-- Mobis -->
                <div class="mb-3 relative">
                    <label class="block font-medium text-sm text-gray-700">
                        رمزعبور
                        <span class="text-red-500">*</span>
                    </label>
                    <i onclick="togglePass(this)" class="material-icons cursor-pointer" style="position: absolute; left:5px; top: 40%">remove_red_eye</i>
                    <input style="direction: ltr !important;" required name="password" minlength="5" maxlength="50" class="border-2 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="mobis" type="password" />
                </div>
                <!-- Korea section -->
                <div class="mb-3">
                    <label class="block font-medium text-sm text-gray-700">
                        نوعیت حساب کاربری
                        <span class="text-red-500">*</span>
                    </label>
                    <select name="type" class="border-2 p-2 text-sm mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" id="status">
                        <option value="1" class="text-sm">
                            تماس با بازار </option>

                        <option value="2" class="text-sm">
                            حسابداری </option>
                        <option value="3" class="text-sm">
                            انبار </option>

                        <option value="4" class="text-sm">
                            مدیر </option>

                    </select>
                    <p class="mt-2"> </p>
                </div>
                <div class="flex justify-between items-center">
                    <button class="text-sm bg-green-700 text-white py-2 px-3 hover:bg-green-600" type="submit">ایجاد حساب کاربری</button>
                    <?= $success ? '<p class="bg-green-600 text-white px-5 py-2 rounded-md text-sm font-semibold">حساب کاربری موفقانه ایجاد شد</p>' : ''; ?>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    function togglePass(element) {
        const target = element.nextElementSibling;
        const inputType = target.type;

        if (inputType === 'password') {
            target.type = 'test';
            return;
        }

        target.type = 'password';
    }
</script>
<?php
require_once './components/footer.php';
