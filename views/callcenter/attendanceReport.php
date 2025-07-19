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
        <div>
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="material-icons font-semibold text-orange-400">security</i>
                <?= jdate('l J F'); ?> -
                <?= jdate('Y/m/d')  ?>
            </h2>
            <input class="text-sm py-2 px-3 font-semibold sm:w-60 border-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="selected_date" id="selected_date">
        </div>
        <div class="flex items-start gap-2">
            <select class="text-xs py-2 px-3 font-semibold sm:w-60 border-2" name="user" id="reportedUser">
                <option class="text-xs" value="0">همه کاربران</option>
                <?php foreach ($users as $user) : ?>
                    <option class="text-xs" value="<?= $user['selectedUser'] ?>"><?= $user['name'] . ' ' . $user['family'] ?></option>
                <?php endforeach; ?>
            </select>
            <div>
                <input class="text-sm py-2 px-3 font-semibold sm:w-60 border-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="start_time" id="start_time">
                <p class="text-xs text-gray-500">تاریخ شروع گزارش را انتخاب نمایید.</p>
            </div>
            <div>
                <input class="text-sm py-2 px-3 font-semibold sm:w-60 border-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="end_time" id="end_time">
                <p class="text-xs text-gray-500">تاریخ ختم گزارش را انتخاب نمایید.</p>
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
                            <?php for ($counter = 0; $counter < 6; $counter++):
                                $date = strtotime("+$counter days", $startDate);
                                $reportDate = date("Y-m-d", $date);

                                $WeekDay = jdate("l", $date);
                                $iterationDate = jdate("Y/m/d", $date);
                            ?>
                                <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                    <?= $WeekDay ?>
                                    </br>
                                    <?= $iterationDate ?>
                                </th>
                            <?php endfor; ?>
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 px-6 py-3">
                                <img src="./assets/img/settings.svg" alt="settings icon">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $index => $user) : ?>
                            <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                                <td class="px-6 py-3  font-semibold text-gray-800 text-right">
                                    <?= $index + 1; ?>
                                </td>
                                <td class="px-6 py-3  font-semibold text-gray-800 text-right">
                                    <?= $user['name'] . ' ' . $user['family'] ?>
                                </td>
                                <?php
                                for ($counter = 0; $counter < 6; $counter++):
                                    require './components/attendance/timeTable.php';
                                endfor; ?>
                                <td class="px-6 py-3  font-semibold text-gray-800 text-center">
                                    <img src="./assets/img/edit.svg" alt="edit icon">
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
        const startDate = document.getElementById('start_time').getAttribute('data-gdate');
        const endDate = document.getElementById('end_time').getAttribute('data-gdate');
        const user = document.getElementById('reportedUser').value;

        const param = new URLSearchParams();
        param.append('start', startDate);
        param.append('end', endDate);
        param.append('user', user);

        element.innerText = 'لطفا صبور باشید...';
        element.disabled = true;

        axios({
            url: REPORT_END_POINT,
            method: 'POST',
            data: param,
            responseType: 'blob',
        }).then((response) => {
            // Download Excel
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            const filename = response.headers['content-disposition']?.split('filename=')[1] || 'report.xlsx';
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            setTimeout(() => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = './attendance-display.php';

                const fields = {
                    start: startDate,
                    end: endDate,
                    user: user
                };

                for (const key in fields) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            }, 1000);


        }).catch((e) => {
            console.error('Download failed', e);
            element.innerText = 'گزارش';
            element.disabled = false;
        });
    }


    $(function() {
        const datepickerConfig = {
            months: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
            dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
            shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
            showGregorianDate: false,
            persianNumbers: true,
            formatDate: "YYYY/MM/DD",
            selectedBefore: false,
            selectedDate: null,
            startDate: null,
            endDate: null,
            prevArrow: '◄',
            nextArrow: '►',
            theme: 'default',
            alwaysShow: false,
            selectableYears: null,
            selectableMonths: Array.from({
                length: 12
            }, (_, i) => i + 1), // [1,2,3,...,12]
            cellWidth: 34,
            cellHeight: 28,
            fontSize: 16,
            isRTL: true,
            calendarPosition: {
                x: 0,
                y: 0
            },
            onShow: function() {},
            onHide: function() {},
            onSelect: function() {},
            onRender: function() {}
        };

        $("#start_time, #end_time").persianDatepicker(datepickerConfig);

        $("#selected_date").persianDatepicker({
            months: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
            dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
            shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
            showGregorianDate: false,
            persianNumbers: true,
            formatDate: "YYYY/MM/DD",
            selectedBefore: false,
            selectedDate: null,
            startDate: null,
            endDate: null,
            prevArrow: '◄',
            nextArrow: '►',
            theme: 'default',
            alwaysShow: false,
            selectableYears: null,
            selectableMonths: Array.from({
                length: 12
            }, (_, i) => i + 1), // [1,2,3,...,12]
            cellWidth: 34,
            cellHeight: 28,
            fontSize: 16,
            isRTL: true,
            calendarPosition: {
                x: 0,
                y: 0
            },
            onShow: function() {},
            onHide: function() {},
            onSelect: function() {
                const gdate = $('#selected_date').attr('data-gdate');

                if (gdate) {
                    // Convert date (YYYY-MM-DD) to timestamp (seconds)
                    const timestamp = Math.floor(new Date(gdate).getTime() / 1000);

                    // Update the URL with the timestamp
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('date', timestamp); // Store as Unix timestamp

                    // Reload the page with the new query string
                    window.location.href = newUrl.toString();
                }
            },
            onRender: function() {}
        })
    });

    function setOffDay() {
        const user = event.target.dataset.user;
        const date = event.target.dataset.date;
        const selectedUser = event.target.dataset.selecteduser;

        const params = new URLSearchParams({
            action: 'SetOffDay',
            user,
            date,
            selectedUser
        });

        confirm(`آیا می خواهید مرخصی کاربر ${user} را ثبت کنید؟`) ?
            axios.post(END_POINT, params)
            .then(response => {
                const data = response;
                if (data.status == 200) {
                    message.innerText = data.data.message;
                    setTimeout(() => {
                        alert(`مرخصی کاربر ${user} ثبت شد.`);
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.log(error);
            }) :
            alert(`مرخصی کاربر ${user} ثبت نشد.`);


    }
</script>

<?php
require_once './components/footer.php';
