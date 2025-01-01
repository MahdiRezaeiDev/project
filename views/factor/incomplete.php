<?php
$pageTitle = "ویرایش فاکتور";
$iconUrl = 'factor.svg';
require_once './components/header.php';
require_once '../../utilities/callcenter/GivenPriceHelper.php';
require_once '../../utilities/inventory/InventoryHelpers.php';
require_once '../../app/controller/factor/LoadFactorItemBrands.php';
require_once '../../utilities/callcenter/DollarRateHelper.php';
require_once '../../app/controller/factor/IncompleteFactorController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<link rel="stylesheet" href="./assets/css/bill.css" />
<link rel="stylesheet" href="./assets/css/incomplete.css" />
<style>
    .exclude {
        border-radius: 5px;
        background: #000000;
        padding: 0 5px;
        color: white;
    }
</style>
<div id="wholePage" class="bg-rose-300 mb-12">
    <?php require_once './components/factorSearch.php'; ?>
    <!-- Bill editing and information section -->
    <section class="rtl mb-4 mt-2">
        <!-- bill and customer information table -->
        <div class="bg-white shadow-md w-full">
            <div class="bg-gray-800 text-white p-3 ">
                مشخصات خریدار
            </div>
            <div class="min-w-full border border-gray-800 text-gray-400 mb-5 grid md:grid-cols-3 lg:grid-cols-5 gap-3 p-3">
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">تلفون</td>
                    <td class="py-2 px-4">
                        <input autocomplete="off" onblur="ifCustomerExist(this)" onkeyup="sanitizeCustomerPhone(this);updateCustomerInfo(this)" class="w-full p-2 border text-gray-500" placeholder="093000000000" type="text" name="phone" id="phone">
                        <p id="phone_error" class="hidden text-xs text-red-500 py-1">لطفا شماره تماس مشتری را وارد نمایید.</p>
                    </td>
                </div>
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">نام</td>
                    <td class="py-2 px-4">
                        <input class="w-full p-2 border" type="hidden" name="id" id="id">
                        <input class="w-full p-2 border" type="hidden" name="type" id="mode" value='create'>
                        <input autocomplete="off" onkeyup="updateCustomerInfo(this)" class="w-full p-2 border text-gray-500" placeholder="نام مشتری را وارد کنید..." type="text" name="name" id="name">
                        <p id="name_error" class="hidden text-xs text-red-500 py-1">لطفا اسم مشتری را وارد نمایید.</p>
                        <label class="text-xs ml-2 cursor-pointer" for="mr">
                            <input type="radio" class="ml-1" name="suffix" id="mr" onclick="appendPrefix('جناب آقای'); event.stopPropagation();">جناب آقای
                        </label>

                        <label class="text-xs ml-2 cursor-pointer" for="miss">
                            <input type="radio" class="ml-1" name="suffix" id="miss" onclick="appendPrefix('سرکار خانم'); event.stopPropagation();">سرکار خانم
                        </label>

                        <label class="text-xs ml-2 cursor-pointer" for="compony">
                            <input type="radio" class="ml-1" name="suffix" id="compony" onclick="appendPrefix('شرکت'); event.stopPropagation();">شرکت
                        </label>

                        <label class="text-xs ml-2 cursor-pointer" for="store">
                            <input type="radio" class="ml-1" name="suffix" id="store" onclick="appendPrefix('فروشگاه'); event.stopPropagation();">فروشگاه
                        </label>
                    </td>
                </div>
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">نام خانوادگی</td>
                    <td class="py-2 px-4">
                        <input autocomplete="off" onkeyup="updateCustomerInfo(this)" class="w-full p-2 border text-gray-500" placeholder="نام خانوادگی مشتری را وارد کنید..." type="text" name="family" id="family">
                    </td>
                </div>

                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">آدرس</td>
                    <td class="py-2 px-4">
                        <textarea autocomplete="off" onkeyup="updateCustomerInfo(this)" name="address" id="address" cols="30" rows="1" class="border p-2 w-full text-gray-500" placeholder="آدرس مشتری"></textarea>
                        <p id="address_error" class="hidden text-xs text-red-500 py-1">لطفا آدرس مشتری را وارد نمایید.</p>
                    </td>
                </div>
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">ماشین</td>
                    <td class="py-2 px-4">
                        <input autocomplete="off" onkeyup="updateCustomerInfo(this)"
                            onchange="handleInputChange(event)"
                            data-old=''
                            class="w-full p-2 border text-gray-500" placeholder="نوعیت ماشین مشتری را مشخص کنید" type="text" name="car" id="car">
                    </td>
                </div>
            </div>
        </div>
        <script>
            function handleInputChange(event) {
                const oldValue = event.target.getAttribute('data-old');
                const inputValue = event.target.value.trim(); // Get and trim the input value
                event.target.setAttribute('data-old', inputValue);


                if (inputValue) {
                    let found = false; // Flag to track if a match is found

                    for (let item of factorItems) {
                        if (oldValue != '' && item.partName.includes(oldValue)) {
                            item.partName = item.partName.replace(oldValue, inputValue);
                        } else {
                            const lastDashIndex = item.partName.lastIndexOf('-');

                            if (lastDashIndex !== -1) {
                                // Insert inputValue before the last '-'
                                item.partName =
                                    item.partName.slice(0, lastDashIndex).trim() +
                                    ` ${inputValue} - ` +
                                    item.partName.slice(lastDashIndex + 1).trim();
                            } else {
                                // If no '-' is found, add inputValue at the end
                                item.partName = `${item.partName} ${inputValue}`;
                            }
                        }
                    }
                }
                displayBill();
            }
        </script>
        <!-- bill body table -->
        <div class="bg-white shadow-md p-2 w-full col-span-3 mb-3">
            <div class=" mx-auto">
                <table class="min-w-full border border-gray-800 text-gray-400">
                    <thead>
                        <tr class="bg-gray-800">
                            <th class="py-2 px-4 border-b text-white w-10">#</th>
                            <th class="py-2 px-4 border-b text-white text-right w-2/4">نام قطعه</th>
                            <th class="py-2 px-4 border-b text-white w-18"> تعداد</th>
                            <th class="py-2 px-4 border-b text-white  w-18"> قیمت</th>
                            <th class="py-2 px-4 border-b text-white  w-18"> قیمت کل</th>
                            <th class="py-2 px-4 border-b w-12 h-12 font-medium  w-18">
                                <img class="bill_icon" src="./assets/img/setting.svg" alt="settings icon">
                            </th>
                        </tr>
                    </thead>
                    <tbody id="bill_body" class="text-gray-800">
                    </tbody>
                </table>
                <div class="flex flex-row justify-between py-5 gap-5">
                    <textarea onkeyup="updateFactorInfo(this)" class="border-2 border-gray-400 focus:border-gray-800 w-1/3 p-5 outline-none" name="description" id="description" placeholder="توضیحات فاکتور را وارد نمایید ..." cols="20" rows="3"></textarea>
                    <section class="rtl p-5 backdrop-blur-xl bg-black/20 m-5 rounded-md">
                        <ul class="list-disc list-inside">
                            <li class="text-sm">برای ایجاد آیتم جدید در فاکتور از کلیدهای ترکیبی <code class="text-white bg-black px-1 rounded-md text-xs">Ctrl + Shift</code> استفاده نمایید. </li>
                            <li class="text-sm">با استفاده از کلید <span class="text-white bg-black px-1 rounded-md text-xs">F9</span> میتوانید پیش فاکتور مشتری را مشاهده کنید.</li>
                            <li class="text-sm">برای جابجای راحت میان ستون ها از کلید <span class="text-white bg-black px-1 rounded-md text-xs">Tab</span> میتوانید استفاده کنید.</li>
                            <li class="text-sm">برای جابجای میان سطرها از کلید <span class="text-white bg-black px-1 rounded-md text-xs">Enter</span> میتوانید استفاده کنید.</li>
                            <li class="text-sm">برای ذخیره فاکتور میتوانید از کلیدهای ترکیبی <span class="text-white bg-black px-1 rounded-md text-xs">Alt + S</span> استفاده نمایید.</li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
        <?php require_once './components/factorDetails.php' ?>
    </section>
    <?php if ($_SESSION["financialYear"] == '1403') : ?>
        <!-- Bill Operation Section -->
        <section class="rtl fixed flex justify-between items-center min-w-full h-12 bottom-0 bg-gray-800 px-3">
            <ul class="flex gap-3">
                <li>
                    <button onclick="updateIncompleteFactor()" id="incomplete_save_button" class="bg-blue-400 text-white rounded px-3 py-1 cursor-pointer">
                        ذخیره تغییرات پیش فاکتور
                    </button>
                </li>
                <li>
                    <button onclick="generateBill(this)" id="complete_save_button" class="bg-white rounded text-gray-800 px-3 py-1 cursor-pointer disabled:opacity-50 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        صدور فاکتور
                    </button>
                </li>
            </ul>
            <p id="save_message" class="hidden bg-white text-green-400 px-3 py-1">ویرایش موفقانه صورت گرفت</p>
            <p id="save_error_message" class="hidden bg-red-500 text-white px-3 py-1">خطا در ویرایش اطلاعات</p>
        </section>
    <?php endif; ?>
</div>
<?php
// Required Components
require_once './components/modal.php';
require_once './components/factor.php';
?>
<script>
    const BRANDS_ENDPOINT = '../../app/api/factor/LoadFactorItemBrandsAPI.php';
    // Accessing the conatainers to have global access for easy binding data
    const customer_results = document.getElementById('customer_results');
    const resultBox = document.getElementById("selected_box");
    const stock_result = document.getElementById("stock_result");
    const bill_body = document.getElementById("bill_body");
    let title = 'ویرایش پیش فاکتور';

    // Assign the customer info received from the server to the JS Object to work with and display after ward
    const customerInfo = <?= json_encode($customerInfo); ?>;
    factorInfo.totalInWords = numberToPersianWords(<?= (float)$factorInfo['total'] ?>)
    const factorItems = <?= $billItems ?>;
    const ItemsBrands = <?= $billItemsBrandAndPrice ?>;
    const AllBrands = <?= json_encode($brands) ?>;
    const originalPrices = <?= $originalPrices ?>;

    function bootstrap() {
        displayCustomer(customerInfo);
        displayBill();
    }

    // A functionn to display Bill customer information in the table
    function displayCustomer(customer) {
        if (customerInfo.displayName != "" && customerInfo.family != "") {
            title = customerInfo.displayName + " " + customerInfo.family;
        }

        document.getElementById("customer_factor").innerHTML = title;
        document.getElementById('id').value = customerInfo.id;
        document.getElementById('mode').value = customerInfo.mode;
        document.getElementById('name').value = customerInfo.displayName;
        document.getElementById('family').value = customerInfo.family;
        document.getElementById('phone').value = customerInfo.phone;
        document.getElementById('car').value = customerInfo.car;
        document.getElementById('car').setAttribute('data-old', customerInfo.car);
        document.getElementById('address').value = customerInfo.address;
    }

    // A function to display bill items and calculate the amount and goods count and display bill details afterword
    function displayBill() {
        let counter = 0;
        let template = ``;
        let totalPrice = 0;
        factorInfo.quantity = 0;

        for (const item of factorItems) {
            const payPrice = Number(item.quantity) * Number(item.price_per);
            totalPrice += payPrice;
            factorInfo.quantity += Number(item.quantity);

            if (!item.hasOwnProperty('actual_price')) {
                item.actual_price = item.price_per;
            }

            let border = false;

            if (Number(item.actual_price) !== 0 && Number(item.actual_price) > Number(item.price_per)) {
                border = true;
            }

            template += `
            <tr id="${item.id}" class="even:bg-gray-100 border-gray-800 add-column" >
                <td class="py-3 px-4 w-10 relative text-left">
                    <span>${counter + 1}</span>
                    <div class="absolute inset-0 flex flex-col items-start justify-center hidden-action">
                        <img onclick="addNewRowAt('before','${counter}')" title="افزودن ردیف قبل از این ردیف" class="cursor-pointer w-6" src="./assets/img/top_arrow.svg" />
                        <img onclick="addNewRowAt('after','${counter + 1}')" title="افزودن ردیف بعد از این ردیف" class="cursor-pointer w-6" src="./assets/img/bottom_arrow.svg" />
                    </div>
                </td>
                <td class="relative py-3 px-4 w-3/5" >
                    <input name="itemName" type="text"class="tab-op w-2/4 p-2 border-dotted border-1 text-gray-500 w-42" onchange="editCell(this, 'partName', '${item.id}', '${item.partName}')" value="${item.partName}" />`;
            if (ItemsBrands[item['partNumber']]) {
                template += `<div class="absolute left-1/2 top-5 transform -translate-x-1/2 flex flex-wrap gap-1">`;
                for (const brand of Object.keys(ItemsBrands[item['partNumber']])) {
                    template += `<span style="font-size:12px" onclick="appendSufix('${item.id}','${brand}'); adjustPrice(this, '${item.id}',${ItemsBrands[item['partNumber']][brand]})" class="priceTag cursor-pointer text-md text-white bg-sky-600 rounded p-1" title="">${brand}</span>`;
                }
                template += `</div>`;
            }
            template += `<div class="absolute left-5 top-5 flex flex-wrap gap-1 w-42">
                        <span style="font-size:12px" onclick="appendSufix('${item.id}','اصلی')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">اصلی</span>
                        <span style="font-size:12px" onclick="appendSufix('${item.id}','چین')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">چین</span>
                        <span style="font-size:12px" onclick="appendSufix('${item.id}','کره')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">کره</span>
                        <span style="font-size:12px" onclick="appendSufix('${item.id}','متفرقه')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">متفرقه</span>
                        <span style="font-size:12px" onclick="appendSufix('${item.id}','تایوان')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">تایوان</span>
                        <span style="font-size:12px" onclick="appendSufix('${item.id}','شرکتی')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">شرکتی</span>
                        <span style="font-size:12px" onclick="appendSufix('${item.id}','ترک')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">ترک</span>`;
            if (customerInfo.car != '' && customerInfo.car != null) {
                template += `<span style="font-size:12px" onclick="appendCarSufix('${item.id}','${customerInfo.car}')" class="cursor-pointer text-md text-white bg-gray-600 rounded p-1" title="">${customerInfo.car}</span>`;
            }
            template += `</div>
                </td>
                <td class="text-center w-18 py-3 px-4">
                    <input name="quantity"  onchange="editCell(this, 'quantity', '${item.id}', '${item.quantity}')" type="number" style="direction:ltr !important;" class="tab-op tab-op-number  p-2 border border-1 w-16" value="${item.quantity}" />
                </td>
                <td class="text-center py-3 px-4 w-18" >
                    <input name="price" onchange="editCell(this, 'price_per', '${item.id}', '${item.price_per}')" type="text" style="direction:ltr !important; ${border ? 'border: 2px solid red !important': ''}" class="tab-op tab-op-number w-18 p-2 border" onkeyup="displayAsMoney(this);convertToEnglish(this)" value="${formatAsMoney(item.price_per)}" />
                </td>
                <td class="text-center py-3 px-4 ltr">${formatAsMoney(payPrice)}</td>
                <td class="text-center py-3 px-4 w-18 h-12 font-medium">
                    <img onclick="deleteItem(${item.id})" class="bill_icon" src="./assets/img/subtract.svg" alt="subtract icon">
                </td>
            </tr> `;
            counter++;
        }

        bill_body.innerHTML = template;
        factorInfo.totalPrice = (totalPrice);
        factorInfo.totalInWords = numberToPersianWords(totalPrice - factorInfo.discount);
        // Display the Bill Information
        document.getElementById('quantity').value = factorInfo.quantity;
        document.getElementById('discount').value = factorInfo.discount;
        document.getElementById('totalPrice').value = formatAsMoney(factorInfo.totalPrice);
        document.getElementById('total_in_word').innerHTML = factorInfo.totalInWords;
        document.getElementById('description').innerHTML = factorInfo.description;
    }

    // A function to display bill items and calculate the amount and goods count and display bill details afterword
    function updateBillDisplay() {
        let counter = 1;
        let totalPrice = 0;
        factorInfo.quantity = 0;

        for (const item of factorItems) {
            const payPrice = Number(item.quantity) * Number(item.price_per);
            totalPrice += payPrice;
            factorInfo.quantity += Number(item.quantity);
        }

        factorInfo.totalPrice = (totalPrice);
        factorInfo.totalInWords = numberToPersianWords(totalPrice);
        // Display the Bill Information
        // document.getElementById('billNO').value = factorInfo.billNO;
        document.getElementById('quantity').value = factorInfo.quantity;
        document.getElementById('quantity').value = factorInfo.quantity;
        document.getElementById('totalPrice').value = formatAsMoney(factorInfo.totalPrice);
        document.getElementById('total_in_word').innerHTML = factorInfo.totalInWords;
    }

    // Add new bill item manually using the icon on the browser or shift + ctrl key press
    function addNewBillItemManually() {
        factorItems.push({
            id: Math.floor(Math.random() * (9000000 - 1000000 + 1)) + 1000000,
            partName: "اسم قطعه",
            price_per: 0,
            quantity: 1,
            max: 'undefined',
            partNumber: 'NOTPART'
        });
        displayBill();
    }

    // This function adds a new bill item manually at a specific position
    function addNewRowAt(position, targetIndex) {
        // Insert the new object either before or after the target index
        const newItem = {
            id: Math.floor(Math.random() * (9000000 - 1000000 + 1)) + 1000000,
            partName: "اسم قطعه",
            price_per: 0,
            quantity: 1,
            max: 'undefined',
            partNumber: 'NOTPART'
        };

        // Ensure the targetIndex is within the valid range
        if (targetIndex >= 0 && targetIndex < factorItems.length) {
            console.log(targetIndex);
            if (position === 'before') {
                factorItems.splice(targetIndex, 0, newItem);
            } else if (position === 'after') {
                factorItems.splice(targetIndex, 0, newItem);
            } else {
                console.error("Invalid position. Use 'before' or 'after'.");
            }

            displayBill();
        } else if (targetIndex == factorItems.length && position === 'after') {
            // If 'after' is selected and the target index is at the end, add to the end
            factorItems.push(newItem);
            displayBill();
        } else {
            console.error("Invalid target index.");
        }
    }

    // Updating the bill inforation section (EX: setting the discount or tax)
    function updateFactorInfo(element) {
        const proprty = element.getAttribute("name");
        factorInfo[proprty] = element.value;
    }

    // updating the customer information by modifying the customer information table section 
    function updateCustomerInfo(element) {
        const proprty = element.getAttribute("name");
        customerInfo[proprty] = element.value;
        if (proprty == 'name') {
            customerInfo.displayName = element.value;
        }
        if (proprty == 'name' || proprty == 'family') {
            const name = customerInfo.displayName != null ? customerInfo.displayName : '';
            const family = customerInfo.family != null ? customerInfo.family : '';
            title = name + ' ' + family;
            document.getElementById("customer_factor").innerHTML = title;
        }
        displayBill();
    }

    // Edit the item property by clicking on it and giving a new value
    function editCell(cell, property, itemId, originalValue) {
        const newValue = cell.value;

        if (property == 'price_per') {
            for (let i = 0; i < factorItems.length; i++) {
                if (factorItems[i].id == itemId) {
                    const sanitized = newValue.replaceAll(',', '');
                    if (Number(factorItems[i]['actual_price']) > Number(sanitized)) {
                        const systemPrice = `\n قیمت سیستم: ${formatAsMoney(factorItems[i]['actual_price'])}`;
                        const confirmation = confirm('قیمت سیستم بیشتر از مقدار داده شده است آیا تایید میکنید ؟' + systemPrice);
                        if (!confirmation) {
                            cell.value = formatAsMoney(originalValue); // Reset to original value if not confirmed
                            return null;
                        } else {
                            break;
                        }
                    }
                }
            }
        }

        // Update the corresponding item in your data structure (factorItems)
        updateItemProperty(itemId, property, newValue, cell);

        if (property == 'partName') {
            loadBrands(cell, itemId, newValue);
        }

        if (property == 'quantity' || property == 'price_per') {
            const parentRow = cell.closest('tr');
            const secondToLastTd = parentRow.querySelector('td:nth-last-child(2)');

            const totalpriceParent = parentRow.querySelector('td:nth-last-child(3)');
            const totalpriceValue = Number(totalpriceParent.querySelector('input').value.replace(/\D/g, ""));

            const thirdToLastTd = parentRow.querySelector('td:nth-last-child(4)');
            const value = (thirdToLastTd.querySelector('input').value);
            // Find the second-to-last td element in the same row


            // Modify the innerHTML of the second-to-last td element
            if (secondToLastTd) {
                secondToLastTd.innerHTML = formatAsMoney(Number(totalpriceValue) * value); // Replace 'New Value' with the desired content
            }
        }
    }

    function loadBrands(cell, itemId, value) {
        const partNumber = filterPartNumber(value);

        if (partNumber.length > 6) {
            const params = new URLSearchParams();
            params.append('completeCode', value);
            axios.post(BRANDS_ENDPOINT, params).then(response => {
                const data = response.data;
                const key = Object.keys(data)[0];
                if (key) {
                    ItemsBrands[key] = data[key]['prices'];
                    let originalPrice = data[key]['original'];
                    if (originalPrice.includes("(LR)")) {
                        alert('این قطعه دارای شاخص (LR) می باشد.')
                    }
                    const specificItemsQuantity = {
                        "51712": 2,
                        "54813": 2,
                        "55513": 2,
                        "58411": 2,
                        "234102": 4,
                        "230412": 4,
                        "234103": 6,
                        "230413": 6,
                    };

                    for (let i = 0; i < factorItems.length; i++) {
                        if (factorItems[i].id == itemId) {

                            factorItems[i]['partNumber'] = key;
                            factorItems[i]['partName'] = data[key]['partName'];
                            factorItems[i]['price_per'] = data[key]['prices']['اصلی'] ?? 0;
                            factorItems[i]['actual_price'] = data[key]['prices']['اصلی'] ?? 0;

                            const ICN = key.substring(0, 5); // Extracts the first 5 characters
                            const ICN_BIG = key.substring(0, 6); // Extracts the first 6 characters
                            let quantity = 1;


                            // Check if ICN or ICN_BIG exist as keys in the specificItemsQuantity object
                            if (specificItemsQuantity.hasOwnProperty(ICN)) {
                                quantity = specificItemsQuantity[ICN];
                            } else if (specificItemsQuantity.hasOwnProperty(ICN_BIG)) {
                                quantity = specificItemsQuantity[ICN_BIG];
                            } else {
                                quantity = 1;
                            }
                            factorItems[i]['quantity'] = quantity;
                            break;
                        }
                    }
                    displayBill();
                }

            }).catch(error => {
                console.error(error);
            });
        }
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

    // Update the edited item property in the data source
    function updateItemProperty(itemId, property, newValue, cell) {
        newValue = newValue.replace(/,/g, '');
        for (let i = 0; i < factorItems.length; i++) {
            if (factorItems[i].id == itemId) {
                if (property !== 'quantity') {
                    factorItems[i][property] = newValue;
                    break;
                } else {
                    if (factorItems[i]['max'] === 'undefined') {
                        factorItems[i][property] = newValue;
                        break;
                    } else {
                        if (factorItems[i]['max'] >= newValue) {
                            factorItems[i][property] = newValue;
                            break;
                        } else {
                            displayModal("مقدار انتخاب شده بیشتر از مقداری موجودی در انبار بوده نمیتواند.");
                            break;
                        }
                    }
                }
            }
        }
        updateBillDisplay();
    }

    function appendSufix(itemId, suffix) {

        for (let i = 0; i < factorItems.length; i++) {
            if (factorItems[i].id == itemId) {
                const partName = factorItems[i].partName;
                let lastIndex = partName.lastIndexOf('-');
                let result = lastIndex !== -1 ? partName.substring(0, lastIndex).trim() : partName.trim();
                factorItems[i].partName = result.trim() + ' - ' + suffix;
            }
        }
        displayBill();
    }

    function adjustPrice(element, itemId, price) {
        const priceTages = document.querySelectorAll('.priceTag');
        element.classList.remove('bg-sky-600');
        element.classList.add('text-black');
        let itemFound = false;
        for (let i = 0; i < factorItems.length; i++) {
            if (factorItems[i].id == itemId) {
                factorItems[i].price_per = price;
                factorItems[i].actual_price = price;
                itemFound = true;
                break;
            }
        }
        if (!itemFound) {
            console.warn('Item not found:', itemId);
        }

        displayBill(); // Assuming this updates the UI with the new price
    }

    // This function append a related prefix to the customer name
    function appendPrefix(prefix) {
        const nameElement = document.getElementById('name');
        if (customerInfo.name) {
            nameElement.value = prefix + ' ' + customerInfo.name.trim();
            customerInfo.displayName = prefix + ' ' + customerInfo.name.trim();
        }
    }

    // Append the customer car brand to the items
    function appendCarSufix(itemId, suffix) {
        for (let i = 0; i < factorItems.length; i++) {
            if (factorItems[i].id == itemId) {

                const partName = factorItems[i].partName;
                if (partName.indexOf(suffix) == -1) {
                    factorItems[i].partName = partName + ' ' + suffix;
                }
            }
        }
        displayBill();
    }

    // deleiting the specific bill item
    function deleteItem(id) {
        for (let i = 0; i < factorItems.length; i++) {
            if (factorItems[i].id == id) {
                factorItems.splice(i, 1);
                break;
            }
        }
        displayBill();
    }

    function hasLR() {
        let keysWithLR = []; // Array to store keys with "(LR)"

        // Loop through the key-value pairs
        for (let [key, value] of Object.entries(originalPrices)) {
            if (typeof value === 'string' && value.includes("(LR)")) {
                keysWithLR.push(key); // Add key to the array
            }
        }

        // Return the array of keys if any (LR) items are found, or null otherwise
        return keysWithLR.length > 0 ? keysWithLR : null;
    }

    function generateBill(element) {
        // Disable the element to avoid multiple requests
        element.disabled = true;
        element.innerHTML = 'در حال انتظار';

        // Set the date using Moment.js with Persian (Farsi) locale
        factorInfo.date = moment().locale('fa').format('YYYY/MM/DD');

        // Check for items with (LR)
        const keysWithLR = hasLR();

        // If there are items with (LR), show the confirm dialog
        if (keysWithLR) {
            // Create a message showing the keys with (LR)
            const lrItems = `${keysWithLR.join(", ")}`;

            // Show confirm dialog with the keys and the message
            const proceed = confirm(`${lrItems}\n\nبعضی اقلام دارای شاخص (LR) هستند. آیا فاکتور را صادر میکنید؟`);

            // If the user cancels, enable the element and stop further execution
            if (!proceed) {
                element.disabled = false;
                element.innerHTML = 'صدور فاکتور';
                return; // Stop further actions by exiting the function
            }
        }

        // Validate phone, name, and address (if applicable)
        if (!checkIfReadyToUpdate('phone') || !checkIfReadyToUpdate('name') ||
            (!factorInfo['partner'] && !checkIfReadyToUpdate('address'))) {
            element.disabled = false;
            element.innerHTML = 'صدور فاکتور';
            return; // Stop further actions if validation fails
        }

        // Validate factor items (ensure there are items in the invoice)
        if (factorItems.length <= 0) {
            displayModal('فاکتور مشتری خالی بوده نمیتواند.');
            element.disabled = false;
            element.innerHTML = 'صدور فاکتور';
            return; // Stop further actions if there are no items
        }

        // Validate factor items' correctness
        if (factorItems.length > 0 && !checkIfFactorItemsValid()) {
            displayModal('لطفا موجودیت و صحت برند قطعات را بررسی نمایید.');
            element.disabled = false;
            element.innerHTML = 'صدور فاکتور';
            return; // Stop further actions if items are invalid
        }

        // Prepare parameters for the HTTP request
        var params = new URLSearchParams();
        params.append('GenerateCompleteFactor', 'GenerateCompleteFactor');
        params.append('customerInfo', JSON.stringify(customerInfo));
        params.append('factorInfo', JSON.stringify(factorInfo));
        params.append('factorItems', JSON.stringify(factorItems));

        // Send POST request to save invoice data
        axios.post("../../app/api/factor/CompleteFactorApi.php", params)
            .then(function(response) {
                const data = response.data;
                const factorNumber = data.factorNumber;
                params.append('factorNumber', factorNumber);

                if (data.status == 'success') {
                    const save_message = document.getElementById('save_message');
                    save_message.classList.remove('hidden');
                    setTimeout(() => {
                        save_message.classList.add('hidden');
                        if (factorInfo['id']) {
                            localStorage.setItem('displayName', customerInfo.displayName);
                            if (factorInfo['partner']) {
                                window.location.href = './partnerFactor.php?factorNumber=' + factorInfo['id'];
                            } else {
                                window.location.href = './yadakFactor.php?factorNumber=' + factorInfo['id'];
                            }
                        }
                    }, 1000);
                } else {
                    const save_error_message = document.getElementById('save_error_message');
                    save_error_message.classList.remove('hidden');

                    setTimeout(() => {
                        save_error_message.classList.add('hidden');
                        // Enable the element after unsuccessful request
                        element.disabled = false;
                        element.innerHTML = 'صدور فاکتور';
                    }, 3000);
                }

            })
            .catch(function(error) {
                // Handle errors
                console.error("Error saving invoice data:", error);
                displayModal("An error occurred while saving the invoice data. Please try again later.");
                // Enable the element after error
                element.disabled = false;
                element.innerHTML = 'صدور فاکتور';
            });
    }

    function generateUniqueId() {
        const array = new Uint8Array(16); // 16 bytes = 128 bits
        window.crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }

    function insuranceBillDisplay() {
        localStorage.setItem('displayName', customerInfo.displayName);
        window.location.href = "./individualInsurance.php?factorNumber=" + factorInfo['id'];
    }

    // Update the incomplete 
    function updateIncompleteFactor() {
        if (factorInfo.date == 'null')
            factorInfo.date = moment().locale('fa').format('YYYY/MM/DD');
        // Check for items with (LR)
        const keysWithLR = hasLR();

        // If there are items with (LR), show the confirm dialog
        if (keysWithLR) {
            // Create a message showing the keys with (LR)
            const lrItems = `${keysWithLR.join(", ")}`;

            // Show confirm dialog with the keys and the message
            const proceed = confirm(`${lrItems}\n\nبعضی اقلام دارای شاخص (LR) هستند. آیا فاکتور را صادر میکنید؟`);

            // If the user cancels, enable the element and stop further execution
            if (!proceed) {
                element.disabled = false;
                element.innerHTML = 'صدور فاکتور';
                return; // Stop further actions by exiting the function
            }
        }

        if (!checkIfReadyToUpdate('phone')) {
            return false
        }

        if (!checkIfReadyToUpdate('name')) {
            return false
        }

        if (!factorInfo['partner']) {

            if (!checkIfReadyToUpdate('address')) {
                return false
            }
        }

        if (factorItems.length <= 0) {
            displayModal('فاکتور مشتری خالی بوده نمیتواند.')
            return false;
        }

        if (!checkIfFactorItemsValid()) {
            displayModal('لطفا موجودیت و صحت برند قطعات را بررسی نمایید.');
            return false;
        }

        var params = new URLSearchParams();
        params.append('updateIncompleteFactor', 'updateIncompleteFactor');
        params.append('customerInfo', JSON.stringify(customerInfo));
        params.append('factorInfo', JSON.stringify(factorInfo));
        params.append('factorItems', JSON.stringify(factorItems));

        axios.post("../../app/api/factor/IncompleteFactorApi.php", params)
            .then(function(response) {
                const data = response.data;
                if (data) {
                    const save_message = document.getElementById('save_message');
                    save_message.classList.remove('hidden');

                    setTimeout(() => {
                        save_message.classList.add('hidden');
                    }, 3000);
                } else {
                    const save_error_message = document.getElementById('save_error_message');
                    save_error_message.classList.remove('hidden');

                    setTimeout(() => {
                        save_error_message.classList.add('hidden');
                    }, 3000);
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    // A function to check if the necessary information is provided to update the incomplete factor
    function checkIfReadyToUpdate(property) {
        if (customerInfo[property] === '' || customerInfo[property] === null) {
            document.getElementById(property).classList.add('border-2');
            document.getElementById(property).classList.add('border-red-600');
            document.getElementById(property + "_error").classList.remove('hidden');
            setTimeout(() => {
                document.getElementById(property + "_error").classList.add('hidden');
                document.getElementById(property).classList.remove('border-2');
                document.getElementById(property).classList.remove('border-red-600');
            }, 4000);
            return false;
        }
        return true;
    }

    function checkIfFactorItemsValid() {
        for (const item of factorItems) {
            let brandSection = item.partName.split('-');
            brandSection = brandSection.filter((item) => item.trim() != '');

            const ItemBrand = brandSection[brandSection.length - 1].trim();
            AllBrands.push('اصلی', 'چین', 'کره', 'متفرقه', 'تایوان', 'شرکتی');

            if (brandSection.length < 2) {
                return false;
            }
            return true;
        }
    }

    // This function checks wheter the phone numbers is a valid number and correct the format
    function sanitizeCustomerPhone(inputElement) {
        // Get the input value and remove white spaces
        const phone = inputElement.value.replace(/\s/g, '')
        let cleanPhoneNumber = convertToEnglishNumbers(phone);

        // Remove any character except digits and '+'
        cleanPhoneNumber = cleanPhoneNumber.replace(/[^\d+]/g, '');

        // Check if cleanPhoneNumber is defined and not null
        if (cleanPhoneNumber && cleanPhoneNumber.indexOf('+98') === 0) {
            // If it does, replace '+98' with '0'
            cleanPhoneNumber = '0' + cleanPhoneNumber.slice(3);
        }

        // Update the input value
        inputElement.value = cleanPhoneNumber;
    }

    // A function to check if the phone number is already assigned to a customer
    function ifCustomerExist(element) {

        if (element.value.length > 0) {
            var params = new URLSearchParams();
            params.append('isPhoneExist', 'isPhoneExist');
            params.append('phone', element.value);

            axios.post("../../app/api/factor/FactorCommonApi.php", params)
                .then(function(response) {
                    const customer = response.data;
                    if (customer !== 0) {
                        document.getElementById('name').value = customer.name;
                        document.getElementById('family').value = customer.family;
                        document.getElementById('address').value = customer.address;
                        document.getElementById('car').value = customer.car;
                        customerInfo['id'] = customer.id;
                        customerInfo['name'] = customer.name;
                        customerInfo['displayName'] = customer.name;
                        customerInfo['family'] = customer.family;
                        customerInfo['address'] = customer.address;
                        customerInfo['car'] = customer.car;
                        customerInfo.mode = "update";
                    } else {
                        document.getElementById('name').value = null;
                        customerInfo['displayName'] = customer.name;
                        document.getElementById('family').value = null;
                        document.getElementById('address').value = null;
                        document.getElementById('car').value = null;
                        customerInfo['id'] = null;
                        customerInfo['name'] = null;
                        customerInfo['family'] = null;
                        customerInfo['address'] = null;
                        customerInfo['car'] = null;
                        customerInfo.mode = "create";
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        }


    }

    bootstrap(); // Display the form data after retrieving every initial data

    document.addEventListener("keydown", function(event) {
        // Check if the Ctrl key is pressed and the key is 'S'
        if (event.altKey && (event.key === "s" || event.key === "س")) {
            // Prevent the default browser behavior for Ctrl + S (e.g., saving the page)
            event.preventDefault();

            // Call the saveIncompleteForm function
            updateIncompleteFactor();

            // Optionally, use return false to further prevent default behavior
            return false;
        }
    });
</script>
<script src="./assets/js/billSearchPart.js?=<?= rand() ?>"></script>
<script src="./assets/js/displayBill.js?v=<?= rand() ?>"></script>
<script src="./assets/js/modal.js?v=<?= rand() ?>"></script>
<script src="./assets/js/billShortcuts.js?v=<?= rand() ?>"></script>
<?php
require_once './components/footer.php';
