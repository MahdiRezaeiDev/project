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
        <div class="flex items-center justify-between px-4 py-2 bg-white shadow-md">
            <h1 class="text-lg font-semibold text-gray-800">مدیریت ارسال اجناس</h1>
            <div class="relative">
                <input type="text" name="date" id="datePicker"
                    value="<?= jdate('Y/m/d') ?>"
                    class="border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <img src="./assets/icons/calender.svg" class="absolute top-2 left-2" alt="">
            </div>
        </div>
        <div class="flex flex-col p-4">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-700 text-white">
                    <tr>
                        <th class="px-4 py-2 border-b text-right">#</th>
                        <th class="px-4 py-2 border-b text-right"></th>
                        <th class="px-4 py-2 border-b text-right">مشتری</th>
                        <th class="px-4 py-2 border-b text-right">شماره فاکتور</th>
                        <th class="px-4 py-2 border-b text-right">نوع تماس</th>
                        <th class="px-4 py-2 border-b text-right">آدرس</th>
                        <th class="px-4 py-2 border-b text-right">نوع ارسال</th>
                        <th class="px-4 py-2 border-b text-right">عملیات</th>
                    </tr>
                </thead>
                <tbody id="deliveryTableBody">
                    <?php foreach ($todayDeliveries as $index => $delivery): ?>
                        <tr id="record_<?= ($delivery['bill_number']) ?>" class="hover:bg-gray-100 even:bg-gray-50">
                            <td class="px-4 py-2 border-b text-sm"><?= ++$index; ?></td>
                            <td class="px-4 py-2 border-b text-sm">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                    <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-4 py-2 border-b text-sm"><?= htmlspecialchars($delivery['kharidar']) ?></td>
                            <td class="px-4 py-2 border-b text-sm"><?= htmlspecialchars($delivery['bill_number']) ?></td>
                            <td class="px-4 py-2 border-b text-sm"><?= htmlspecialchars($delivery['contact_type']) ?></td>
                            <td class="px-4 py-2 border-b text-sm"><?= htmlspecialchars($delivery['destination']) ?></td>
                            <td class="px-4 py-2 border-b text-sm"><?= htmlspecialchars($delivery['type']) ?></td>
                            <td class="px-4 py-2 border-b text-sm">
                                <button
                                    class="px-2 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
                                    onclick="displayDeliveryModal(this)"
                                    data-bill="<?= htmlspecialchars($delivery['bill_number']) ?>"
                                    data-contact="<?= htmlspecialchars($delivery['contact_type']) ?>"
                                    data-destination="<?= htmlspecialchars($delivery['destination']) ?>"
                                    data-type="<?= htmlspecialchars($delivery['type']) ?>"
                                    data-address="<?= htmlspecialchars($delivery['destination']) ?>">
                                    ویرایش
                                </button>
                                <button
                                    class="px-2 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                                    onclick="deleteDelivery(<?= htmlspecialchars($delivery['bill_number']) ?>)">
                                    حذف
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div id="deliveryModal" class="hidden fixed inset-0 bg-gray-900/75 flex justify-center items-center">
    <div class="bg-white p-4 rounded w-2/3">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl mb-2">ارسال اجناس</h2>
            <img class="cursor-pointer" src="../callcenter/assets/img/close.svg" alt="close icon" onclick="document.getElementById('deliveryModal').classList.add('hidden')">
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
<script>
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
                if (response.data.status === 'success') {
                    showToast('ویرایش موفقانه صورت گرفت', 'success');
                } else {
                    showToast(response.data.message || 'عملیات انجام نشد', 'error');
                }
                document.getElementById('deliveryModal').classList.add('hidden');
            })
            .catch(function() {
                showToast("خطا در هنگام ثبت ارسال، لطفا مجددا تلاش نمایید", 'error');
            });
    }

    // Toast function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow-lg text-white z-50 transition-opacity duration-500 ${
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

    function deleteDelivery(billNumber) {
        if (confirm("آیا از حذف این ارسال مطمئن هستید؟")) {
            const params = new URLSearchParams();
            params.append('deleteDelivery', 'deleteDelivery');
            params.append('billNumber', billNumber);
            axios.post("../../app/api/factor/DeliveryApi.php", params)
                .then(function(response) {
                    if (response.data.status === 'success') {
                        showToast('ارسال با موفقیت حذف شد', 'success');
                        const record = document.getElementById(`record_${billNumber}`);
                        if (record) {
                            record.remove();
                        }
                    } else {
                        showToast(response.data.message || 'خطا در حذف ارسال', 'error');
                    }
                })
                .catch(function() {
                    showToast("خطا در حذف ارسال، لطفا مجددا تلاش نمایید", 'error');
                });
        }
    }

    function showPreviousDeliveries(date) {
        const params = new URLSearchParams();
        params.append('getPreviousDeliveries', 'getPreviousDeliveries');
        params.append('date', date);
        axios.post("../../app/api/factor/DeliveryApi.php", params)
            .then(function(response) {

                if (response.data) {
                    const deliveries = response.data.data || [];
                    let html = '';
                    let index = 0;
                    deliveries.forEach(delivery => {

                        html += `<tr>
                            <td class="px-4 py-2 border-b text-sm">${ ++index }</td>
                            <td class="px-4 py-2 border-b text-sm">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=${ delivery.bill_id}">
                                    <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-4 py-2 border-b text-sm">${delivery.kharidar}</td>
                            <td class="px-4 py-2 border-b text-sm">${delivery.bill_number}</td>
                            <td class="px-4 py-2 border-b text-sm">${delivery.contact_type}</td>
                            <td class="px-4 py-2 border-b text-sm">${delivery.destination}</td>
                            <td class="px-4 py-2 border-b text-sm">${delivery.type}</td>
                            <td class="px-4 py-2 border-b text-sm">
                                <button class="px-2 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
                                    onclick="displayDeliveryModal(this)"
                                    data-bill="${delivery.bill_number}"
                                    data-contact="${delivery.contact_type}"
                                    data-destination="${delivery.destination}"
                                    data-type="${delivery.type}"
                                    data-address="${delivery.destination}">
                                    ویرایش
                                </button>
                                <button class="px-2 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                                    onclick="deleteDelivery(${delivery.bill_number})">
                                    حذف
                                </button>
                            </td>
                        </tr>`;
                    });
                    document.querySelector('tbody').innerHTML = html;
                } else {
                    document.querySelector('tbody').innerHTML = `<tr>
                        <td colspan="5" class="px-4 py-2 border-b text-sm text-center">هیچ ارسال ثبت نشده است.</td>
                    </tr>`;
                }
            })
            .catch(function() {
                showToast("خطا در بارگذاری ارسال‌ها، لطفا مجددا تلاش نمایید", 'error');
            });
    }
</script>
<?php require_once './components/footer.php'; ?>