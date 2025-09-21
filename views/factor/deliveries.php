<?php
$pageTitle = "مدیریت ارسال اجناس";
$iconUrl = 'delivery.svg';
require_once './components/header.php';
require_once '../../app/controller/factor/DeliveriesController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';?>

<div class="flex flex-col w-full h-full">
    <div class="flex flex-col w-full h-full">
        <div class="flex items-center justify-between px-2 py-2 bg-white shadow-md">
            <h1 class="text-lg font-semibold text-gray-800">مدیریت ارسال اجناس</h1>
            <div class="flex gap-2">
                <select id="selectedUser" onchange='showFilteredData(this)' class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs">
                    <option selected value="0">همه کاربران</option>

                    <?php
                    foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= $user['name'] . ' ' . $user['family']  ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="relative">
                    <input type="text" name="date" id="datePicker"
                        value="<?= jdate('Y/m/d') ?>"
                        class="border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs">
                    <img src="./assets/icons/calender.svg" class="absolute top-2 left-2" alt="">
                </div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 p-4">
            <div>
                <h1 class="text-xl font-semibold mb-2"> پیک یدک شاپ</h1>
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="px-2 py-2 border-b text-right text-xs">#</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                            <th class="px-2 py-2 border-b text-right text-xs">مشتری</th>
                            <th class="px-2 py-2 border-b text-right text-xs">شماره فاکتور</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آدرس</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آماده</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                        </tr>
                    </thead>
                    <tbody id="yadak">

                        <?php if (!empty($todayDeliveries)): ?>
                            <?php foreach ($todayDeliveries as $index => $delivery): ?>
                                <tr id="record_<?= htmlspecialchars($delivery['bill_number']) ?>"
                                    class="hover:bg-gray-100 even:bg-gray-50 relative group">

                                    <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>

                                    <td class="border-b text-xs">
                                        <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                            <img class="w-5 cursor-pointer" title="مشاهده جزئیات"
                                                src="../callcenter/assets/img/explore.svg" />
                                        </a>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?></td>

                                    <!-- شماره فاکتور + Tooltip -->
                                    <td class="px-2 py-2 border-b text-xs relative">
                                        <?= htmlspecialchars($delivery['bill_number']) ?>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?>
                                        <?php if (!empty($delivery['items_preview'])): ?>
                                            <div class="absolute left-0 top-full mt-1 w-64 bg-gray-800 text-white text-xs 
                                        rounded p-2 hidden group-hover:block z-50 whitespace-pre-line shadow-lg">
                                                <?php foreach ($delivery['items_preview'] as $item): ?>
                                                    • <?= htmlspecialchars(mb_strimwidth($item['partName'], 0, 40, '...')) ?>
                                                    (x<?= $item['quantity'] ?>)<br>
                                                <?php endforeach; ?>
                                                <?php if (count(json_decode($delivery['billDetails'], true)) > 3): ?>
                                                    ...
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs">
                                        <input type="checkbox" onclick="toggleStatus(this, <?= $delivery['id'] ?>)"
                                            name="is_ready" <?= $delivery['is_ready'] ? 'checked' : ''; ?>>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs">
                                        <?php if ($delivery['is_ready']): ?>
                                            <img class="w-6 h-6 rounded-full"
                                                src="../../public/userimg/<?= $delivery['is_ready'] ?>.jpg" alt="">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="hover:bg-gray-100 even:bg-gray-50">
                                <td colspan="7" class="px-2 py-2 border-b text-xs text-center">
                                    موردی برای این تاریخ ثبت نشده است.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
            <div>
                <h1 class="text-xl font-semibold mb-2"> پیک مشتری بعد تایید</h1>
                <table class="min-w-full bg-white">
                    <thead class="bg-green-700 text-white">
                        <tr>
                            <th class="px-2 py-2 border-b text-right text-xs">#</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                            <th class="px-2 py-2 border-b text-right text-xs">مشتری</th>
                            <th class="px-2 py-2 border-b text-right text-xs">شماره فاکتور</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آدرس</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آماده</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                        </tr>
                    </thead>
                    <tbody id="customer">
                        <?php if (!empty($customerDeliveries)): ?>
                            <?php foreach ($customerDeliveries as $index => $delivery): ?>
                                <tr id="record_<?= $delivery['bill_number'] ?>"
                                    class="hover:bg-gray-100 even:bg-gray-50 relative group">

                                    <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>

                                    <td class="border-b text-xs">
                                        <a href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                            <img class="w-5 cursor-pointer" title="مشاهده جزئیات"
                                                src="../callcenter/assets/img/explore.svg" />
                                        </a>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?></td>

                                    <!-- شماره فاکتور + Tooltip -->
                                    <td class="px-2 py-2 border-b text-xs relative">
                                        <?= htmlspecialchars($delivery['bill_number']) ?>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?>
                                        <?php if (!empty($delivery['items_preview'])): ?>
                                            <div class="absolute left-0 top-full mt-1 w-64 bg-gray-800 text-white text-xs rounded 
                                        p-2 hidden group-hover:block z-50 whitespace-pre-line shadow-lg">
                                                <?php foreach ($delivery['items_preview'] as $item): ?>
                                                    • <?= htmlspecialchars(mb_strimwidth($item['partName'], 0, 40, '...')) ?>
                                                    (x<?= $item['quantity'] ?>)<br>
                                                <?php endforeach; ?>
                                                <?php if (count(json_decode($delivery['billDetails'], true)) > 3): ?>
                                                    ...
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs">
                                        <input type="checkbox"
                                            onclick="toggleStatus(this, <?= $delivery['id'] ?>)"
                                            <?= $delivery['is_ready'] ? 'checked' : ''; ?>>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs">
                                        <?php if ($delivery['is_ready']): ?>
                                            <img class="w-6 h-6 rounded-full"
                                                src="../../public/userimg/<?= $delivery['is_ready'] ?>.jpg" alt="">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-xs py-2">
                                    موردی برای این تاریخ ثبت نشده است.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
            <div>
                <h1 class="text-xl font-semibold mb-2">سایر مرسولات</h1>
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
                        <?php if (!empty($deliveries)):
                            foreach ($deliveries as $index => $delivery): ?>
                                <tr class="hover:bg-gray-100 even:bg-gray-50 relative group">
                                    <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>
                                    <td class="border-b text-xs">
                                        <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                            <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                        </a>
                                    </td>
                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?>
                                        <div class="absolute left-0 top-full mt-1 min-w-[16rem] max-w-[24rem] bg-gray-800 text-white text-xs rounded p-2 hidden group-hover:block z-50 whitespace-normal shadow-lg">
                                            <?php
                                            if (!empty($delivery['items_preview'])) {
                                                foreach ($delivery['items_preview'] as $item) {
                                                    echo htmlspecialchars($item['partName']) . ' - ' . htmlspecialchars($item['quantity']) . "<br>";
                                                }
                                                if (count($delivery['items_preview']) >= 3) echo "...";
                                            } else {
                                                echo "هیچ آیتمی ثبت نشده";
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['bill_number']) ?></td>
                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['contact_type']) ?></td>
                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?></td>
                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['type']) ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr class="hover:bg-gray-100 even:bg-gray-50">
                                <td class="px-2 py-2 border-b text-xs text-center" colspan="7">موردی برای این تاریخ ثبت نشده است.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
            <div class="py-8">
                <h1 class="text-xl font-semibold mb-2">روز های قبل پیک یدک شاپ</h1>
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="px-2 py-2 border-b text-right text-xs">#</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                            <th class="px-2 py-2 border-b text-right text-xs">مشتری</th>
                            <th class="px-2 py-2 border-b text-right text-xs">شماره فاکتور</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آدرس</th>
                            <th class="px-2 py-2 border-b text-right text-xs">تاریخ</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آماده</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                        </tr>
                    </thead>
                    <tbody id="yadakRemining">

                        <?php if (!empty($yadakRemaining)): ?>
                            <?php foreach ($yadakRemaining as $index => $delivery): ?>
                                <tr id="record_<?= htmlspecialchars($delivery['bill_number']) ?>"
                                    class="hover:bg-gray-100 even:bg-gray-50 relative group">

                                    <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>

                                    <td class="border-b text-xs">
                                        <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                            <img class="w-5 cursor-pointer" title="مشاهده جزئیات"
                                                src="../callcenter/assets/img/explore.svg" />
                                        </a>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?></td>

                                    <!-- شماره فاکتور + Tooltip -->
                                    <td class="px-2 py-2 border-b text-xs relative">
                                        <?= htmlspecialchars($delivery['bill_number']) ?>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?>
                                        <?php if (!empty($delivery['items_preview'])): ?>
                                            <div class="absolute left-0 top-full mt-1 w-64 bg-gray-800 text-white text-xs 
                                        rounded p-2 hidden group-hover:block z-50 whitespace-pre-line shadow-lg">
                                                <?php foreach ($delivery['items_preview'] as $item): ?>
                                                    • <?= htmlspecialchars(mb_strimwidth($item['partName'], 0, 40, '...')) ?>
                                                    (x<?= $item['quantity'] ?>)<br>
                                                <?php endforeach; ?>
                                                <?php if (count(json_decode($delivery['billDetails'], true)) > 3): ?>
                                                    ...
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-2 border-b text-xs">
                                        <?= jdate('Y/m/d', strtotime($delivery['created_at'])) ?>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs">
                                        <input type="checkbox" onclick="toggleStatus(this, <?= $delivery['id'] ?>)"
                                            name="is_ready" <?= $delivery['is_ready'] ? 'checked' : ''; ?>>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs">
                                        <?php if ($delivery['is_ready']): ?>
                                            <img class="w-6 h-6 rounded-full"
                                                src="../../public/userimg/<?= $delivery['is_ready'] ?>.jpg" alt="">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="hover:bg-gray-100 even:bg-gray-50">
                                <td colspan="7" class="px-2 py-2 border-b text-xs text-center">
                                    موردی برای این تاریخ ثبت نشده است.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
            <div class="py-8">
                <h1 class="text-xl font-semibold mb-2"> پیک مشتری بعد تایید روز های قبل</h1>
                <table class="min-w-full bg-white">
                    <thead class="bg-green-700 text-white">
                        <tr>
                            <th class="px-2 py-2 border-b text-right text-xs">#</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                            <th class="px-2 py-2 border-b text-right text-xs">مشتری</th>
                            <th class="px-2 py-2 border-b text-right text-xs">شماره فاکتور</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آدرس</th>
                            <th class="px-2 py-2 border-b text-right text-xs">تاریخ</th>
                            <th class="px-2 py-2 border-b text-right text-xs">آماده</th>
                            <th class="px-2 py-2 border-b text-right text-xs"></th>
                        </tr>
                    </thead>
                    <tbody id="customerRemaining">
                        <?php if (!empty($customerRemaining)): ?>
                            <?php foreach ($customerRemaining as $index => $delivery): ?>
                                <tr id="record_<?= $delivery['bill_number'] ?>"
                                    class="hover:bg-gray-100 even:bg-gray-50 relative group">

                                    <td class="px-2 py-2 border-b text-xs"><?= ++$index; ?></td>

                                    <td class="border-b text-xs">
                                        <a href="../factor/externalView.php?factorNumber=<?= $delivery['bill_id'] ?>">
                                            <img class="w-5 cursor-pointer" title="مشاهده جزئیات"
                                                src="../callcenter/assets/img/explore.svg" />
                                        </a>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['kharidar']) ?></td>

                                    <!-- شماره فاکتور + Tooltip -->
                                    <td class="px-2 py-2 border-b text-xs relative">
                                        <?= htmlspecialchars($delivery['bill_number']) ?>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs"><?= htmlspecialchars($delivery['destination']) ?>
                                        <?php if (!empty($delivery['items_preview'])): ?>
                                            <div class="absolute left-0 top-full mt-1 w-64 bg-gray-800 text-white text-xs rounded 
                                        p-2 hidden group-hover:block z-50 whitespace-pre-line shadow-lg">
                                                <?php foreach ($delivery['items_preview'] as $item): ?>
                                                    • <?= htmlspecialchars(mb_strimwidth($item['partName'], 0, 40, '...')) ?>
                                                    (x<?= $item['quantity'] ?>)<br>
                                                <?php endforeach; ?>
                                                <?php if (count(json_decode($delivery['billDetails'], true)) > 3): ?>
                                                    ...
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 py-2 border-b text-xs">
                                        <?= jdate('Y/m/d', strtotime($delivery['created_at'])) ?>
                                    </td>
                                    <td class="px-2 py-2 border-b text-xs">
                                        <input type="checkbox"
                                            onclick="toggleStatus(this, <?= $delivery['id'] ?>)"
                                            <?= $delivery['is_ready'] ? 'checked' : ''; ?>>
                                    </td>

                                    <td class="px-2 py-2 border-b text-xs">
                                        <?php if ($delivery['is_ready']): ?>
                                            <img class="w-6 h-6 rounded-full"
                                                src="../../public/userimg/<?= $delivery['is_ready'] ?>.jpg" alt="">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-xs py-2">
                                    موردی برای این تاریخ ثبت نشده است.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
                const user = document.getElementById('selectedUser').value;
                showPreviousDeliveries(date, user);
            },
            onRender: function() {}
        });
    });

    function showFilteredData(element) {
        const date = ($("#datePicker").attr("data-gdate"));
        showPreviousDeliveries(date, element.value);
    }

    function showPreviousDeliveries(date, user = 0) {
        const params = new URLSearchParams();
        params.append('getPreviousDeliveries', 'getPreviousDeliveries');
        params.append('date', date);
        params.append('user', user);

        axios.post("../../app/api/factor/DeliveryApi.php", params)
            .then(function(response) {

                // 🟢 Yadak deliveries
                if (response.data.yadakDeliveries.length) {
                    const deliveries = response.data.yadakDeliveries || [];
                    let html = '';
                    let index = 0;

                    deliveries.forEach(delivery => {

                        // Prepare items tooltip
                        let tooltip = '';
                        if (delivery.items_preview && delivery.items_preview.length > 0) {
                            delivery.items_preview.forEach(item => {
                                tooltip += `${item.partName} - ${item.quantity} \n`;
                            });
                            if (delivery.items_preview.length > 3) tooltip += '...';
                        } else {
                            tooltip = 'بدون آیتم';
                        }

                        html += `
                        <tr class="relative group">
                            <td class="px-2 py-2 border-b text-xs">${++index}</td>
                            <td class="border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=${delivery.bill_id}">
                                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.kharidar}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.bill_number}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.destination}</td>
                            <td class="px-2 py-2 border-b text-xs">
                                <input type="checkbox" onclick="toggleStatus(this, ${delivery.id})" 
                                    name="is_ready" ${delivery.is_ready != 0 ? 'checked' : '' }>
                            </td>
                            <td class="px-2 py-2 border-b text-xs">
                                ${delivery.is_ready ? `<img class="w-6 h-6 rounded-full" src="../../public/userimg/${delivery.is_ready}.jpg" alt="">` : '' }
                            </td>

                            <!-- Tooltip -->
                            <td class="absolute left-0 top-full mt-1 w-56 bg-gray-800 text-white text-xs rounded p-2 hidden group-hover:block z-50 whitespace-pre-line shadow-lg">
                                ${tooltip}
                            </td>
                        </tr>
                    `;
                    });

                    document.getElementById('yadak').innerHTML = html;
                } else {
                    document.getElementById('yadak').innerHTML = `<tr>
                    <td colspan="8" class="px-2 py-2 border-b text-xs text-center">هیچ ارسال ثبت نشده است.</td>
                </tr>`;
                }

                // 🟢 Customer deliveries
                if (response.data.customerDeliveries.length) {
                    const deliveries = response.data.customerDeliveries || [];
                    let html = '';
                    let index = 0;

                    deliveries.forEach(delivery => {

                        // Prepare items tooltip
                        let tooltip = '';
                        if (delivery.items_preview && delivery.items_preview.length > 0) {
                            delivery.items_preview.forEach(item => {
                                tooltip += `${item.partName} - ${item.quantity}\n`;
                            });
                            if (delivery.items_preview.length > 3) tooltip += '...';
                        } else {
                            tooltip = 'بدون آیتم';
                        }

                        html += `
                        <tr class="relative group">
                            <td class="px-2 py-2 border-b text-xs">${++index}</td>
                            <td class="border-b text-xs">
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=${delivery.bill_id}">
                                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                                </a>
                            </td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.kharidar}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.bill_number}</td>
                            <td class="px-2 py-2 border-b text-xs">${delivery.destination}</td>
                            <td class="px-2 py-2 border-b text-xs">
                                <input type="checkbox" onclick="toggleStatus(this, ${delivery.id})" 
                                    name="is_ready" ${delivery.is_ready != 0 ? 'checked' : '' }>
                            </td>
                            <td class="px-2 py-2 border-b text-xs">
                                ${delivery.is_ready ? `<img class="w-6 h-6 rounded-full" src="../../public/userimg/${delivery.is_ready}.jpg" alt="">` : '' }
                            </td>

                            <!-- Tooltip -->
                            <td class="absolute left-0 top-full mt-1 w-56 bg-gray-800 text-white text-xs rounded p-2 hidden group-hover:block z-50 whitespace-pre-line shadow-lg">
                                ${tooltip}
                            </td>
                        </tr>
                    `;
                    });

                    document.getElementById('customer').innerHTML = html;
                } else {
                    document.getElementById('customer').innerHTML = `<tr>
                    <td colspan="8" class="px-2 py-2 border-b text-xs text-center">هیچ ارسال ثبت نشده است.</td>
                </tr>`;
                }

                // 🟢 All deliveries (with tooltip for up to 3 items)
                if (response.data.allDeliveries.length) {
                    const deliveries = response.data.allDeliveries || [];
                    let html = '';
                    let index = 0;

                    deliveries.forEach(delivery => {
                        // Prepare tooltip items if available
                        let tooltipContent = '';
                        if (delivery.items_preview && delivery.items_preview.length > 0) {
                            delivery.items_preview.slice(0, 3).forEach(item => {
                                tooltipContent += `${item.partName} - ${item.quantity}<br>`;
                            });
                            if (delivery.items_preview.length > 3) tooltipContent += '...';
                        } else {
                            tooltipContent = 'هیچ آیتمی ثبت نشده';
                        }

                        html += `<tr class="relative group">
            <td class="px-2 py-2 border-b text-xs">${++index}</td>
            <td class="border-b text-xs">
                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=${delivery.bill_id}">
                    <img class="w-5 cursor-pointer d-block" title="مشاهده جزئیات" src="../callcenter/assets/img/explore.svg" />
                </a>
            </td>
            <td class="px-2 py-2 border-b text-xs">${delivery.kharidar}</td>
            <td class="px-2 py-2 border-b text-xs">${delivery.bill_number}</td>
            <td class="px-2 py-2 border-b text-xs">${delivery.contact_type}</td>
            <td class="px-2 py-2 border-b text-xs">${delivery.destination}</td>
            <td class="px-2 py-2 border-b text-xs">${delivery.type}</td>

            <!-- Tooltip -->
            <td class="relative">
                <div class="absolute left-0 top-full mt-1 min-w-[16rem] max-w-[24rem] bg-gray-800 text-white text-xs rounded p-2 hidden group-hover:block z-50 whitespace-normal shadow-lg">
                    ${tooltipContent}
                </div>
            </td>
        </tr>`;
                    });

                    document.getElementById('deliveries').innerHTML = html;
                } else {
                    document.getElementById('deliveries').innerHTML = `<tr>
        <td colspan="8" class="px-2 py-2 border-b text-xs text-center">هیچ ارسال ثبت نشده است.</td>
    </tr>`;
                }


            })
            .catch(function() {
                showToast("خطا در بارگذاری ارسال‌ها، لطفا مجددا تلاش نمایید", 'error');
            });
    }


    function toggleStatus(element, id) {
        const is_checked = element.checked ? 1 : 0;

        const params = new URLSearchParams();
        params.append('toggleStatus', 'toggleStatus');
        params.append('status', is_checked);
        params.append('delivery', id);
        params.append('approved_by', <?= $_SESSION['id'] ?>);

        axios.post("../../app/api/factor/DeliveryApi.php", params).then((response) => {

            showToast('بروز رسانی موفقانه صورت گرفت');

        }).catch(error => {

        })

    }
</script>
<?php require_once './components/footer.php'; ?>