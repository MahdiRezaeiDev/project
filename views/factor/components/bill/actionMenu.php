<ul class="action_menu">
    <li style="position: relative;">
        <a class="action_button print bg-white rounded-full flex justify-center items-center text-white text-sm" href="./yadakFactor.php?factorNumber=<?= $BillInfo['id'] ?>">
            <img src="./assets/img/logo.png" class="rounded-full" alt="">
        </a>
        <p class="action_tooltip text-sm">فاکتور یدک شاپ</p>
    </li>
    <li style="position: relative;">
        <a target="_blank" class="action_button print bg-sky-600 rounded-full flex justify-center items-center text-white text-sm" href="./addPayment.php?factor=<?= $BillInfo['bill_number'] ?>">
            وازیزی
        </a>
        <p class="action_tooltip text-sm">ثبت واریزی</p>
    </li>
    <li style="position: relative;">
        <a class="action_button print bg-green-500 rounded-full flex justify-center items-center text-white text-sm" href="./insuranceFactor.php?factorNumber=<?= $BillInfo['id'] ?>">بیمه</a>
        <p class="action_tooltip">فاکتور بیمه</p>
    </li>
    <li style="position: relative;">
        <a class="action_button print bg-blue-500 rounded-full flex justify-center items-center text-white text-sm" href="./partnerFactor.php?factorNumber=<?= $BillInfo['id'] ?>">همکار</a>
        <p class="action_tooltip">فاکتور همکار</p>
    </li>
    <li style="position: relative;">
        <a class="action_button print bg-gray-500 rounded-full flex justify-center items-center text-white text-sm" href="./koreaFactor.php?factorNumber=<?= $BillInfo['id'] ?>">کوریا</a>
        <p class="action_tooltip">فاکتور کوریا</p>
    </li>
    <li style="position: relative;">
        <img class="action_button print" onclick="handlePrint('<?= $factorNumber ?>')" src="./assets/img/print.svg" alt="print icon">
        <p class="action_tooltip">پرینت</p>
    </li>
    <li style="position: relative;">
        <img class="action_button share" src="./assets/img/share.svg" alt="print icon">
        <p class="action_tooltip">اشتراک گذاری</p>
    </li>
    <li style="position: relative;">
        <img class="action_button pdf" src="./assets/img/pdf.svg" onclick="saveAsPDF()" alt="print icon">
        <p class="action_tooltip">پی دی اف</p>
    </li>
    <?php
    // Determine delivery icon
    switch ($BillInfo['delivery_type']) {
        case 'تیپاکس':
        case 'اتوبوس':
        case 'سواری':
        case 'باربری':
            $src = './assets/img/delivery.svg';
            break;
        case 'پیک مشتری':
            $src = './assets/img/customer.svg';
            break;
        case 'پیک یدک شاپ':
            $src = './assets/img/yadakshop.svg';
            break;
        case 'هوایی':
            $src = './assets/img/airplane.svg';
            break;
        default:
            $src = './assets/img/customer.svg';
    }
    ?>
    <li style="position: relative;">
        <img
            onclick="displayDeliveryModal(this)"
            data-bill="<?= $BillInfo['shomare'] ?>"
            data-contact="<?= $BillInfo['contact_type'] ?>"
            data-destination="<?= $BillInfo['destination'] ?>"
            data-type="<?= $BillInfo['delivery_type'] ?>"
            data-address="<?= $customerInfo['address'] ?>"
            src="<?= $src; ?>"
            class="action_button bg-green-600 rounded-full p-2" src="./assets/img/customer_copy.svg" alt="delivery icon">
        <p class="action_tooltip">ثبت روش ارسال</p>
    </li>
</ul>
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
<style>
    @media print {
        .hide_while_print {
            display: none !important;
        }
    }
</style>
<script>
    function handlePrint(factorNumber) {
        const params = new URLSearchParams();
        params.append('factorNumber', factorNumber);
        params.append('action', 'print');

        axios.post('../../app/api/factor/CompleteFactorApi.php', params).then(res => {
            console.log(res);
        }).catch(err => {
            console.log(err);
        });

        window.print();
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
        toast.className = `fixed bottom-5 right-5 px-4 py-2 rounded shadow-lg text-white z-50 transition-opacity duration-500 hide_while_print ${
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
</script>