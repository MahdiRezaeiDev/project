<?php
$pageTitle = "واریزی ها";
$iconUrl = 'factor.svg';
require_once './components/header.php';
require_once '../../app/controller/payment/paymentController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

$payments = getPayments($factorInfo['id']);
$totalPayment = array_sum(array_column($payments, 'amount'));
$remainingAmount = $factorInfo['total'] - $totalPayment;

$errorMessage = '';
$success = false;
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
<?php if ($remainingAmount <= 0): ?>
    <div class="flex items-center justify-center w-2/4 mx-auto rounded shadow-lg bg-gray-100 mb-10">
        <div class="bg-white rounded-xl shadow-lg w-full p-5 text-center">
            <h2 class="text-2xl font-bold text-gray-700 pb-2">پرداخت کامل شده است</h2>
            <p class="text-sm text-gray-600">این فاکتور به طور کامل پرداخت شده است و نیازی به ثبت پرداخت جدید نیست.</p>
            <p class="text-xs text-gray-500 mt-2">برای مشاهده جزئیات پرداخت‌ها، لطفاً به لیست واریزی‌ها مراجعه کنید.</p>
            <div class="mt-4">
                <a href="./paymentDetails.php?factor=<?= urlencode($factor_number) ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow">
                    مشاهده لیست واریزی‌ها
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="w-2/4 mx-auto rounded shadow-lg bg-gray-100 mb-10">
            <form id="paymentForm"
                method="POST"
                enctype="multipart/form-data"
                action="../../app/controller/payment/addPaymentController.php"
                class="bg-white rounded-xl shadow-lg w-full p-5 text-right">

                <h2 class="text-2xl font-bold text-gray-700 pb-2">فرم ثبت پرداخت</h2>
                <div class=" text-xs text-rose-500 border-b pb-2 mb-5">
                    <p>* لطفاً توجه داشته باشید که پس از ثبت پرداخت، امکان ویرایش اطلاعات وجود ندارد.</p>
                    <p>* در صورت نیاز به تغییر اطلاعات، لطفاً با پشتیبانی تماس بگیرید.</p>
                </div>
                <?php if (isset($_GET['error'])):

                    switch ($_GET['error']):
                        case 1: ?>
                            <div class="mt-4 text-red-600 bg-red-100 border border-red-300 rounded px-3 py-2 mb-5 text-xs">
                                مبلغ وارد شده معتبر نیست یا بیشتر از باقیمانده فاکتور است.
                            </div>
                        <?php
                            break;
                        case 2: ?>
                            <div class="mt-4 text-red-600 bg-red-100 border border-red-300 rounded px-3 py-2 mb-5 text-xs">
                                شماره حساب وارد نشده است.
                            </div>
                    <?php
                            break;
                    endswitch;

                elseif (isset($_GET['success'])): ?>
                    <div class="relative group inline-block">
                        <div class="text-green-600 bg-green-100 border border-green-300 rounded px-3 py-2 mb-5 text-xs">
                            پرداخت با موفقیت در سیستم ذخیره شد.
                        </div>
                    </div>
                <?php endif; ?>
                <!-- مبلغ -->
                <div class="mb-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">مبلغ واریزی</label>
                    <input type="text" id="amount_display"
                        class="text-xs w-full border rounded-md px-3 py-2 text-right"
                        inputmode="numeric" autocomplete="off" max="<?= $remainingAmount ?>">
                    <input type="hidden" name="amount" id="amount_real" max="<?= $remainingAmount ?>" required>
                    <input type="hidden" name="bill_id" value="<?= $factorInfo['id'] ?>" required>
                    <input type="hidden" name="factor" value="<?= $_GET['factor'] ?>" required>
                </div>
                <!-- شماره حساب -->
                <div class="mb-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">شماره حساب</label>
                    <input type="text" name="account_number" class="text-xs w-full border rounded-md px-3 py-2 text-right" required>
                </div>

                <!-- تاریخ و زمان -->
                <div class="mb-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">تاریخ واریز</label>
                    <input class="text-xs w-full border rounded-md px-3 py-2 text-right"
                        data-gdate="<?= date('Y/m/d') ?>"
                        value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>"
                        type="text" name="invoice_time" id="invoice_time">
                </div>
                <!-- تاریخ و زمان -->
                <div class="mb-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">ساعت واریز</label>
                    <input type="time" name="time"
                        class="text-xs w-full border rounded-md px-3 py-2 text-right"
                        value="<?php echo date('H:i'); ?>" required>
                </div>

                <!-- نام مشتری -->
                <div class="mb-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">نام مشتری</label>
                    <input type="text" name="customer_name" class="text-xs w-full border rounded-md px-3 py-2 text-right readonly:bg-gray-200" readonly
                        value="<?= $factorInfo['name'] ?? '---' ?>">
                    <input type="hidden" name="customer_id" value="<?= $factorInfo['customer_id'] ?? '' ?>">
                </div>

                <!-- کاربر -->
                <div class="mb-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">ثبت کننده</label>
                    <input type="text" name="user_name" class="text-xs w-full border rounded-md px-3 py-2 text-right readonly:bg-gray-200" readonly
                        value="<?= $factorInfo['user_name'] . ' ' . $factorInfo['user_family'] ?? '---' ?>">
                    <input type="hidden" name="user_id" value="<?= $factorInfo['user_id'] ?? '' ?>">
                </div>

                <!-- تصویر -->
                <div class="mb-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">چسباندن عکس رسید یا پرداخت</label>
                    <div id="dropArea"
                        contenteditable="true"
                        class="border-2 border-dashed border-gray-400 min-h-[250px] rounded-lg p-4 text-center bg-gray-50 text-gray-500">
                        عکس را اینجا با Ctrl + V بچسبانید
                    </div>
                    <!-- اینپوت پنهان برای نگهداری عکس -->
                    <input type="file" name="photo" id="photoInput" style="display: none;">
                </div>

                <!-- دکمه ارسال -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow">
                        ثبت پرداخت
                    </button>
                </div>

            </form>
        </div>
    <?php endif; ?>
    <script>
        const dropArea = document.getElementById("dropArea");
        const photoInput = document.getElementById("photoInput");

        dropArea.addEventListener("paste", function(event) {
            const items = event.clipboardData.items;

            for (const item of items) {
                if (item.type.startsWith("image/")) {
                    event.preventDefault();
                    const file = item.getAsFile();

                    // نمایش پیش‌ نمایش
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        dropArea.innerHTML = "";

                        const img = document.createElement("img");
                        img.src = e.target.result;
                        img.className = "max-w-[50%] mx-auto my-4 rounded-md shadow";

                        const btn = document.createElement("button");
                        btn.textContent = "حذف تصویر";
                        btn.type = "button";
                        btn.className = "mt-4 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow";
                        btn.onclick = () => {
                            dropArea.innerHTML = "عکس را اینجا با Ctrl + V بچسبانید";
                            photoInput.value = ""; // حذف فایل
                        };

                        dropArea.appendChild(img);
                        dropArea.appendChild(btn);
                    };
                    reader.readAsDataURL(file);

                    // ساختن FileList برای input[type=file]
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    photoInput.files = dataTransfer.files;
                    break;
                }
            }
        });

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
                    params.append('getFactor', 'getFactor');
                    params.append('date', date);
                    axios.post("../../app/partials/factors/factor.php", params)
                        .then(function(response) {
                            resultBox.innerHTML = response.data;
                        })
                        .catch(function(error) {
                            console.log(error);
                        });
                },
                onRender: function() {}
            });
        });

        const displayInput = document.getElementById('amount_display');
        const hiddenInput = document.getElementById('amount_real');

        displayInput.addEventListener('input', function() {
            // حذف هر کاراکتر غیر عددی
            let raw = this.value.replace(/[^0-9]/g, '');

            // مقدار خام را داخل input مخفی قرار می‌دهیم
            hiddenInput.value = raw;

            // فرمت کردن با کاما
            this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    </script>
    <?php
    require_once './components/footer.php';
