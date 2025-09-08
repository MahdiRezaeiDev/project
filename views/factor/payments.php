<?php
$pageTitle = "واریزی ها";
$iconUrl = 'pay.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$payments = getAllPayments();
$users = getAllUsers();

function getAllPayments()
{
    $startOfDay = date('Y-m-d 00:00:00');
    $endOfDay = date('Y-m-d 23:59:59');

    $stmt = PDO_CONNECTION->prepare("
    SELECT 
        payments.*,
        bill.total,
        bill.bill_number,
        bill.bill_date,
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
        payments.created_at DESC");


    $stmt->bindValue(':startOfDay', $startOfDay);
    $stmt->bindValue(':endOfDay', $endOfDay);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllUsers()
{
    $stmt = PDO_CONNECTION->prepare("SELECT id, name, family FROM yadakshop.users WHERE password IS NOT NULL AND password != '' AND name is not null AND family is not null ORDER BY name, family");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$financeTeam = ['mahdi', 'babak', 'niyayesh', 'reyhan', 'ahmadiyan', 'sabahashemi', 'hadishasanpouri', 'rana'];

?>
<style>
    #customer_results.fade-out {
        opacity: 0;
        transition: opacity 0.3s ease;
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

        #wrapper {
            padding: 0;
            margin: 0;
            /* Remove padding and margin */
            box-shadow: none;
        }
    }

    @media print {
        body {
            font-size: 11px;
            line-height: 1.2;
        }

        table {
            font-size: 10px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 3px 5px !important;
        }

        h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .text-xs {
            font-size: 10px !important;
        }

        .text-sm {
            font-size: 11px !important;
        }

        .py-1,
        .py-2,
        .py-3,
        .py-4 {
            padding-top: 2px !important;
            padding-bottom: 2px !important;
        }

        .px-2,
        .px-3,
        .px-4,
        .px-5 {
            padding-left: 4px !important;
            padding-right: 4px !important;
        }

        input[type="text"] {
            font-size: 10px !important;
            padding: 2px 4px !important;
        }

        .w-full {
            width: 100% !important;
        }

        /* Optional: force full width */
        html,
        body,
        #wrapper {
            width: 100%;
            margin: 0;
            padding: 0;
        }
    }
</style>
<div class="p-6">
    <div class="flex justify-between mb-5">
        <h2 class="text-xl font-bold mb-4">لیست واریزی‌ها</h2>
        <button class="rounded-md bg-sky-800 hover:bg-sky-600 text-white px-5 print:hidden" onclick="window.print()">پرینت</button>
    </div>

    <div class="print:hidden">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4 p-4 bg-white rounded-lg shadow mb-4">

            <div>
                <label class="text-sm">تاریخ ثبت</label>
                <input type="text" id="register_date" name="register_date_display"
                    class="w-full border rounded px-2 py-1" autocomplete="off" />
                <input type="hidden" name="register_date" id="register_date_real" />
            </div>
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
            <div>
                <label class="text-sm">صاحب حساب</label>
                <input type="text" name="card_number" class="w-full border rounded px-2 py-1" />
            </div>
            <div>
                <label class="text-sm">کاربر</label>
                <select type="text" name="user" class="w-full border rounded px-2 py-1">
                    <option value="0">همه کاربران</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= $user['name'] . ' ' . $user['family'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full text-sm">
                    فیلتر
                </button>
                <button type="button" id="clearFilters"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 w-full text-sm">
                    پاک کردن
                </button>
            </div>

        </form>
    </div>
    <div id="description-success-msg"
        class="hidden px-3 py-2 bg-green-100 border border-green-300 text-green-700 text-sm rounded mb-3 transition-all duration-300">
    </div>

    <table class="w-full border border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2 text-right">#</th>
                <th class="border px-3 py-2 text-right">شماره فاکتور</th>
                <th class="border px-3 py-2 text-right">مشتری</th>
                <th class="border px-3 py-2 text-right">مبلغ فاکتور</th>
                <th class="border px-3 py-2 text-right">تاریخ فاکتور</th>
                <th class="border px-3 py-2 text-right">کاربر</th>
                <th class="border px-3 py-2 text-right">مبلغ واریزی</th>
                <th class="border px-3 py-2 text-right">تاریخ واریزی</th>
                <th class="border px-3 py-2 text-right">صاحب حساب</th>
                <th class="border px-3 py-2 text-right hide_while_print">تصویر</th>
                <th class="border px-3 py-2 text-right">ذی نفع</th>
                <th class="border px-3 py-2 text-right">تایید کننده</th>
            </tr>
        </thead>
        <tbody id="result_box">
            <?php if (count($payments)):
                $totalPayment = 0;
                foreach ($payments as $index => $payment):
                    $totalPayment += $payment['amount']; ?>
                    <tr class="border-t">
                        <td class="px-3 py-1 print:text-xs text-center"><?= ++$index; ?></td>
                        <td class="px-3 py-1 print:text-xs text-center"><?= $payment['bill_number'] ?></td>
                        <td class="px-3 py-1 print:text-xs"><?= $payment['customer_name'] . ' ' . $payment['customer_family'] ?></td>
                        <td class="px-3 py-1 print:text-xs"><?= number_format($payment['total']) ?>ریال</td>
                        <td class="px-3 py-1 print:text-xs"><?= $payment['bill_date'] ?></td>
                        <td class="px-3 py-1 print:text-xs"><?= $payment['user_name'] . ' ' . $payment['user_family'] ?></td>

                        <?php if (in_array($_SESSION['username'], $financeTeam)): ?>
                            <!-- Editable amount -->
                            <td class="px-3 py-1 print:text-xs text-right">
                                <input class="border-2 p-2 text-xs w-full" type="text"
                                    value="<?= number_format($payment['amount']) ?>"
                                    onchange="updatePaymentProperty(this.value, <?= $payment['id'] ?>, 'amount')">
                            </td>

                            <!-- Editable date -->
                            <td class="px-3 py-1 print:text-xs text-right">
                                <input
                                    type="text"
                                    class="border-2 p-2 text-xs w-full jalali-date"
                                    id="payment_date_<?= $payment['id'] ?>"
                                    data-payment-id="<?= $payment['id'] ?>"
                                    value="<?= jdate($payment['date']) ?>"
                                    data-original-date="<?= $payment['date'] ?>"
                                    readonly />
                            </td>


                            <!-- Editable account -->
                            <td class="px-3 py-1 print:text-xs text-right">
                                <input class="border-2 p-2 text-xs w-full" type="text"
                                    value="<?= $payment['account'] ?>"
                                    onchange="updatePaymentProperty(this.value, <?= $payment['id'] ?>)">
                            </td>
                        <?php else: ?>
                            <!-- Read-only for non-finance -->
                            <td class="px-3 py-1 print:text-xs text-right"><?= number_format($payment['amount']) ?></td>
                            <td class="px-3 py-1 print:text-xs"><?= $payment['date'] ?></td>
                            <td class="px-3 py-1 print:text-xs"><?= $payment['account'] ?></td>
                        <?php endif; ?>

                        <td class="px-3 py-1 print:text-xs text-center hide_while_print">
                            <?php if (!empty($payment['photo'])): ?>
                                <a href="../../app/controller/payment/<?= $payment['photo'] ?>" target="_blank" class="text-blue-600">نمایش</a>
                            <?php else: ?>
                                <span class="text-gray-400">ندارد</span>
                            <?php endif; ?>
                        </td>

                        <!-- Description editable field -->
                        <td class="px-3 py-1 print:text-xs relative">
                            <input
                                onkeyup="convertToPersian(this); searchCustomer(this.value, <?= $payment['id'] ?>)"
                                type="text"
                                name="customer"
                                data-payment-id="<?= $payment['id'] ?>"
                                class="py-3 px-3 w-full print:border-none border-2 text-xs border-gray-300 focus:outline-none text-gray-900 font-semibold"
                                id="customer_name_<?= $payment['id'] ?>"
                                value="<?= $payment['description'] ?>"
                                placeholder="اسم کامل مشتری را وارد نمایید ..." />

                            <div
                                id="customer_results_<?= $payment['id'] ?>"
                                class="absolute top-full mb-1 left-0 right-0 bg-white rounded-md shadow z-50 max-h-56 overflow-y-auto text-sm">
                            </div>
                        </td>

                        <!-- Approval -->
                        <td class="text-center">
                            <input
                                type="checkbox"
                                <?= !empty($payment['approved_by']) ? 'checked' : '' ?>
                                onchange="updateApproval(this, <?= $payment['id'] ?>)"
                                name="approved">
                            <br>
                            <span class="text-xs text-gray-500">
                                <?= !empty($payment['approved_by_name']) ? $payment['approved_by_name'] . ' ' . $payment['approved_by_family'] : '—' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- Total Row -->
                <tr class="border-t bg-gray-800 text-white">
                    <td class="px-3 py-2 font-semibold text-left" colspan="6">
                        مجموع واریزی
                    </td>
                    <td class="px-3 py-2 text-right font-semibold" colspan="6">
                        <?= number_format($totalPayment); ?>
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td class="py-2 text-red-500 text-center font-semibold" colspan="12">
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
        $("#register_date").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            onSelect: function() {
                const gdate = $("#register_date").attr("data-gdate");
                $("#register_date_real").val(gdate);
            }
        });

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
                    showSuccessMessage("تغیرات با موفقیت ذخیره شد");
                    element.nextElementSibling.nextElementSibling.innerHTML = "<?= $_SESSION['user']['name'] ?>" + " " + "<?= $_SESSION['user']['family'] ?>";
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
        document.getElementById("register_date_real").value = "";

        // Optionally, trigger form submission again to reset the results
        // form.submit();
    });

    function updateDescription(button) {
        const name = button.dataset.name ?? '';
        const family = button.dataset.family ?? '';
        const paymentId = button.dataset.paymentId ?? '';

        const fullName = (name + ' ' + family).trim();

        const formData = new FormData();
        formData.append('updateDescription', true);
        formData.append('id', paymentId);
        formData.append('description', fullName);

        axios.post('../../app/api/payments/paymentApi.php', formData)
            .then(response => {
                if (response.data.status === 'success') {
                    showSuccessMessage("توضیحات با موفقیت ذخیره شد");

                    const input = document.querySelector(`#customer_name_${paymentId}`);
                    if (input) input.value = fullName;

                    const resultBox = document.getElementById('customer_results_' + paymentId);
                    if (resultBox) resultBox.innerHTML = '';
                } else {
                    alert('خطا در ذخیره توضیحات');
                }
            })
            .catch(error => {
                console.error(error);
                alert('خطا در ارتباط با سرور');
            });
    }

    function searchCustomer(pattern, paymentId) {
        const commonApiEndpoint = "../../app/api/factor/FactorCommonApi.php";
        const customer_results = document.getElementById('customer_results_' + paymentId);

        pattern = pattern.trim();
        if (pattern.length >= 3) {
            customer_results.innerHTML = '<p class="text-sm text-gray-400">در حال جستجو...</p>';

            const params = new URLSearchParams();
            params.append("customer_search", "customer_search");
            params.append("pattern", pattern);

            axios.post(commonApiEndpoint, params)
                .then(function(response) {
                    let template = `
                    <div class="w-full flex items-center justify-between gap-2 border border-red-300 rounded-lg p-3 shadow-sm hover:shadow-md transition mb-2 bg-red-50">
                        <span class="text-xs font-bold text-gray-700">حذف مشتری</span>
                        <button
                            onclick="clearCustomer(${paymentId})"
                            class="w-6 h-6 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-full shadow transition"
                            title="پاک کردن مشتری">
                            <i class="material-icons text-xs">delete</i>
                        </button>
                    </div>
                `;

                    if (response.data.length > 0) {
                        for (const customer of response.data) {
                            template += `
                            <div class="w-full flex items-center justify-between gap-2 border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow-md transition mb-2 bg-white">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800">${customer.name} ${customer.family}</span>
                                    <span class="text-xs text-gray-500">${customer.phone}</span>
                                </div>
                                <button
                                    onclick="updateDescription(this)"
                                    data-payment-id="${paymentId}"
                                    data-name="${customer.name}"
                                    data-family="${customer.family}"
                                    class="w-6 h-6 flex items-center justify-center bg-green-500 hover:bg-green-600 text-white rounded-full shadow transition"
                                    title="انتخاب مشتری">
                                    <i class="material-icons text-xs">add</i>
                                </button>
                            </div>
                        `;
                        }
                    } else {
                        template += `<p class="text-sm text-gray-500 text-center">مشتری یافت نشد.</p>`;
                    }

                    customer_results.innerHTML = template;
                })
                .catch(function(error) {
                    console.error(error);
                    customer_results.innerHTML = `<p class="text-sm text-red-500 text-center">خطا در دریافت داده‌ها</p>`;
                });
        } else {
            customer_results.innerHTML = "";
        }
    }

    function clearCustomer(paymentId) {
        const formData = new FormData();
        formData.append('updateDescription', true);
        formData.append('id', paymentId);
        formData.append('description', '');

        axios.post('../../app/api/payments/paymentApi.php', formData)
            .then(response => {
                if (response.data.status === 'success') {
                    showSuccessMessage("مشتری با موفقیت حذف شد");

                    const input = document.querySelector(`#customer_name_${paymentId}`);
                    if (input) input.value = '';

                    const resultBox = document.getElementById('customer_results_' + paymentId);
                    if (resultBox) resultBox.innerHTML = '';
                } else {
                    alert('خطا در حذف مشتری');
                }
            })
            .catch(error => {
                console.error(error);
                alert('خطا در حذف مشتری از پایگاه داده');
            });
    }

    function showSuccessMessage(message) {
        const msgDiv = document.getElementById('description-success-msg');
        msgDiv.textContent = message;
        msgDiv.classList.remove('hidden');

        setTimeout(() => {
            msgDiv.classList.add('hidden');
        }, 3000); // مخفی کردن بعد از ۳ ثانیه
    }

    function updatePaymentProperty(value, paymentId, property) {
        const formData = new FormData();
        formData.append('updateProperty', true);
        formData.append('id', paymentId);
        formData.append('owner', value);
        formData.append('property', property || 'account');

        axios.post('../../app/api/payments/paymentApi.php', formData)
            .then(response => {
                if (response.data.status === 'success') {
                    showSuccessMessage("توضیحات با موفقیت ذخیره شد");
                } else {
                    alert('خطا در ذخیره توضیحات');
                }
            })
            .catch(error => {
                console.error(error);
                alert('خطا در ارتباط با سرور');
            });
    }

    const customerResults = document.getElementById('customer_results');
    if (customerResults) {
        customerResults.classList.add('fade-out');
        setTimeout(() => {
            customerResults.innerHTML = '';
            customerResults.classList.remove('fade-out');
        }, 300);
    }

    $(function() {
        $(".jalali-date").each(function() {
            const input = $(this);
            const id = input.attr("id");

            input.persianDatepicker({
                showGregorianDate: !1,
                persianNumbers: !0,
                formatDate: "YYYY/MM/DD",
                onSelect: function() {
                    const jalaliDate = $("#" + id).attr("data-jdate");
                    const paymentId = $("#" + id).data("payment-id");

                    updatePaymentProperty(jalaliDate, paymentId, 'date');
                }
            });
        });
    });
</script>

<?php
require_once './components/footer.php';
