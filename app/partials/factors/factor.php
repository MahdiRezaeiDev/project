<?php
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../app/partials/factors/helpers.php';

if (isset($_POST['getNewFactor'])) {
    $startDate = date_create($_POST['date']);
    $endDate = date_create($_POST['date']);

    $endDate = $endDate->setTime(23, 59, 59);
    $startDate = $startDate->setTime(1, 1, 0);

    $end = date_format($endDate, "Y-m-d H:i:s");
    $start = date_format($startDate, "Y-m-d H:i:s");

    $factors = getFactors($start, $end);
    $countFactorByUser = getCountFactorByUser($start, $end);
    displayUI($factors, $countFactorByUser);
}


if (isset($_POST['getFactor'])) :
    $startDate = date_create($_POST['date']);
    $endDate = date_create($_POST['date']);

    $endDate = $endDate->setTime(23, 59, 59);
    $startDate = $startDate->setTime(1, 1, 0);

    $end = date_format($endDate, "Y-m-d H:i:s");
    $start = date_format($startDate, "Y-m-d H:i:s");

    $factors = getFactors($start, $end);
    $countFactorByUser = getCountFactorByUser($start, $end);
    displayUI($factors, $countFactorByUser);
endif;

if (filter_has_var(INPUT_POST, 'getReport')) {

    $user = $_POST['user'];
    $startDate = date_create($_POST['date']);
    $endDate = date_create($_POST['date']);

    $endDate = $endDate->setTime(23, 59, 59);
    $startDate = $startDate->setTime(1, 1, 0);

    $end = date_format($endDate, "Y-m-d H:i:s");
    $start = date_format($startDate, "Y-m-d H:i:s");

    $factors = getFactors($start, $end, $user);
    $countFactorByUser = getCountFactorByUser($start, $end, $user);
    displayUI($factors, $countFactorByUser);
}


function displayUI($factors, $countFactorByUser)
{
    $TOTAL = 0;
    $PARTNER = 0;
    $PARTNER_COUNT = 0;
    $REGULAR = 0;
    $REGULAR_COUNT = 0;
    $NOT_INCLUDED = [];
    $qualified = ['mahdi', 'babak', 'niyayesh', 'reyhan', 'ahmadiyan', 'sabahashemi', 'hadishasanpouri', 'rana'];
?>
    <div class="sm:col-span-6">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr class="text-white">
                    <th class="p-3 text-sm font-semibold">شماره فاکتور</th>
                    <th class="p-3 text-sm font-semibold"></th>
                    <th class="p-3 text-sm font-semibold">خریدار</th>
                    <th class="p-3 text-sm font-semibold">کاربر</th>
                    <?php if (in_array($_SESSION['username'], $qualified)): ?>
                        <th class="p-3 text-sm font-semibold hide_while_print">وضعیت</th>
                    <?php endif; ?>
                    <?php
                    $isAdmin = $_SESSION['username'] === 'niyayesh' || $_SESSION['username'] === 'mahdi' || $_SESSION['username'] === 'babak' ? true : false;
                    ?>
                    <th class="p-3 text-sm font-semibold hide_while_print">واریزی</th>
                    <th class="p-3 text-sm font-semibold hide_while_print">خروج</th>
                    <th class="p-3 text-sm font-semibold">ارسال</th>
                    <?php if ($isAdmin) : ?>
                        <th class="p-3 text-sm font-semibold hide_while_print" class="edit">ویرایش</th>
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
                        }
                ?>
                        <tr class="<?= $factor['partner'] ? 'bg-green-200' : 'even:bg-gray-100' ?> factor_row" data-total="<?= $factor['total'] ?? 'xxx' ?>" data-status="<?= $factor['status'] ?? 'xxx' ?>">
                            <td class="text-center align-middle">
                                <span class="flex justify-center items-center gap-2 bg-blue-500 rounded-sm text-white w-24 py-2 mx-auto cursor-pointer" title="کپی کردن شماره فاکتور" data-billNumber="<?= $factor['shomare'] ?>" onClick="copyBillNumberSingle(this)">
                                    <?= $factor['shomare'] ?>
                                    <img class="hide_while_print" src="./assets/img/copy.svg" alt="copy icon" />
                                </span>
                            </td>
                            <td class="text-center align-middle">
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
                                        <img class="w-6 cursor-pointer d-block hide_while_print" title="چاپ شده" src="./assets/img/printed.svg" />
                                    <?php endif; ?>
                                    <?php if ($factor['exists_in_payments']) : ?>
                                        <a class="relative inline-block w-6 h-6" href="../factor/paymentDetails.php?factor=<?= $factor['shomare'] ?>">
                                            <img class="w-full h-full cursor-pointer" title="مشاهده واریزی ها" src="./assets/img/payment.svg" />

                                            <?php if ($factor['payment_count'] > 0): ?>
                                                <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-semibold rounded-full w-4 h-4 flex items-center justify-center shadow">
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
                                <img onclick="userReport(this)" class="w-10 rounded-full hover:cursor-pointer mt-2 mx-auto" data-id="<?= $factor['user']; ?>" src="<?= getUserProfile($factor['user'], "../") ?>" />
                            </td>
                            <?php if (in_array($_SESSION['username'], $qualified)): ?>
                                <td class="hide_while_print">
                                    <div class="flex justify-center items-center">
                                        <input onclick="changeStatus(this)" <?= ($factor["exists_in_phones"] || $factor["approved"]) ? 'checked' : '' ?> type="checkbox" name="status" id="<?= $factor['shomare'] ?>">
                                    </div>
                                </td>
                            <?php endif; ?>
                            <?php
                            $isAdmin = $_SESSION['username'] === 'niyayesh' || $_SESSION['username'] === 'mahdi' || $_SESSION['username'] === 'babak' ? true : false;
                            ?>
                            <?php
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
                            <?= count($factors)  ?>
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
                                    <img onclick="userReport(this)" data-id="<?= $row['user'] ?>" class="rounded-full" src="<?= getUserProfile($row['user'], '../') ?>" alt="ananddavis" />
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
            <?php endif;
            ?>
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
                        <td class="p-2 bg-sky-800 text-white font-semibold text-xs">جمع کل :</td>
                        <td id="total_price" class="p-2 bg-sky-800 text-white font-semibold text-xs">
                            <?= displayAsMoney($TOTAL); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2 bg-sky-800 text-white font-semibold text-xs">جمع همکار :
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
                        <td class="p-2 bg-sky-800 text-white font-semibold text-xs"> شماره فاکتور های لحاظ نشده :</td>
                        <td id="total_notIncluded" class="p-2 bg-sky-800 text-white font-semibold text-xs">
                            <?= implode(' , ', $NOT_INCLUDED); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php
}
