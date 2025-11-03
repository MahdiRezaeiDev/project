<?php
$pageTitle = "اطلاعات مشتری";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/MainController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<link href="./assets/css/jquery.tagselect.css" rel="stylesheet" />
<link href="./assets/css/jquery.tagselect2.css" rel="stylesheet" />

<!-- STYLES SECTION -->
<style>
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    textarea#messageInput {
    min-height: 285px;
}
</style>

<!-- CUSTOMER INFORMATION SECTION -->
<section class="px-5">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 lg:gap-6 mb-5 overflow-auto items-start">
        <div class="flex flex-col h-full lg:col-span-3">
            <div class="flex justify-between items-center bg-gray-800 mb-5 px-1">
                <h2 class="text-lg font-bold p-2 text-white">مشخصات مشتری</h2>
            </div>

            <form class="save-contact form" action="php/save.php" method="get" autocomplete="off">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-200 p-3  flex flex-col gap-2">
                        <label class="text-sm font-semibold" for="phone">
                            شماره تماس
                        </label>
                        <input style="direction: ltr !important;" class="p-2 grow border-2 focus:border-gray-500 outline-none" onkeyup="convertToEnglish(this)" id="phone" name="phone" type="text" value="<?= $phone ?>" readonly>
                    </div>
                    <div class="bg-gray-200 p-3  flex flex-col gap-2">
                        <label class="text-sm font-semibold" for="name">نام</label>
                        <input class="p-2 grow border-2 focus:border-gray-500 outline-none" onkeyup="convertToPersian(this)" id="name" name="name" type="text" value="<?= !empty($name) ?  $name :  '';  ?>">
                    </div>
                    <div class="bg-gray-200 p-3  flex flex-col gap-2">
                        <label class="text-sm font-semibold" for="last_name">نام خانوادگی</label>
                        <input class="p-2 grow border-2 focus:border-gray-500 outline-none" onkeyup="convertToPersian(this)" id="last_name" name="family" type="text" value="<?= !empty($family) ? $family : ''; ?>">
                    </div>
                    <div class="bg-gray-200 p-3  flex flex-col gap-2">
                        <label class="text-sm font-semibold" for="vin">شماره شاسی</label>
                        <input style="direction: ltr !important;" class="p-2 grow border-2 focus:border-gray-500 outline-none" onkeyup="convertToEnglish(this)" id="vin" name="vin" type="text" value="<?= !empty($vin) ? $vin : '' ?>">
                    </div>
                    <div class="bg-gray-200 p-3  flex flex-col gap-2">
                        <label class="text-sm font-semibold" for="car">ماشین</label>
                        <input class="p-2 grow border-2 focus:border-gray-500 outline-none" id="car" name="car" type="text" value="<?= !empty($car) ? $car : '' ?>">
                    </div>
                    <div class="bg-gray-200 p-3  flex flex-col gap-2" style="display: none;">
                        <label class="text-sm font-semibold" for="kind">نوع</label>
                        <input class="p-2 grow border-2 focus:border-gray-500 outline-none" id="kind" name="kind" type="text" value="null">
                    </div>
                    <div class="bg-gray-200 p-3  flex flex-col gap-2">
                        <label class="text-sm font-semibold" for="address">آدرس</label>
                        <textarea class="p-2 grow border-2 focus:border-gray-500 outline-none" id="address" rows="1" name="address"><?= !empty($address) ? $address : '' ?></textarea>
                    </div>
                    <div class="bg-gray-200 p-3  flex flex-col gap-2">
                        <label class="text-sm font-semibold" for="des">توضیحات مشتری</label>
                        <textarea class="p-2 grow border-2 focus:border-gray-500 outline-none" id="des" name="des"><?= !empty($des) ? $des : '' ?></textarea>
                    </div>
                    <input name="isOld" id="isold" type="hidden" value="<?= ($isOld) ?>">
                    <div class="col-span-2 bg-gray-200 p-3 flex flex-col gap-2">
                        <label class="flex justify-between text-sm font-semibold" for="call_info_text">
                            درج اطلاعات استعلام
                            <div class="flex justify-end gap-2" style="width: 200px;">
                                <input style="width: 15px;" type="checkbox" name="pin" id="pin">
                                <label for="pin">
                                    پین کردن استعلام
                                </label>
                            </div>
                        </label>
                        <textarea id="call_info_text" class="callInfo p-2 grow border-2 focus:border-gray-500 outline-none" name="callInfo"></textarea>
                        <div class="flex items-start">
                            <div class="callInfoBox-option flex flex-wrap gap-2">
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> درخواست بارنامه </div>
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> درخواست شماره کارت</div>
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> پیگیری پیک </div>
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> پیگیری روند فاکتور </div>
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> درخواست ثبت فاکتور </div>
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> ارجاع به واتساپ </div>
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> درخواست شماره واتساپ </div>
                                <div class="bg-gray-100 rounded text-xs p-2 hover:cursor-pointer hover:bg-gray-400 hover:text-white"> اطلاعات واریز وجه </div>
                            </div>
                            <img onclick="toGivenPrice()" src="../../public/icons/backward.svg" class="bg-indigo-500 text-white rounded py-2 px-5 hover:cursor-pointer hover:bg-indigo-600" title='انتقال کد به بخش جستجو' alt="forward icon" />
                        </div>
                    </div>
                </div>
                <div class="fixed right-0 left-0 bottom-0 px-5 py-3 bg-gray-800 shadow-lg" style="z-index: 99;">
                    <input class="bg-sky-600 text-white text-sm rounded py-2 px-5 cursor-pointer" type="submit" value="ذخیره" id="saveContact">
                    <a href="../factor/createIncomplete.php?phone=<?= $phone ?>&type=1" class="bg-green-600 text-white text-sm rounded py-2 px-5 cursor-pointer">پیش فاکتور همکار</a>
                    <a href="../factor/createIncomplete.php?phone=<?= $phone ?>&type=0" class="bg-amber-700 text-white text-sm rounded py-2 px-5 cursor-pointer">پیش فاکتور مصرف کننده</a>
                    <p class="text-md font-semibold" id="operation_status"></p>
                </div>
            </form>
        </div>
        <!-- The main block for the search codes for giving price and displaying the already given prices to the specified client -->
        <div class="flex flex-col h-full">
            <div class="mb-5 bg-gray-800">
                <h2 class="text-lg font-bold p-2 text-white"> کد های مد نظر برای جستجو</h2>
            </div>
            <form method="post" target="_blank" class="bg-gray-200" action="./orderedPrice.php">
                <?php if (isset($id)) { ?>
                    <input type="text" name="givenPrice" value="givenPrice" id="form" hidden>
                    <input type="text" id="givenUser" name="user" value="<?= $_SESSION["id"] ?>" hidden>
                    <input hidden name="customer" required id="givenCustomer" type="number" value="<?= $id ?>" />
                    <div class="p-3">
                        <textarea style="direction: ltr !important;" onchange="filterCode(this)" class="p-2 w-full text-sm border-2 focus:border-gray-500 outline-none" id="givenCode" rows="17" name="code" required placeholder="لطفا کد های مورد نظر خود را در خط های مجزا قرار دهید"></textarea>
                        <div class="flex justify-between items-center">
                            <button type="submit" class="text-white bg-green-600 hover:bg-green-800 px-4 py-2 rounded text-sm"> جستجو</button>
                            <img onclick="toEstelam()" class="bg-indigo-500 text-white rounded py-2 px-5 hover:cursor-pointer hover:bg-indigo-600" title='انتقال کد به بخش استعلام' src="../../public/icons/forward.svg" alt="">
                        </div>
                    </div>
                <?php } else {
                    echo "<div class='bg-red-500 text-white px-5 text-sm py-2'>
                     شماره تماس $phone در سیستم ثبت نمی باشد.
                     </div>";
                    $isOld = 0;
                } ?>
            </form>
        </div>
    </div>
</section>

<!-- CALL OPERATION SECTION -->
<section class="grid grid-cols-1 lg:grid-cols-3 gap-3 px-3 mx-5 mb-5 bg-gray-200 rounded py-5 shadow">
    <div class="lg:col-span-1 flex flex-wrap items-center gap-2">
        <a class="text-sm grow text-center text-semibold text-white px-4 py-2 bg-sky-600" href="#">تماس با مشتری</a>
        <a class="text-sm grow text-center text-semibold text-white px-4 py-2 bg-rose-600" href="#">قطع تماس جاری</a>
        <a class="text-sm grow text-center text-semibold text-white px-4 py-2 bg-yellow-600" onclick="messageModal()" href="#">ارسال پیامک</a>
        <a class="text-sm grow text-center text-semibold text-white px-4 py-2 bg-green-600" target="_blank" href="https://web.whatsapp.com/send/?phone=98<?= $phone ?>&text=با عرض سلام و وقت بخیر
                                                        %0a
                                                        از واحد فروش مجموعه یدک شاپ خدمتتون پیام ارسال شده است
                                                        %0a
                                                        اگر نیاز به مشاوره یا استعلام قیمت قطعات دارید می توانید برای ما پیام ارسال کنید ، کارشناسان فروش ما در سریع ترین حالت ممکن پاسخگوی شما هستند.
                                                        %0a
                                                        کارشناس شما : <?= getFamilyById($_SESSION["id"]); ?>
                                                        &type=phone_number&app_absent=0">ارسال واتساپ
        </a>
    </div>
    <!-- CARTABLE OPERATION SECTION -->
    <div class="lg:col-span-2">
        <form class="cartable-save-form" action="#" method="post" autocomplete="off">
            <div class="flex justify-center items-center">
                <div class="grow">
                    <div class="w-full qtagselect isw360">
                        <select class="qtagselect__select" name="label[]" id="label" multiple>
                            <?php tagLabelList() ?>
                        </select>
                        <script>
                            <?php
                            if (isset($label)) :
                                $myString = substr($label, 0, -1);
                                $myArray = explode(',', $myString);
                                foreach ($myArray as $ttt) {
                                    $ttt = (int) $ttt - 1;
                                    echo "$('#label option:eq($ttt)').attr('selected', 'selected');";
                                }
                            endif;
                            ?>
                        </script>

                    </div>
                </div>
                <div class="grow">
                    <input name="phone" type="text" value="<?= $phone ?>" hidden>
                    <div class="quserselect isw360">
                        <select class="quserselect__select" name="userSelector[]" id="userSelector" multiple>
                            <?php userLabelList() ?>
                        </select>
                        <script>
                            <?php
                            if (isset($userSelect)) :
                                $myString = substr($userSelect, 0, -1);
                                $myArray = explode(',', $myString);
                                foreach ($myArray as $ttt) {
                                    $ttt = (int) $ttt - 1;
                                    echo "$('#userSelector option:eq($ttt)').attr('selected', 'selected');";
                                }
                            endif;
                            ?>
                        </script>
                    </div>
                </div>
                <div class="grow">
                    <select class="w-full border-2 text-xs p-1" name="cartable-pos" id="cartable-pos">
                        <option value="0">انتخاب کارتابل</option>
                        <option value="1">نیاز به پیگیری</option>
                        <option value="2">نیاز به قفلی</option>
                        <option value="3">داستان شده</option>
                        <option value="0">حذف از کارتابل</option>
                    </select>
                </div>
                <input class="bg-sky-500 text-white py-2 px-3 m-3 rounded-sm text-xs font-semibold cursor-pointer" type="submit" value="ذخیره لیبل">
            </div>
        </form>
    </div>
</section>

<!-- RECORDS SECTION -->
<section class="mb-20 mx-5">
    <div class="bg-gray-800">
        <nav class="tabs flex flex-col sm:flex-row">
            <span data-target="panel-1" class="tab text-sm cursor-pointer py-4 px-6 block hover:text-blue-500 focus:outline-none active text-blue-500 border-b-2 font-medium border-blue-500">
                فاکتور ها
            </span>
            <span data-target="panel-2" class="tab text-sm cursor-pointer text-white py-4 px-6 block hover:text-blue-500 focus:outline-none">
                استعلام های قبلی
            </span>
            <span data-target="panel-3" class="tab text-sm cursor-pointer text-white py-4 px-6 block hover:text-blue-500 focus:outline-none">
                تماس های قبلی
            </span>
            <span data-target="panel-4" class="tab text-sm cursor-pointer text-white py-4 px-6 block hover:text-blue-500 focus:outline-none">
                قیمت های داده شده
            </span>
        </nav>
    </div>
    <div id="panels" class="bg-gray-200">
        <div class="panel-1 tab-content active p-5">
            <article class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php
                if ($bills) :
                    foreach ($bills as $bill) :
                        $profile = "../../public/userimg/{$bill['user_id']}.jpg";
                        if (!file_exists($profile)) {
                            $profile = "../../public/userimg/default.png";
                        }
                ?>
                        <a href="../factor/complete.php?factor_number=<?= $bill['id'] ?>" target="_blank" class="flex justify-between cursor-pointer h-24 bg-white border p-3 rounded shadow-sm flex-wrap mb-2">
                            <div class="flex-grow flex flex-col justify-between px-3">
                                <div class="flex justify-between">
                                    <p class="text-sm font-bold">
                                        شماره فاکتور:
                                        <?= $bill['bill_number'] ?>
                                    </p>
                                    <p class="text-sm font-bold">
                                        تاریخ فاکتور:
                                        <?= $bill['bill_date'] ?>
                                    </p>
                                </div>
                                <div class="flex justify-between">
                                    <p class="text-sm font-bold">
                                        مشتری:
                                        <?= $bill['name'] ?? '' ?>
                                        <?= $bill['family'] ?? '' ?>
                                    </p>
                                    <p class="text-sm font-bold">
                                        قیمت کل:
                                        <?= $bill['total'] ?>
                                    </p>
                                </div>
                            </div>
                            <div class="w-14 flex justify-center items-center">
                                <img class="w-10 h-10 rounded-full" src="<?= $profile ?>" alt="user iamge" title="<?= $bill['username'] ?>" />
                            </div>
                        </a>
                    <?php endforeach;
                else : ?>
                    <div class="bg-sky-300 p-3 text-white text-center">
                        <p>هیچ فاکتوری برای این مشتری ثبت نشده است</p>
                    </div>
                <?php endif; ?>
            </article>
        </div>
        <div class="panel-2 tab-content p-5">
            <table class="w-full">
                <tr class="bg-cyan-600">
                    <th class="p-3 text-white text-sm font-semibold">اطلاعات</th>
                    <th class="p-3 text-white text-sm font-semibold">کاربر ثبت کننده</th>
                    <th class="p-3 text-white text-sm font-semibold">زمان</th>
                </tr>
                <?php
                if ($records) :
                    foreach ($records as $record) :
                        $time = $record['time'];
                        $callinfo = $record['callinfo'];
                        $user = $record['user']; ?>
                        <tr class="even:bg-sky-50 odd:bg-sky-100">
                            <td class="p-3 text-sm font-semibold text-center"><?= nl2br($callinfo) ?></td>
                            <td class="p-3 text-sm font-semibold text-center">
                                <?php
                                $fetchedUser = getUser($user);
                                if ($fetchedUser) {
                                    $name = $fetchedUser['name'];
                                    $family = $fetchedUser['family'];
                                    $file = "../../public/userimg/$user.jpg";
                                    if (!file_exists("../../public/userimg/$user.jpg")) {
                                        $file = "../../public/userimg/default.png";
                                    }
                                }
                                ?>
                                <img title='<?= "$name $family" ?>' class='w-8 h-8 rounded-full mx-auto' src='<?= $file ?>' alt='userimage'>
                            </td>
                            <td class="p-3 text-sm font-semibold text-center"><?= passedTime($time); ?></td>
                        </tr>
                <?php
                    endforeach;
                else :
                    echo '<tr class="bg-sky-300 ">
                        <td class="text-sm text-white text-center p-3" colspan="4">هیچ اطلاعاتی موجود نیست</td>
                    </tr>';
                endif;
                ?>
            </table>
        </div>
        <div class="panel-3 tab-content p-5">
            <table class="w-full">
                <tr class="bg-cyan-600">
                    <th class="text-sm text-white text-center p-3">پاسخ دهنده</th>
                    <th class="text-sm text-white text-center p-3">زمان</th>
                </tr>
                <?php
                $pretime = "";
                $incomingCalls = getIncomingCallReports($phone);
                if ($incomingCalls) :
                    foreach ($incomingCalls as $record) :
                        $time = $record['time'];
                        $user = $record['user'];
                        $id = $record['callid'];
                        $status = $record['status'];
                        $start = $record['starttime'];
                        $end = $record['endtime'];
                        $interval1 = timeDef(date('Y/m/d H:i:s'), $time);
                        $interval2 = timeDef($start, $end);
                        if ($status == 0 and $pretime == $time) {
                            $pretime = $time;
                            continue;
                        }
                        $pretime = $time; ?>
                        <tr class="odd:bg-sky-100 even:bg-sky-50">
                            <td class=" p-3 text-sm font-semibold text-center">
                                <?php
                                if ($status == 0) {
                                    echo ("<p class='answer-x mx-2'>X</p>");
                                } else {
                                    echo "<span class='inline mx-2'>&#10004;</span>";
                                    echo "<span>" . getNameByInternal($user) . "</span>";
                                }
                                ?>
                            </td>
                            <td class=" p-3 text-sm font-semibold text-center ">
                                <?= "<p>" . passedTime($time) . "</p>" ?>
                            </td>
                        </tr>
                <?php
                    endforeach;
                else :
                    echo '<tr class="bg-sky-300 ">
                        <td class="text-sm text-white text-center p-3" colspan="4">هیچ اطلاعاتی موجود نیست</td>
                    </tr>';
                endif;
                ?>
            </table>
        </div>
        <div class="panel-4 tab-content p-5">
            <?php
            if (isset($customer_id)) {
                $givenPrice = (givenPrice($customer_id));
            }
            if (isset($customer_id)) { ?>
                <table class="w-full">
                    <thead>
                        <tr class="bg-cyan-600">
                            <th class="text-sm text-white text-center p-3">قیمت</th>
                            <th class="text-sm text-white text-center p-3">کد فنی</th>
                            <th class="text-sm text-white text-center p-3">قیمت دهنده</th>
                            <th class="text-sm text-white text-center p-3">زمان</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (count($givenPrice) > 0) : ?>
                            <?php foreach ($givenPrice as $price) : ?>
                                <?php if ($price['price'] !== null) :
                                    $profile = "../../public/userimg/{$price['userID']}.jpg";
                                    if (!file_exists($profile)) {
                                        $profile = "../../public/userimg/default.png";
                                    } ?>
                                    <tr class="odd-bg-sky-100 even:bg-sky-50">
                                        <td style="direction: ltr !important;" class="p-3 text-sm font-semibold text-center ">
                                            <?= $price['price'] === null ? 'ندارد' : $price['price']  ?>
                                        </td>
                                        <td class="p-3 text-sm font-semibold text-center ">
                                            <?= $price['partnumber']; ?>
                                        </td>
                                        <td style="width:100px;">
                                            <img title="<?= $price['username'] ?>" class="w-8 h-8 rounded-full mx-auto" src="<?= $profile ?>" alt="userimage">
                                        </td>
                                        <td class="p-3 text-sm font-semibold text-center">
                                            <?= passedTime($price['created_at']); ?>
                                        </td>
                                    </tr>
                            <?php
                                endif;
                            endforeach;
                        else : ?>
                            <tr class="bg-sky-300 ">
                                <td class="text-sm text-white text-center p-3" colspan="4">هیچ اطلاعاتی موجود نیست</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
</section>
<div class="operation_acknowledge text-white px-3 py-2" id="operation_acknowledge"></div>


<div id="smsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-lg w-1/3 p-6 text-right relative">
        <h2 class="text-xl font-bold mb-4 text-gray-700">ارسال پیامک</h2>

        <label class="block mb-2 text-sm font-medium text-gray-700">شماره موبایل:</label>
        <input id="phone" type="text"
            value="<?php echo htmlspecialchars($phone ?? ''); ?>"
            class="w-full border border-gray-300 rounded-lg p-2 mb-4 focus:ring-2 focus:ring-yellow-400 outline-none"
            placeholder="مثلاً 09123456789" disabled>

        <label class="block mb-2 text-sm font-medium text-gray-700">متن پیام:</label>
        <textarea id="messageInput"
            class="w-full border border-gray-300 rounded-lg p-2 mb-3 h-28 resize-none focus:ring-2 focus:ring-yellow-400 outline-none"
            placeholder="متن پیام خود را وارد کنید"></textarea>


        <div id="readyMessages" class="flex flex-wrap gap-2 mb-4"></div>

        <div class="flex justify-between">
            <button onclick="sendSMS(this)" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
                ارسال
            </button>
            <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-400 rounded-lg hover:bg-gray-200 transition">
                بستن
            </button>
        </div>

        <div id="resultsBox"></div>
    </div>
    <script>
        const modal = document.getElementById('smsModal');
        const messageInput = document.getElementById('messageInput');
        const readyMessagesDiv = document.getElementById('readyMessages');
        const resultsBox = document.getElementById('resultsBox');

        function messageModal() {
            modal.classList.remove('hidden');
            loadMessages();
        }

        function closeModal() {
            modal.classList.add('hidden');
        }
        window.onclick = e => {
            if (e.target === modal) closeModal();
        };


        function updateFullName() {
            const fname = document.getElementById('name').value.trim();
            const lname = document.getElementById('last_name').value.trim();

            let fullname = [fname, lname].filter(Boolean).join(' ');

            if (!fullname) {
                fullname = 'مشتری';
            }

            const fullNameInput = document.getElementById('full_name');
            if (fullNameInput) {
                fullNameInput.value = fullname;
            }

            return fullname;
        }


        async function loadMessages() {
            const res = await fetch('./messages.json');
            const messages = await res.json();

            readyMessagesDiv.innerHTML = '';

            messages.forEach(m => {
                const btn = document.createElement('button');
                btn.textContent = m.title;
                btn.className = 'bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-lg text-sm';
                btn.onclick = () => {
                    const full_name = updateFullName();
                    messageInput.value = `${full_name} ${m.message}`;
                };
                readyMessagesDiv.appendChild(btn);
            });
        }


        async function sendSMS(element) {
            const phone = document.getElementById('phone').value.trim();
            const messageInput = document.getElementById('messageInput'); // ensure correct ID
            const resultsBox = document.getElementById('resultsBox'); // ensure correct ID
            const message = messageInput.value.trim();

            if (!phone || !message) {
                alert('شماره و متن پیام الزامی است');
                return;
            }

            element.disabled = true;
            const originalText = element.innerText;
            element.innerText = 'در حال ارسال پیام';

            try {
                const res = await fetch('../../app/api/callcenter/sendMessageAPI.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        phone,
                        message
                    })
                });

                const result = await res.json();
                const responseData = JSON.parse(result.response);

                resultsBox.innerText = responseData.message;
                resultsBox.className = 'bg-blue-100 text-blue-700 px-4 m-3 py-2 border border-gray-400 rounded-lg hover:bg-gray-200';
                if (responseData.status == 200) {
                    closeModal();
                }
            } catch (error) {
                console.error('Error sending SMS:', error);
                alert('خطا در ارسال پیام. لطفا دوباره تلاش کنید.');
            } finally {
                element.disabled = false;
                element.innerText = originalText;
            }
        }
    </script>




    <!-- MODAL SECTION -->
    <script src="./assets/js/cartable.js"></script>
    <script src="./assets/js/jquery.tagselect.js"></script>
    <script src="./assets/js/jquery.tagselect2.js"></script>
    <script>
        const price_textarea = document.getElementById('givenCode');
        const call_info_text = document.getElementById('call_info_text');

        function toEstelam() {
            call_info_text.value = price_textarea.value;
            price_textarea.value = null;
        }

        function toGivenPrice() {
            price_textarea.value = call_info_text.value;
            call_info_text.value = null;
        }

        $('.qtagselect__select').tagselect();
        $('.quserselect__select').userselect();

        $(document).ready(function() {
            $(".click-to-call").click(function() {
                window.open('http://admin:1028400NRa@<?= getIP($_SESSION["id"]) ?>/servlet?key=number=<?= $phone ?>&outgoing_uri=@192.168.9.10', 'برقراری تماس', 'width=400,height=400')
            });

            $(".click-to-cancell").click(function() {
                window.open('http://admin:1028400NRa@<?= getIP($_SESSION["id"]) ?>/servlet?key=CALLEND', 'برقراری تماس', 'width=400,height=400')
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll(".tab");
            const panels = document.querySelectorAll(".tab-content");

            tabs.forEach((tab) => {
                tab.addEventListener("click", function() {
                    const target = tab.dataset.target;

                    // Remove active classes from all tabs and panels
                    tabs.forEach((item) => {
                        item.classList.remove("active", "text-blue-500", "border-b-2", "font-medium", "border-blue-500");
                        item.classList.add("text-white");
                    });

                    panels.forEach((panel) => {
                        panel.classList.remove("active");
                    });

                    // Add active class to the clicked tab and corresponding panel
                    tab.classList.add("active", "text-blue-500", "border-b-2", "font-medium", "border-blue-500");
                    tab.classList.remove("text-white");
                    document.querySelector(`.${target}`).classList.add("active");
                });
            });
        });
    </script>

    <?php
    require_once './components/footer.php';
