<?php
$pageTitle = "مدیریت فاکتورها";
$iconUrl = 'management.svg';
require_once './components/header.php';
require_once '../../app/controller/factor/ManageFactorsController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<div class="rtl min-h-screen grid grid-cols-1 md:grid-cols-6 gap-2 lg:gap-3 mb-4">
    <div class="bg-white min-h-full md:col-span-3 shadow-md">
        <div class="flex items-center justify-between p-3 bg-gray-900">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <img class="w-7 h-7" src="./assets/img/incomplete.svg" alt="customer icon">
                پیش فاکتور ها
            </h2>
            <?php
            if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                <div>
                    <span onclick="createIncompleteBill(null, 0)" class="cursor-pointer  text-white rounded bg-sky-600 hover:bg-sky-500 px-3 py-2 text-xs"> پیش فاکتور مصرف کننده</span>
                    <span onclick="createIncompleteBill(null, 1)" class="cursor-pointer bg-green-600 hover:bg-green-700 text-white rounded px-3 py-2 text-xs"> پیش فاکتور همکار </span>
                </div>
            <?php endif; ?>
        </div>
        <div class="border-t border-gray-200"></div>
        <div class="p-3">
            <label for="incompleteBill" class="mb-2 text-xs font-medium text-gray-900 sr-only">جستجو</label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input type="search" id="incompleteBill" class="block w-full p-3 ps-10 text-sm text-gray-900 border-2 border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500  focus:outline-none" placeholder="جستجوی پیش فاکتور به اساس مشتری" required>
                <button onclick="searchForBill('incomplete')" type="submit" class="text-white absolute end-2 bottom-1.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">جستجو</button>
            </div>
        </div>
        <div id="incomplete_bill" class="p-3 overflow-y-auto">
            <!-- Search Results are going to be appended here -->
        </div>

    </div>

    <div class="bg-white min-h-full md:col-span-2 shadow-md">
        <div class="flex items-center justify-between p-3 bg-gray-900">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <img class="w-7 h-7" src="./assets/img/saved_bill.svg" alt="customer icon">
                فاکتورهای ثبت شده
            </h2>
        </div>
        <div class="border-t border-gray-200"></div>
        <div class="p-3">
            <label for="completeBill" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">جستجو</label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input type="search" id="completeBill" class="block w-full p-3 ps-10 text-sm text-gray-900 border-2 border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500  focus:outline-none" placeholder="جستجوی پیش فاکتور به اساس مشتری" required>
                <button onclick="searchForBill('complete')" type="submit" class="text-white absolute end-2 bottom-1.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">جستجو</button>
            </div>
        </div>
        <div id="completed_bill" class="p-3">
            <!-- selected items are going to be added here -->
        </div>
    </div>

    <div class="bg-white min-h-full shadow-md md:col-span-1">
        <div class="p-3 bg-gray-900">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <img class="w-7 h-7" src="./assets/img/select_user.svg" alt="inventory icon">
                انتخاب کاربر
            </h2>
        </div>
        <div class="border-t border-gray-200"></div>

        <div id="users_list" class="accordion flex flex-col min-h-screen py-3 px-2">
            <select onchange="setUserId(this.value)" name="user_id" id="users" class="border-2 border-gray-300 text-gray-900 text-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2 outline-none">
                <option class="text-sm">کاربر مد نظر خود را انتخاب کنید.</option>
                <option class="text-sm" value="all">همه کاربران</option>
                <?php
                foreach ($users as $user) : ?>
                    <option class="text-sm" <?= $user['id'] == $_SESSION['id'] ? 'selected' : '' ?> value="<?= $user['id'] ?>"><?= $user['name'] . " " . $user['family'] ?></option>
                <?php endforeach; ?>
            </select>
            <div class="accordion flex flex-col w-full py-3">
                <?php foreach (MONTHS as $index => $month) : ?>
                    <div class="">
                        <input class="accordion_condition hidden" type="checkbox" name="panel" id="month-<?= $index ?>">
                        <label for="month-<?= $index ?>" class="cursor-pointer relative bg-gray-800 text-white p-2 shadow flex justify-between ">
                            <span><?= $month ?></span>
                        </label>
                        <div class="accordion__content overflow-hidden bg-grey-lighter">
                            <div class="flex justify-center items-center">
                                <div class="grid grid-cols-7 bg-gray-200 p-1 gap-0">
                                    <?php
                                    for ($counter = 1; $counter <= DAYS[$index]; $counter++) : ?>
                                        <div onclick="selectDay(this)" data-day="<?= $counter ?>" data-month="<?= $index + 1 ?>" id="<?= $index . '-' . $counter . '-day' ?>" class="days w-8 h-8 flex justify-center items-center text-sm font-bold cursor-pointer hover:bg-gray-300"><?= $counter; ?></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>
<div id="success_message" class="transition-all opacity-0 fixed bg-green-600 text-white rounded-md text-sm font-bold bottom-5 right-5 px-5 py-3">
    پیش فاکتور شما با موفقیت ایجاد شد.
    <a id="factor_link" class="text-blue-800 text-sm px-2 underline" href="">ویرایش فاکتور</a>
</div>
<div id="deliveryModal" class="hidden fixed inset-0 bg-gray-900/75 flex justify-center items-center">
    <div class="bg-white p-4 rounded w-2/3">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl mb-2">ارسال اجناس</h2>
            <img class="cursor-pointer" src="./assets/img/close.svg" alt="close icon" onclick="document.getElementById('deliveryModal').classList.add('hidden')">
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
                        <option value="پیک خود مشتری بعد از اطلاع">پیک خود مشتری بعد از اطلاع </option>
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
    const factorManagementApi = '../../app/api/factor/FactorManagementApi.php';

    // Retrieve user_id from sessionStorage
    let user_id = <?= $_SESSION['id'] ?>;
    let login_user = <?= $_SESSION['id'] ?>;
    let now = moment().locale('en').format('YYYY-MM-DD');

    const year = '<?= $_SESSION["financialYear"] ?>';
    const month = (moment().locale('fa').format('M'));
    const day = (moment().locale('fa').format('D'));

    let active_date = null;
    let active_user = null;

    // for styling the current day and month
    const current_month = Number(month) - 1;
    document.getElementById('month-' + current_month).checked = 'checked';
    document.getElementById(current_month + '-' + day + '-day').style.backgroundColor = 'red';
    document.getElementById(current_month + '-' + day + '-day').style.color = 'white';

    // Select all elements with the class '.accordion_condition' and toggle the accordion
    const accordions = document.querySelectorAll('.accordion_condition');
    const days = document.querySelectorAll('.days');
    accordions.forEach(function(accordion) {
        accordion.addEventListener('click', function(e) {
            accordions.forEach(function(accordion) {
                accordion.checked = false;
            });
            e.target.checked = 'checked'
        });
    });

    // Attach a click event listener to each day of month for generating specific date report
    days.forEach(function(day) {
        day.addEventListener('click', function(e) {
            unCheckDays();
            e.target.classList.add('selected_day');
        });
    });

    // Select users from the user filter option
    function setUserId(id) {
        user_id = id;
        unCheckDays();
        bootStrap();
    }

    // Select the specific day of the month for filtering the date
    function selectDay(element) {
        const selectedMonth = element.getAttribute('data-month');
        const selectedDay = element.getAttribute('data-day');
        now = moment.from(year + "/" + selectedMonth + "/" + selectedDay, 'fa', 'YYYY/MM/DD').format('YYYY/MM/DD');
        bootStrap();
    }

    // Get the specific user saved bills for the specific date
    function getUserSavedBills() {
        const completed_bill = document.getElementById('completed_bill');
        const params = new URLSearchParams();
        params.append('getUserCompleteBills', 'getUserCompleteBills');
        params.append('user', user_id);
        params.append('date', now);
        completed_bill.innerHTML = '';

        axios.post(factorManagementApi, params)
            .then(function(response) {
                const factors = response.data;
                appendCompleteFactorResults(factors)
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    // Attache the above retrieved data to its specific container
    function getDeliveryIcon(type) {
        switch (type) {
            case 'تیپاکس':
            case 'اتوبوس':
            case 'سواری':
            case 'باربری':
                return './assets/img/delivery.svg';
            case 'پیک مشتری':
                return './assets/img/customer_copy.svg';
            case 'پیک یدک شاپ':
                return './assets/img/yadakshop.svg';
            case 'هوایی':
                return './assets/img/airplane.svg';
            default:
                return './assets/img/customer_copy.svg';
        }
    }

    function appendCompleteFactorResults(factors) {
        const completed_bill = document.getElementById('completed_bill');
        completed_bill.innerHTML = '';
        const user = '<?= $_SESSION['username']; ?>';

        if (factors.length > 0) {
            for (const factor of factors) {
                const deliveryIcon = getDeliveryIcon(factor.delivery_type);

                completed_bill.innerHTML += `
                <div class="card-container flex justify-between cursor-pointer h-24 relative border p-3 rounded shadow-sm flex-wrap mb-2 ${factor.partner ? 'bg-green-200': ''} ">
                    <div class="flex-grow flex flex-col justify-between px-3">
                        <div class="flex justify-between">
                            <p class="text-sm font-bold">
                                شماره فاکتور:
                                ${factor.bill_number}
                            </p>
                            <p class="text-sm font-semibold">
                                اقلام:
                                ${factor.quantity}
                            </p>
                            <p class="text-sm font-semibold">
                                تاریخ فاکتور:
                                ${factor.bill_date}
                            </p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm font-bold">
                                مشتری: 
                                ${factor.name ?? ''} ${factor.family ?? ''}
                            </p>
                             <p class="text-sm font-semibold">
                                ردیف:
                                ${JSON.parse(factor.billDetails).length}
                            </p>
                            <p class="text-sm font-semibold">
                                قیمت کل:
                                ${formatAsMoney(factor.total)}
                            </p>
                        </div>
                        <div class="edit-container absolute left-0 right-0 bottom-0 top-0 bg-gray-100 flex justify-center items-center">
                            <ul class="flex items-center gap-2">
                                <a target="_blank" href="./complete.php?factor_number=${factor.id}" >
                                    <img src="./assets/img/editFactor.svg" class="hover:scale-125" />
                                </a>
                                <li onClick="createIncompleteBill('${factor.id}', ${factor.partner})" title="ایجاد پیش فاکتور از این فاکتور">
                                    <img src="./assets/img/useFactorTemplate.svg" class="hover:scale-125" />
                                </li>
                                <li onClick="copyFactorInfo(this,'${factor.name}', '${factor.family}','${factor.bill_number}', '${formatAsMoney(factor.total)}')" title="کپی کردن مشخصات فاکتور">
                                    <img src="./assets/img/copy.svg" class="hover:scale-125" />
                                </li>
                                <li title="تنظیم فاکتور پیش خروج">
                                    <a target="_blank" href="./preSellFactorDetails.php?factor_number=${factor.id}">
                                        <img src="./assets/img/sell.svg" class="hover:scale-125" />
                                    </a>
                                </li>
                                <li title="ثبت واریزی">
                                    <a target="_blank" href="./addPayment.php?factor=${factor.bill_number}">
                                        <img class="w-5 h-5" src="./assets/img/factor.svg" class="hover:scale-125" />
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="w-14 flex flex-col justify-center items-center">
                        <img class="w-10 h-10 rounded-full" src="../../public/userimg/${factor.user_id}.jpg"/>
                        <span title="این فاکتور ${factor.exists_in_deliveries} بار تحویل شده است" class="absolute bottom-0 left-0 text-white text-xs rounded-full px-1">
                            <img 
                             onclick="displayDeliveryModal(this)"
                            data-bill="${factor.bill_number}"
                            data-contact="${factor.contact_type}"
                            data-destination="${factor.destination}"
                            data-type="${factor.delivery_type}"
                            data-address="${factor.customer_address}"
                            alt="arrow icon"
                            title="ارسال اجناس"
                            class="w-6 h-6 mt-1" src="${deliveryIcon}" title="${factor.delivery_type}" />
                        </span>
                    </div> 
                </div>
            `;
            }
        } else {
            completed_bill.innerHTML = `<div class="flex flex-col justify-center items-center h-24 border border-rose-400 p-3 rounded shadow-sm shadow-rose-300 bg-rose-300">
            <svg width="40px" height="40px" viewBox="0 -0.5 17 17" xmlns="http://www.w3.org/2000/svg" class="si-glyph si-glyph-folder-error mb-2">
                <g stroke="none" fill="none" fill-rule="evenodd">
                    <g transform="translate(1.000000, 2.000000)" fill="#fff">
                        <path d="M7.35,3 L5.788,0.042 L2.021,0.042 L2.021,1.063 L0.023,1.063 L0.023,10.976 L1.043,10.976 L1.045,11.976 L15.947,11.976 L15.968,3 L7.35,3 L7.35,3 Z M10.918,9.109 L10.09,9.938 L8.512,8.361 L6.934,9.938 L6.104,9.109 L7.682,7.531 L6.104,5.953 L6.934,5.125 L8.512,6.701 L10.088,5.125 L10.918,5.953 L9.34,7.531 L10.918,9.109 L10.918,9.109 Z"/>
                        <path d="M13.964,1.982 L13.964,1.042 L8.024,1.042 L8.354,1.982 L13.964,1.982 Z"/>
                    </g>
                </g>
            </svg>      
            <p class="text-md text-white">فاکتوری برای تاریخ مشخص شده درج نشده است.</p>
        </div>`;
        }
    }


    // Get the specific user Incomplete bills for specific date
    function getUserIncompleteBills() {
        const incomplete_bill = document.getElementById('incomplete_bill');

        const params = new URLSearchParams();
        params.append('getUserIncompleteBills', 'getUserIncompleteBills');
        params.append('user', user_id);
        params.append('date', now);

        incomplete_bill.innerHTML = '';

        axios.post(factorManagementApi, params)
            .then(function(response) {
                const factors = response.data;
                appendIncompleteFactorResult(factors)
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    // Attache the above retrieved data to its specific container
    function appendIncompleteFactorResult(factors) {
        const incomplete_bill = document.getElementById('incomplete_bill');
        incomplete_bill.innerHTML = '';
        if (factors.length > 0) {
            for (const factor of factors) {
                incomplete_bill.innerHTML += `
                        <div id="card-${factor.id}" class="card-container flex justify-between cursor-pointer h-24 relative border p-3 rounded shadow-sm flex-wrap mb-2 ${factor.partner ? 'bg-green-200': ''}">
                            <div class="w-14 flex justify-center items-center">
                                <img class=" w-10 h-10 rounded-full" src ="../../public/userimg/${factor.user_id}.jpg"/>
                            </div>
                            <div class="flex-grow flex flex-col justify-between px-3">   
                                <div class="flex justify-between">
                                    <p class="text-md font-semibold">
                                        مشتری: 
                                        ${factor.name ?? ''} ${factor.family ?? ''}
                                    </p>
                                    <p class="text-md font-semibold">
                                        تاریخ فاکتور:
                                        ${factor.bill_date}
                                    </p>
                                </div>
                                <div class="flex justify-between">
                                    <p class="text-md font-semibold">
                                        تعداد اقلام: 
                                        ${factor.quantity }
                                    </p>
                                    <p class="text-md font-semibold">
                                        قیمت کل:
                                        ${formatAsMoney(factor.total)}
                                    </p>
                                    </div>
                                    <div class="edit-container absolute left-0 right-0 bottom-0 top-0 bg-gray-100 flex justify-center items-center">
                                        <ul class="flex gap-2">
                                            <li title="ویرایش فاکتور">
                                                <a target="_blank" href="./incomplete.php?factor_number=${factor.id}" >
                                                    <img src="./assets/img/editFactor.svg" class="hover:scale-125" />
                                                </a>
                                            </li>
                                            ${login_user == '1' || login_user == '5' || login_user == factor.user_id ? `<li title="حذف پیش فاکتور" onClick="confirmDelete('${factor.id}')">
                                                <img src="./assets/img/deleteBill.svg" class="hover:scale-125" />
                                            </li>` : ''}
                                            <li onClick="copyFactorInfo(this,'${factor.name}', '${factor.family}','${factor.bill_number}', '${formatAsMoney(factor.total)}')" title="کپی کردن مشخصات فاکتور">
                                                <img src="./assets/img/copy.svg" class="hover:scale-125" />
                                            </li>
                                        </ul>
                                    </div>
                            </div>
                        </div>`;
            }
        } else {
            incomplete_bill.innerHTML = `
                        <div class="flex flex-col justify-center items-center h-24 border border-orange-400 p-3 rounded shadow-sm shadow-orange-300 bg-orange-300">
                            <svg width="40px" height="40px" viewBox="0 -0.5 17 17" version="1.1"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" class="si-glyph si-glyph-folder-error mb-2">
                                <title>938</title>
                                <defs>
                                </defs>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(1.000000, 2.000000)" fill="#fff">
                                        <path d="M7.35,3 L5.788,0.042 L2.021,0.042 L2.021,1.063 L0.023,1.063 L0.023,10.976 L1.043,10.976 L1.045,11.976 L15.947,11.976 L15.968,3 L7.35,3 L7.35,3 Z M10.918,9.109 L10.09,9.938 L8.512,8.361 L6.934,9.938 L6.104,9.109 L7.682,7.531 L6.104,5.953 L6.934,5.125 L8.512,6.701 L10.088,5.125 L10.918,5.953 L9.34,7.531 L10.918,9.109 L10.918,9.109 Z" class="si-glyph-fill"></path>
                                        <path d="M13.964,1.982 L13.964,1.042 L8.024,1.042 L8.354,1.982 L13.964,1.982 Z" class="si-glyph-fill"></path>
                                    </g>
                                </g>
                            </svg>      
                            <p class="text-md text-white">پیش فاکتوری برای تاریخ مشخص شده درج نشده است.</p>
                        </div>`;
        }
    }

    // Getting the bill id and send it for the editing
    // it works both for complete and incomplete bills 
    function EditFactorFormSubmission(formId) {
        document.getElementById(formId).submit();
    }

    // remove the selected styles from the day selected previously
    function unCheckDays() {
        days.forEach(function(day) {
            day.classList.remove('selected_day');
        });
    }

    // Start retrieving the data fro the user and specific date after initializing initial data
    function bootStrap() {
        active_date = now;
        getUserSavedBills();
        getUserIncompleteBills();
    }

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
                document.getElementById('deliveryModal').classList.add('hidden');
                showToast("ارسال با موفقیت ثبت شد.");
            })
            .catch(function(error) {
                alert("خطا در هنگام ثبت ارسال، لطفا مجددا تلاش نمایید");
            });

    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow-lg text-white z-50 transition-opacity duration-500 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;

        document.body.appendChild(toast);

        // Remove toast and reload after 3s
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.remove();
                location.reload();
            }, 500); // wait for fade-out animation
        }, 3000);
    }

    // create new Incomplete date for modification or assigning new bill
    function createIncompleteBill(factor_id = null, type = null) {
        const success_message = document.getElementById('success_message');
        const factor_link = document.getElementById('factor_link');
        const params = new URLSearchParams();
        params.append('create_incomplete_bill', 'create_incomplete_bill');
        params.append('date', moment().locale('fa').format('YYYY/MM/DD'));
        params.append('factor_id', factor_id);
        params.append('type', type);

        axios.post("../../app/api/factor/IncompleteFactorApi.php", params)
            .then(function(response) {
                bootStrap();
                success_message.classList.remove('opacity-0');
                success_message.classList.add('opacity-1');
                factor_link.href = './incomplete.php?factor_number=' + response.data;

                setTimeout(() => {
                    success_message.classList.remove('opacity-1');
                    success_message.classList.add('opacity-0');
                }, 5000);
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    let templateClone = null; // get a clone of bill card to have it if user cancelled deletion the bill

    // Display the confirmation message to ask whether confirms deletion or not
    function confirmDelete(factorId) {
        templateClone = document.getElementById('card-' + factorId).innerHTML;
        document.getElementById('card-' + factorId).innerHTML = `
                    <div class="w-full h-full flex flex-col justify-center items-center bg-rose-300">
                        <p class="text-white">آیا مطمئن هستید که میخواهید حذف صورت بگیرد ؟</p>
                        <div class="py-2">
                            <button onclick="deleteFactor('${factorId}')" class="px-4 text-white bg-red-700 rounded">بلی</button>
                            <button onclick="rollBack('${factorId}')" class="px-4 text-white bg-green-700 rounded">خیر</button>
                        </div>
                    </div>`;
    }

    // roll back the bill card after cancelling the deletion operation
    function rollBack(factorId) {
        document.getElementById('card-' + factorId).innerHTML = templateClone;
    }

    // Deletion confirmed and send ajax request to delete from the database the selected record
    function deleteFactor(factorId) {
        const params = new URLSearchParams();
        params.append('deleteFactor', 'deleteFactor');
        params.append('factorId', factorId);

        axios.post(factorManagementApi, params)
            .then(function(response) {
                if (response.data) {
                    document.getElementById('card-' + factorId).innerHTML = `
                    <div class="w-full h-full flex justify-center items-center bg-orange-500">
                        <p class="text-white">عملیات حذف موفقانه صورت گرفت</p>
                    </div>`;
                    setTimeout(() => {
                        document.getElementById('card-' + factorId).style.display = 'none';
                    }, 1000);
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    // helper function to display the bill total amount in money format
    function formatAsMoney(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' ریال';
    }

    // Search among the completed bill or incomplete bills base on the customer name and last name
    function searchForBill(format) {
        let pattern = '';

        switch (format) {
            case 'incomplete':
                pattern = document.getElementById('incompleteBill').value;
                const incomplete_bill = document.getElementById('incomplete_bill');
                incomplete_bill.innerHTML = `
                <p class="p-5 rounded shadow">
                    <img src="../../public/img/loading.png" class="w-8 h-8 mx-auto" alt="loading icon">
                </p>
                `;

                search(pattern, '0').then(function(factors) {
                    appendIncompleteFactorResult(factors);
                }).catch(function(error) {
                    console.log(error);
                });
                break;
            case 'complete':
                pattern = document.getElementById('completeBill').value;
                const completed_bill = document.getElementById('completed_bill');
                completed_bill.innerHTML = `
                <p class="p-5 rounded shadow">
                    <img src="../../public/img/loading.png" class="w-8 h-8 mx-auto" alt="loading icon">
                </p>
                `;
                search(pattern, '1').then(function(factors) {
                    const bills = factors;
                    appendCompleteFactorResults(bills);
                }).catch(function(error) {
                    console.log(error);
                });
                break;
        }
    }

    function search(pattern, mode) {
        const isPartNumber = filterPartNumber(pattern).length > 6 ? true : false;
        const params = new URLSearchParams();
        params.append('searchForBill', 'searchForBill');
        params.append('pattern', pattern);
        params.append('mode', mode);
        params.append('isPartNumber', isPartNumber);

        return axios.post(factorManagementApi, params)
            .then(function(response) {
                return response.data;
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function copyFactorInfo(element, name, family, billNo, total) {

        const combinedText = `مشتری : ${name} ${family} \nشماره فاکتور : ${billNo} \n مبلغ قابل پرداخت: ${total}`;

        const textarea = document.createElement("textarea");
        textarea.value = combinedText;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        document.body.removeChild(textarea);

        Image = element.querySelector('img');

        Image.src = "./assets/img/complete.svg";

        setTimeout(() => {
            Image.src = "./assets/img/copy.svg";
        }, 2000);
    }

    function filterPartNumber(message) {
        if (!message) {
            return "";
        }

        const codes = message.split("\n");

        const filteredCodes = codes
            .map(function(code) {
                code = code.replace(/\[[^\]]*\]/g, "");

                const parts = code.split(/[:,]/, 2);

                // Check if parts[1] contains a forward slash
                if (parts[1] && parts[1].includes("/")) {
                    // Remove everything after the forward slash
                    parts[1] = parts[1].split("/")[0];
                }

                const rightSide = (parts[1] || "").replace(/[^a-zA-Z0-9 ]/g, "").trim();

                return rightSide ? rightSide : code.replace(/[^a-zA-Z0-9 ]/g, "").trim();
            })
            .filter(Boolean);

        const finalCodes = filteredCodes.filter(function(item) {
            const data = item.split(" ");
            if (data[0].length > 4) {
                return item;
            }
        });

        const mappedFinalCodes = finalCodes.map(function(item) {
            const parts = item.split(" ");
            if (parts.length >= 2) {
                const partOne = parts[0];
                const partTwo = parts[1];
                if (!/[a-zA-Z]{4,}/i.test(partOne) && !/[a-zA-Z]{4,}/i.test(partTwo)) {
                    return partOne + partTwo;
                }
            }
            return parts[0];
        });

        const nonConsecutiveCodes = mappedFinalCodes.filter(function(item) {
            const consecutiveChars = /[a-zA-Z]{4,}/i.test(item);
            return !consecutiveChars;
        });

        return nonConsecutiveCodes.map(function(item) {
            return item.split(" ")[0];
        }).join("\n") + "\n";
    }

    bootStrap();
</script>
<?php
require_once './components/footer.php';
