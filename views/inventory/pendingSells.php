<?php
$pageTitle = "فاکتورهای منتظر خروج";
$iconUrl = 'pending.svg';
require_once './components/header.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../app/controller/inventory/pendingSellsController.php';
require_once '../../layouts/inventory/sidebar.php';
$dateTime = jdate('Y-m-d'); ?>
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <img src="../../public/img/<?= $iconUrl ?>" alt="icon" class="w-6 h-6">
            <?= $pageTitle ?>
        </h1>
        <span class="text-sm text-gray-500">
            تاریخ:
            <span class="inline-block" dir="rtl">
                <?= $dateTime ?>
            </span>
        </span>
    </div>

    <!-- Factors Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-lg text-gray-700">لیست فاکتورها</h2>
            <form>
                <div class="relative cursor-pointer mb-2">
                    <label class="text-sm font-semibold absolute top-1.5 left-0" for="invoice_time">
                        <img class="hidden sm:inline" src="./assets/img/calender.svg" alt="calender icon">
                    </label>
                    <input class="text-sm py-2 px-3 font-semibold sm:w-60 border-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="invoice_time" id="invoice_time">
                </div>
                <div id="loading_box" class="flex gap-2 items-center hidden">
                    <svg width="15px" height="15px" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="none" class="animate-spin">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g fill="#000000" fill-rule="evenodd" clip-rule="evenodd">
                                <path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13zM0 8a8 8 0 1116 0A8 8 0 010 8z" opacity=".2"></path>
                                <path d="M7.25.75A.75.75 0 018 0a8 8 0 018 8 .75.75 0 01-1.5 0A6.5 6.5 0 008 1.5a.75.75 0 01-.75-.75z"></path>
                            </g>
                        </g>
                    </svg>
                    <p class="text-xs"> لطفا صبور باشید ...</p>
                </div>
            </form>
        </div>
        <div id="resultBox">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">شماره فاکتور</th>
                            <th class="px-4 py-3">مشتری</th>
                            <th class="px-4 py-3">تاریخ</th>
                            <th class="px-4 py-3">مبلغ فاکتور</th>
                            <th class="px-4 py-3">وضعیت</th>
                            <th class="px-4 py-3">مقدار فاکتور/خروج</th>
                            <th class="px-4 py-3">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php
                        // Example data - replace with DB fetch
                        $factors = $allPendingSells; // Assume this variable is populated with pending sells data

                        foreach ($factors as $i => $f):
                            $statusMatch = $f['bill_quantity'] == $f['difference']; // Example condition
                        ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3"><?= $i + 1 ?></td>
                                <td class="px-4 py-3 font-medium"><?= $f['bill_number'] ?></td>
                                <td class="px-4 py-3"><?= $f['customer_name'] . ' ' . $f['customer_family'] ?></td>
                                <td class="px-4 py-3"><?= $f['bill_date'] ?></td>
                                <td class="px-4 py-3 text-gray-700"><?= number_format($f['total']) ?> ریال</td>
                                <td class="px-4 py-3">
                                    <?php if ($f['exit_quantity'] > 0): ?>
                                        <?php if ($statusMatch): ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                                مطابقت دارد
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                                مغایرت دارد
                                            </span an>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold text-yellow-600 bg-yellow-100 rounded-full">
                                            خروج نخورده
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?= $f['bill_quantity'] . ' / ' . $f['exit_quantity'] ?>
                                </td>
                                <td class="px-4 py-3 flex gap-2">
                                    <a class="hide_while_print" href="../factor/complete.php?factor_number=<?= $f['id'] ?>">
                                        <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده فاکتور" src="./assets/icons/receipt.svg" />
                                    </a>
                                    <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $f['id'] ?>">
                                        <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده جزئیات" src="./assets/icons/telescope.svg" />
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const resultBox = document.getElementById('resultBox');
    const loading_box = document.getElementById('loading_box');
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
                loading_box.classList.remove('hidden')
                var params = new URLSearchParams();
                params.append('getFactor', 'getFactor');
                params.append('date', date);
                axios.post("../../app/api/inventory/pendingSellsApi.php", params)
                    .then(function(response) {
                        resultBox.innerHTML = response.data;
                        loading_box.classList.add('hidden');
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
            },
            onRender: function() {}
        });
    });
</script>

<?php
require_once './components/footer.php';
?>