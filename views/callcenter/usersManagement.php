<?php
$pageTitle = "مدیریت کاربران";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/ManageUsersController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$users = getUsers();


function getRolesArray() {
    return [
        "1"  => "خرید",
        "2"  => "فروش",
        "3"  => "حسابداری",
        "4"  => "انبار",
        "5"  => "مدیر کل",
        "10" => "ادمین ارشد"
    ];
}


function getRoleName($roll) {
    $roles = [
    "1" => "خرید",
    "2" => "فروش",
    "3" => " حسابداری ",
    "4" => "انبار",
    "5" => "مدیر کل",
    "10" => "ادمین ارشد"
    ];

    return $roles[$roll] ?? "نقش نامعتبر";
}

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

    .imgpermission {
        filter: opacity(0.5);
    }

    input[type="checkbox"].switch {
        appearance: none;
        -webkit-appearance: none;
        width: 42px;
        height: 22px;
        margin-right: 3px;
        background: #d1d5db;
        /* bg-gray-300 */
        border-radius: 9999px;
        position: relative;
        outline: none;
        cursor: pointer;
        vertical-align: middle;
        transition: background-color 0.3s ease;
        margin: 3px 5px 0px 18px;
    }

    /* دکمه سفید درون سوئیچ */
    input[type="checkbox"].switch::before {
        content: "";
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;

        background: #fff;
        border-radius: 50%;
        transition: transform 0.3s ease;
        box-shadow: 0 0 2px rgba(0, 0, 0, 0.2);
    }

    /* حالت روشن */
    input[type="checkbox"].switch:checked {
        background: #22c55e;

    }

    input[type="checkbox"].switch:checked::before {
        transform: translateX(20px);
    }


    .permission-item {
        display: flex;
        align-items: center;

        padding: 6px 0;
    }

    .permission-item label {
        font-size: 14px;
        color: #333;
    }

    .permission-item img {
        filter: opacity(0.3);
        width: 18px;
        margin: 2px;
    }

    body {
        background: #f0f0f1;
    }

</style>



<div class="bg-white w-3/5 " style="margin:0 auto;">
    <div class="flex items-center justify-between px-2 py-4">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center gapx-2 py-4">
            <i class="material-icons font-semibold text-orange-400">security</i>
            مدیریت دسترسی کاربران
        </h2>
        <a href="./createUserProfile.php" class="bg-gray-600 text-white  py-2 px-3 rounded-lg text-sm">ثبت کاربر جدید +</a>
    </div>
    <div class="table-wrapper rounded-lg shadow-md m-3">
        <table class="table-fixed min-w-full text-sm  font-light">
            <thead id="blur" class="font-medium  top-12" style="z-index: 99;">
                <tr class="bg-gray-600 text-right" style="filter: none;">

                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        کاربر
                    </th>

                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        مدیریت کاربران
                    </th>


                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        سطح دسترسی
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        ویرایش
                    </th>
                    <th scope="col" class="text-white px-2 py-3 text-xs font-semibold">
                        نقش کاربر
                    </th>
                </tr>
            </thead>
            <tbody id="results" class="divide-y divide-gray-300 text-right">
                <?php
                foreach ($users as $index => $user) :
                    $auth = json_decode($user['auth'], true); ?>
                <tr class="even:bg-gray-200">

                    <?php
            $id=$user['id'];

        $profile = '../../public/userimg/default.png';
        if (file_exists("../../public/userimg/" . $id . ".jpg")) {
            $profile = "../../public/userimg/" . $id . ".jpg";
        }
        ?>
                    <td class='px-2 py-4 flex'>
                        <img id="profileBtn" class="w-12 h-12 rounded-lg ml-2 shadow-md" src="<?= $profile ?>" alt="user image" />


                        <div class="px-2">

                            <P style="font-weight: 900;line-height: 2em;"> <strong><?= $user['name'] . ' ' . $user['family'] ?></strong></P>
                            <P style="font-size: 13px; color: #818181;"><?= $user['username'] ?></P>
                        </div>
                    </td>

                    <td class="px-2 py-4">
                        <input type="checkbox" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" <?= $auth['usersManagement'] ? 'checked' : '' ?> data-authority="usersManagement" data-user="<?= $user['id'] ?>" data-icon="./assets/img/usersManagement.png">
                    </td>




                    <td class=' px-2 py-4 '>
                        <?php $modalId = 'modal-' . $user['id']; ?>
                        <div class="flex ">
                            <div id="permission" class="w-1/2"></div>
                            <!-- Modal toggle -->
                            <button data-modal-target="<?= $modalId ?>" data-modal-toggle="<?= $modalId ?>" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 mr-4 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 py-2 " type="button">
                                دسترسی
                            </button>
                        </div>
                        <!-- Main modal -->
                        <div id="<?= $modalId ?>" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden  shadow-sm fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                            <div class="relative p-4 w-full max-w-2xl max-h-full">

                                <!-- Modal content -->
                                <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 border-gray border-2">

                                    <!-- Modal header -->
                                    <div class="flex items-center justify-between p-2 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200 bg-gray-100">
                                        <div class="dark:bg-gray-800 rounded-2xl shadow-md p-2 w-full max-w-sm mx-auto text-center flex">
                                            <img id="profileBtn" class="w-20 h-20 rounded-lg ml-4" src="<?= $profile ?>" alt="user image" />
                                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                                <?= htmlspecialchars($user['name']) ?>

                                                <?= htmlspecialchars($user['family']) ?>
                                                <p class="text-gray-600 dark:text-gray-300 text-sm mt-2">
                                                    <span class="font-semibold text-gray-800 dark:text-gray-200">نام کاربری:</span>
                                                    <?= htmlspecialchars($user['username']) ?>
                                                </p>
                                            </h3>


                                        </div>
                                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                                            <button data-modal-hide="<?= $modalId ?>" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700">
                                                بستن
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Modal body -->
                                    <div class="p-4 md:p-5 space-y-4">



                                        <div class="p-2 bg-gray-700 text-white text-center rounded pr-2">
                                            <p>گزارش ها</p>
                                        </div>
                                        <div class=" flex bg-gray-100 shadow rounded">

                                            <div class="permission-item">
                                                <img src="./assets/img/sellsReport.png">
                                                <label for="sellsReport">گزارش خروج</label>
                                                <input id="sellsReport" class="switch user-<?= $user['id'] ?>" type="checkbox" onclick="updateUserAuthority(this)" <?= $auth['sellsReport'] ? 'checked' : '' ?> data-authority="sellsReport" data-user="<?= $user['id'] ?>" data-icon="./assets/img/sellsReport.png">
                                            </div>

                                            <div class="permission-item">
                                                <img src="./assets/img/purchaseReport.png">
                                                <label for="purchaseReport">گزارش ورود</label>
                                                <input id="purchaseReport" class="switch user-<?= $user['id'] ?>" type="checkbox" onclick="updateUserAuthority(this)" <?= $auth['purchaseReport'] ? 'checked' : '' ?> data-authority="purchaseReport" data-user="<?= $user['id'] ?>" data-icon="./assets/img/purchaseReport.png">
                                            </div>

                                        </div>


                                        <div class="p-2 bg-gray-700 text-white text-center rounded">
                                            <p>تلگرام</p>
                                        </div>
                                        <div class=" flex bg-gray-100 shadow rounded pr-2">

                                            <div class="permission-item">
                                                <img src="./assets/img/hamkarTelegram.png">
                                                <label for="telegramPartner">تلگرام خرید</label>
                                                <input id="telegramPartner" class="switch user-<?= $user['id'] ?>" type="checkbox" onclick="updateUserAuthority(this)" <?= (isset($auth['telegramPartner']) && $auth['telegramPartner']) ? 'checked' : '' ?> data-authority="telegramPartner" data-user="<?= $user['id'] ?>" data-icon="./assets/img/hamkarTelegram.png">
                                            </div>

                                            <!--<div class="permission-item">
                                                <img src="./assets/img/telegramPartner.png">

                                                <label for="telegramProcess">تلگرام</label>
                                                <input id="telegramProcess" class="switch user-<?= $user['id'] ?>" type="checkbox" onclick="updateUserAuthority(this)" <?= (isset($auth['telegramProcess']) && $auth['telegramProcess']) ? 'checked' : '' ?> data-authority="telegramProcess" data-user="<?= $user['id'] ?>" data-icon="./assets/img/telegramPartner.png">
                                            </div> -->

                                        </div>



                                        <div class="p-2 bg-gray-700 text-white text-center rounded">
                                            <p>قیمت گذاری</p>
                                        </div>
                                        <div class=" flex bg-gray-100 shadow rounded pr-2">

                                            <div class="permission-item">
                                                <img src="./assets/img/defineExchangeRate.png">
                                                <label for="defineExchangeRate">
                                                    تغییرات نرخ دلار </label>
                                                <input id="defineExchangeRate" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['defineExchangeRate'] ? 'checked' : '' ?> data-authority="defineExchangeRate" data-user='<?= $user['id'] ?>' data-icon="./assets/img/defineExchangeRate.png">
                                            </div>
                                            <div class="permission-item">
                                                <img src="./assets/img/priceRates.png">
                                                <label for="priceRates">

                                                    نمایش قیمت دلار </label>
                                                <input id="priceRates" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['priceRates'] ? 'checked' : '' ?> data-authority="priceRates" data-user='<?= $user['id'] ?>' data-icon="./assets/img/priceRates.png">
                                            </div>
                                            <div class="permission-item">
                                                <img src="./assets/img/givePrice.png">
                                                <label for="givePrice">
                                                    قیمت دستوری

                                                </label>
                                                <input id="givePrice" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['givePrice'] ? 'checked' : '' ?> data-authority="givePrice" data-user='<?= $user['id'] ?>' data-icon="./assets/img/givePrice.png">
                                            </div>
                                        </div>


                                        <div class="p-2 bg-gray-700 text-white text-center rounded">
                                            <p>انبار</p>
                                        </div>
                                        <div class=" flex bg-gray-100 shadow rounded pr-2">

                                            <div class="w-1/2">
                                                <div class="permission-item">
                                                    <img src="./assets/img/stockAdjustment.png">
                                                    <label for="stockAdjustment">
                                                        انبار گردانی
                                                    </label>
                                                    <input id="stockAdjustment" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['stockAdjustment'] ? 'checked' : '' ?> data-authority="stockAdjustment" data-user='<?= $user['id'] ?>' data-icon="./assets/img/stockAdjustment.png">
                                                </div>
                                                <div class="permission-item">
                                                    <img src="./assets/img/stockAdjustment.png">
                                                    <label for="transferGoods">
                                                        انتقال به انبار
                                                    </label>
                                                    <input id="transferGoods" class=" switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['transferGoods'] ? 'checked' : '' ?> data-authority="transferGoods" data-user='<?= $user['id'] ?>' data-icon="./assets/img/stockAdjustment.png">
                                                </div>


                                                <div class="permission-item">
                                                    <img src="./assets/img/stockAdjustment.png">
                                                    <label for="sell"> ثبت خروج کالا
                                                    </label>
                                                    <input id="sell" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= isset($auth['sell']) && $auth['sell'] ? 'checked' : '' ?> data-authority="sell" data-user='<?= $user['id'] ?>' data-icon="./assets/img/stockAdjustment.png">
                                                </div>
                                            </div>
                                            <div class="w-1/2">
                                                <div class="permission-item">
                                                    <img src="./assets/img/purchase.png">
                                                    <label for="purchase">
                                                        ثبت ورود کالا
                                                    </label>
                                                    <input id="purchase" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['purchase'] ? 'checked' : '' ?> data-authority="purchase" data-user='<?= $user['id'] ?>' data-icon="./assets/img/purchase.png">
                                                </div>

                                                <div class="permission-item">
                                                    <img src="./assets/img/relationships.png">
                                                    <label for="relationships">
                                                        رابطه اجناس
                                                    </label>

                                                    <input id="relationships" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['relationships'] ? 'checked' : '' ?> data-authority="relationships" data-user='<?= $user['id'] ?>' data-icon="./assets/img/relationships.png">
                                                </div>
                                                <div class="permission-item">
                                                    <img src="./assets/img/transferReport.png">
                                                    <label for="transferReport">
                                                        گزارش انتقالات به انبار
                                                    </label>
                                                    <input id="transferReport" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['transferReport'] ? 'checked' : '' ?> data-authority="transferReport" data-user='<?= $user['id'] ?>' data-icon="./assets/img/transferReport.png">
                                                </div>
                                            </div>
                                            <div class="w-1/2">
                                                <div class="permission-item">
                                                    <img src="./assets/img/requiredGoods.png">
                                                    <label for="requiredGoods">
                                                        نیاز به انتقال
                                                    </label>
                                                    <input id="requiredGoods" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['requiredGoods'] ? 'checked' : '' ?> data-authority="requiredGoods" data-user='<?= $user['id'] ?>' data-icon="./assets/img/requiredGoods.png">
                                                </div>

                                                <div class="permission-item">
                                                    <img src="./assets/img/generalRequiredGoods.png">
                                                    <label for="generalRequiredGoods">
                                                        گزارش کسرات
                                                    </label>
                                                    <input id="generalRequiredGoods" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= $auth['generalRequiredGoods'] ? 'checked' : '' ?> data-authority="generalRequiredGoods" data-user='<?= $user['id'] ?>' data-icon="./assets/img/generalRequiredGoods.png">

                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-2 bg-gray-700 text-white text-center rounded">
                                            <p> پرینت</p>
                                        </div>
                                        <div class=" flex bg-gray-100 shadow rounded pr-2">


                                            <div class="permission-item">
                                                <img src="./assets/img/hamkarprint.svg">

                                                <label for="hamkarprint">پرینت همکار</label>
                                                <input id="hamkarprint" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= (isset($auth['hamkarprint']) ? $auth['hamkarprint'] : '') ? 'checked' : '' ?> data-authority="hamkarprint" data-user='<?= $user['id'] ?>' data-icon="./assets/img/hamkarprint.svg">
                                            </div>

                                            <div class="permission-item">
                                                <img src="./assets/img/hamkarprint.svg">

                                                <label for="customerprint">پرینت مصرف کننده </label>
                                                <input id="customerprint" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= (isset($auth['customerprint']) ? $auth['customerprint'] : '') ? 'checked' : '' ?> data-authority="customerprint" data-user='<?= $user['id'] ?>' data-icon="./assets/img/hamkarprint.svg">
                                            </div>


                                            <div class="permission-item">
                                                <img src="./assets/img/readonly.svg">

                                                <label for="readonly">فقط خواندن</label>
                                                <input id="readonly" class="switch user-<?= $user['id'] ?>" onclick="updateUserAuthority(this)" type="checkbox" <?= (isset($auth['readonly']) ? $auth['readonly'] : '') ? 'checked' : '' ?> data-authority="readonly" data-user='<?= $user['id'] ?>' data-icon="./assets/img/readonly.svg">
                                            </div>

                                        </div>




                                    </div>


                                </div>
                            </div>
                        </div>
                    </td>
                    <td class='flex px-2 py-4'>
                        <a href="./updateUserProfile.php?user=<?= $user['id'] ?>">
                            <i data-user="<?= $user['id'] ?>" class="material-icons cursor-pointer text-indigo-600 hover:text-indigo-800">edit</i>
                        </a>
                        <i onclick="deleteUser(this)" data-user="<?= $user['id'] ?>" class="material-icons cursor-pointer text-red-600 hover:text-red-800">do_not_disturb_on</i>
                        <img title="اشتراک لینک حضور و غیاب" onclick="shareRegisterToken(<?= $user['id'] ?>)" class="w-5 h-5 cursor-pointer" src="./assets/img/share.svg" alt="share icon">
                        <?php if ($user['access_token']): ?>
                        <img title="حذف توکن ثبت موبایل کاربر" onclick="DeleteRegisterToken(<?= $user['id'] ?>)" class="w-5 h-5 cursor-pointer" src="./assets/img/token.svg" alt="share icon">
                        <?php endif; ?>
                    </td>
                    <td class='px-2 py-4 '>


                        <div class="px-2">

                            <?php $roles = getRolesArray(); ?>

                            <div>

                                <select id="user-role-<?= $user['id'] ?>" class="user-role user-<?= $user['id'] ?>" data-user="<?= $user['id'] ?>" data-authority="role" onchange="updateUserRole(this)">
                                    <?php foreach ($roles as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= ($user['roll'] == $key ? 'selected' : '') ?>>
                                        <?= $value ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>

                            </div>

                        </div>
                    </td>


                </tr>
                <?php
                endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white w-3/5 " style="margin:0 auto;">
    <table class="min-w-full text-sm text-right border border-gray-200 rounded-xl overflow-hidden shadow">
        <thead class="bg-gray-100 text-gray-800 font-bold">
            <tr>
                <th class="p-3">🏷️ دسته‌بندی</th>
                <th class="p-3">🔗 لینک</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <!-- گزارش‌ها -->
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/inventory/purchaseReport.php" target="_blank">گزارش ورود</a></td>
                <td class="p-3">گزارش‌ها</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/inventory/sellsReport.php" target="_blank">گزارش خروج</a></td>
                <td class="p-3">گزارش‌ها</td>
            </tr>

            <!-- تلگرام -->
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/callcenter/telegramPartner.php" target="_blank">تلگرام خرید</a></td>
                <td class="p-3">تلگرام</td>
            </tr>

            <!-- قیمت‌گذاری -->
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/callcenter/defineExchangeRate.php" target="_blank">تغییرات نرخ دلار</a></td>
                <td class="p-3">قیمت‌گذاری</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/callcenter/priceRates.php" target="_blank">نمایش قیمت دلار</a></td>
                <td class="p-3">قیمت‌گذاری</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/callcenter/givenPrice.php" target="_blank">قیمت دستوری</a></td>
                <td class="p-3">قیمت‌گذاری</td>
            </tr>

            <!-- انبار -->
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/YadakShop-APP/views/inventory/stockAdjustment.php" target="_blank">انبارگردانی</a></td>
                <td class="p-3">انبار</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/YadakShop-APP/views/inventory/transferGoods.php" target="_blank">انتقال به انبار</a></td>
                <td class="p-3">انبار</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/inventory/Sell.php" target="_blank">ثبت خروج کالا</a></td>
                <td class="p-3">انبار</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/inventory/purchase.php" target="_blank">ثبت ورود کالا</a></td>
                <td class="p-3">انبار</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/callcenter/relationships.php" target="_blank">رابطه اجناس</a></td>
                <td class="p-3">انبار</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/inventory/transferReport.php" target="_blank">گزارش انتقالات به انبار</a></td>
                <td class="p-3">انبار</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/inventory/requiredGoods.php" target="_blank">نیاز به انتقال</a></td>
                <td class="p-3">انبار</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/views/inventory/generalRequiredGoods.php" target="_blank">گزارش کسرات</a></td>
                <td class="p-3">انبار</td>

            </tr>

            <!-- پرینت -->
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/" target="_blank">پرینت همکار</a></td>
                <td class="p-3">پرینت</td>

            </tr>
            <tr class="hover:bg-gray-50">
                <td class="p-3 font-semibold text-blue-600"><a href="http://192.168.9.14/yadakshop-app/" target="_blank">پرینت مصرف‌کننده</a></td>
                <td class="p-3">پرینت</td>

            </tr>

        </tbody>
    </table>
</div>


<script src="./assets/js/usersManagement.js"></script>
<script>
    function shareRegisterToken(userId) {
        const ENDPOINT = '../../app/api/attendance/AttendanceApi.php';
        const params = new URLSearchParams();
        params.append('createRegistrationToken', 'createRegistrationToken');
        params.append('user_id', userId);

        axios.post(ENDPOINT, params)
            .then((response) => {
                const token = response.data.token;
                const URL = 'http://192.168.9.14/YadakShop-APP/views/attendance/register.php?token=' + token;

                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    navigator.clipboard.writeText(URL)
                        .then(() => {
                            alert("توکن با موفقیت ایجاد و در کلیپ‌بورد کپی شد:\n" + URL);
                        })
                        .catch((err) => {
                            console.error("خطا در کپی توکن:", err);
                            fallbackCopyText(URL);
                        });
                } else {
                    fallbackCopyText(URL);
                }
            })
            .catch((error) => {
                console.error("خطا در ایجاد توکن:", error);
                alert("خطایی در ایجاد توکن رخ داد.");
            });
    }

    function fallbackCopyText(text) {
        // ایجاد یک input موقت برای کپی دستی
        const textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.style.position = "fixed"; // جلوگیری از اسکرول
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                alert("توکن ایجاد شد و در کلیپ‌بورد کپی شد:\n" + text);
            } else {
                alert("توکن ایجاد شد اما امکان کپی خودکار نبود:\n" + text);
            }
        } catch (err) {
            console.error("کپی دستی نیز با خطا مواجه شد:", err);
            alert("توکن: " + text);
        }

        document.body.removeChild(textarea);
    }

    function DeleteRegisterToken(USERID) {
        const ENDPOINT = '../../app/api/attendance/AttendanceApi.php';
        const params = new URLSearchParams();
        params.append('delete_token', 'delete_token');
        params.append('user_id', USERID);

        axios.post(ENDPOINT, params)
            .then((response) => {
                alert('توکت دسترسی حضور و غیاب کاربر مشخص شده حذف گردید.')
            })
            .catch((error) => {
                console.error("خطا در ایجاد توکن:", error);
                alert("خطایی در ایجاد توکن رخ داد.");
            });
    }

</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-modal-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-toggle');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        document.querySelectorAll('[data-modal-hide]').forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-hide');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }
            });
        });

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                    document.body.style.overflow = '';
                }
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="checkbox"][data-authority]').forEach(checkbox => {
            const iconUrl = checkbox.dataset.icon;
            const userId = checkbox.dataset.user;
            const authority = checkbox.dataset.authority;

            const userRow = checkbox.closest('tr');
            const permissionBox = userRow.querySelector('#permission');

            if (permissionBox.querySelector(`img[data-authority="${authority}"]`)) return;

            const img = document.createElement('img');
            img.src = iconUrl || "http://localhost:8080/yadakshop-app/layouts/callcenter/icons/default.svg";
            img.classList.add('imgpermission');
            img.dataset.authority = authority;
            img.style.width = '20px';
            img.style.marginRight = '4px';
            img.style.verticalAlign = 'middle';
            img.style.display = checkbox.checked ? 'inline-block' : 'none';

            if (permissionBox) permissionBox.appendChild(img);

            checkbox.addEventListener('change', () => {
                img.style.display = checkbox.checked ? 'inline-block' : 'none';
            });
        });
    });



    document.addEventListener("DOMContentLoaded", () => {
        const switches = document.querySelectorAll('input[type="checkbox"][data-authority]');

        switches.forEach(input => {
            const td = input.closest('td');
            if (!td) return;

            const switchUI = td.querySelector('.switch-ui');
            const knob = td.querySelector('.switch-knob');

            if (!switchUI || !knob) return;

            function updateSwitchAppearance() {
                if (input.checked) {
                    switchUI.classList.remove("bg-gray-300");
                    switchUI.classList.add("bg-green-500");
                    knob.style.transform = "translateX(22px)";
                } else {
                    switchUI.classList.remove("bg-green-500");
                    switchUI.classList.add("bg-gray-300");
                    knob.style.transform = "translateX(0)";
                }
            }

            updateSwitchAppearance();

            switchUI.addEventListener("click", () => {
                input.checked = !input.checked; // تغییر وضعیت چک‌باکس
                updateSwitchAppearance(); // به‌روزرسانی ظاهر

                if (typeof updateUserAuthority === "function") {
                    updateUserAuthority(input);
                } else {
                    console.warn("⚠️ تابع updateUserAuthority تعریف نشده است!");
                }
            });
        });
    });


    function updateUserRole(selectElement) {
        const userId = selectElement.dataset.user; //  
        const newRole = selectElement.value; //   

        const params = new URLSearchParams();
        params.append("operation", "updateRole");
        params.append("user", userId);
        params.append("role", newRole);

        fetch("../../app/api/callcenter/UsersApi.php", {
                method: "POST",
                body: params
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`✅ نقش کاربر ${userId} با موفقیت به ${newRole} تغییر کرد.`);
                } else {
                    console.error("❌ خطا در به‌روزرسانی نقش:", data.message);
                }
            })
            .catch(error => console.error("❌ خطا در ارتباط با سرور:", error));
    }

</script>


<?php
require_once './components/footer.php';
