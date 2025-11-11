<?php
$pageTitle = "خروج کالا";
$iconUrl = 'purchase.svg';
require_once './components/header.php';
require_once '../../app/controller/inventory/SellController.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php';
?>
<!-- bill container and information section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 px-4 mb-16">
    <div class="min-h-screen bg-white shadow rounded-md overflow-hidden">
        <div class="bg-gray-800 p-4 h-20">
            <input style="direction: ltr !important;" onkeyup="searchGoods(this.value)" class="bg-transparent text-white p-3 font-semibold text-center border-2 border-white focus:outline-none w-full rounded-md text-sm placeholder:text-gray-500" type="search" name="search" id="searchGoods" placeholder="جستجو به اساس کد فنی">
        </div>
        <div class="p-4" id="resultBox">
            <!-- matched goods with the pattern will be presented here -->
        </div>
        <div id="error_box" class="fixed rounded text-sm flex flex-wrap flex-col gap-2" style="bottom: 60px; left:50%; transform: translateX(-50%); z-index: 200000; max-height: 150px;"></div>
        <div id="similar_box" class="fixed rounded text-sm flex flex-wrap flex-col gap-2 bg-gray-500/50 p-2" style="bottom: 60px; left:30px; z-index: 200000; max-height: 150px;"></div>
    </div>
    <div class="min-h-screen bg-white shadow rounded-md overflow-hidden">
        <div class="bg-gray-800 p-4 h-20 flex items-center justify-center">
            <h1 class="text-white font-semibold text-center">اطلاعات فاکتور</h1>
        </div>
        <div class="p-4">
            <table class="w-full">
                <tbody>
                    <tr>
                        <td class="py-2" class="w-32">
                            <p class="text-sm font-semibold">شماره فاکتور</p>
                        </td>
                        <td class="py-2">
                            <input class="border-2 p-2 w-full" min='1' type="number" name="invoice_number" id="invoice_number" onchange="getFactorCustomer(this.value),setFactorInfo('number',this.value)">
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2">
                            <p class="text-sm font-semibold">
                                <i class="text-rose-600">*</i>
                                خریدار
                            </p>
                        </td>
                        <td class="py-2">
                            <input class="border-2 p-2 w-full" type="text" onchange="setFactorInfo('client',this.value)" name="customer" id="customer">
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2">
                            <p class="text-sm font-semibold">
                                <i class="text-rose-600">*</i>
                                تحویل گیرنده
                            </p>
                        </td>
                        <td class="py-2">
                            <select class="border-2 p-2 w-full" name="receiver" id="receiver" onchange="setFactorInfo('receiver',this.value)">
                                <?php
                                foreach ($receivers as $receiver): ?>
                                    <option <?= $receiver['id'] == 6 ? 'selected' : ''  ?> value="<?= $receiver['id'] ?>"><?= $receiver['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2">
                            <p class="text-sm font-semibold">
                                <i class="text-rose-600">*</i>
                                زمان فاکتور
                            </p>
                        </td>
                        <td class="py-2">
                            <input class="border-2 p-2 w-full" onchange="setFactorInfo('date',this.value)" value="<?php echo (jdate("Y/m/d", time(), "", "Asia/Tehran", "en")) ?>" type="text" name="invoice_time" id="invoice_time">
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2">
                            <p class="text-sm font-semibold">
                                جمع کننده
                            </p>
                        </td>
                        <td class="py-2">
                            <input class="border-2 p-2 w-full" onchange="setFactorInfo('collector',this.value)" onkeyup="convertToPersian(this)" type="text" name="collector" id="collector">
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2" style="vertical-align:super;">
                            <p class="text-sm font-semibold">
                                توضیحات
                            </p>
                        </td>
                        <td class="py-2">
                            <textarea class="border-2 p-2 w-full" onchange="setFactorInfo('description',this.value)" name="description" id="description"></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="min-h-screen bg-white shadow rounded-md overflow-hidden">
        <div class="bg-gray-800 p-4 h-20 flex items-center justify-center">
            <h1 class="text-white font-semibold text-center">پیش نمایش فاکتور</h1>
        </div>
        <div id="previewFactor" class="p-3">
            <!-- factor items will be appended here -->
        </div>
        <div class="bg-gray-800 p-3 text-white">
            مجموع اقلام:
            <span id="totalGoods">0</span>
        </div>
    </div>
</div>

<!-- Bottom action bar for saving sells factor and operation message -->
<div class="bg-gray-800 fixed right-0 left-0 bottom-0 px-4 py-3 flex justify-between items-center">
    <button onclick="saveSells()" id="save_sell_factor" class="px-5 py-1 rounded bg-rose-500 text-white">ذخیره</button>
    <p id="operation_message" class="px-3 py-2 bg-green-600 text-white rounded hidden">عملیات موفقانه صورت گرفت.</p>
</div>

<!-- Page logical scripts -->
<script>
    const SELL_API = "../../app/api/inventory/SellApi.php";
    const RelatedGoodsAPI = "../../app/api/utilities/RelatedGoodsAPI.php";
    const FACTOR_API = "../../app/api/factor/CompleteFactorApi.php";
    const ERROR_BOX = document.getElementById('error_box');

    let billItems = {};
    let billItemOrder = [];

    let factor_info = {
        number: null,
        date: null,
        client: null,
        receiver: 6,
        collector: null,
        description: null,
        user: <?= $_SESSION['id'] ?>,
        quantity: 0
    };

    function getFactorCustomer(factorNo) {
        var params = new URLSearchParams();
        params.append('getClientName', 'getClientName');
        params.append('factorNo', factorNo);
        document.getElementById('similar_box').innerHTML = '';

        // First axios request to get client name
        axios.post(SELL_API, params)
            .then(function(response) {
                if (response.data) {
                    const customerName = response.data.trim();
                    setFactorInfo('client', customerName);
                    document.getElementById('customer').value = customerName;
                } else {
                    setFactorInfo('client', null);
                    document.getElementById('customer').value = null;
                }
            })
            .catch(function(error) {
                console.log(error);
            });

        // Second axios request to get factor items
        params.append('getFactorItems', 'getFactorItems');

        const previewContainer = document.getElementById('previewFactor');
        previewContainer.innerHTML = `<p class="p-2 text-sky-700 text-center text-sm font-semibold shadow">
                                            لطفا صبور باشید ...
                                            <img class="w-8 h-8 mx-auto my-2" src="../../public/img/loading.png" />
                                        </p>`;

        axios.post(FACTOR_API, params)
            .then(async function(response) {
                if (response.data) {

                    ERROR_BOX.innerHTML = '';
                    const factorItems = response.data;
                    billItems = {};
                    trackingQuantity = {};

                    for (const item of factorItems) {
                        try {
                            const FACTOR_ITEM = item.partName.split('-');

                            let GOOD_NAME_BRAND = FACTOR_ITEM[1].trim();
                            const GOOD_NAME_PART = FACTOR_ITEM[0].split(' ')[0].trim();

                            const ALL_ALLOWED_BRANDS = getFinalBrands(GOOD_NAME_BRAND);
                            const goods = await getSimilarCodes(GOOD_NAME_PART, ALL_ALLOWED_BRANDS, true);

                            let INVENTORY_GOODS = goods ? goods['goods'] : [];
                            const INVENTORY_CODES = goods ? goods['codes'] : [];

                            let billItemQuantity = item.quantity;
                            let counter = 1;
                            const totalQuantity = getTotalQuantity(INVENTORY_GOODS, ALL_ALLOWED_BRANDS);

                            if (INVENTORY_GOODS.length == 0) {
                                ERROR_BOX.innerHTML += `<p class="p-2 text-red-500 text-xs font-semibold shadow">
                                کالای 
                                <span class="text-blue-600 underline cursor-pointer" onclick= "getSimilarCodes('${GOOD_NAME_PART}','${ALL_ALLOWED_BRANDS}')">${GOOD_NAME_PART}</span>
                                 در انبار موجود نیست.
                                </p>`;
                            }

                            const excluded = new Set([
                                'کاربر دستوری',
                                'کاربر دستوری معیوب',
                                'کاربر دستوری مفقود'
                            ]);

                            INVENTORY_GOODS = INVENTORY_GOODS.filter(good => !excluded.has(good.seller_name));


                            if (!INVENTORY_CODES.includes(GOOD_NAME_PART) && INVENTORY_GOODS.length != 0) {
                                ERROR_BOX.innerHTML += `<p class="p-2 text-green-500 text-xs font-semibold shadow">
                                کد مشابه 
                                <span class="text-blue-400 underline cursor-pointer" onclick= "getSimilarCodes('${GOOD_NAME_PART}','${ALL_ALLOWED_BRANDS}')">${GOOD_NAME_PART}</span>
                                 در فاکتور استفاده گردید.
                                </p>`;
                            }

                            let index = 0; // Counter to track the current index
                            for (const good of INVENTORY_GOODS) {
                                if (billItemQuantity == 0) {
                                    break;
                                }

                                if (ALL_ALLOWED_BRANDS.includes(good.brandName)) {
                                    if (totalQuantity >= billItemQuantity && billItemQuantity > 0) {
                                        sellQuantity = billItemQuantity;
                                        if (trackingQuantity.hasOwnProperty(good.quantityId)) {
                                            // Skip goods with no remaining quantity
                                            if (trackingQuantity[good.quantityId] <= 0) {
                                                continue;
                                            }

                                            // Update the good's remaining quantity from the tracker
                                            good.remaining_qty = trackingQuantity[good.quantityId];
                                        } else {
                                            // Initialize tracker with the good's original remaining quantity
                                            trackingQuantity[good.quantityId] = good.remaining_qty;
                                        }

                                        if (billItemQuantity >= Number(good.remaining_qty)) {
                                            sellQuantity = Number(good.remaining_qty);
                                            billItemQuantity -= Number(good.remaining_qty);
                                            trackingQuantity[good.quantityId] = 0;
                                            addToBillItems(good, sellQuantity);
                                        } else {
                                            sellQuantity = billItemQuantity;
                                            trackingQuantity[good.quantityId] -= sellQuantity;
                                            addToBillItems(good, sellQuantity);
                                            break;
                                        }

                                    } else {
                                        ERROR_BOX.innerHTML += `<p class="p-2 text-red-500 text-xs font-semibold shadow">
                                            برای کالای 
                                            <span class="text-blue-600 underline cursor-pointer" onclick= "getSimilarCodes('${GOOD_NAME_PART}','${ALL_ALLOWED_BRANDS}')">${GOOD_NAME_PART}</span>
                                            در انبار مقدار کافی موجود نیست.
                                            مقدار موجود: ${totalQuantity}
                                            </p>`;
                                    }
                                } else {
                                    if (index === INVENTORY_GOODS.length - 1) {
                                        if (GOOD_NAME_BRAND == 'اصلی') {
                                            GOOD_NAME_BRAND = 'GEN یا MOB';
                                        }
                                        ERROR_BOX.innerHTML += `<p class="p-2 text-red-500 text-xs font-semibold shadow">
                                            برند ${GOOD_NAME_BRAND} برای کالای 
                                            <span class="text-blue-600 underline cursor-pointer" onclick= "getSimilarCodes('${GOOD_NAME_PART}','${ALL_ALLOWED_BRANDS}')">${GOOD_NAME_PART}</span>
                                            در انبار موجود نیست.
                                            </p>`;
                                    }
                                }
                                counter++;
                                index++;
                            }

                        } catch (error) {
                            console.log(error);
                        }
                    }
                    previewFactor(); // Call previewFactor after processing all items
                } else {
                    previewContainer.innerHTML = `<p class="p-2 text-sky-700 text-center text-sm font-semibold shadow">
                    برای افزودن اقلام به فاکتور، لطفا شماره فاکتور درست را وارد کنید یا از جستجو استفاده کنید.
                    </p>`;
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function getFinalBrands(GOOD_NAME_BRAND) {
        let ALLOWED_BRANDS = [];

        ALLOWED_BRANDS.push(GOOD_NAME_BRAND);

        if (GOOD_NAME_BRAND == 'اصلی' || GOOD_NAME_BRAND == 'GEN' || GOOD_NAME_BRAND == 'MOB') {
            ALLOWED_BRANDS.push('GEN');
            ALLOWED_BRANDS.push('MOB');
        }

        if (GOOD_NAME_BRAND == 'شرکتی') {
            ALLOWED_BRANDS.push('IRAN');
        }

        if (GOOD_NAME_BRAND == 'متفرقه' || GOOD_NAME_BRAND == 'چین') {
            ALLOWED_BRANDS.push('CHINA');
        }

        if (GOOD_NAME_BRAND == 'کره' || GOOD_NAME_BRAND == 'کره ای') {
            ALLOWED_BRANDS.push('KOREA');
        }

        ALLOWED_BRANDS = [...ALLOWED_BRANDS];

        const ALL_ALLOWED_BRANDS = [...ALLOWED_BRANDS, ...getRelatedBrandsByKeywords(ALLOWED_BRANDS)];

        return ALL_ALLOWED_BRANDS;
    }

    function getTotalQuantity(goods = [], brandsName = []) {
        let totalQuantity = 0;

        for (const good of goods) {
            if (brandsName.includes(good.brandName)) {
                totalQuantity += Number(good.remaining_qty);
            }
        }
        return totalQuantity;
    }

    function addToBillItems(good, quantity) {

        if (billItems.hasOwnProperty(good.quantityId)) {
            // If the item already exists, sum the quantities
            billItems[good.quantityId].quantity = Number(billItems[good.quantityId].quantity) + Number(quantity);
        } else {
            // If the item does not exist, add it to billItems
            billItems[good.quantityId] = {
                quantityId: good.quantityId,
                id: billItemOrder.length + 1, // Generate a new id based on the number of items
                goodId: good.goodId,
                partNumber: good.partNumber,
                stockId: good.stockId,
                purchase_Description: good.purchase_Description,
                stockName: good.stockName,
                brandName: good.brandName,
                sellerName: good.seller_name,
                quantity: quantity
            };

            if (!billItemOrder.includes(good.quantityId)) {
                billItemOrder.push(good.quantityId);
            }
        }
    }

    async function searchGoods(pattern) {
        document.getElementById('searchGoods').value = pattern;
        pattern = pattern.trim().replace(/\s+/g, '');
        if (pattern.length < 7) {
            return;
        }

        const resultBox = document.getElementById('resultBox');
        resultBox.innerHTML = '<img class="w-8 h-8 mx-auto" src="../../public/img/loading.png" />';

        try {
            const goods = await getGoods(pattern); // Await the result from getGoods
            displayGoods(goods); // Pass the goods to displayGoods function
        } catch (error) {
            console.error('Error fetching goods:', error);
        }
    }

    async function getSimilarCodes(partNumber, allowedBrands, fullCode = false) {
        const params = new URLSearchParams();
        params.append('getSimilarCodes', 'getSimilarCodes');
        params.append('partNumber', partNumber);
        params.append('allowedBrands', allowedBrands);
        params.append('fullCode', fullCode);

        try {
            // Wait for the axios request to resolve
            const response = await axios.post(RelatedGoodsAPI, params);
            const similarCodes = response.data;

            // If fullCode is true, return the similar codes
            if (fullCode) {
                return similarCodes;
            }

            // Otherwise, display the similar codes
            displaySimilarCods(similarCodes);
        } catch (error) {
            console.log(error);
        }
    }

    async function findSimilarCodes(partNumber, allowedBrands, fullCode = false) {
        const params = new URLSearchParams();
        params.append('getSimilarCodes', 'getSimilarCodes');
        params.append('partNumber', partNumber);
        params.append('allowedBrands', allowedBrands);
        params.append('fullCode', fullCode);

        try {
            // Wait for the axios request to resolve
            const response = await axios.post(RelatedGoodsAPI, params);
            const similarCodes = response.data;

            // If fullCode is true, return the similar codes
            if (fullCode) {
                return similarCodes;
            }

            // Otherwise, display the similar codes
            displaySimilarCods(similarCodes);
        } catch (error) {
            console.log(error);
        }
    }

    function displaySimilarCods(similarCodes) {

        similarCodes = similarCodes ? similarCodes['codes'] : [];

        const container = document.getElementById('similar_box');
        if (!container) return; // Check if the container exists

        container.innerHTML = ''; // Clear the container

        if (Array.isArray(similarCodes) && similarCodes.length > 0) {
            // Create the HTML for all codes and update innerHTML once
            const codeHTML = similarCodes.map(code =>
                `<p onclick="searchGoods('${code}')" class="cursor-pointer p-2 text-xs font-semibold text-white bg-gray-800 rounded">
                ${code}
            </p>`
            ).join('');
            container.innerHTML = codeHTML;
        } else {
            // If no similar codes, show the fallback message
            container.innerHTML = '<p class="p-2 text-xs font-semibold text-white bg-gray-800 rounded">هیچ کدی یافت نشد</p>';
        }
    }

    async function getGoods(pattern) {
        const params = new URLSearchParams();
        params.append('searchGoods', 'searchGoods');
        params.append('pattern', pattern);

        try {
            const response = await axios.post(SELL_API, params); // Await the axios response
            let goods = Object.values(response.data);
            goods = sanitizeData(goods); // Sanitize the goods data
            return goods; // Return the sanitized goods
        } catch (error) {
            console.error('Error during API call:', error);
            throw error; // Rethrow error to handle it in searchGoods
        }
    }

    function getRelatedBrandsByKeywords(keywords) {
        // Map of brands to their related brands
        const brandAssociations = {
            'HI Q': ['HIQ', 'HI'],
            'MOB': ['MOB', 'GEN'],
            'GEN': ['MOB', 'GEN'],
            'OEMAX': ['CHINA'],
            'JYR': ['CHINA'],
            'RB2': ['CHINA'],
            'Rb2': ['CHINA'],
            'IRAN': ['CHINA'],
            'UBZ': ['CHINA'],
            'Z PRO': ['CHINA'],
            'FAKE MOB': ['CHINA', 'KOREA'],
            'FAKE GEN': ['CHINA', 'KOREA'],
            'OE MAX': ['CHINA'],
            'MAXFIT': ['CHINA'],
            'ICBRI': ['CHINA'],
            'HOH': ['CHINA'],
            'DOOWON': ['HCC', 'HANON', 'DOOWON'],
            'HANON': ['HCC', 'HANON', 'DOOWON'],
            'HCC': ['HCC', 'HANON', 'DOOWON'],

            // KOREA
            'YONG': ['KOREA'],
            'YONG HOO': ['KOREA'],
            'OEM': ['KOREA'],
            'ONNURI': ['KOREA'],
            'GY': ['KOREA'],
            'MIDO': ['KOREA'],
            'MIRE': ['KOREA'],
            'CARDEX': ['KOREA'],
            'MANDO': ['KOREA'],
            'OSUNG': ['KOREA'],
            'DONGNAM': ['KOREA'],
            'HYUNDAI BRAKE': ['KOREA'],
            'SAM YUNG': ['KOREA'],
            'BRC': ['KOREA'],
            'GEO SUNG': ['KOREA'],
            'YULIM': ['KOREA'],
            'CARTECH': ['KOREA'],
            'HSC': ['KOREA'],
            'KOREA STAR': ['KOREA'],
            'DONI TEC': ['KOREA'],
            'ATC': ['KOREA'],
            'VALEO': ['KOREA'],
            'MB KOREA': ['KOREA'],
            'IACE': ['KOREA'],
            'MB': ['KOREA'],
            'PH': ['KOREA'],
            'CAP': ['KOREA'],
            'BRG': ['KOREA'],
            'GMB': ['KOREA'],
            'KGC': ['KOREA'],
            'GATES': ['KOREA'],
            'KOART': ['KOREA'],
            'SAEHAN': ['KOREA'],
            'FORCEONE': ['KOREA'],
            'DAEWHA': ['KOREA'],
            'AUTOFIX': ['KOREA'],
            'BOSUNG': ['KOREA'],
            'SIB': ['KOREA'],
            'DL KOREA': ['KOREA'],
        };


        // Normalize keywords to uppercase
        keywords = keywords.map(keyword => keyword.trim().toUpperCase());

        // Collect brands that are linked to any of the keywords
        let relatedBrands = [];

        // Iterate through brandAssociations to find matches
        for (let brand in brandAssociations) {
            // Check if any of the keywords exist in the associated brands
            if (brandAssociations[brand].some(keyword => keywords.includes(keyword))) {
                relatedBrands.push(brand);
            }
        }

        return relatedBrands;
    }

    function sanitizeData(goods) {
        goods = goods.map(good => {
            if (billItems[good.quantityId]) {
                good.remaining_qty -= Number(billItems[good.quantityId].quantity);
            }
            return good;
        });

        goods = goods.filter(good => Number(good.remaining_qty) > 0);
        return goods;
    }

    function displayGoods(goods) {
        const resultBox = document.getElementById('resultBox');
        const bgColors = ['bg-red-600', 'bg-orange-600', 'bg-lime-700', 'bg-emerald-600', 'bg-teal-600', 'bg-cyan-600', 'bg-sky-600', 'bg-indigo-700', 'bg-pink-600'];

        resultBox.innerHTML = '';

        if (goods.length > 0) {
            const excluded = new Set([
                'کاربر دستوری',
                'کاربر دستوری معیوب',
                'کاربر دستوری مفقود'
            ]);

            for (const good of goods) {
                if (!excluded.has(good.sellerName)) {
                    resultBox.innerHTML += `
                <div id="${good.quantityId}" class="bg-gray-100 shadow rounded-lg overflow-hidden mb-2 selected_card">
                    <div class="bg-gray-800 rounded-t-md p-2">
                        <p class="text-left font-semibold text-white text-sm">
                            ${good.partNumber}
                        </p>
                    </div>
                    <div>
                        <div class="cardBody">
                            <table style="direction: ltr !important;" class="w-full border border-x-2 border-gray-800">
                                <tbody>
                                    <tr>
                                        <td class="p-2 text-center text-gray-800 font-semibold text-sm quantity">${Number(good.remaining_qty)}</td>
                                        <td class="p-2 text-center text-gray-800 font-semibold text-sm brandName">${good.brandName}</td>
                                        <td class="p-2 text-center text-gray-800 font-semibold text-sm sellerName">${good.sellerName}</td>
                                        <td class="px-1 text-center text-gray-800 font-semibold text-xs stockName">
                                            <p class="${bgColors[good.stockId - 1]} text-white p-2 w-20 rounded text-center">
                                                ${good.stockName}
                                            </p>
                                            <p class="text-xs text-red-500 text-center p-1">${good.quantityDescription}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-2 py-1 bg-gray-800">
                            <input class="px-2 py-1 w-20 rounded text-sm text-center font-semibold" placeholder="تعداد" type="number" value="1" min="1" max="${Number(good.remaining_qty)}">
                            <button onclick="addToFactor(this)"
                                data-partNumber="${good.partNumber}"
                                data-quantityId="${good.quantityId}"
                                data-goodId="${good.goodId}"
                                data-stockId="${good.stockId}"
                                data-stockName="${good.stockName}"
                                data-brandName="${good.brandName}"
                                data-sellerName="${good.sellerName}"
                                data-brandId="${good.brandId}"
                                data-sellerId="${good.sellerId}"
                                data-quantity="${Number(good.remaining_qty)}"
                                class="bg-rose-500 hover:bg-rose-600 text-white px-3 py-1 rounded text-sm">
                                افزودن
                            </button>
                        </div>
                    </div>
                </div>
                `;
                }
            }

            const selectedCards = document.querySelectorAll('.selected_card');
            selectedCards.forEach(card => {
                card.addEventListener('click', function() {
                    selectedCards.forEach(c => c.querySelector(".cardBody").classList.remove('bg-green-300'));
                    card.querySelector(".cardBody").classList.add('bg-green-300');
                });
            });

        } else {
            resultBox.innerHTML = '<p class="text-center bg-gray-200 text-sm font-semibold p-3 rounded text-red-500">هیچ کالایی یافت نشد</p>';
        }
    }

    function addToFactor(element) {
        const sellQuantity = Number(element.previousElementSibling.value);
        const partNumber = element.getAttribute('data-partNumber');
        const quantityId = element.getAttribute('data-quantityId');
        const goodId = element.getAttribute('data-goodId');
        const stockId = element.getAttribute('data-stockId');
        const stockName = element.getAttribute('data-stockName');
        const brandName = element.getAttribute('data-brandName');
        const sellerName = element.getAttribute('data-sellerName');
        const brandId = element.getAttribute('data-brandId');
        const sellerId = element.getAttribute('data-sellerId');
        const quantity = Number(element.getAttribute('data-quantity'));

        if (sellQuantity < 1 || sellQuantity > quantity) {
            alert('تعداد وارد شده باید بین 1 تا ' + quantity + ' باشد');
            return;
        }

        const difference = quantity - sellQuantity;
        const parentElement = document.getElementById(quantityId);
        parentElement.querySelector('.quantity').innerHTML = difference;
        element.setAttribute('data-quantity', difference);
        element.previousElementSibling.value = 1;
        element.previousElementSibling.setAttribute('max', difference);

        if (difference === 0) {
            parentElement.remove();
        }

        if (billItems.hasOwnProperty(quantityId)) {
            // If the item already exists, sum the quantities
            billItems[quantityId].quantity = Number(billItems[quantityId].quantity) + Number(sellQuantity);
        } else {
            // If the item does not exist, add it to billItems
            billItems[quantityId] = {
                quantityId: quantityId,
                id: billItemOrder.length + 1, // Generate a new id based on the number of items
                goodId: goodId,
                partNumber: partNumber,
                stockId: stockId,
                stockName: stockName,
                brandName: brandName,
                sellerName: sellerName,
                quantity: sellQuantity
            };
            if (!billItemOrder.includes(quantityId)) {
                billItemOrder.push(quantityId);
            }
        }
        previewFactor();
    }

    function previewFactor() {
        const previewFactor = document.getElementById('previewFactor');
        previewFactor.innerHTML = '';
        factor_info.quantity = 0;
        const bgColors = ['bg-red-600', 'bg-orange-600', 'bg-lime-700', 'bg-emerald-600', 'bg-teal-600', 'bg-cyan-600', 'bg-sky-600', 'bg-indigo-700', 'bg-pink-600'];

        // Check if there are any items in billItems
        if (Object.keys(billItems).length === 0) {
            previewFactor.innerHTML = '<p class="p-2 text-rose-700 text-center text-sm font-semibold shadow">موردی برای نمایش وجود ندارد</p>';
            document.getElementById('totalGoods').innerHTML = 0;
            return;
        }

        let totalGoods = 0;

        billItemOrder.forEach(quantityId => {
            const item = billItems[quantityId];
            if (item) {
                totalGoods += Number(item.quantity);
                previewFactor.innerHTML += `
                <div id='item-${item.quantityId}' class="rounded bg-gray-200 shadow mb-2 overflow-hidden border border-2 border-gray-800">
                    <div class="bg-gray-800 p-3">
                        <p class="text-left font-semibold text-white text-sm">
                            ${item.partNumber}
                        </p>
                    </div>
                    <div>
                        <table style="direction: ltr !important;" class="w-full">
                            <tbody>
                                <tr>
                                    <td class="p-3 text-left text-gray-800 font-semibold text-xs">${item.quantity}</td>
                                    <td class="p-3 text-left text-gray-800 font-semibold text-xs">${item.brandName}</td>
                                    <td class="p-3 text-left text-gray-800 font-semibold text-xs">${item.sellerName}</td>
                                    <td class="p-3 text-left text-gray-800 font-semibold text-xs">
                                        <p class="${bgColors[item.stockId - 1]} text-white py-1 px-2 w-20 text-xs rounded text-center">
                                            ${item.stockName}
                                        </p>
                                        <p class="text-xs text-red-500 text-center p-1">${item.purchase_Description ?? ''}</p>
                                    </td>
                                    <td class="p-3 text-left text-gray-800 font-semibold text-sm" onclick="deleteItem(${item.quantityId})">
                                        <img class="cursor-pointer" src="./assets/icons/delete.svg" alt="delete">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>`;
                factor_info.quantity += item.quantity;
            }
        });

        document.getElementById('totalGoods').innerHTML = totalGoods;
    }

    function deleteItem(quantityId) {
        const item = billItems[quantityId];
        const parentElement = document.getElementById('item-' + quantityId);
        parentElement.remove();
        delete billItems[quantityId];
        const itemIndex = billItemOrder.indexOf(quantityId);
        billItemOrder.splice(itemIndex, 1);

        previewFactor();
    }

    function validateFactorInfo(factorInfo) {
        const requiredFields = ['date', 'client', 'receiver', 'quantity'];
        for (const field of requiredFields) {
            if (!factorInfo[field]) {
                return false;
            }
        }
        return true;
    }

    function setFactorInfo(property, value) {
        factor_info[property] = value;
    }

    $(function() {
        $("#invoice_time").persianDatepicker({
            months: ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"],
            dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
            shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
            showGregorianDate: !1,
            persianNumbers: !0,
            formatDate: "YYYY/0M/0D",
            selectedBefore: 0,
            selectedDate: null,
            startDate: null,
            endDate: null,
            prevArrow: '\u25c4',
            nextArrow: '\u25ba',
            theme: 'default',
            alwaysShow: !1,
            selectableYears: null,
            selectableMonths: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            cellWidth: 30, // by px
            cellHeight: 25, // by px
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
            onRender: function() {
                const date = ($("#invoice_time").val());
                factor_info.date = date;
            }
        });
    });

    function saveSells() {
        if (Object.keys(billItems).length === 0) {
            alert('لطفا حداقل یک کالا انتخاب کنید');
            return;
        }

        if (!validateFactorInfo(factor_info)) {
            alert('لطفا تمام فیلدهای مورد نیاز را پر کنید');
            return;
        }

        var params = new URLSearchParams();
        params.append('saveFactor', 'saveFactor');
        params.append('billItems', JSON.stringify(billItems));
        params.append('factorInfo', JSON.stringify(factor_info));
        axios.post(SELL_API, params)
            .then(function(response) {
                if (response.data == 'success') {
                    document.getElementById('operation_message').classList.remove('hidden');
                    clearPreviousData();
                    setTimeout(() => {
                        document.getElementById('operation_message').classList.add('hidden');
                        document.getElementById('previewFactor').innerHTML = '';
                        document.getElementById('resultBox').innerHTML = '';
                    }, 3000);
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function clearPreviousData() {
        billItems = {};

        factor_info = {
            number: null,
            date: ($("#invoice_time").val()),
            client: null,
            receiver: 6,
            collector: null,
            description: null,
            user: <?= $_SESSION['id'] ?>,
            quantity: 0
        };
        const inputs = document.getElementsByTagName('input');
        for (input of inputs) {
            input.value = '';
        }
        document.getElementById('invoice_time').value = factor_info.date;
        const textarea = document.getElementsByTagName('textarea');
        for (item of textarea) {
            item.value = '';
        }
    }
</script>
<?php
require_once './components/footer.php';
?>