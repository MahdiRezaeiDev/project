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
?>
    <div class="col-span-6">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr class="text-white">
                    <th class="p-3 text-sm font-semibold">شماره فاکتور</th>
                    <th class="p-3 text-sm font-semibold hide_while_print"></th>
                    <th class="p-3 text-sm font-semibold">خریدار</th>
                    <th class="p-3 text-sm font-semibold">کاربر</th>
                    <?php
                    $isAdmin = $_SESSION['username'] === 'niyayesh' || $_SESSION['username'] === 'mahdi' || $_SESSION['username'] === 'babak' ? true : false;
                    if ($isAdmin) : ?>
                        <th class="p-3 text-sm font-semibold hide_while_print" class="edit">ویرایش</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($factors)) :
                    foreach ($factors as $factor) : ?>
                        <tr class="even:bg-gray-100">
                            <td class="text-center align-middle">
                                <span class="flex justify-center items-center gap-2 bg-blue-500 rounded-sm text-white w-24 py-2 mx-auto cursor-pointer" title="کپی کردن شماره فاکتور" data-billNumber="<?= $factor['shomare'] ?>" onClick="copyBillNumberSingle(this)">
                                    <?= $factor['shomare'] ?>
                                    <img class="hide_while_print" src="./assets/img/copy.svg" alt="copy icon" />
                                </span>
                            </td>
                            <td class="text-center align-middle hide_while_print">
                                <?php if ($factor['exists_in_bill']) : ?>
                                    <a href="../factor/complete.php?factor_number=<?= $factor['bill_id'] ?>">
                                        <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده فاکتور" src="./assets/img/bill.svg" />
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle font-semibold">
                                <?= $factor['kharidar'] ?>
                            </td>
                            <td class="text-center align-middle">
                                <img onclick="userReport(this)" class="w-10 rounded-full hover:cursor-pointer mt-2" data-id="<?= $factor['user']; ?>" src="<?= getUserProfile($factor['user'], "../") ?>" />
                            </td>

                            <?php
                            $isAdmin = $_SESSION['username'] === 'niyayesh' || $_SESSION['username'] === 'mahdi' || $_SESSION['username'] === 'babak' ? true : false;
                            if ($isAdmin) : ?>
                                <td class="text-center align-middle hide_while_print">
                                    <a onclick="toggleModal(this); edit(this)"
                                        data-factor="<?= $factor["id"] ?>"
                                        data-user="<?= $factor['user']; ?>"
                                        data-billNO="<?= $factor['shomare'] ?>"
                                        data-user-info="<?= getUserInfo($factor['user']) ?>" data-customer="<?= $factor['kharidar'] ?>" class="text-xs bg-cyan-500 text-white cursor-pointer px-2 py-1 rounded">
                                        ویرایش
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php
                    endforeach;
                else : ?>
                    <tr class="bg-gray-100">
                        <td class="text-center py-40" colspan="5">
                            <p class="text-rose-500 font-semibold">هیچ فاکتوری برای امروز ثبت نشده است.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="col-span-2 hide_while_print">
        <div class="px-3">
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
                    <div class="relative bg-gray-100 hover:bg-gray-200 p-5 shadow rounded-lg m-3 mb-10 cursor-pointer">
                        <div class="flex justify-between">
                            <div class="w-16 h-16 overflow-hidden rounded-full bg-gray-100 hover:bg-gray-200 p-2" style="position: absolute; top: -50%;">
                                <img class="rounded-full" src="<?= getUserProfile($row['user'], '../') ?>" alt="ananddavis" />
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
<?php
}
