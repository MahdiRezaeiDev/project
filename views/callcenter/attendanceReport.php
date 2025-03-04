<?php
$pageTitle = "مدیریت حضور و غیاب";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/AttendanceReportController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$users = getUsers();
$today = date('Y-m-d');
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
    <div class="flex items-center justify-between p-5">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="material-icons font-semibold text-orange-400">security</i>
            <?= jdate('l J F'); ?> -
            <?= jdate('Y/m/d')  ?>
        </h2>
        <div class="flex items-start gap-2">
            <select class="text-xs py-2 px-3 font-semibold sm:w-60 border-2" name="user" id="reportedUser">
                <option class="text-xs" value="0">همه کاربران</option>
                <?php foreach ($users as $user) : ?>
                    <option class="text-xs" value="<?= $user['selectedUser'] ?>"><?= $user['name'] . ' ' . $user['family'] ?></option>
                <?php endforeach; ?>
            </select>
            <div>
                <input class="text-sm py-2 px-3 font-semibold sm:w-60 border-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="invoice_time" id="invoice_time">
                <p class="text-xs text-gray-500">تاریخ شروع گزارش را انتخاب نمایید.</p>
            </div>
            <button onclick="getReport(this)" class="bg-sky-700 text-white rounded px-4 py-2 disabled:cursor-not-allowed">گزارش</button>
        </div>
    </div>
    <div class="bg-white rounded-lg p-5 shadow-md hover:shadow-xl">
        <div class="border border-dashed border-gray-800 flex flex-col items-center h-full rounded-lg">
            <div class="overflow-x-auto shadow-md sm:rounded-lg w-full h-full">
                <table id="reportTable" class="w-full text-sm text-left rtl:text-right text-gray-800 h-full">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                شماره
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                کاربر
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                شنبه
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                یکشنبه
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                دوشنبه
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                سه شنبه
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                چهار شنبه
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                پنج شنبه
                            </th>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                جمعه
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
                                <?php

                                for ($counter = 0; $counter < 7; $counter++):
                                    require './components/attendance/timeTable.php';
                                endfor; ?>
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
    const END_POINT = '../../app/api/callcenter/AttendanceApi.php';
    const REPORT_END_POINT = '../../app/api/callcenter/AttendanceReportApi.php';

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

        axios.post(END_POINT, params)
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

    function getReport(element) {
        const startDate = document.getElementById('invoice_time').getAttribute('data-gdate');
        const user = document.getElementById('reportedUser').value;

        const param = new URLSearchParams();
        param.append('date', startDate);
        param.append('user', user);

        element.innerText = 'لطفا صبور باشید...';
        element.disabled = true;
        axios({
            url: REPORT_END_POINT, // No need to append query parameters here
            method: 'POST',
            data: param, // Send the parameters in the `data` field for POST
            responseType: 'blob', // Set responseType to 'blob' to handle binary data (like files)
        }).then((response) => {
            // Create a URL for the blob (file)
            const url = window.URL.createObjectURL(new Blob([response.data]));

            // Create a link element to trigger the download
            const link = document.createElement('a');
            link.href = url;

            // Set filename from content-disposition header (if available)
            const filename = response.headers['content-disposition']?.split('filename=')[1] || 'report.xlsx';

            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click(); // Trigger download

            // Cleanup
            link.remove();
            window.URL.revokeObjectURL(url);
            element.innerText = 'گزارش';
            element.disabled = false;
        }).catch((e) => {
            console.log('Download failed', e);
        });
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
            onSelect: function() {},
            onRender: function() {}
        });
    });
</script>

<?php
require_once './components/footer.php';
