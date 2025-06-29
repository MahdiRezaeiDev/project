<?php
$pageTitle = "واریزی ها";
$iconUrl = 'factor.svg';
require_once './components/header.php';
require_once '../../app/controller/payment/paymentController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

$factor_number = $_GET['factor'] ?? null;
$success = false;
$error = null;

if (!$factor_number) {
    header("Location: ../../../views/factor/list.php");
    exit;
}

// Get factor info
$factorInfo = getFactorInfo($factor_number);
if (!$factorInfo) {
    die("فاکتور پیدا نشد.");
}

$payments = getPayments($factorInfo['id']);
$totalPayment = array_sum(array_column($payments, 'amount'));
$remainingAmount = $factorInfo['total'] - $totalPayment;

?>


<div class="w-2/4 bg-white shadow-lg rounded p-3 md:p-5 max-w-4xl mx-auto mb-5">
    <div class="flex items-center justify-between flex-row- mb-4">
        <div class="text-right">
            <h1 class="text-lg font-bold mb-2">مجموعه ی یدک شاپ</h1>
            <p class="text-xs mb-1">
                در یدک شاپ، مأموریت ما تأمین قطعات یدکی اصلی با نازل‌ترین قیمت و بالاترین کیفیت است.
        </div>
        <img src="../../public/img/logo.jpg" alt="Logo" class="w-20 mb-2">
    </div>
    <table class="w-full text-xs text-right border border-gray-300 mt-4">
        <tbody>
            <tr class="border-b">
                <td class="border-l px-2 py-1 font-semibold w-32">نمبر مسلسل:</td>
                <td class="px-2 py-1"><?= $factorInfo['bill_number'] ?></td>
                <td class="border-l px-2 py-1 font-semibold w-32">اسم مشتری:</td>
                <td class="px-2 py-1"><?= $factorInfo['name'] ?? '---' ?></td>
            </tr>
            <tr class="border-b">
                <td class="border-l px-2 py-1 font-semibold w-32">اسم مشتری:</td>
                <td class="px-2 py-1"><?= $factorInfo['name'] ?? '---' ?></td>
                <td class="border-l px-2 py-1 font-semibold w-32">شماره تماس:</td>
                <td class="px-2 py-1"><?= $factorInfo['phone'] ?></td>
            </tr>
            <tr class="border-b">
                <td class="border-l px-2 py-1 font-semibold">تاریخ سفارش:</td>
                <td class="px-2 py-1" dir="ltr">
                    <?= ($factorInfo['bill_date']) ?>
                </td>
                <td class="border-l px-2 py-1 font-semibold">مبلغ فاکتور</td>
                <td class="px-2 py-1" dir="ltr">
                    <?= number_format($factorInfo['total']) ?> تومان
                </td>
            </tr>
            <tr class="border-b">
                <td class="border-l px-2 py-1 font-semibold">باقی مانده:</td>
                <td class="px-2 py-1" dir="ltr">
                    <?= number_format($remainingAmount) ?> تومان
                </td>
                <td class="border-l px-2 py-1 font-semibold">ثبت کننده:</td>
                <td class="px-2 py-1"><?= $factorInfo['user_name'] . ' ' . $factorInfo['user_family'] ?? '---' ?></td>
            </tr>
        </tbody>
    </table>
</div>

<?php
if ($status): ?>
    <div class="w-2/4 mx-auto border bg-green-500 border-green-600 rounded py-3 text-center text-white font-semibold">
        واریزی مدنظر شما موفقانه حذف گردید.
    </div>
<?php
endif;
foreach ($payments as $payment): ?>
    <div class="w-2/4 bg-white shadow-lg rounded p-3 md:p-5 max-w-4xl mx-auto mb-5">
        <h2 class="text-lg font-bold mb-2">جزئیات واریزی</h2>
        <table class="w-full text-xs text-right border border-gray-300 mt-4">
            <tbody>
                <tr class="border-b">
                    <td class="border-l px-2 py-1 font-semibold w-32">مبلغ واریزی:</td>
                    <td class="px-2 py-1"><?= number_format($payment['amount']) ?> تومان</td>
                </tr>
                <tr class="border-b">
                    <td class="border-l px-2 py-1 font-semibold w-32">شماره حساب:</td>
                    <td class="px-2 py-1"><?= htmlspecialchars($payment['account']) ?></td>
                </tr>
                <tr class="border-b">
                    <td class="border-l px-2 py-1 font-semibold">تاریخ واریز:</td>
                    <td class="px-2 py-1" dir="ltr">
                        <?= $payment['date'] ?>
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="border-l px-2 py-1 font-semibold">ثبت کننده:</td>
                    <td class="px-2 py-1"><?= htmlspecialchars($payment['user_name'] . ' ' . $payment['user_family']) ?></td>
                </tr>
                <tr class="border-b">
                    <td class="border-l px-2 py-1 font-semibold">عکس رسید:</td>
                    <td class="px-2 py-1">
                        <?php if (!empty($payment['photo'])): ?>
                            <img src="<?= htmlspecialchars(preg_replace('/^\.\.\//', '', $payment['photo'])) ?>"
                                alt="Payment Receipt"
                                class="max-w-[50%] mx-auto my-4 rounded-md shadow">
                        <?php else: ?>
                            <span class="text-gray-500">عکسی وجود ندارد</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- حذف واریزی -->
        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?factor=' . urlencode($factor_number) . '&deletePayment=' . $payment['id'] ?>"
            class="inline-block bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded shadow mt-5"
            onclick="return confirm('آیا مطمئن هستید که می‌خواهید این واریزی را حذف کنید؟');">
            حذف واریزی
        </a>

    </div>
<?php endforeach;
require_once './components/footer.php';
