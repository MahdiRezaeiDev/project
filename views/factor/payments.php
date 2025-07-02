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
<style>
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

        #wrapper {
            padding: 0;
            margin: 0;
            /* Remove padding and margin */
            box-shadow: none;
        }
    }
</style>
<div class="p-6">
    <div class="flex justify-between mb-5">
        <h2 class="text-xl font-bold mb-4">لیست واریزی‌ها</h2>
    </div>

    <div>
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 p-4 bg-white rounded-lg shadow mb-4">

            <div>
                <label class="text-sm">تاریخ فاکتور</label>
                <input type="text" id="factor_date" name="factor_date_display"
                    class="w-full border rounded px-2 py-1" autocomplete="off" />
                <input type="hidden" name="factor_date" id="factor_date_real" />
            </div>

            <div>
                <label class="text-sm">تاریخ واریزی</label>
                <input type="text" id="payment_date" name="payment_date_display"
                    class="w-full border rounded px-2 py-1" autocomplete="off" />
                <input type="hidden" name="payment_date" id="payment_date_real" />
            </div>

            <div>
                <label class="text-sm">شماره فاکتور</label>
                <input type="text" name="factor_number" class="w-full border rounded px-2 py-1" />
            </div>

            <div>
                <label class="text-sm">اسم مشتری</label>
                <input type="text" name="customer_name" class="w-full border rounded px-2 py-1" />
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full text-sm">
                    اعمال فیلتر
                </button>
                <button type="button" id="clearFilters"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 w-full text-sm">
                    پاک کردن
                </button>
            </div>

        </form>
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

            <?php if (count($payments)):
                $totalPayment = 0;
                foreach ($payments as $payment):
                    $totalPayment += $payment['amount']; ?>
                    <tr class="border-t">
                        <td class="px-3 py-1 text-center"><?= $payment['bill_number'] ?></td>
                        <td class="px-3 py-1"><?= $payment['customer_name'] . ' ' . $payment['customer_family'] ?></td>
                        <td class="px-3 py-1"><?= number_format($payment['total']) ?>ریال</td>
                        <td class="px-3 py-1"><?= $payment['user_name'] . ' ' . $payment['user_family'] ?></td>
                        <td class="px-3 py-1 text-right"><?= number_format($payment['amount']) ?> ریال</td>
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
                <?php endforeach; ?>
                <tr class="border-t bg-gray-800 text-white">
                    <td class="px-3 py-2 font-semibold text-left" colspan="4">
                        مجموع واریزی
                    </td>
                    <td class="px-3 py-2 text-right font-semibold" colspan="5">
                        <?= number_format($totalPayment); ?>
                    </td>
                </tr>
            <?php
            endif;
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

    // اتصال به تقویم شمسی
    $(function() {
        $("#factor_date").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            onSelect: function() {
                const gdate = $("#factor_date").attr("data-gdate");
                $("#factor_date_real").val(gdate);
            }
        });

        $("#payment_date").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            onSelect: function() {
                const gdate = $("#payment_date").attr("data-gdate");
                $("#payment_date_real").val(gdate);
            }
        });
    });

    // گرفتن تمام داده‌های فرم و ارسال به API
    document.getElementById("filterForm").addEventListener("submit", function(e) {
        e.preventDefault(); // جلوگیری از ارسال پیش‌فرض فرم

        const form = e.target;
        const formData = new FormData(form);

        // اضافه کردن یک فیلد اضافی (اختیاری)
        formData.append("filterRequest", "true");

        result_box.innerHTML = "<tr><td colspan='9' class='text-center py-4 text-gray-500'>در حال بارگذاری...</td></tr>";

        axios.post("../../app/api/payments/paymentApi.php", formData)
            .then(function(response) {
                result_box.innerHTML = response.data;
            })
            .catch(function(error) {
                console.error("خطا در ارسال فرم:", error);
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

    document.getElementById("clearFilters").addEventListener("click", function() {
        const form = document.getElementById("filterForm");
        form.reset();

        // Also clear hidden inputs if needed
        document.getElementById("factor_date_real").value = "";
        document.getElementById("payment_date_real").value = "";

        // Optionally, trigger form submission again to reset the results
        // form.submit();
    });
</script>
<?php
require_once './components/footer.php';
