<?php
$pageTitle = "واریزی ها";
$iconUrl = 'factor.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$payments = getAllPayments();

function getAllPayments()
{
    $startOfDay = date('Y-m-d 00:00:00');
    $endOfDay = date('Y-m-d 23:59:59');

    $stmt = PDO_CONNECTION->prepare("
    SELECT 
        payments.*,
        bill.total,
        bill.bill_number,
        user.name AS user_name, 
        user.family AS user_family, 
        approved_user.name AS approved_by_name,
        approved_user.family AS approved_by_family,
        customer.name AS customer_name, 
        customer.family AS customer_family
    FROM 
        factor.payments
    JOIN 
        factor.bill AS bill ON payments.bill_id = bill.id
    JOIN 
        yadakshop.users AS user ON payments.user_id = user.id
    LEFT JOIN 
        yadakshop.users AS approved_user ON payments.approved_by = approved_user.id
    JOIN 
        callcenter.customer AS customer ON payments.customer_id = customer.id
    WHERE 
        payments.created_at >= :startOfDay AND payments.created_at <= :endOfDay
    ORDER BY 
        payments.created_at DESC
");


    $stmt->bindValue(':startOfDay', $startOfDay);
    $stmt->bindValue(':endOfDay', $endOfDay);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<div class="p-6">
    <div class="flex justify-between mb-5">
        <h2 class="text-xl font-bold mb-4">لیست واریزی‌ها</h2>
        <input class="text-xs border rounded-md px-3 py-2 text-right"
            data-gdate="<?= date('Y/m/d') ?>"
            value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>"
            type="text" name="invoice_time" id="invoice_time">
    </div>
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
                <th class="border px-3 py-2 text-right">تایید کننده</th>
            </tr>
        </thead>
        <tbody id="result_box">
            <?php foreach ($payments as $payment): ?>
                <tr class="border-t">
                    <td class="px-3 py-1 text-center"><?= $payment['bill_number'] ?></td>
                    <td class="px-3 py-1"><?= $payment['customer_name'] . ' ' . $payment['customer_family'] ?></td>
                    <td class="px-3 py-1"><?= number_format($payment['total']) ?>تومان</td>
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
                    <td class="text-center">
                        <input
                            type="checkbox"
                            <?= !empty($payment['approved_by']) ? 'checked' : '' ?>
                            onchange="updateApproval(this, <?= $payment['id'] ?>)"
                            name="approved">
                        <br>
                        <span class="text-xs text-gray-500">
                            <?= !empty($payment['approved_by_name']) ?
                                $payment['approved_by_name'] . ' ' . $payment['approved_by_family'] :
                                '—' ?>
                        </span>

                    </td>
                </tr>
            <?php endforeach;
            if (!count($payments)): ?>
                <tr>
                    <td class="py-2 text-red-500 text-center font-semibold" colspan="8">
                        واریزی ای ثبت نشده است.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
    const result_box = document.getElementById('result_box');
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
                params.append('getPaymentReports', 'getPaymentReports');
                params.append('date', date);
                axios.post("../../app/api/payments/paymentApi.php", params)
                    .then(function(response) {
                        result_box.innerHTML = response.data;
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
            },
            onRender: function() {}
        });
    });

    function updateApproval(element, paymentId) {
        const isApproved = element.checked ? 1 : 0;

        axios.post('../../app/api/payments/paymentApi.php', new URLSearchParams({
                updateApproval: 'updateApproval',
                payment_id: paymentId,
                approved: isApproved
            }))
            .then(response => {
                if (response.data.success) {
                    alert("عملیات موفقانه صورت گرفت.");
                    location.reload();
                } else {
                    alert('Failed to update approval');
                    element.checked = !element.checked; // Revert checkbox
                }
            })
            .catch(error => {
                console.error('Approval error:', error);
                alert('Network error occurred');
                element.checked = !element.checked; // Revert checkbox
            });
    }
</script>
<?php
require_once './components/footer.php';
