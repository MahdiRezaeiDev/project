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
                <circle cx="12" cy="12" r="9" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
                <path d="M14.5 9.08333L14.3563 8.96356C13.9968 8.66403 13.5438 8.5 13.0759 8.5H10.75C9.7835 8.5 9 9.2835 9 10.25V10.25C9 11.2165 9.7835 12 10.75 12H13.25C14.2165 12 15 12.7835 15 13.75V13.75C15 14.7165 14.2165 15.5 13.25 15.5H10.412C9.8913 15.5 9.39114 15.2969 9.01782 14.934L9 14.9167" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M12 8L12 7" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M12 17V16" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
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

        console.log("Display Name:", displayName);
        console.log("Factor Number:", factorNumber);
        console.log("BillInfo.bill_number:", BillInfo.bill_number);



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