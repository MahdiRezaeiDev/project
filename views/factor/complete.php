<?php
$pageTitle = "ویرایش فاکتور";
$iconUrl = 'factor.svg';
require_once './components/header.php';
require_once '../../utilities/callcenter/TemporaryHelper.php';
require_once '../../app/controller/factor/LoadFactorItemBrands.php';
require_once '../../utilities/callcenter/DollarRateHelper.php';
require_once '../../app/controller/factor/CompleteFactorController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php'; ?>

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
<div id="wholePage">
    <?php require_once './components/factorSearch.php'; ?>
    <!-- Bill editing and information section -->
    <section class="mt-3 mb-12">
        <!-- bill and customer information table -->
        <div class="bg-white rounded-lg shadow-md w-full">
            <div class="bg-gray-800 text-white text-center flex items-center justify-between p-1">
                <p class="p-3">
                    مشخصات خریدار
                </p>
                <div class="flex items-center gap-2">
                    <a href="../callcenter/main.php?phone=<?= $customerInfo['phone'] ?>" class="bg-green-600 px-3 py-2 rounded text-sm">مشاهده کارتابل</a>
                    <div class="<?= !$factorInfo['billNO'] ? 'hidden' : '' ?> px-3 py-2 flex gap-3 bg-rose-500 rounded text-sm">
                        <p class="text-white text-md">شماره فاکتور:</p>
                        <input readonly onkeyup="updateFactorInfo(this)" class="text-white bg-transparent border-none outline-none w-12" placeholder="شماره فاکتور را وارد نمایید" type="text" name="billNO" id="billNO">
                    </div>
                </div>
            </div>
            <div class="min-w-full border border-gray-800 text-gray-800 p-3 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 px-3 gap-3">
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">تلفون</td>
                    <td class="py-2 px-4">
                        <input autocomplete="off" onblur="ifCustomerExist(this)" onkeyup="sanitizeCustomerPhone(this);updateCustomerInfo(this)" class="w-full p-2 border text-gray-800 outline-none focus::border-gray-500 ltr" placeholder="093000000000" type="text" name="phone" id="phone">
                        <p id="phone_error" class="hidden text-xs text-red-500 py-1">لطفا شماره تماس مشتری را وارد نمایید.</p>
                    </td>
                </div>
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">نام</td>
                    <td class="py-2 px-4">
                        <input class="w-full p-2 border-2" type="hidden" name="id" id="id">
                        <input class="w-full p-2 border-2" type="hidden" name="type" id="mode" value='create'>
                        <input autocomplete="off" onkeyup="updateCustomerInfo(this)" class="w-full p-2 border text-gray-800 outline-none focus::border-gray-500" placeholder="نام مشتری را وارد کنید..." type="text" name="name" id="name">
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
                        <input autocomplete="off" onkeyup="updateCustomerInfo(this)" class="w-full p-2 border-2 text-gray-800 outline-none focus::border-gray-500" placeholder="نام خانوادگی مشتری را وارد کنید..." type="text" name="family" id="family">
                    </td>
                </div>
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">آدرس</td>
                    <td class="py-2 px-4">
                        <textarea autocomplete="off" onkeyup="updateCustomerInfo(this)" name="address" id="address" cols="30" rows="1" class="border-2 p-2 w-full text-gray-800 outline-none focus::border-gray-500" placeholder="آدرس مشتری"></textarea>
                    </td>
                </div>
                <div>
                    <td class="py-2 px-3 text-white bg-gray-800 text-md">ماشین</td>
                    <td class="py-2 px-4">
                        <input autocomplete="off" data-old='' onchange="handleInputChange(event)" onkeyup="updateCustomerInfo(this)" class="w-full p-2 border-2 text-gray-800 outline-none focus::border-gray-500" placeholder="نوعیت ماشین مشتری را مشخص کنید" type="text" name="car" id="car">
                    </td>
                </div>
                </tbody>
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
        <div class="bg-white rounded-lg shadow-md p-2 w-full col-span-3">
            <div class=" mx-auto">
                <table class="min-w-full border border-gray-800 text-gray-800">
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
                <div class="flex justify-between py-4 gap-5">
                    <textarea onkeyup="updateFactorInfo(this)" class="border border-gray-800 w-1/2 p-5" name="description" id="description" placeholder="توضیحات فاکتور را وارد نمایید ..." cols="20" rows="4"></textarea>
                    <div class="p-5 backdrop-blur-xl bg-black/20 rounded-md">
                        <ul class="list-disc list-inside">
                            <li class="text-sm">برای ایجاد آیتم جدید در فاکتور از کلیدهای ترکیبی <code class="text-white bg-black px-1 rounded-md text-xs">Ctrl + Shift</code> استفاده نمایید. </li>
                            <li class="text-sm">با استفاده از کلید <span class="text-white bg-black px-1 rounded-md text-xs">F9</span> میتوانید پیش فاکتور مشتری را مشاهده کنید.</li>
                            <li class="text-sm">برای جابجای راحت میان ستون ها از کلید <span class="text-white bg-black px-1 rounded-md text-xs">Tab</span> میتوانید استفاده کنید.</li>
                            <li class="text-sm">برای جابجای میان سطرها از کلید <span class="text-white bg-black px-1 rounded-md text-xs">Enter</span> میتوانید استفاده کنید.</li>
                            <li class="text-sm">برای ذخیره فاکتور میتوانید از کلیدهای ترکیبی <span class="text-white bg-black px-1 rounded-md text-xs">Alt + S</span> استفاده نمایید.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once './components/factorDetails.php' ?>
    </section>

    <!-- Bill Operation Section -->
    <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
        <div class="fixed flex justify-between items-center min-w-full h-12 bottom-0 bg-gray-800 px-3">
            <ul class="flex gap-3">
                <?php if ($date_difference_days <= 1 || $_SESSION['id'] == 1 || $_SESSION['id'] == 5 || $_SESSION['id'] == 6) : ?>
                    <li>
                        <button onclick="updateCompleteFactor(this)" id="incomplete_save_button" class="bg-white rounded text-gray-800 px-3 py-1 cursor-pointer disabled:cursor-not-allowed disabled:bg-gray-200">
                            ویرایش
                        </button>
                    </li>
                <?php endif; ?>
                <li>
                    <button onclick="printFactor()" id="complete_save_button" class="diable bg-white rounded text-gray-800 px-3 py-1 cursor-pointer">
                        پرینت
                    </button>
                </li>
            </ul>
            <p id="save_message" class="hidden bg-white text-green-400 px-3 py-1">ویرایش موفقانه صورت گرفت</p>
            <p id="save_error_message" class="hidden bg-red-500 text-white px-3 py-1">خطا در ویرایش اطلاعات</p>
        </div>
    <?php endif; ?>
</div>
<?php
require_once './components/modal.php';
require_once './components/factor.php'; ?>
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
        document.getElementById('billNO').value = factorInfo.billNO;
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
        document.getElementById('billNO').value = factorInfo.billNO;
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
            partNumber: 'NOTPART',
            original_price: 'موجود نیست'
        });
        displayBill();
    }

    // This function adds a new bill item manually at a specific position
    function addNewRowAt(position, targetIndex) {
        // Insert the new object either before or after the target index
        const newItem = {
            id: Math.floor(Math.random() * (9000000 - 1000000 + 1)) + 1000000,
            partName: "اسم قطعه را وارد کنید.",
            price_per: 0,
            quantity: 1,
            max: 'undefined',
            partNumber: 'NOTPART',
            original_price: 'موجود نیست'
        };
        // Ensure the targetIndex is within the valid range
        if (targetIndex >= 0 && targetIndex < factorItems.length) {
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

                    for (let i = 0; i < factorItems.length; i++) {
                        if (factorItems[i].id == itemId) {

                            factorItems[i]['partNumber'] = key;
                            factorItems[i]['original_price'] = originalPrice;
                            factorItems[i]['partName'] = data[key]['partName'];
                            factorItems[i]['price_per'] = data[key]['prices']['اصلی'] ?? 0;
                            factorItems[i]['actual_price'] = data[key]['prices']['اصلی'] ?? 0;
                            factorItems[i]['quantity'] = getGoodItemAmount(key);
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

    function getGoodItemAmount(partNumber) {
        let quantity = 1;

        // Exact part numbers with fixed quantities (exceptions)
        const exceptionCodes = {
            '2102025150': 1
        };

        // Exact complete codes with fixed quantities
        const completeCodes = {
            '1884111051': 4,
            '2741023700': 6
        };

        // Specific substrings-based quantities
        const specificItemsQuantity = {
            '51712': 2,
            '54813': 2,
            '55513': 2,
            '58411': 2,
            '230602': 4,
            '234102': 4,
            '210203': 4,
            '230412': 4,
            '210202': 5,
            '273012': 4,
            '273013': 6,
            '230603': 6,
            '234103': 6,
            '230413': 6,
            '273002': 4,
            '2730137': 1,
            '2730103': 4,
            '230603F': 8,
            '210203F': 4,
            '18858100': 4
        };

        // Regular expression-based patterns and their corresponding quantities
        const patternQuantities = [{
                pattern: /^23060[\w]{2}9[\w]*$/,
                quantity: 1
            }, // Matches "23060-any-2-alphanumeric-characters-9-any-more-characters"
            {
                pattern: /^21020[\w]{2}9[\w]*$/,
                quantity: 1
            } // Matches "21020-any-2-alphanumeric-characters-9-any-more-characters"
        ];

        // STEP 1: Check for exact matches in exceptions
        if (exceptionCodes.hasOwnProperty(partNumber)) {
            return exceptionCodes[partNumber];
        }

        // STEP 2: Check for exact matches in complete codes
        if (completeCodes.hasOwnProperty(partNumber)) {
            return completeCodes[partNumber];
        }

        // STEP 3: Check for pattern-based matches using regular expressions (longer patterns first)
        for (const {
                pattern,
                quantity
            }
            of patternQuantities) {
            if (pattern.test(partNumber)) {
                return quantity; // Matches specific pattern (quantity 1)
            }
        }

        // STEP 4: Check for specific substring-based matches
        const sortedKeys = Object.keys(specificItemsQuantity).sort((a, b) => b.length - a.length); // Sort by length (desc)
        for (const key of sortedKeys) {
            if (partNumber.startsWith(key)) {
                return specificItemsQuantity[key];
            }
        }

        // STEP 5: Default quantity if no match is found
        return quantity;
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

    // Adding item snameElement
    function appendSufix(itemId, suffix) {
        for (let i = 0; i < factorItems.length; i++) {
            if (factorItems[i].id == itemId) {

                const partName = factorItems[i].partName;
                let lastIndex = partName.lastIndexOf('-');

                let result = lastIndex !== -1 ? partName.substring(0, lastIndex).trim() : partName.trim();
                factorItems[i].partName = result + ' - ' + suffix;
            }
        }
        displayBill();
    }

    function adjustPrice(element, itemId, price) {
        // Manipulate the specific element
        if (element) {
            element.classList.remove('bg-sky-600');
            element.classList.add('text-black');
        } else {
            console.warn('Element is not defined');
        }

        let itemFound = false;
        for (let i = 0; i < factorItems.length; i++) {
            if (factorItems[i].id == itemId) {
                factorItems[i].price_per = price;
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

    function printFactor() {
        // Set the date using Moment.js with Persian (Farsi) locale
        factorInfo.date = moment().locale('fa').format('YYYY/MM/DD');

        if (!checkIfReadyToUpdate('phone')) {
            return false
        }

        if (!checkIfReadyToUpdate('name')) {
            return false
        }
        if (factorItems.length <= 0) {

            displayModal('فاکتور مشتری خالی بوده نمیتواند.')
            return false;
        }

        localStorage.setItem('displayName', customerInfo.displayName);
        if (factorInfo['partner']) {
            window.location.href = './partnerFactor.php?factorNumber=' + factorInfo['id'];
            return false;
        }
        window.location.href = './yadakFactor.php?factorNumber=' + factorInfo['id'];
    }

    // Update the incomplete 
    function updateCompleteFactor(element) {
        element.disabled = true;
        if (factorInfo.date == 'null')
            factorInfo.date = moment().locale('fa').format('YYYY/MM/DD');

        if (!checkIfReadyToUpdate('phone')) {
            element.disabled = false;
            return false
        }

        if (!checkIfReadyToUpdate('name')) {
            element.disabled = false;
            return false
        }
        if (factorItems.length <= 0) {
            element.disabled = false;
            displayModal('فاکتور مشتری خالی بوده نمیتواند.')
            return false;
        }

        // Validate factor items' correctness
        // if (factorItems.length > 0 && !checkIfFactorItemsValid()) {
        //     displayModal('لطفا موجودیت و صحت برند قطعات را بررسی نمایید.');
        //     element.disabled = false;
        //     return false;
        // }


        var params = new URLSearchParams();
        params.append('updateCompleteFactor', 'updateCompleteFactor');
        params.append('customerInfo', JSON.stringify(customerInfo));
        params.append('factorInfo', JSON.stringify(factorInfo));
        params.append('factorItems', JSON.stringify(factorItems));

        axios.post("../../app/api/factor/CompleteFactorApi.php", params)
            .then(function(response) {
                const data = response.data;
                if (data) {
                    const save_message = document.getElementById('save_message');
                    save_message.classList.remove('hidden');

                    setTimeout(() => {
                        save_message.classList.add('hidden');
                        element.disabled = false;
                    }, 3000);
                } else {
                    element.disabled = false;
                    const save_error_message = document.getElementById('save_error_message');
                    save_error_message.classList.remove('hidden');

                    setTimeout(() => {
                        save_error_message.classList.add('hidden');
                    }, 3000);
                }
            })
            .catch(function(error) {
                element.disabled = false;
                console.log(error);
            });
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
        }
        return true;
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
            updateCompleteFactor();

            // Optionally, use return false to further prevent default behavior
            return false;
        }
    });
</script>
<script src="./assets/js/billSearchPart.js"></script>
<script src="./assets/js/displayBill.js"></script>
<script src="./assets/js/modal.js"></script>
<script src="./assets/js/billShortcuts.js"></script>
<?php
require_once './components/footer.php';
