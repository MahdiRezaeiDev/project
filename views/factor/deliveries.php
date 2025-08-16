<?php
$pageTitle = "مدیریت ارسال اجناس";
$iconUrl = 'delivery.svg';
require_once './components/header.php';
require_once '../../app/controller/factor/DeliveriesController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<div class="flex flex-col w-full h-full">
    <div class="flex flex-col w-full h-full">
        <div class="flex items-center justify-between px-2 py-2 bg-white shadow-md">
            <h1 class="text-lg font-semibold text-gray-800">مدیریت ارسال اجناس</h1>
            <div class="relative">
                <input type="text" name="date" id="datePicker"
                    value="<?= jdate('Y/m/d') ?>"
                    class="border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs">
                <img src="./assets/icons/calender.svg" class="absolute top-2 left-2" alt="">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 p-4">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-700 text-white">
                    <tr>
                        <th class="px-2 py-2 border-b text-right text-xs">#</th>
                        <th class="px-2 py-2 border-b text-right text-xs"></th>
                        <th class="px-2 py-2 border-b text-right text-xs">مشتری</th>
                        <th class="px-2 py-2 border-b text-right text-xs">شماره فاکتور</th>
                        <th class="px-2 py-2 border-b text-right text-xs">نوع تماس</th>
                        <th class="px-2 py-2 border-b text-right text-xs">آدرس</th>
                        <th class="px-2 py-2 border-b text-right text-xs">نوع ارسال</th>
                    </tr>
                </thead>
                <tbody id="yadak">
                    <?php foreach ($todayDeliveries as $index => $delivery): ?>
                        <tr id="record_<?= ($delivery['bill_number']) ?>" class="hover:bg-gray-100 even:bg-gray-50">
                            <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>
                            <td class=" border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['bill_number']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['contact_type']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="min-w-full bg-white">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-2 py-2 border-b text-right text-xs">#</th>
                        <th class="px-2 py-2 border-b text-right text-xs"></th>
                        <th class="px-2 py-2 border-b text-right text-xs">مشتری</th>
                        <th class="px-2 py-2 border-b text-right text-xs">شماره فاکتور</th>
                        <th class="px-2 py-2 border-b text-right text-xs">نوع تماس</th>
                        <th class="px-2 py-2 border-b text-right text-xs">آدرس</th>
                        <th class="px-2 py-2 border-b text-right text-xs">نوع ارسال</th>
                    </tr>
                </thead>
                <tbody id="customer">
                    <?php foreach ($customerDeliveries as $index => $delivery): ?>
                        <tr id="record_<?= ($delivery['bill_number']) ?>" class="hover:bg-gray-100 even:bg-gray-50">
                            <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>
                            <td class="border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                    <img class="w-5 h-5 object-contain cursor-pointer block"
                                        title="مشاهده جزئیات"
                                        src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>

                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['bill_number']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['contact_type']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="min-w-full bg-white">
                <thead class="bg-sky-700 text-white">
                    <tr>
                        <th class="px-2 py-2 border-b text-right text-xs">#</th>
                        <th class="px-2 py-2 border-b text-right text-xs"></th>
                        <th class="px-2 py-2 border-b text-right text-xs">مشتری</th>
                        <th class="px-2 py-2 border-b text-right text-xs">شماره فاکتور</th>
                        <th class="px-2 py-2 border-b text-right text-xs">نوع تماس</th>
                        <th class="px-2 py-2 border-b text-right text-xs">آدرس</th>
                        <th class="px-2 py-2 border-b text-right text-xs">نوع ارسال</th>
                    </tr>
                </thead>
                <tbody id="deliveries">
                    <?php foreach ($deliveries as $index => $delivery): ?>
                        <tr id="record_<?= ($delivery['bill_number']) ?>" class="hover:bg-gray-100 even:bg-gray-50">
                            <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>
                            <td class="border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['bill_number']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['contact_type']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?></td>
                            <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // Toast function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-2 py-2 rounded shadow-lg text-white z-50 transition-opacity duration-500 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;

        document.body.appendChild(toast);

        // Fade out and remove after 3s
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.remove();
                location.reload();
            }, 500); // wait for fade-out animation
        }, 3000);
    }

    $(function() {
        $("#datePicker").persianDatepicker({
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
                const date = ($("#datePicker").attr("data-gdate"));
                showPreviousDeliveries(date);
            },
            onRender: function() {}
        });
    });

    function showPreviousDeliveries(date) {
        const params = new URLSearchParams();
        params.append('getPreviousDeliveries', 'getPreviousDeliveries');
        params.append('date', date);
        axios.post("../../app/api/factor/DeliveryApi.php", params)
            .then(function(response) {

                if (response.data.yadakDeliveries) {
                    const deliveries = response.data.yadakDeliveries || [];
                    let html = '';
                    let index = 0;
                    deliveries.forEach(delivery => {
                        html += `<tr>
                            <td class="px-2 py-2 border-b text-xs">${ ++index }</td>
                            <td class="border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=${ delivery.bill_id}">
                                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.kharidar}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.bill_number}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.contact_type}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.destination}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.type}</td>
                        </tr>`;
                    });
                    document.getElementById('yadak').innerHTML = html;
                } else {
                    document.getElementById('yadak').innerHTML = `<tr>
                        <td colspan="7" class="px-2 py-2 border-b text-xs text-center">هیچ ارسال ثبت نشده است.</td>
                    </tr>`;
                }

                if (response.data.customerDeliveries) {
                    const deliveries = response.data.customerDeliveries || [];
                    let html = '';
                    let index = 0;
                    deliveries.forEach(delivery => {
                        html += `<tr>
                            <td class="px-2 py-2 border-b text-xs">${ ++index }</td>
                            <td class="border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=${ delivery.bill_id}">
                                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.kharidar}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.bill_number}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.contact_type}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.destination}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.type}</td>
                        </tr>`;
                    });
                    document.getElementById('customer').innerHTML = html;
                } else {
                    document.getElementById('customer').innerHTML = `<tr>
                        <td colspan="7" class="px-2 py-2 border-b text-xs text-center">هیچ ارسال ثبت نشده است.</td>
                    </tr>`;
                }

                if (response.data.allDeliveries) {
                    const deliveries = response.data.allDeliveries || [];
                    let html = '';
                    let index = 0;
                    deliveries.forEach(delivery => {
                        html += `<tr>
                            <td class="px-2 py-2 border-b text-xs">${ ++index }</td>
                            <td class="border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=${ delivery.bill_id}">
                                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.kharidar}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.bill_number}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.contact_type}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.destination}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.type}</td>
                        </tr>`;
                    });
                    document.getElementById('deliveries').innerHTML = html;
                } else {
                    document.getElementById('deliveries').innerHTML = `<tr>
                        <td colspan="7" class="px-2 py-2 border-b text-xs text-center">هیچ ارسال ثبت نشده است.</td>
                    </tr>`;
                }


            })
            .catch(function() {
                showToast("خطا در بارگذاری ارسال‌ها، لطفا مجددا تلاش نمایید", 'error');
            });
    }
</script>
<?php require_once './components/footer.php'; ?>