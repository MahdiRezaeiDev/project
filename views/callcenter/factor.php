<?php
$pageTitle = "شماره فاکتور";
$iconUrl = 'bill.png';
require_once './components/header.php';
require_once '../../app/controller/callcenter/FactorController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$TOTAL = 0;
$PARTNER = 0;
$PARTNER_COUNT = 0;
$REGULAR = 0;
$REGULAR_COUNT = 0;
$NOT_INCLUDED = [];

$qualified = ['mahdi', 'babak', 'niyayesh', 'reyhan', 'ahmadiyan', 'sabahashemi', 'hadishasanpouri', 'rana'];
?>
<!-- COMPONENT STYLES -->
<style>
    #editFactorModal {
        position: fixed;
        z-index: 1;
        inset: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
    }

    #saved_factor_message {
        position: fixed;
        top: 120%;
        left: 50%;
        /* Changed 'right' to 'left' */
        transform: translate(-50%, -50%);
        /* Centering both horizontally and vertically */
        transform-origin: top center;
        /* Ensuring transform origin is centered vertically */
        transition: all 0.5s ease;
    }

    /* Hide everything except #factor_table for print */
    @media print {
        @page {
            size: auto;
            /* auto is the default size */
            margin: 0;
            /* remove default margin */
        }

        body {
            margin: 20px;
            padding: 0 !important;
            /* remove body margin */
        }

        nav,
        aside,
        .hide_while_print,
        #operation_message,
        #tvMessage {
            display: none !important;
        }

        #resultBox.grid {
            display: block;
            /* Change from grid to block */
            grid-template-columns: none;
            /* Remove grid columns */
            grid-template-rows: none;
            padding: 0;
            margin: 0;
            /* Remove grid rows */
        }

        #wrapper {
            padding: 0;
            margin: 0;
            /* Remove padding and margin */
            box-shadow: none;
        }
    }
</style>

<!-- HTML STRUCTURE -->
<section id="wrapper" class="mx-5 rounded-lg shadow overflow-hidden">
    <div class="bg-gray-800 p-3 flex justify-between h-28 pt-8 hide_while_print">
        <div class="flex sm:block justify-between items-center gap-5">
            <input minlength="3" id="customer" class="bg-transparent border-2 border-white py-1 px-1 sm:px-3 sm:py-2 text-white sm:w-72 outline-none text-xs sm:text-sm" autofocus="true" name="customer" type="text" placeholder="نام خریدار را وارد کنید ...">
            <button onclick="getNewFactorNumber()" class="bg-blue-500 border-2 border-transparent py-1 px-1 sm:py-2 sm:px-3 text-white text-xs sm:text-sm" type="button"> گرفتن شماره فاکتور</button>
            <p id="customer_error" class="py-2 text-rose-500 text-xs font-semibold hidden">نام خریدار باید بیشتر از ۳ حرف باشد.</p>
        </div>
        <p class="text-white text-lg font-semibold hidden sm:block">
            <?= jdate('l J F'); ?> -
            <?= jdate('Y/m/d')  ?>
        </p>
    </div>
    <div class="bg-gray-100 p-5 flex justify-between hide_while_print">
        <form>
            <label class="text-sm font-semibold" for="invoice_time">
                <img class="hidden sm:inline" src="./assets/img/filter.svg" alt="filter icon">
            </label>
            <input class="text-sm py-2 px-3 font-semibold sm:w-60 border-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="invoice_time" id="invoice_time">
        </form>
        <div class="flex justify-center items-center gap-2">
            <a title="چاپ کردن گزارش" class="bg-blue-500 hover:bg-blue-600 px-3 rounded-md cursor-pointer w-12" onClick="window.print()">
                <img class="w-6 h-6 sm:w-12 sm:h-11" src="./assets/img/print.svg" alt="print icon" />
            </a>
            <img onclick="calculateTotal()" title="گزارش فروشات امروز" class="w-8 h-8 sm:w-12 sm:h-11 cursor-pointer" src="./assets/img/chasier.svg" alt="chasier icon">
        </div>
    </div>

    <!-- Saved new factor success message START -->
    <div id="saved_factor_message" class="flex justify-between py-3 px-2 gap-5 rounded-md  bg-green-600 hide_while_print">
        <div class="flex justify-center items-center gap-2 cursor-pointer">
            <img src="./assets/img/copy.svg" alt="copy icon" />
            <p id="success_message" class="text-white text-sm font-semibold"></p>
        </div>
        <img class="cursor-pointer" title="بستن" src="./assets/img/close.svg" alt="close icon" onclick="closeAlert()">
    </div>
    <!-- Saved new factor success message END -->

    <div id="resultBox" class="grid sm:grid-cols-8 gap-3 py-5">
        <div class="sm:col-span-6">
            <table id="factor_table" class="w-full">
                <thead class="bg-gray-800">
                    <tr class="text-white">
                        <th class="p-3 text-sm font-semibold">شماره فاکتور</th>
                        <th class="p-3 text-sm font-semibold "></th>
                        <th class="p-3 text-sm font-semibold">خریدار</th>
                        <th class="p-3 text-sm font-semibold">کاربر</th>
                        <?php if (in_array($_SESSION['username'], $qualified)): ?>
                            <th class="p-3 text-sm font-semibold hide_while_print">وضعیت</th>
                        <?php endif ?>
                        <?php
                        $isAdmin = $_SESSION['username'] === 'niyayesh' || $_SESSION['username'] === 'mahdi' || $_SESSION['username'] === 'babak' ? true : false;
                        ?>
                        <th class="p-3 text-sm font-semibold hide_while_print">واریزی</th>
                        <th class="p-3 text-sm font-semibold hide_while_print">خروج</th>
                        <th class="p-3 text-sm font-semibold">ارسال</th>
                        <?php if ($isAdmin) : ?>
                            <th class="p-3 text-sm font-semibold hide_while_print hidden sm:table-cell">ویرایش</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($factors)) :
                        foreach ($factors as $factor) :
                            if (!$factor['exists_in_bill']) {
                                array_push($NOT_INCLUDED, $factor['shomare']);
                            }
                            $TOTAL += $factor['total'];

                            if ($factor['isPartner']) {
                                $PARTNER += $factor['total'];
                                $PARTNER_COUNT++;
                            } else {
                                $REGULAR += $factor['total'];
                                $REGULAR_COUNT++;
                            } ?>
                            <tr class="<?= $factor['partner'] ? 'bg-green-200' : 'even:bg-gray-100' ?> factor_row" data-total="<?= $factor['total'] ?? 'xxx' ?>" data-status="<?= $factor['status'] ?? 'xxx' ?>">
                                <td class="text-center align-middle">
                                    <span class="flex justify-center items-center gap-2 bg-blue-500 rounded-sm text-white sm:w-24 py-2 mx-auto cursor-pointer" title="کپی کردن شماره فاکتور" data-billNumber="<?= $factor['shomare'] ?>" onClick="copyBillNumberSingle(this)">
                                        <span class="factorNumberContainer"><?= $factor['shomare'] ?></span>
                                        <?php if (!$factor["status"]): ?>
                                            <img src="./assets/img/close.svg" alt="cross icon">
                                        <?php else: ?>
                                            <img src="./assets/img/copy.svg" alt="copy icon" />
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle ">
                                    <div class="flex items-center gap-2">
                                        <?php if ($factor['exists_in_bill']) : ?>
                                            <a class="hide_while_print" href="../factor/complete.php?factor_number=<?= $factor['bill_id'] ?>">
                                                <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده فاکتور" src="./assets/img/bill.svg" />
                                            </a>
                                            <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $factor['bill_id'] ?>">
                                                <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده جزئیات" src="./assets/img/explore.svg" />
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($factor['printed']) : ?>
                                            <img class="w-6 hide_while_print cursor-pointer d-block" title="چاپ شده" src="./assets/img/printed.svg" />
                                        <?php endif; ?>
                                        <?php if ($factor['exists_in_payments']) : ?>
                                            <a class="relative inline-block w-6 h-6" href="../factor/paymentDetails.php?factor=<?= $factor['shomare'] ?>">
                                                <img class="w-full h-full cursor-pointer" title="مشاهده واریزی ها" src="./assets/img/payment.svg" />
                                                <?php if ($factor['payment_count'] > 0): ?>
                                                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center shadow">
                                                        <?= $factor['payment_count'] ?>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle font-semibold">
                                    <?= $factor['kharidar'] ?>
                                </td>
                                <td class="text-center align-middle">
                                    <img onclick="userReport(this)" class="w-10 rounded-full hover:cursor-pointer mt-2 mx-auto" data-id="<?= $factor['user']; ?>" src="<?= getUserProfile($factor['user']) ?>" />
                                </td>
                                <?php if (in_array($_SESSION['username'], $qualified)): ?>
                                    <td class="hide_while_print">
                                        <div class="flex justify-center items-center">
                                            <input onclick="changeStatus(this)" <?= ($factor["exists_in_phones"] || $factor["approved"]) ? 'checked' : '' ?> type="checkbox" name="status" id="<?= $factor['shomare'] ?>">
                                        </div>
                                    </td>
                                <?php endif;
                                $payment_bg = 'bg-gray-400 hover:bg-gray-300';
                                if ($factor['is_paid_off']):
                                    $payment_bg = 'bg-green-500 hover:bg-green-600';
                                ?>
                                    <td class="text-center align-middle hide_while_print hidden sm:table-cell">
                                        <a href="../factor/paymentDetails.php?factor=<?= $factor['shomare'] ?>"
                                            class="relative inline-block text-xs <?= $payment_bg; ?>  text-white cursor-pointer px-3 py-1 rounded transition">
                                            مشاهده واریزی
                                        </a>
                                    </td>
                                <?php else:
                                    if ($factor['payment_count'] > 0):
                                        $payment_bg = 'bg-cyan-500 hover:bg-cyan-600';
                                    endif;
                                ?>
                                    <td class="text-center align-middle hide_while_print hidden sm:table-cell">
                                        <a href="../factor/addPayment.php?factor=<?= $factor['shomare'] ?>"
                                            class="relative inline-block text-xs <?= $payment_bg; ?> text-white cursor-pointer px-3 py-1 transition rounded">
                                            ثبت واریزی
                                        </a>
                                    </td>
                                <?php endif; ?>
                                <td class="hide_while_print">
                                    <div class="flex justify-center items-center">
                                        <?php if ($factor['sellout']): ?>
                                            <img src="./assets/img/checked.svg" alt="">
                                        <?php else: ?>
                                            <img src="./assets/img/ignored.svg" alt="">
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="flex flex-col items-center gap-1">
                                        <?php
                                        // Determine delivery icon
                                        switch ($factor['delivery_type']) {
                                            case 'تیپاکس':
                                            case 'اتوبوس':
                                            case 'سواری':
                                            case 'باربری':
                                                $src = './assets/img/delivery.svg';
                                                break;
                                            case 'پیک مشتری':
                                                $src = './assets/img/customer.svg';
                                                break;
                                            case 'پیک یدک شاپ':
                                                $src = './assets/img/yadakshop.svg';
                                                break;
                                            case 'هوایی':
                                                $src = './assets/img/airplane.svg';
                                                break;
                                            default:
                                                $src = './assets/img/customer.svg';
                                        }
                                        ?>

                                        <img
                                            onclick="displayDeliveryModal(this)"
                                            data-bill="<?= $factor['shomare'] ?>"
                                            data-contact="<?= $factor['contact_type'] ?>"
                                            data-destination="<?= $factor['destination'] ?>"
                                            data-type="<?= $factor['delivery_type'] ?>"
                                            data-address="<?= $factor['customer_address'] ?>"
                                            src="<?= $src; ?>"
                                            alt="arrow icon"
                                            class="w-6 h-6 cursor-pointer"
                                            title="ارسال اجناس" />

                                        <?php
                                        // Only show destination if not "پیک مشتری"
                                        if ($factor['delivery_type'] !== 'پیک مشتری') {
                                            // Pick text color
                                            $color = $factor['delivery_type'] === 'پیک یدک شاپ'
                                                ? 'text-sky-700'
                                                : 'text-green-700';

                                            // Limit to 3 words
                                            $words = explode(' ', $factor['destination']);
                                            $displayText = count($words) > 3
                                                ? implode(' ', array_slice($words, 0, 3)) . '...'
                                                : $factor['destination'];

                                            echo "<span class='text-[9px] {$color} font-semibold'>{$displayText}</span>";
                                        }
                                        ?>
                                    </div>

                                </td>
                                <?php if ($isAdmin) : ?>
                                    <td class="text-center align-middle hide_while_print hidden sm:table-cell">
                                        <a onclick="toggleModal(this); edit(this)" data-factor="<?= $factor["id"] ?>" data-user="<?= $factor['user']; ?>" data-billNO="<?= $factor['shomare'] ?>" data-user-info="<?= getUserInfo($factor['user']) ?>" data-customer="<?= $factor['kharidar'] ?>"
                                            class="">
                                            <img src="./assets/img/edit.svg" alt="edit icon" class="w-6 h-6 cursor-pointer mx-auto" title="ویرایش فاکتور" />
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php
                        endforeach;
                    else : ?>
                        <tr class="bg-gray-100">
                            <td class="text-center py-40" colspan="9">
                                <p class="text-rose-500 font-semibold">هیچ فاکتوری برای امروز ثبت نشده است.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="sm:col-span-2 hide_while_print">
            <div class="px-">
                <table class="w-full">
                    <thead class="bg-gray-800">
                        <tr class="text-white">
                            <th class="text-right p-3 text-sm font-semibold">
                                تعداد کل
                            </th>
                            <th class="text-center p-3 text-sm font-semibold">
                                <?= count($factors) ?>
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="py-10 hide_while_print">
                <?php
                if (count($countFactorByUser)) :
                    foreach ($countFactorByUser as $index => $row) : $index++; ?>
                        <div class="group">
                            <div class="relative bg-gray-100 group-hover:hover:bg-gray-200 p-5 shadow rounded-lg m-3 mb-10 cursor-pointer">
                                <div class="flex justify-between">
                                    <div class="w-16 h-16 overflow-hidden rounded-full bg-gray-100 group-hover:bg-gray-200 p-2" style="position: absolute; top: -50%;">
                                        <img onclick="userReport(this)" data-id="<?= $row['user'] ?>" class="rounded-full" src="<?= getUserProfile($row['user']) ?>" alt="ananddavis" />
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="grow text-left">
                                        <img style="z-index: 10000;" src="../../public/icons/<?= getRankingBadge($index) ?>" alt="first" />
                                    </div>
                                    <div class="grow">
                                        <h4 class="text-left font-semibold text-sm"><?= getUserInfo($row['user']) ?></h4>
                                    </div>
                                    <div class="grow">
                                        <div class="text-sm text-left font-semibold">فاکتورها
                                            <span class="profile__key"><?= $row['count_shomare']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    endforeach;
                else : ?>
                    <div class="flex justify-center items-center h-64 bg-gray-100 mx-3">
                        <p class="text-rose-500 font-semibold">هیچ فاکتوری برای امروز ثبت نشده است.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div onclick="toggleDollarModal()" id="dollarContainerModal" class="hide_while_print hidden fixed flex inset-0 bg-gray-900/75 justify-center items-center">
            <div class="bg-white p-4 rounded w-1/3">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl mb-2">گزارش مجموع فروشات روزانه</h2>
                    <img class="cursor-pointer" src="./assets/img/close.svg" alt="close icon">
                </div>
                <table class="w-full">
                    <tbody>
                        <tr>
                            <td class="p-2 bg-sky-800 text-white font-semibold text-xs">جمع کل :
                                (<?= count($factors) ?>)
                            </td>
                            <td id="total_price" class="p-2 bg-sky-800 text-white font-semibold text-xs">
                                <?= displayAsMoney($TOTAL); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-2 bg-sky-800 text-white font-semibold text-xs">
                                جمع همکار :
                                (<?= $PARTNER_COUNT ?>)
                            </td>
                            <td id="total_partner" class="p-2 bg-sky-800 text-white font-semibold text-xs">
                                <?= displayAsMoney($PARTNER); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-2 bg-sky-800 text-white font-semibold text-xs">جمع مصرف کننده :
                                (<?= $REGULAR_COUNT ?>)
                            </td>
                            <td id="total_consumer" class="p-2 bg-sky-800 text-white font-semibold text-xs">
                                <?= displayAsMoney($REGULAR); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-2 bg-sky-800 text-white font-semibold text-xs"> شماره فاکتور های لحاظ نشده :
                                (<?= count($NOT_INCLUDED) ?>)
                            </td>
                            <td id="total_notIncluded" class="p-2 bg-sky-800 text-white font-semibold text-xs">
                                <?= implode(' , ', $NOT_INCLUDED); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal for editing factor number -->
<div id="editFactorModal" class="justify-center items-center hidden">
    <div class="w-2/3 rounded-md overflow-hidden">
        <div class="bg-gray-800 flex justify-between p-5 ">
            <h2 class="text-xl text-white">ویرایش مشخصات فاکتور</h2>
            <span class="text-rose-600 text-2xl cursor-pointer" onclick="toggleModal()">&times;</span>
        </div>
        <div class="bg-white p-5">
            <table class="w-full">
                <thead class="bg-gray-600 text-white">
                    <tr>
                        <th class="p-3 font-semibold">شماره فاکتور</th>
                        <th class="p-3 font-semibold">خریدار</th>
                        <th class="p-3 font-semibold">کاربر</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-100">
                        <td id="edit_billNo" class="p-3 text-center">123456</td>
                        <td id="edit_customer" class="p-3 text-center">محمدرضا نیایش</td>
                        <td id="edit_user_info" class="p-3 text-center">محمدرضا نیایش</td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <p id="operation_message" class="bg-green-600 text-white text-sm font-semibold text-center py-3 hidden">
                                تغیررات موفقانه ذخیره شد.
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div id="editFactorForm" class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <input type="hidden" name="factor_id" id="edit_facto_id">
                        <label class="text-sm font-semibold ml-3" for="editFactorCustomer">نام خریدار</label>
                        <input class="text-sm py-2 px-3 font-semibold border-2 border-gray-500 " name="editFactorCustomer" id="editFactorCustomer" type="text">
                    </div>
                    <div>
                        <label class="text-sm font-semibold ml-3" for="edit_user_id">کاربر ثبت کننده</label>
                        <select class="text-sm py-2 px-3 font-semibold border-2 border-gray-500" name="edit_user_id" id="edit_user_id">
                            <?php foreach ($users as $user) { ?>
                                <option id="option-<?= $user['id'] ?>" value="<?= $user['id'] ?>"><?= $user['name'] . ' ' . $user['family'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <button onclick="saveChanges()" class="bg-blue-500 text-white py-2 px-5 rounded-md tet-sm" type="button">ثبت تغیرات</button>
                        <button onclick="cancelFactor()" class="bg-rose-500 text-white py-2 px-5 rounded-md tet-sm" type="button">لغو فاکتور</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 flex justify-between p-5 ">
            <ul>
                <li>
                    <p class="text-rose-500 text-sm font-semibold">
                        شماره فاکتور قابلیت حذف شدن ندارد.
                    </p>
                </li>
                <li>
                    <p class="text-rose-500 text-sm font-semibold py-3">
                        شماره فاکتور را میتوانید به کاربر و یا خریدار دیگری نسبت دهید یا در قسمت خریدار علت عدم استفاده از آن را بنویسید.
                    </p>
                </li>
                <li>
                    <p class="text-rose-500 text-sm font-semibold">
                        هر گونه تغییر باید به مسئول مربوطه اطلاع داده شود.
                    </p>
                </li>
            </ul>
        </div>
    </div>
</div>

<div id="deliveryModal" class="hidden fixed inset-0 bg-gray-900/75 flex justify-center items-center">
    <div class="bg-white p-4 rounded w-2/3">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl mb-2">ارسال اجناس</h2>
            <img class="cursor-pointer" src="./assets/img/close.svg" alt="close icon" onclick="document.getElementById('deliveryModal').classList.add('hidden')">
        </div>
        <div class="modal-body">
            <table class="w-full my-4 ">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="text-xs text-white font-semibold p-3">شماره فاکتور</th>
                        <th class="text-xs text-white font-semibold p-3">روش ارسال</th>
                        <th class="text-xs text-white font-semibold p-3">آدرس مقصد</th>
                        <th class="text-xs text-white font-semibold p-3">پیام رسان مشتری</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-100">
                        <td class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_billNumber"></td>
                        <td class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_deliveryType"></td>
                        <td class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_destination"></td>
                        <td class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_contactType"></td>
                    </tr>
                </tbody>
            </table>

            <form action="" onsubmit="submitDelivery(event)" class="mt-4">
                <input type="hidden" name="billNumber" id="deliveryBillNumber" value="">
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-2" for="deliveryType">روش ارسال:</label>
                    <select required id="deliveryType" name="deliveryType" class="w-full border-2 border-gray-300 p-2 rounded">
                        <option value="پیک مشتری">پیک خود مشتری</option>
                        <option value="پیک خود مشتری بعد از اطلاع">پیک خود مشتری بعد از اطلاع </option>
                        <option value="پیک یدک شاپ">پیک یدک شاپ</option>
                        <option value="اتوبوس">اتوبوس</option>
                        <option value="تیپاکس">تیپاکس</option>
                        <option value="سواری">سواری</option>
                        <option value="باربری">باربری</option>
                        <option value="هوایی">هوایی</option>
                    </select>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-2" for="address">آدرس مقصد:</label>
                    <input value="تهران" type="text" id="address" name="address" class="w-full border-2 border-gray-300 p-2 rounded" placeholder="آدرس ارسال را وارد کنید...">
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-2" for="contactType"> پیام رسان مشتری:</label>
                    <select required id="contactType" name="contactType" class="w-full border-2 border-gray-300 p-2 rounded">
                        <option value="واتساپ" selected>واتساپ</option>
                        <option value="واتساپ راست">واتساپ راست</option>
                        <option value="واتساپ چپ">واتساپ چپ</option>
                        <option value="تلگرام">تلگرام</option>
                        <option value="تلگرام پشتیبانی ">تلگرام پشتیبانی </option>
                        <option value="تلگرام یدک شاپ ">تلگرام یدک شاپ </option>
                        <option value="تلگرام واریزی">تلگرام واریزی</option>
                        <option value="تلگرام کره">تلگرام کره</option>
                    </select>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">ثبت ارسال</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    const resultBox = document.getElementById('resultBox');
    const element = document.getElementById('invoice_time');
    let filter = false;

    $(function() {
        $("#invoice_time").persianDatepicker({
            months: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
            dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
            shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
            showGregorianDate: !1,
            persianNumbers: !0,
            formatDate: "YYYY/MM/DD",
            selectedBefore: !1,
            selectedDate: null,
            startDate: null,
            endDate: null,
            prevArrow: '\u25c4',
            nextArrow: '\u25ba',
            theme: 'default',
            alwaysShow: !1,
            selectableYears: null,
            selectableMonths: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            cellWidth: 25, // by px
            cellHeight: 20, // by px
            fontSize: 13, // by px
            isRTL: !1,
            calendarPosition: {
                x: 0,
                y: 0,
            },
            onShow: function() {},
            onHide: function() {},
            onSelect: function() {
                const date = ($("#invoice_time").attr("data-gdate"));
                var params = new URLSearchParams();
                params.append('getFactor', 'getFactor');
                params.append('date', date);
                axios.post("../../app/partials/factors/factor.php", params)
                    .then(function(response) {
                        resultBox.innerHTML = response.data;
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
            },
            onRender: function() {}
        });
    });

    function userReport(element) {
        const id = element.getAttribute('data-id');
        const date = ($("#invoice_time").attr("data-gdate"));
        var params = new URLSearchParams();

        filter = !filter;

        if (filter == false) {
            params.append('getFactor', 'getFactor');
            params.append('date', date);
            axios.post("../../app/partials/factors/factor.php", params)
                .then(function(response) {
                    resultBox.innerHTML = response.data;
                })
                .catch(function(error) {
                    console.log(error);
                });
            return;
        }

        params.append('getReport', 'getReport');
        params.append('date', date);
        params.append('user', id);
        axios.post("../../app/partials/factors/factor.php", params)
            .then(function(response) {
                resultBox.innerHTML = response.data;
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function getNewFactorNumber() {
        const date = ($("#invoice_time").attr("data-gdate"));
        const customer = document.getElementById('customer');
        if (customer.value.length >= 3) {
            var params = new URLSearchParams();
            params.append('getNewFactorNumber', 'getNewFactorNumber');
            params.append('customer', customer.value);
            axios.post("../../app/api/callcenter/FactorApi.php", params)
                .then(function(response) {
                    const message_container = document.getElementById('saved_factor_message');
                    const success_message = document.getElementById('success_message');
                    success_message.innerHTML = 'شماره فاکتور ' + response.data + ' برای ' + customer.value + ' ثبت شد.';
                    message_container.style.top = '93%';
                    customer.value = null;
                    params.append('getNewFactor', 'getNewFactor');
                    params.append('date', date);
                    axios.post("../../app/partials/factors/factor.php", params)
                        .then(function(response) {
                            resultBox.innerHTML = response.data;
                        })
                        .catch(function(error) {
                            console.log(error);
                        });

                    setTimeout(() => {
                        message_container.style.top = '120%';
                    }, 7000);
                })
                .catch(function(error) {
                    console.log(error);
                });
        } else {
            document.getElementById('customer_error').classList.remove('hidden');

            setTimeout(() => {
                document.getElementById('customer_error').classList.add('hidden');
            }, 2000);
        }
    }

    function closeAlert() {
        const message_container = document.getElementById('saved_factor_message');
        message_container.style.top = '120%';
    }

    function copyBillNumberSingle(element) {
        const billNumber = element.getAttribute('data-billNumber');
        copyToClipboard(billNumber);
        element.classList.remove('bg-blue-500');
        element.classList.add('bg-green-500');
        element.innerHTML = billNumber + '<img class="w-5" src="./assets/img/done.svg" alt="copy icon" />';
    }

    function displayBill(billNumber) {
        window.location.href = "../factor/complete.php?factor_number=" + billNumber;
    }

    function toggleModal(element) {
        const modal = document.getElementById('editFactorModal');
        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');
    }

    function edit(element) {
        const factor = element.getAttribute('data-factor');
        const user = element.getAttribute('data-user');
        const billNO = element.getAttribute('data-billNO');
        const userInfo = element.getAttribute('data-user-info');
        const customer = element.getAttribute('data-customer');

        document.getElementById('edit_facto_id').value = factor;
        document.getElementById('edit_billNo').innerHTML = billNO;
        document.getElementById('edit_customer').innerHTML = customer;
        document.getElementById('edit_user_info').innerHTML = userInfo;
        document.getElementById('editFactorCustomer').value = customer;
        document.getElementById('option-' + user).selected = true;
    }

    function saveChanges() {

        const factor = document.getElementById('edit_facto_id').value;
        const customer = document.getElementById('editFactorCustomer').value;
        const edit_user_id = document.getElementById('edit_user_id').value;
        var params = new URLSearchParams();
        params.append('saveChanges', 'saveChanges');
        params.append('customer', customer);
        params.append('factor', factor);
        params.append('edit_user_id', edit_user_id);
        axios.post("../../app/api/callcenter/FactorApi.php", params)
            .then(function(response) {

                const date = ($("#invoice_time").attr("data-gdate"));
                params.append('getNewFactor', 'getNewFactor');
                params.append('date', date);
                axios.post("../../app/partials/factors/factor.php", params)
                    .then(function(response) {
                        resultBox.innerHTML = response.data;
                    })
                    .catch(function(error) {
                        console.log(error);
                    });

                document.getElementById('operation_message').classList.remove('hidden');

                setTimeout(() => {
                    document.getElementById('operation_message').classList.add('hidden');
                    toggleModal();
                }, 4000);
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function cancelFactor() {
        const factor = document.getElementById('edit_facto_id').value;
        var params = new URLSearchParams();
        params.append('cancelFactor', 'cancelFactor');
        params.append('factor', factor);
        axios.post("../../app/api/callcenter/FactorApi.php", params)
            .then(function(response) {

                const date = ($("#invoice_time").attr("data-gdate"));
                params.append('getNewFactor', 'getNewFactor');
                params.append('date', date);
                axios.post("../../app/partials/factors/factor.php", params)
                    .then(function(response) {
                        resultBox.innerHTML = response.data;
                    })
                    .catch(function(error) {
                        console.log(error);
                    });

                document.getElementById('operation_message').classList.remove('hidden');

                setTimeout(() => {
                    document.getElementById('operation_message').classList.add('hidden');
                    toggleModal();
                }, 4000);
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function calculateTotal() {
        toggleDollarModal();
    }

    function toggleDollarModal() {
        dollarContainerModal.classList.toggle('hidden');
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            getNewFactorNumber();
        }
    });

    function changeStatus(element) {
        const params = new URLSearchParams();
        const status = element.checked ? 1 : 0;
        const factor = element.id;
        params.append('changeStatus', 'changeStatus');
        params.append('status', status);
        params.append('factor', factor);
        axios.post("../../app/api/callcenter/FactorApi.php", params)
            .then(function(response) {
                alert("تغیرات موفقانه اعمال شد.");
            })
            .catch(function(error) {
                alert("خطا در هنگام ثبت وضعیت، لطفا مجددا تلاش نمایید");
            });
    }

    function displayDeliveryModal(element) {
        const billNumber = element.dataset.bill;
        const contactType = element.dataset.contact;
        const destination = element.dataset.destination;
        const deliveryType = element.dataset.type;
        const address = element.dataset.address || 'تهران';

        // Set display text
        document.getElementById('display_billNumber').innerText = billNumber;
        document.getElementById('display_contactType').innerText = contactType;
        document.getElementById('display_destination').innerText = destination;
        document.getElementById('display_deliveryType').innerText = deliveryType;

        // Set form values
        document.getElementById('deliveryBillNumber').value = billNumber;
        document.getElementById('address').value = address;

        // Select dropdown options if they exist
        const deliverySelect = document.getElementById('deliveryType');
        if (deliverySelect && deliveryType) {
            deliverySelect.value = deliveryType;
        }

        const contactSelect = document.getElementById('contactType');
        if (contactSelect && contactType) {
            contactSelect.value = contactType;
        }

        // Show modal
        document.getElementById('deliveryModal').classList.remove('hidden');
    }

    function submitDelivery(event) {
        event.preventDefault();
        const deliveryType = document.getElementById('deliveryType').value;
        const deliveryBillNumber = document.getElementById('deliveryBillNumber').value;
        const address = document.getElementById('address').value;
        const contactType = document.getElementById('contactType').value;
        const params = new URLSearchParams();
        params.append('submitDelivery', 'submitDelivery');
        params.append('deliveryType', deliveryType);
        params.append('address', address);
        params.append('contactType', contactType);
        params.append('billNumber', deliveryBillNumber);
        axios.post("../../app/api/factor/DeliveryApi.php", params)
            .then(function(response) {
                document.getElementById('deliveryModal').classList.add('hidden');
                showToast("ارسال با موفقیت ثبت شد.");
            })
            .catch(function(error) {
                alert("خطا در هنگام ثبت ارسال، لطفا مجددا تلاش نمایید");
            });

    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow-lg text-white z-50 transition-opacity duration-500 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;

        document.body.appendChild(toast);

        // Remove toast and reload after 3s
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.remove();
                location.reload();
            }, 500); // wait for fade-out animation
        }, 3000);
    }
</script>
<?php
require_once './components/footer.php';
