<?php
$pageTitle = "ورود کالا";
$iconUrl = 'purchase.svg';
require_once './components/header.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php';
?>
<div class="flex justify-center px-5">
    <div class="w-2/3">
        <table class="w-full border border-1 border-gray-800">
            <thead class="bg-gray-100 border-b border-gray-800">
                <tr class="bg-gray-800">
                    <th class="p-3 text-white" colspan="10">فاکتور ورود
                    </th>
                </tr>
                <tr>
                    <th colspan="2" class="text-right p-3 text-sm">
                        <label class="cursor-pointer" for="bill_number">شماره فاکتور</label>
                    </th>
                    <th colspan="2" class="p-3">
                        <input onkeyup="convertToEnglish(this);" onblur="setBillNumber(this.value)" class="p-2 border w-full" type="text" name="bill_number" id="bill_number">
                    </th>
                    <th colspan="2" class="text-right p-3 text-sm">
                        <label class="cursor-pointer" for="invoice_time">تاریخ
                            <span class="text-red-500">*</span>
                        </label>
                    </th>
                    <th colspan="2" class="p-3">
                        <input onchange="setFactorDate(this.value)" class="p-2 w-full h-full" type="text" name="invoice_time" id="invoice_time" value="<?php echo (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>">
                        <span id="span_invoice_time"></span>
                    </th>
                </tr>
                <tr>
                    <th colspan="2" class="text-right p-3 text-sm">
                        <label class="cursor-pointer" for="seller">فروشنده
                            <span class="text-red-500">*</span>
                        </label>
                    </th>
                    <th colspan="2" class="p-3 relative">
                        <input class="p-2 border w-full uppercase" type="text" name="seller" id="seller" onkeyup="convertToPersian(this);searchSellers(this.value)">
                        <div id="seller_container" style="top:85%; z-index:1000000;" class="hidden absolute shadow-lg mx-3 bg-white right-0 left-0 max-h-80 p-3 rounded border  overflow-y-auto">
                            <!-- matched sellers will be appended here -->
                        </div>
                    </th>
                    <th colspan="2" class="text-right p-3 text-sm">
                        <label class="cursor-pointer">وارد انبار شده؟</label>
                    </th>
                    <th colspan="2" class="p-3 text-sm">
                        <div class="flex justify-between items-center px-10">
                            <div class="flex justify-between items-center gap-1">
                                <input checked onclick="setIsEntered(true)" class="p-2 w-full h-full" type="radio" name="is_entered" id="is_entered_true">
                                <label class="cursor-pointer" for="is_entered_true">بلی</label>
                            </div>
                            <div class="flex justify-between items-center gap-1">
                                <input onclick="setIsEntered(false)" class="p-2 w-full h-full" type="radio" name="is_entered" id="is_entered_false">
                                <label class="cursor-pointer" for="is_entered_false">خیر</label>
                            </div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="receiver">تحویل دهنده</label>
                        <span class="text-red-500">*</span>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold relative">
                        <input onkeyup="convertToPersian(this);searchReceiver(this.value)" class="p-2 w-full uppercase" type="text" min='1' name="receiver" id="receiver">
                        <div id="receiver_container" style="top:85%; z-index:1000000;" class="hidden absolute shadow-lg mx-3 bg-white right-0 left-0 max-h-80 p-3 rounded border  overflow-y-auto">
                            <!-- matched sellers will be appended here -->
                        </div>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="inventory">
                            انبار
                            <span class="text-red-500">*</span>
                        </label>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold relative">
                        <input onkeyup="convertToPersian(this);searchInventory(this.value)" class="p-2 w-full uppercase" type="text" name="inventory" id="inventory">
                        <div id="inventory_container" style="top:85%; z-index:1000000;" class="hidden absolute shadow-lg mx-3 bg-white right-0 left-0 max-h-80 p-3 rounded border  overflow-y-auto">
                            <!-- matched sellers will be appended here -->
                        </div>
                    </td>
                </tr>
            </thead>
            <tbody id="bill_items_container" class="m-h-12">
                <tr class="bg-teal-600">
                    <td class="p-3 text-sm text-white">ردیف</td>
                    <td class="p-3 text-sm text-white">کد فنی</td>
                    <td class="p-3 text-sm text-white">اصالت</td>
                    <td class="p-3 text-sm text-white">تعداد</td>
                    <td class="p-3 text-sm text-white">قفسه</td>
                    <td class="p-3 text-sm text-white">راهرو</td>
                    <td class="p-3 text-sm text-white">توضیحات</td>
                    <td class="p-3 text-sm text-white w-16"> <img src="./assets/icons/setting.svg" /></td>
                </tr>
                <tr>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                </tr>
                <tr>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                    <td class="p-3"></td>
                </tr>
            </tbody>
            <tfoot class="bg-gray-100 border-t border-gray-800">
                <tr>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="partNumber"> کدفنی</label>
                        <span class="text-red-500">*</span>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold relative">
                        <input onkeyup="convertToEnglish(this);searchParts(this.value)" class="p-2 w-full uppercase" type="text" name="partNumber" id="partNumber">
                        <div id="part_container" style="top:85%; z-index:1000000;" class="hidden absolute shadow-lg mx-3 bg-white right-0 left-0 max-h-80 p-3 rounded border  overflow-y-auto">
                            <!-- matched sellers will be appended here -->
                        </div>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="brand">اصالت</label>
                        <span class="text-red-500">*</span>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold relative">
                        <input onkeyup="convertToEnglish(this);searchBrand(this.value)" class="p-2 w-full uppercase" type="text" name="brand" id="brand">
                        <div id="brand_container" style="top:85%; z-index:1000000;" class="hidden absolute shadow-lg mx-3 bg-white right-0 left-0 max-h-80 p-3 rounded border  overflow-y-auto">
                            <!-- matched sellers will be appended here -->
                        </div>
                    </td>
                </tr>
                <tr class="">
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="quantity">تعداد</label>
                        <span class="text-red-500">*</span>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <input class="p-2 w-full" type="number" min='1' name="quantity" id="quantity">
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="position2">راهرو</label>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <input class="p-2 w-full uppercase" type="text" name="position1" id="position1">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="position1">قفسه</label>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <input class="p-2 w-full uppercase" type="text" name="position2" id="position2">
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <label for="description">توضیحات</label>
                    </td>
                    <td colspan="2" class="p-3 text-sm font-bold">
                        <textarea name="description" id="description" class="w-full p-2" rows="2"></textarea>
                    </td>
                </tr>
            </tfoot>
        </table>
        <img src="./assets/icons/add.svg" onclick="addItem()" class="w-6 h-6 cursor-pointer" alt="add item to the bill">
    </div>
</div>

<div class="fixed bottom-1 left-1 bg-cyan-900 opacity-90 p-5 rounded-lg">
    <p class="text-center text-gray-400 text-xs">موارد فوق را به دقت وارد نموده سپس بروی علامت
        <img src="./assets/icons/add.svg" class="w-5 h-5 bg-white p-1 rounded inline" alt="add item to the bill">
        کلیک نمایید تا به فاکتورد اضافه شود.
    </p>
    <br>
    <p class="text-center text-gray-400 text-xs">سرانجام با زدن دکمه ثبت فاکتور میتوانید همه موارد اضافه شده را به یکباره ثبت نمایید.</p>
    <p id="message" class="fixed text-sm py-3 px-5 rounded left-5 bottom-5 hidden"></p>
</div>
<?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
    <button onclick="saveFactor()" id="message" class="fixed text-sm py-3 px-5 rounded right-5 bottom-5 bg-blue-500 text-white">ثبت فاکتور</button>
<?php endif ?>
<script src="../../public/js/assets/jalaliMoment.js"></script>
<script>
    const apiEndPoint = "../../app/api/inventory/PurchaseApi.php";

    const sellerContainer = document.getElementById('seller_container');
    const part_container = document.getElementById('part_container');
    const brand_container = document.getElementById('brand_container');
    const receiver_container = document.getElementById('receiver_container');
    const inventory_container = document.getElementById('inventory_container');
    const message = document.getElementById('message');

    const partNumber = document.getElementById('partNumber');
    const brand = document.getElementById('brand');
    const receiver = document.getElementById('receiver');
    const inventory = document.getElementById('inventory');
    const quantity = document.getElementById('quantity');
    const position1 = document.getElementById('position1');
    const position2 = document.getElementById('position2');
    const description = document.getElementById('description');


    let factor_info = {
        seller_id: null,
        seller_name: null,
        date: moment().locale('fa').format('YYYY/MM/DD'),
        bill_number: null,
        is_entered: true,
        stock_id: null,
        deliverer: null,
    }

    let factor_items = [];

    function searchSellers(pattern = '') {

        if (pattern.length >= 1) {
            var params = new URLSearchParams();
            params.append('searchForSeller', 'searchForSeller');
            params.append('pattern', pattern);

            sellerContainer.innerHTML = '';
            sellerContainer.classList.remove('hidden');
            axios.post(apiEndPoint, params)
                .then(function(response) {
                    const sellers = response.data;

                    for (const seller of sellers) {
                        sellerContainer.innerHTML += `
                            <div class="flex justify-between py-2 my-1 bg-gray-100 px-3" onclick=SelectSeller(this) 
                            data-id="${seller.id}"
                            data-name="${seller.name}"
                            >
                                <p class="text-xs">${seller.name}</p>
                                <img src="./assets/icons/plus.svg" />
                            </div>`;
                    }


                })
                .catch(function(error) {
                    console.log(error);
                });
        } else {
            sellerContainer.classList.add('hidden');
        }
    }

    function SelectSeller(element) {
        const id = element.getAttribute('data-id');
        const name = element.getAttribute('data-name');
        document.getElementById('seller').value = name;

        factor_info.seller_id = id;
        factor_info.seller_name = name;
        sellerContainer.classList.add('hidden');
    }

    let cancelToken;

    function searchParts(pattern = '') {
        part_container.innerHTML = '';
        if (pattern.length > 6) {
            // Cancel the previous request if it's still ongoing
            if (cancelToken) {
                cancelToken.cancel();
            }

            // Create a new cancel token
            cancelToken = axios.CancelToken.source();

            var params = new URLSearchParams();
            params.append('searchForPart', 'searchForPart');
            params.append('pattern', pattern);

            part_container.innerHTML = '';
            part_container.classList.remove('hidden');

            axios.post(apiEndPoint, params, {
                    cancelToken: cancelToken.token
                })
                .then(function(response) {
                    const parts = response.data;
                    for (const part of parts) {
                        part_container.innerHTML += `
                        <div class="flex justify-between py-2 my-1 bg-gray-100 px-3 cursor-pointer" onclick=SelectPart(this) 
                        data-id="${part.id}"
                        data-name="${part.partnumber}">
                            <p class="text-xs">${part.partnumber}</p>
                            <img src="./assets/icons/plus.svg" />
                        </div>`;
                    }
                })
                .catch(function(error) {
                    if (axios.isCancel(error)) {
                        console.log('Previous request canceled', error.message);
                    } else {
                        console.log(error);
                    }
                });
        } else {
            part_container.classList.add('hidden');
        }
    }


    function SelectPart(element) {
        const id = element.getAttribute('data-id');
        const name = element.getAttribute('data-name');
        partNumber.value = name;
        partNumber.setAttribute('data-id', id);
        part_container.classList.add('hidden');
    }

    let cancelBrandToken;

    function searchBrand(pattern) {
        if (pattern.length >= 2) {
            // Cancel the previous request if it's still ongoing
            if (cancelBrandToken) {
                cancelBrandToken.cancel();
            }

            // Create a new cancel token
            cancelBrandToken = axios.CancelToken.source();

            var params = new URLSearchParams();
            params.append('searchForBrand', 'searchForBrand');
            params.append('pattern', pattern.toUpperCase());

            brand_container.innerHTML = '';
            brand_container.classList.remove('hidden');

            axios.post(apiEndPoint, params, {
                    cancelToken: cancelBrandToken.token
                })
                .then(function(response) {
                    const parts = response.data;

                    for (const part of parts) {
                        brand_container.innerHTML += `
                        <div class="flex justify-between py-2 my-1 bg-gray-100 px-3 cursor-pointer" onclick=SelectBrand(this) 
                        data-id="${part.id}"
                        data-name="${part.name}">
                            <p class="text-xs">${part.name}</p>
                            <img src="./assets/icons/plus.svg" />
                        </div>`;
                    }
                })
                .catch(function(error) {
                    if (axios.isCancel(error)) {
                        console.log('Previous request canceled', error.message);
                    } else {
                        console.log(error);
                    }
                });
        } else {
            brand_container.classList.add('hidden');
        }
    }


    function SelectBrand(element) {
        const id = element.getAttribute('data-id');
        const name = element.getAttribute('data-name');
        brand.value = name;
        brand.setAttribute('data-id', id);
        brand_container.classList.add('hidden');
    }

    function searchReceiver(pattern) {
        if (pattern.length >= 2) {
            var params = new URLSearchParams();
            params.append('searchForReceiver', 'searchForReceiver');
            params.append('pattern', pattern.toUpperCase());

            receiver_container.innerHTML = '';
            receiver_container.classList.remove('hidden');
            axios.post(apiEndPoint, params)
                .then(function(response) {
                    const parts = response.data;

                    for (const part of parts) {
                        receiver_container.innerHTML += `
                            <div class="flex justify-between py-2 my-1 bg-gray-100 px-3 cursor-pointer" onclick=SelectReceiver(this) 
                            data-id="${part.id}"
                            data-name="${part.name}">
                                <p class="text-xs">${part.name}</p>
                                <img src="./assets/icons/plus.svg" />
                            </div>`;
                    }


                })
                .catch(function(error) {
                    console.log(error);
                });
        } else {
            brand_container.classList.add('hidden');
        }
    }

    function SelectReceiver(element) {
        const id = element.getAttribute('data-id');
        const name = element.getAttribute('data-name');
        receiver.value = name;
        factor_info.deliverer = id;
        receiver_container.classList.add('hidden');
    }

    function searchInventory(pattern) {
        if (pattern.length >= 2) {
            var params = new URLSearchParams();
            params.append('searchForInventory', 'searchForInventory');
            params.append('pattern', pattern.toUpperCase());

            inventory_container.innerHTML = '';
            inventory_container.classList.remove('hidden');
            axios.post(apiEndPoint, params)
                .then(function(response) {
                    const parts = response.data;

                    for (const part of parts) {
                        inventory_container.innerHTML += `
                            <div class="flex justify-between py-2 my-1 bg-gray-100 px-3 cursor-pointer" onclick=SelectInventory(this) 
                            data-id="${part.id}"
                            data-name="${part.name}">
                                <p class="text-xs">${part.name}</p>
                                <img src="./assets/icons/plus.svg" />
                            </div>`;
                    }


                })
                .catch(function(error) {
                    console.log(error);
                });
        } else {
            brand_container.classList.add('hidden');
        }
    }

    function SelectInventory(element) {
        const id = element.getAttribute('data-id');
        const name = element.getAttribute('data-name');
        inventory.value = name;
        factor_info.stock_id = id;
        inventory_container.classList.add('hidden');
    }


    $(function() {
        $("#invoice_time").persianDatepicker({
            months: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
            dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
            shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
            showGregorianDate: !1,
            persianNumbers: !0,
            formatDate: "YYYY/0M/0D",
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
            isRTL: 1,
            calendarPosition: {
                x: 0,
                y: 0,
            },
            onShow: function() {},
            onHide: function() {},
            onSelect: function() {
                const date = ($("#invoice_time").val());
                factor_info.date = date;
            },
            onRender: function() {}
        });
    });

    function setBillNumber(billNumber) {
        factor_info.bill_number = billNumber;
    }

    function setIsEntered(isEntered) {
        factor_info.is_entered = isEntered;
    }

    function addItem() {
        const partNumberId = partNumber.getAttribute('data-id');
        const brandId = brand.getAttribute('data-id');

        if (partNumberId && brandId && quantity.value !== '') {
            factor_items.push({
                partNumber: partNumber.value,
                part_id: partNumberId,
                brand_id: brandId,
                brand: brand.value.toUpperCase(),
                quantity: quantity.value,
                position1: position1.value,
                position2: position2.value,
                description: description.value,
            });

            // Clear the input fields
            partNumber.value = '';
            partNumber.setAttribute('data-id', '');
            // brand.value = '';
            // brand.setAttribute('data-id', '');
            quantity.value = '';
            position1.value = '';
            position2.value = '';
            description.value = '';

            displayBill();
        } else {
            message.classList.remove('hidden');
            message.classList.add('bg-rose-800');
            message.classList.add('text-white');
            message.innerHTML = 'لطفا موارد اجباری را بصورت دقیق انتخاب کنید';

            setTimeout(() => {
                message.classList.add('hidden');
                message.classList.remove('bg-rose-800');
                message.classList.remove('text-white');
                message.innerHTML = '';
            }, 3000);
        }
    }

    function displayBill() {
        const bill_items_container = document.getElementById('bill_items_container');

        bill_items_container.innerHTML = `
                <tr class="bg-teal-600">
                    <td class="p-3 text-sm text-white">ردیف</td>
                    <td class="p-3 text-sm text-white">کد فنی</td>
                    <td class="p-3 text-sm text-white">اصالت</td>
                    <td class="p-3 text-sm text-white">تعداد</td>
                    <td class="p-3 text-sm text-white">قفسه</td>
                    <td class="p-3 text-sm text-white">راهرو</td>
                    <td class="p-3 text-sm text-white">توضیحات</td>
                    <td class="p-3 text-sm text-white w-16"> <img src="./assets/icons/setting.svg" /></td>
                </tr>`;
        let counter = 1;
        for (const item of factor_items) {
            bill_items_container.innerHTML += `
                <tr class="odd:bg-blue-50 even:bg-blue-100">
                    <td class="p-3 text-sm">${counter}</td>
                    <td class="p-3 text-sm">${item.partNumber}</td>
                    <td class="p-3 text-sm">${item.brand}</td>
                    <td class="p-3 text-sm">${item.quantity}</td>
                    <td class="p-3 text-sm">${item.position1}</td>
                    <td class="p-3 text-sm">${item.position2}</td>
                    <td class="p-3 text-sm">${item.description}</td>
                    <td class="p-3 text-sm">
                        <img class="cursor-pointer" onclick=deleteItem('${counter-1}') src="./assets/icons/minus.svg" />
                    </td>
                </tr>`;
            counter++;
        }
    }

    function deleteItem(index) {
        if (index >= 0 && index < factor_items.length) {
            factor_items.splice(index, 1);
            displayBill();
        }
    }

    function saveFactor() {
        if (
            factor_info.seller_id != null &&
            factor_info.deliverer != null &&
            factor_info.stock_id != null &&
            factor_items.length > 0) {
            var params = new URLSearchParams();
            params.append('saveFactor', 'saveFactor');
            params.append('factor_info', JSON.stringify(factor_info));
            params.append('factor_items', JSON.stringify(factor_items));

            axios.post(apiEndPoint, params)
                .then(function(response) {
                    message.classList.remove('hidden');
                    message.classList.add('bg-green-800');
                    message.classList.add('text-white');
                    message.innerHTML = 'فاکتور شما با موفقیت ذخیره شد.';
                    clear_form();

                    setTimeout(() => {
                        message.classList.add('hidden');
                        message.classList.remove('bg-green-800');
                        message.classList.remove('text-white');
                        message.innerHTML = '';
                    }, 3000);
                })
                .catch(function(error) {
                    console.log(error);
                });

        } else {
            message.classList.remove('hidden');
            message.classList.add('bg-rose-800');
            message.classList.add('text-white');
            message.innerHTML = 'لطفا معلومات فاکتو را بصورت دقیق اضافه نمایید.';

            setTimeout(() => {
                message.classList.add('hidden');
                message.classList.remove('bg-rose-800');
                message.classList.remove('text-white');
                message.innerHTML = '';
            }, 3000);
        }
    }

    function clear_form() {
        actor_info = {
            seller_id: null,
            seller_name: null,
            date: moment().locale('fa').format('YYYY/MM/DD'),
            bill_number: null,
            is_entered: true,
            stock_id: null,
            deliverer: null,
        }
        factor_items = [];
        partNumber.value = null;
        partNumber.setAttribute('data-id', null);
        brand.value = null;
        brand.setAttribute('data-id', null);
        quantity.value = null;;
        position1.value = null;
        position2.value = null;
        description.value = null;
        displayBill();
    }
</script>
<?php
require_once './components/footer.php';
?>