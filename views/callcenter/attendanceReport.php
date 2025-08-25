<?php
$pageTitle = "مدیریت حضور و غیاب";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/AttendanceReportController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
function getUserLeaveReport($user_id, $date)
{
    $sql = "SELECT * FROM yadakshop.leaves WHERE user_id=:user_id AND date=:date ORDER BY start_time ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
$users = getUsers();
$today = date('Y-m-d');
?>
<div id="modal" class="hidden fixed inset-0 bg-gray-800 opacity- justify-center items-center">
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
                <button type="button" onclick="deleteAttendanceLog()" class="bg-red-500 text-white py-2 px-3 rounded-sm mt-2">
                    حذف ساعات کاری
                </button>
            </div>
            <div class="flex justify-between items-center p-2">
                <p id="message" class="text-xs text-green-500 font-semibold py-1"></p>
            </div>
        </form>
    </section>
</div>

<div id="AttendanceModal" class="hidden fixed inset-0 bg-gray-800 opacity- justify-center items-center">
    <section class="bg-white rounded p-5" style="width: 500px;">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-semibold">مدیریت حضور و غیاب
                    <span class="bg-green-500 text-white rounded-sm px-3 text-md" id="user_info"></span>
                </h2>
                <p class="text-gray-700 text-sm">برای تغییر ساعات کاری کاربر مورد نظر، اطلاعات مربوطه را وارد کنید.</p>
            </div>
            <i class="material-icons text-rose-600 font-semibold cursor-pointer" onclick="closeAttendanceModal()">close</i>
        </div>
        <hr class="my-3">
        <form action="#" onsubmit="saveAttendance(event)" method="post">
            <input class="border border-gray-300 w-full p-2 rounded mt-2" type="text" name="target_user" id="target_user" hidden>
            <select class="border border-gray-300 w-full p-2 rounded mt-2" name="action" id="action">
                <option value="START">شروع به کار</option>
                <option value="LEAVE">ختم کار</option>
            </select>
            <input class="border border-gray-300 w-full p-2 rounded mt-2" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="attendance_user" id="attendance_user">
            <input
                id="time"
                type="time"
                required
                class="border border-gray-300 w-full p-2 rounded mt-2"
                placeholder="ساعت شروع کار">
            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-500 text-white py-2 px-3 rounded-sm mt-2">
                    ثبت
                </button>
                <p id="message" class="text-xs text-green-500 font-semibold py-1"></p>
            </div>
        </form>
    </section>
</div>

<div id="openLeaveModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
    <section class="bg-white rounded p-5 w-[500px]">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-semibold">
                    ثبت مرخصی
                    <span class="bg-green-500 text-white rounded-sm px-3 text-md" id="userInfo"></span>
                </h2>
                <p class="text-gray-700 text-sm">برای ثبت مرخصی کاربر مورد نظر، اطلاعات مربوطه را وارد کنید.</p>
            </div>
            <i class="material-icons text-rose-600 font-semibold cursor-pointer" onclick="closeLeaveModal()">close</i>
        </div>
        <hr class="my-3">

        <form action="#" onsubmit="saveLeave(event)" method="post">
            <input type="text" name="leave_user" id="leave_user" hidden>
            <!-- Daily Leave Checkbox -->
            <div class="flex flex-col gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="daily" id="daily" class="w-4 h-4" onchange="toggleDailyLeave(this)">
                    <label for="daily" class="text-sm text-gray-700">مرخصی روزانه</label>
                </div>
                <input class="border border-gray-300 p-2 rounded mt-2 w-full" data-gdate="<?= date('Y/m/d') ?>" value="<?= (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="leave_date" id="leave_date">
                <label for="reason">
                    <input type="text" name="reason" id="reason" class="border border-gray-300 w-full p-2 rounded mt-2" placeholder="دلیل مرخصی">
                </label>
            </div>

            <!-- Hourly Leave Section -->
            <div id="hourlyLeave">
                <label for="startingTime" class="block mb-2">
                    از ساعت
                    <input
                        id="startingTime"
                        name="startingTime"
                        type="time"
                        class="border border-gray-300 w-full p-2 rounded mt-2"
                        placeholder="ساعت شروع کار">
                </label>
                <label for="endingTime" class="block mb-2">
                    تا ساعت
                    <input
                        id="endingTime"
                        name="endingTime"
                        type="time"
                        class="border border-gray-300 w-full p-2 rounded mt-2"
                        placeholder="ساعت پایان کار">
                </label>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-500 text-white py-2 px-3 rounded-sm mt-2">
                    ثبت مرخصی
                </button>
                <p id="msgBox" class="text-xs text-green-500 font-semibold py-1"></p>
            </div>
        </form>
    </section>
</div>

<script>
    function toggleDailyLeave(checkbox) {
        const hourlyLeave = document.getElementById('hourlyLeave');
        const startingTime = document.getElementById('startingTime');
        const endingTime = document.getElementById('endingTime');

        if (checkbox.checked) {
            hourlyLeave.classList.add('hidden');
            startingTime.removeAttribute('required');
            endingTime.removeAttribute('required');
        } else {
            hourlyLeave.classList.remove('hidden');
            startingTime.setAttribute('required', true);
            endingTime.setAttribute('required', true);
        }
    }

    function saveLeave(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        // For checkbox: send "1" if checked, "0" if not
        formData.set('daily', form.daily.checked ? '1' : '0');
        formData.append('saveLeave', '1'); // mark the action
        formData.append('leave_date', form.leave_date.dataset.gdate);

        axios.post('../../app/api/callcenter/AttendanceApi.php', formData)
            .then(response => {
                console.log(response.data);

                if (response.status === 200) {
                    const msgBox = document.getElementById('msgBox');
                    msgBox.innerText = `${response.data.message}`;

                    // add class for styling (success/error)
                    msgBox.className = response.data.status === 'success' ?
                        'text-green-600 font-bold' :
                        'text-red-600 font-bold';

                    setTimeout(() => {
                        closeLeaveModal();
                        // window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                const msgBox = document.getElementById('message');

                if (error.response && error.response.data.message) {
                    msgBox.innerText = `[error] ${error.response.data.message}`;
                } else {
                    msgBox.innerText = "[error] خطایی رخ داده است.";
                }
                msgBox.className = 'text-red-600 font-bold';
            });
    }

    function deleteLeave(id) {
        if (!confirm('آیا از حذف این مرخصی اطمینان دارید؟')) {
            return;
        }

        const params = new URLSearchParams({
            deleteLeave: 'deleteLeave',
            id: id
        });

        axios.post('../../app/api/attendance/AttendanceApi.php', params)
            .then(data => {
                if (data.status == 200) {
                    alert(data.data.message);
                    window.location.reload();
                }
            })
            .catch(error => {
                alert(error.response.data.message);
            });
    }
</script>

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
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 p-2">
                                #
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
                            <th scope="col" class="font-semibold text-center text-sm text-gray-800 p-3">
                                <img src="./assets/img/settings.svg" class="mx-auto" alt="settings icon">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $index => $user) : ?>
                            <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                                <td class="p-2 font-semibold text-gray-800 text-right">
                                    <?= $index + 1; ?>
                                </td>
                                <td class="p-3 font-semibold text-gray-800 text-right">
                                    <?= $user['name'] . ' ' . $user['family'] ?>
                                </td>
                                <?php
                                for ($counter = 0; $counter < 6; $counter++):
                                    require './components/attendance/timeTable.php';
                                endfor; ?>
                                <td class="p-3 flex justify-center gap-2 text-xs font-semibold text-gray-700">
                                    <!-- ویرایش -->
                                    <button
                                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg shadow-sm hover:bg-blue-200 transition"
                                        data-user="<?= $user['name'] . ' ' . $user['family'] ?>"
                                        data-selectedUser="<?= $user['selectedUser'] ?>"
                                        onclick="openAttendanceModal(this)">
                                        ویرایش
                                    </button>

                                    <!-- مرخصی -->
                                    <button
                                        class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-lg shadow-sm hover:bg-yellow-200 transition"
                                        data-user="<?= $user['name'] . ' ' . $user['family'] ?>"
                                        data-selectedUser="<?= $user['selectedUser'] ?>"
                                        onclick="openLeaveModal(this)">
                                        مرخصی
                                    </button>
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
        openModal(MODAL);
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

    function deleteAttendanceLog() {
        const user_id = USER_ID.value;
        const start = START.value;
        const end = END.value;
        const start_id = START_ID.value;
        const end_id = END_ID.value;

        const params = new URLSearchParams({
            action: 'DELETEAttendance',
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

    function closeLeaveModal() {
        const frame = document.getElementById('openLeaveModal');
        frame.classList.remove('flex');
        frame.classList.add('hidden');
    }

    function closeAttendanceModal() {
        const frame = document.getElementById('AttendanceModal');
        frame.classList.remove('flex');
        frame.classList.add('hidden');
    }

    function saveAttendance(event) {
        event.preventDefault();

        const form = event.target;
        const params = new URLSearchParams();
        params.append('saveAttendance', 'saveAttendance');
        params.append('user_id', form.target_user.value);
        params.append('operation', form.action.value);
        params.append('date', form.attendance_user.dataset.gdate);
        params.append('time', form.time.value);

        axios.post('../../app/api/attendance/AttendanceActionApi.php', params)
            .then(response => {
                if (response.status === 200) {
                    message.innerText = response.data.message;
                    setTimeout(() => {
                        closeAttendanceModal();
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                if (error.response && error.response.data.message) {
                    message.innerText = error.response.data.message;
                } else {
                    message.innerText = "خطایی رخ داده است.";
                }
            });
    }

    function openModal(modal) {
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

    function openAttendanceModal(element) {
        const frame = document.getElementById('AttendanceModal');
        document.getElementById('user_info').innerText = element.dataset.user;
        document.getElementById('target_user').value = element.dataset.selecteduser;
        openModal(frame);

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
        $("#attendance_user").persianDatepicker(datepickerConfig);
        $("#leave_date").persianDatepicker(datepickerConfig);
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

    function openLeaveModal(element) {
        const frame = document.getElementById('openLeaveModal');
        document.getElementById('userInfo').innerText = element.dataset.user;
        document.getElementById('leave_user').value = element.dataset.selecteduser;
        openModal(frame);
    }
</script>

<?php
require_once './components/footer.php';
