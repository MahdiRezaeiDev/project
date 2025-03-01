<?php
$pageTitle = "مدیریت حضور و غیاب";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/AttendanceReportController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$users = getUsers();
?>
<div id="modal" class="hidden fixed inset-0 bg-black opacity-70 justify-center items-center">
    <section class="bg-white rounded p-5" style="width: 500px;">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-semibold">ویرایش ساعات کاری</h2>
                <p class="text-gray-700 text-sm">برای تغییر ساعات کاری کاربر مورد نظر، اطلاعات مربوطه را وارد کنید.</p>
            </div>
            <i class="material-icons text-rose-600 font-semibold cursor-pointer" onclick="closeModal()">close</i>
        </div>
        <hr class="my-3">
        <p class="text-xs text-gray-500 font-semibold py-1">ساعات کاری <span class=" bg-gray-400 rounded-sm px-3 py-1 text-white" id="user"></span></p>
        <form action="#" onsubmit="updateWorkHour(event)" method="post">
            <input type="text" name="user_id" id="user_id" hidden>
            <input type="text" name="start_id" id="start_id" hidden>
            <input type="text" name="end_id" id="end_id" hidden>
            <input id="start" type="time" class="border border-gray-300 w-full p-2 rounded mt-2" placeholder="ساعت شروع کار">
            <input id="end" type="time" class="border border-gray-300 w-full p-2 rounded mt-2" placeholder="ساعت پایان کار">
            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-500 text-white py-2 px-3 rounded-sm mt-2">
                    ویرایش ساعات کاری
                </button>
                <p id="message" class="text-xs text-green-500 font-semibold py-1"></p>
            </div>
        </form>
    </section>
</div>
<div class="bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between p-2">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="material-icons font-semibold text-orange-400">security</i>
            <?= jdate('l J F'); ?> -
            <?= jdate('Y/m/d')  ?>
        </h2>
        <input class="text-sm py-2 px-3 font-semibold sm:w-60 border-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="invoice_time" id="invoice_time">
    </div>
    <div class="bg-white rounded-lg p-5 shadow-md hover:shadow-xl">
        <div class="border border-dashed border-gray-800 flex flex-col items-center h-full rounded-lg">
            <div class="overflow-x-auto shadow-md sm:rounded-lg w-full h-full">
                <table class="w-full text-sm text-left rtl:text-right text-gray-800 h-full">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                شماره
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                کاربر
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                ساعات ورود و خروج
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $index => $user) : ?>
                            <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                                <th class="px-6 py-3  font-semibold text-gray-800 text-right">
                                    <?= $index + 1; ?>
                                </th>
                                <th class="px-6 py-3  font-semibold text-gray-800 text-right">
                                    <?= $user['name'] . ' ' . $user['family'] ?>
                                </th>
                                <td class="px-6 py-3 text-center  font-semibold text-right text-gray-800">
                                    <?php
                                    $START_HOUR = getUserAttendanceReport('start', $user['selectedUser']);
                                    $END_HOUR = getUserAttendanceReport('leave', $user['selectedUser']);
                                    ?>
                                    <table class="w-full text-sm text-left rtl:text-right text-gray-800 h-full">
                                        <?php if (count($START_HOUR) > 0) { ?>
                                            <thead class="text-sm text-gray-700 uppercase bg-gray-500">
                                                <tr>
                                                    <th class="text-center p-2 text-white">#</th>
                                                    <th class="text-center p-2 text-white">ساعت ورود</th>
                                                    <th class="text-center p-2 text-white"> ساعت خروج</th>
                                                    <th class="text-center p-2 text-white">*</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

                                                foreach ($START_HOUR as $index => $item): ?>
                                                    <tr class="text-sm text-gray-800">
                                                        <td class="text-sm text-center p-1 bg-sky-200"><?= $index + 1 ?></td>
                                                        <td class="text-sm text-center p-1 bg-green-200"><?= date('h:i A', strtotime($item['timestamp'])) ?></td>
                                                        <td class="text-sm text-center p-1 bg-rose-300">
                                                            <?php

                                                            if (array_key_exists($index, $END_HOUR)) {
                                                               echo date('h:i A', strtotime($END_HOUR[$index]['timestamp']));
                                                            } else {
                                                                echo '';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-sm text-center p-1 bg-sky-200">
                                                            <span
                                                                data-user="<?= $user['name'] . ' ' . $user['family'] ?>"
                                                                data-selectedUser="<?= $user['selectedUser'] ?>"
                                                                data-start_id="<?= $item['id'] ?>"
                                                                data-end_id="<?= $END_HOUR[$index]['id'] ?>"
                                                                data-start="<?= date('h:i', strtotime($item['timestamp'])) ?>"
                                                                data-end="<?= date('h:i', strtotime($END_HOUR[$index]['timestamp'])) ?>"
                                                                onclick="editWorkHour(this)"
                                                                class="text-blue-500 hover:text-blue-700 cursor-pointer">
                                                                ویرایش ساعات کاری
                                                            </span>
                                                        </td>
                                                    </tr>
                                            <?php
                                                endforeach;
                                            } else {
                                                echo '<tr><td colspan="4" class="text-center text-red-500">هیچ ساعت کاری ثبت نشده است.</td></tr>';
                                            }
                                            ?>
                                    </table>
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
    const MODAL = document.getElementById('modal');
    const START = document.getElementById('start');
    const END = document.getElementById('end');
    const USER = document.getElementById('user');
    const USER_ID = document.getElementById('user_id');
    const START_ID = document.getElementById('start_id');
    const END_ID = document.getElementById('end_id');
    const message = document.getElementById('message');
    const ENDPOINT = '../../app/api/callcenter/AttendanceApi.php';

    function editWorkHour(element) {
        openModal();
        START.value = element.dataset.start;
        END.value = element.dataset.end;
        USER.innerText = element.dataset.user;
        USER_ID.value = element.dataset.selecteduser;
        START_ID.value = element.dataset.start_id;
        END_ID.value = element.dataset.end_id;
    }

    function updateWorkHour(event) {
        event.preventDefault();
        const user_id = USER_ID.value;
        const start = START.value;
        const end = END.value;
        const start_id = START_ID.value;
        const end_id = END_ID.value;

        const params = new URLSearchParams({
            action: 'UpdateAttendance',
            user_id,
            start,
            end,
            start_id,
            end_id
        });

        axios.post(ENDPOINT, params)
            .then(data => {
                if (data.status == 200) {
                    message.innerText = data.data.message;
                    setTimeout(() => {
                        closeModal();
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                message.innerText = error.response.data.message;
            });
    }

    function toggleModal() {

        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');
    }

    function closeModal() {

        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

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
            cellWidth: 34,
            cellHeight: 28,
            fontSize: 16,
            isRTL: !0,
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
</script>
<?php
require_once './components/footer.php';
