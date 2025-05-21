<?php
$pageTitle = "قیمت کدفنی";
$iconUrl = "logo.jpg";
require_once "./components/header.php";
require_once "../../app/controller/admin/UsersController.php";
require_once "../../layouts/admin/nav.php";
require_once "../../layouts/admin/sidebar.php";
?>
<section class="py-11 px-5">
    <h2 class="text-xl font-semibold mb-5">جستجوی اجناس</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class=" sm:px-6 lg:px-8 bg-gray-100 rounded-lg shadow p-5">
            <form target="_blank" action="#" method="post" id="myForm">
                <div class="">
                    <div class="col-span-6 sm:col-span-4">
                        <textarea onchange="filterCode(this)" rows="10" id="code" name="code" required
                            style="direction: ltr !important;"
                            class="border-2 outline-none block w-full border-gray-200 p-3 uppercase text-sm font-semibold"
                            placeholder="لطفا کد های مورد نظر خود را در خط های مجزا قرار دهید"></textarea>
                    </div>
                </div>

                <div class=" flex items-center justify-end py-3 text-right sm:rounded-bl-md sm:rounded-br-md">
                    <button type="submit" class="items-center px-4 py-2 bg-gray-800 border border-transparent font-semibold text-xs text-white">جستجو</button>
                    </button>
                </div>
            </form>
        </div>
        <div id="result_box" class=" sm:px-6 lg:px-8 bg-gray-100 rounded-lg shadow p-5">
        </div>
    </div>
</section>
<script>
    document.getElementById('myForm').addEventListener('submit', submitForm);

    function submitForm(e) {
        e.preventDefault();
        const user_id = <?= $_SESSION['id'] ?>;
        const codeValue = document.getElementById('code').value.trim();

        if (codeValue.length > 0) {
            logAskedCodes(codeValue.toUpperCase());
        }

        let displayPrices = [];

        async function getRequestLimit() {
            const params = new URLSearchParams();
            params.append('getRequestLimit', 'getRequestLimit');
            params.append('userID', user_id);

            const response = await axios.post('../../app/api/auth/RequestAPI.php', params);

            return response.data;

        }

        const remainingRequests = getRequestLimit();


        // Use async/await to wait for the axios.post request to complete
        async function fetchData() {
            try {
                const params = new URLSearchParams();
                const form = document.getElementById('myForm');
                const formData = new FormData(form);

                const result_box = document.getElementById('result_box');
                result_box.classList.add('py-8');

                result_box.innerHTML = `<img class="w-12 h-12 mx-auto mt-10" src="../../public/img/loading.png" alt="Loading" />
                                <p class="text-center my-5 text-sm font-semibold">لطفا صبور باشید</p>`;

                // const response = await axios.post('http://new.test/lastPrice.php', formData);
                const response = await axios.post('http://84.241.41.22:9002/yadakshop-app/lastPrice.php', formData);
                const displayPrices = response.data;

                console.log(response.data);


                const codes = displayPrices?.['explodedCodes'] || [];
                const prices = displayPrices?.['prices'] || {};
                const equal = displayPrices?.['equal'] || {};

                if (codes.length > 0) {
                    let template = `
                <div class="m-2 p-3 col-span-2 bg-gray-600 relative">
                    <table style="direction:ltr !important" class="min-w-full text-sm font-light p-2">
                        <thead class="font-medium">
                            <tr class="border">
                                <th class="text-white text-left px-3 py-2">کد فنی</th>
                                <th class="text-white text-left px-3 py-2">قیمت</th>
                                <th class="text-right py-2">
                                    <img src='../../public/icons/copy.svg' id="copy_all" title="کاپی کردن  همه مقادیر" onclick="copyPrice(this)" class="text-xl pr-5 text-sm material-icons cursor-pointer text-red-500" />
                                </th>
                            </tr>
                        </thead>
                        <tbody id="priceReport">`;

                    for (let code of codes) {
                        let partNumber = equal[code] ? prices[equal[code]] : "کد اشتباه";
                        let price = "کد اشتباه";
                        if (partNumber != "کد اشتباه") {
                            price = partNumber?.['finalPrice'] ?? 'موجود نیست';
                        }
                        if (partNumber?.['finalPrice'] == '') {
                            price = 'موجود نیست';
                        }

                        template += `<tr class="border">
                                <td class="px-3 py-2 text-left text-white cursor-pointer">${code}</td>
                                <td style="direction:ltr !important;" class="px-3 py-2 text-left text-white">
                                    ${price}
                                </td>
                                <td class="text-right py-2">
                                    <img src='../../public/icons/dipCopy.svg' title="کاپی کردن مقادیر" onclick="copyItemPrice(this)" class="px-4 text-white text-sm material-icons cursor-pointer" />
                                </td>
                            </tr>`;
                    }

                    template += `</tbody>
                    </table>
                </div>`;
                    result_box.innerHTML = template;
                    document.getElementById("code").value = null;
                } else {
                    result_box.innerHTML = `<p class="bg-rose-600 text-white text-center text-sm font-semibold rounded p-3">قیمتی برای کد فنی مدنظر شما ثبت نشده است.</p>`;
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                result_box.innerHTML = `<p class="bg-red-600 text-white text-center text-sm font-semibold rounded p-3">خطایی در دریافت قیمت‌ها رخ داده است.</p>`;
            }
        }

        remainingRequests
            .then(result => {
                if (result == 'You have been logged out because you logged in on another device.') {
                    result_box.innerHTML = `<p class="bg-rose-600 text-white text-center text-sm font-semibold rounded p-3">You have been logged out because you logged in on another device.</p>`;
                } else if (result <= 0) {
                    result_box.innerHTML = `<p class="bg-rose-600 text-white text-center text-sm font-semibold rounded p-3">شما به حداکثر درخواست امروز رسیده اید.</p>`;
                } else {
                    if (codeValue.length > 0) {
                        recordRequests();
                        fetchData();
                    }
                }
            })
            .catch(error => {
                console.error(error);
            });

    }

    const id = '<?= $_SESSION['id'] ?>';
    const username = '<?= $_SESSION['username'] ?>';
    const IP = ' <?= $_SERVER['REMOTE_ADDR'] ?>';

    function logAskedCodes(code) {
        const param = new URLSearchParams();
        param.append('price_ask', 'price_ask');
        param.append('code', code);

        axios.post('../../app/api/admin/priceAPI.php', param)
            .then((response) => {}).catch((e) => {
                console.log(e);
            })
    }

    function recordRequests() {
        const param = new URLSearchParams();
        param.append('record_request', 'record_request');
        param.append('id', id);

        axios.post('../../app/api/admin/priceAPI.php', param)
            .then((response) => {}).catch((e) => {
                console.log(e);
            })
    }

    // A function to copy content to clipboard
    function copyPrice(elem) {
        try {
            // Get the text field
            let parentElement = document.getElementById("priceReport");

            let tdElements = parentElement.getElementsByTagName("td");
            let tdTextContent = [];

            const elementLength = tdElements.length;

            const dash = ["موجود نیست", "نیاز به بررسی"];
            const space = ["کد اشتباه", "نیاز به قیمت"];

            for (let i = 0; i < elementLength; i++) {

                if (tdElements[i].textContent.trim() !== '') {
                    let text = "";
                    if (dash.includes(tdElements[i].textContent.trim())) {
                        text = "-";
                    } else if (space.includes(tdElements[i].textContent.trim())) {
                        text = " ";
                    } else {
                        text = tdElements[i].textContent.trim();
                    }

                    tdTextContent.push(text);
                }
            }

            const chunkSize = 2;
            tdTextContent = tdTextContent.filter((td) => td.length > 0);

            let finalResult = [];
            const size = tdTextContent.length;
            for (let i = 0; i < size; i += chunkSize) {
                finalResult.push(tdTextContent.slice(i, i + chunkSize));
            }

            // Copy the text inside the text field
            let text = "";
            for (let item of finalResult) {
                text += item.join("  ");
                text += "\n";
            }
            copyToClipboard(text.trim());
            // Alert the copied text
            elem.src = `../../public/icons/done.svg`;
            setTimeout(() => {
                elem.src = `../../public/icons/copy.svg`;
            }, 1500);
        } catch (e) {
            console.log(e);
        }
    }

    function copyItemPrice(elem) {
        // Get the parent <td> element
        var parentTd = elem.parentNode;

        // Get the siblings <td> elements
        var sibling1 = parentTd.previousElementSibling;
        var sibling2 = sibling1.previousElementSibling;


        // Retrieve the innerHTML of the sibling <td> elements
        var sibling1HTML = sibling1.innerHTML.trim();
        var sibling2HTML = sibling2.innerHTML.trim();

        const dash = ["موجود نیست", "نیاز به بررسی"];
        const space = ["کد اشتباه", "نیاز به قیمت"];

        let value = "";
        if (dash.includes(sibling1HTML)) {
            value = "-";
        } else if (space.includes(sibling1HTML)) {
            value = " ";
        } else {
            value = sibling1HTML;
        }

        let text = sibling2HTML + " : " + value;

        copyToClipboard(text);

        // Alert the copied text
        elem.src = `../../public/icons/done.svg`;
        setTimeout(() => {
            elem.src = `../../public/icons/dipCopy.svg`;
        }, 1500);
    }

    function copyToClipboard(text) {
        if (window.clipboardData && window.clipboardData.setData) {
            // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
            return window.clipboardData.setData("Text", text);

        } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
            var textarea = document.createElement("textarea");
            textarea.textContent = text;
            textarea.style.position = "fixed"; // Prevent scrolling to bottom of page in Microsoft Edge.
            document.body.appendChild(textarea);
            textarea.select();
            try {
                return document.execCommand("copy"); // Security exception may be thrown by some browsers.
            } catch (ex) {
                console.warn("Copy to clipboard failed.", ex);
                return prompt("Copy to clipboard: Ctrl+C, Enter", text);
            } finally {
                document.body.removeChild(textarea);
            }
        }
    }
</script>
<?php
require_once "./components/footer.php";
?>