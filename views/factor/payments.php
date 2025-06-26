<?php
$pageTitle = "واریزی ها";
$iconUrl = 'factor.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$payments = getAllPayments();
function getAllPayments()
{
    $stmt = PDO_CONNECTION->prepare("
        SELECT 
            payments.*,
            bill.total,
            bill.bill_number,
            user.name AS user_name, 
            user.family AS user_family, 
            customer.name AS customer_name, 
            customer.family AS customer_family
        FROM 
            factor.payments
        JOIN 
            factor.bill AS bill ON payments.bill_id = bill.id
        JOIN 
            yadakshop.users AS user ON payments.user_id = user.id
        JOIN 
            callcenter.customer AS customer ON payments.customer_id = customer.id
        ORDER BY 
            payments.date DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<div class="p-6">
    <h2 class="text-xl font-bold mb-4">لیست واریزی‌ها</h2>
    <table class="w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2 text-right">شماره فاکتور</th>
                <th class="border px-3 py-2 text-right">مشتری</th>
                <th class="border px-3 py-2 text-right">مبلغ فاکتور</th>
                <th class="border px-3 py-2 text-right">کاربر ثبت کننده</th>
                <th class="border px-3 py-2 text-right">مبلغ واریزی</th>
                <th class="border px-3 py-2 text-right">تاریخ</th>
                <th class="border px-3 py-2 text-right">شماره حساب</th>
                <th class="border px-3 py-2 text-right">تصویر</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr class="border-t">
                    <td class="px-3 py-1 text-center"><?= $payment['bill_number'] ?></td>
                    <td class="px-3 py-1"><?= $payment['customer_name'] . ' ' . $payment['customer_family'] ?></td>
                    <td class="px-3 py-1"><?= number_format($payment['total']) ?> تومان </td>
                    <td class="px-3 py-1"><?= $payment['user_name'] . ' ' . $payment['user_family'] ?></td>
                    <td class="px-3 py-1 text-right"><?= number_format($payment['amount']) ?> تومان</td>
                    <td class="px-3 py-1"><?= $payment['date'] ?></td>
                    <td class="px-3 py-1"><?= $payment['account'] ?></td>
                    <td class="px-3 py-1 text-center">
                        <?php if (!empty($payment['photo'])): ?>
                            <a href="../../app/controller/payment/<?= $payment['photo'] ?>" target="_blank" class="text-blue-600">نمایش</a>
                        <?php else: ?>
                            <span class="text-gray-400">ندارد</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once './components/footer.php';
