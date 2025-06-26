<div class="bill_header">
    <div class="bill_info">
        <div class="nisha-bill-info">
            <div class="A-main">
                <div class="A-1">شماره</div>
                <div class="A-2"><span id="billNO_finance"></span></div>
            </div>
            <div class="B-main">
                <div class="B-1">تاریخ</div>
                <div class="B-2"><span id="date_finance"></span></div>
            </div>
        </div>
    </div>
    <div class="headline">
        <h2 style="margin-bottom: 7px;">نسخه حسابداری</h2>
        <h2 style="margin-bottom: 7px;"><?= $subTitle; ?></h2>
    </div>
    <div class="log_section">
        <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
            <g id="SVGRepo_iconCarrier">
                <path opacity="0.5" d="M2 16C2 13.1716 2 11.7574 2.87868 10.8787C3.75736 10 5.17157 10 8 10H16C18.8284 10 20.2426 10 21.1213 10.8787C22 11.7574 22 13.1716 22 16C22 18.8284 22 20.2426 21.1213 21.1213C20.2426 22 18.8284 22 16 22H8C5.17157 22 3.75736 22 2.87868 21.1213C2 20.2426 2 18.8284 2 16Z" fill="#1C274C"></path>
                <path d="M8 17C8.55228 17 9 16.5523 9 16C9 15.4477 8.55228 15 8 15C7.44772 15 7 15.4477 7 16C7 16.5523 7.44772 17 8 17Z" fill="#1C274C"></path>
                <path d="M12 17C12.5523 17 13 16.5523 13 16C13 15.4477 12.5523 15 12 15C11.4477 15 11 15.4477 11 16C11 16.5523 11.4477 17 12 17Z" fill="#1C274C"></path>
                <path d="M17 16C17 16.5523 16.5523 17 16 17C15.4477 17 15 16.5523 15 16C15 15.4477 15.4477 15 16 15C16.5523 15 17 15.4477 17 16Z" fill="#1C274C"></path>
                <path d="M6.75 8C6.75 5.10051 9.10051 2.75 12 2.75C14.8995 2.75 17.25 5.10051 17.25 8V10.0036C17.8174 10.0089 18.3135 10.022 18.75 10.0546V8C18.75 4.27208 15.7279 1.25 12 1.25C8.27208 1.25 5.25 4.27208 5.25 8V10.0546C5.68651 10.022 6.18264 10.0089 6.75 10.0036V8Z" fill="#1C274C"></path>
            </g>
        </svg>
    </div>
</div>
<div class="customer_info relative flex justify-between">
    <ul class="w-1/2">
        <li class="text-xs">
            نام :
            <span id="name_finance"></span>
        </li>
        <li class="text-xs">
            شماره تماس :
            <span id="phone_finance"></span>
        </li>
    </ul>
    <ul class="w-1/2">
        <li class="text-xs">
            نشانی :
            <span id="userAddress_finance"></span>
        </li>
        <!-- <li class="text-xs">
            ماشین :
            <span id="car_finance"></span>
        </li> -->
    </ul>
    <!-- <p class="w-1/2" id="userAddress_finance" style="font-size: 13px;"></p> -->

    <div class="text-xs flex items-center gap-2">
        <img class="rounded-full w-9 h-9 mt-2" src="<?= $profile ?>" alt="">
        <p>
            زمان ثبت:
            <span id="time_finance"></span>
            <br>
            زمان پرینت:
            <span><?= date('H:i'); ?></span>
        </p>
    </div>
</div>

<script>
    function displayFinanceBill() {
        const finance_bill_body = document.getElementById('finance_bill_body');
        let counter = 1;
        let template = ``;
        let totalPrice = 0;

        const brands = [
            "شرکتی",
            "کره ای",
            "کره",
            "چین",
            "چینی",
            "متفرقه"
        ];
        const excludeBrands = [
            "اصلی",
            "GEN",
            "MOB"
        ];



        for (const item of billItems) {
            const payPrice = Number(item.quantity) * Number(item.price_per);
            totalPrice += payPrice;

            const isBrand = brands.some(brand => item.partName.includes(brand));
            const specialClass = isBrand ? 'special' : '';

            const nameParts = item.partName.split('-');

            let excludeClass = '';

            const brandPattern = new RegExp(`\\b(${excludeBrands.join('|')})\\b`, 'gu');
            if (nameParts[1]) {
                if (nameParts[1].trim() != "اصلی") {
                    const brand = nameParts[1].trim();

                    if (!brand.match(brandPattern)) {
                        excludeClass = "exclude";
                    }
                }
            }

            <?php if ($factorType == 'partner'): ?>
                template += `
                <tr class="even:bg-gray-100">
                        <td style="padding-block:10px !important;" class="text-sm text-center">
                            <span>${counter}</span>
                        </td>
                        <td style="padding-block:10px !important" class="text-sm ${specialClass}" colspan="2">
                            <span>${nameParts[0]}
                            ${nameParts[1] ? ` - <span class="${excludeClass}">${nameParts[1]}</span>` : ''}
                            </span>
                            <table style="direction:ltr !important; border:none !important" id="${item.id}_finance" class="float-left">
                            </table>
                            <span class="float-left" id="des_${item.id}_finance"></span>
                            
                        </td>
                        <td style="padding:15px 0 !important; width:10px !important" class="text-sm ${item.quantity != 1 ? 'font-semibold' : ''}">
                            <span>${item.quantity}</span>
                        </td>
                        <td class="text-sm text-center">
                            <span>${formatAsMoney((Number(item.price_per))/10000)}</span>
                        </td>
                    </tr>`;
            <?php else: ?>
                template += `
                    <tr style="padding: 10px !important;" class="even:bg-gray-100">
                        <td class="text-sm text-center">
                            <span>${counter}</span>
                        </td>
                        <td class="text-sm ${specialClass}">
                            <span>${item.partName}</span>
                        </td>
                        <td class="text-sm border-r border-l-2 border-gray-800">
                            <span>${item.quantity}</span>
                        </td>
                        <td class="text-sm">
                            <span>${formatAsMoney(Number(item.price_per))}</span>
                        </td>
                        <td class="text-sm">
                            <span>${formatAsMoney(payPrice)}</span>
                        </td>
                    </tr> `;
            <?php endif; ?>


            counter++;
        }
        finance_bill_body.innerHTML = template;
    }

    function displayFinanceCustomer() {
        // Retrieve display name from local storage
        const displayName = sessionStorage.getItem("displayName");

        // Update customer information if display name is available
        if (displayName !== null && displayName !== undefined) {
            // Update customer information if display name is available
            customerInfo.name = displayName;
        }

        // Display customer information on the webpage
        const nameElement = document.getElementById("name_finance");
        const phoneElement = document.getElementById("phone_finance");
        const addressElement = document.getElementById("userAddress_finance");
        // const car_finance = document.getElementById("car_finance");

        nameElement.innerHTML =
            customerInfo.name + (customerInfo.family ? " " + customerInfo.family : "");
        phoneElement.innerHTML = customerInfo.phone;
        if (customerInfo.address && customerInfo.address != "null")
            addressElement.innerHTML = customerInfo.address;

        // if (customerInfo.car && customerInfo.car != "null")
        //     car_finance.innerHTML = customerInfo.car;
    }

    function displayFinanceBillDetails() {
        document.getElementById("billNO_finance").innerHTML = BillInfo.bill_number;
        document.getElementById("date_finance").innerHTML = BillInfo.bill_date.replace(
            /-/g,
            "/"
        );
        document.getElementById("quantity_finance").innerHTML = BillInfo.quantity;
        document.getElementById("totalPrice_finance").innerHTML = formatAsMoney(
            BillInfo.total
        );
        document.getElementById("totalPrice2_finance").innerHTML = formatAsMoney(
            Number(BillInfo.total) - Number(BillInfo.discount)
        );
        document.getElementById("discount_finance").innerHTML = BillInfo.discount;
        document.getElementById("total_in_word_finance").innerHTML = numberToPersianWords(
            BillInfo.total
        );
        document.getElementById("time_finance").innerHTML = now;
        if (document.getElementById("description_finance"))
            document.getElementById("description_finance").innerHTML =
            BillInfo.description.replace(/\n/g, "<br>");
    }
</script>