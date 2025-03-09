<?php
$pageTitle = "مدیریت حضور و غیاب";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/AttendanceManageController.php';
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
            <input id="start" type="time" class="border border-gray-300 w-full p-2 rounded mt-2" placeholder="ساعت شروع کار">
            <input id="end" type="time" class="border border-gray-300 w-full p-2 rounded mt-2" placeholder="ساعت پایان کار">
            <input id="late" type="number" min="0" class="border border-gray-300 w-full p-2 rounded mt-2" placeholder="تاخیر مجاز">
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
            مدیریت حضور و غیاب
        </h2>
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
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                شروع کار
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                پایان کار
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                تاخیر مجاز
                            </th>
                            <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                                عملیات
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
                                <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                    <?= $user['start_hour'] ?>
                                </td>
                                <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                    <?= $user['end_hour'] ?>
                                </td>
                                <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                    <?= $user['max_late_minutes'] ?>
                                    دقیقه
                                </td>
                                <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                    <span
                                        data-user="<?= $user['name'] . ' ' . $user['family'] ?>"
                                        data-selectedUser="<?= $user['selectedUser'] ?>"
                                        data-start="<?= $user['start_hour'] ?>"
                                        data-end="<?= $user['end_hour'] ?>"
                                        data-late="<?= $user['max_late_minutes'] ?>"
                                        onclick="editWorkHour(this)"
                                        class="text-blue-500 hover:text-blue-700 cursor-pointer">
                                        ویرایش ساعات کاری
                                    </span>
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
    const LATE = document.getElementById('late');
    const USER = document.getElementById('user');
    const USER_ID = document.getElementById('user_id');
    const message = document.getElementById('message');
    const ENDPOINTADDR = '../../app/api/callcenter/AttendanceApi.php';

    function editWorkHour(element) {
        openModal();
        START.value = element.dataset.start;
        END.value = element.dataset.end;
        LATE.value = element.dataset.late;
        USER.innerText = element.dataset.user;
        USER_ID.value = element.dataset.selecteduser;
    }

    function updateWorkHour(event) {
        event.preventDefault();
        const user_id = USER_ID.value;
        const start = START.value;
        const end = END.value;
        const late = LATE.value;

        const params = new URLSearchParams({
            action: 'updateWorkHour',
            user_id,
            start,
            end,
            late
        });

        axios.post(ENDPOINTADDR, params)

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
</script>
<?php
require_once './components/footer.php';
