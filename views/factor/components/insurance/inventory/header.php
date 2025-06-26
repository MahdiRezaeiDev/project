<style>
    .special {
        box-shadow: 7px 0px 0px 0px black inset;
    }
</style>
<div class="bill_header">
    <div class="bill_info">
        <div class="nisha-bill-info">
            <div class="A-main">
                <div class="A-1">شماره</div>
                <div class="A-2"><span id="billNO_inventory"></span></div>
            </div>
            <div class="B-main">
                <div class="B-1">تاریخ</div>
                <div class="B-2"><span id="date_inventory"></span></div>
            </div>
        </div>
    </div>
    <div class="headline">
        <h2 style="margin-bottom: 7px;">حواله انبار</h2>
        <h2 style="margin-bottom: 7px;"><?= $subTitle; ?></h2>
    </div>
    <div class="log_section">
        <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
            <g id="SVGRepo_iconCarrier">
                <path d="M6 8H10" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M20.8333 9H18.2308C16.4465 9 15 10.3431 15 12C15 13.6569 16.4465 15 18.2308 15H20.8333C20.9167 15 20.9583 15 20.9935 14.9979C21.5328 14.965 21.9623 14.5662 21.9977 14.0654C22 14.0327 22 13.994 22 13.9167V10.0833C22 10.006 22 9.96726 21.9977 9.9346C21.9623 9.43384 21.5328 9.03496 20.9935 9.00214C20.9583 9 20.9167 9 20.8333 9Z" stroke="#1C274C" stroke-width="1.5"></path>
                <path d="M20.965 9C20.8873 7.1277 20.6366 5.97975 19.8284 5.17157C18.6569 4 16.7712 4 13 4L10 4C6.22876 4 4.34315 4 3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H13C16.7712 20 18.6569 20 19.8284 18.8284C20.6366 18.0203 20.8873 16.8723 20.965 15" stroke="#1C274C" stroke-width="1.5"></path>
                <path d="M17.9912 12H18.0002" stroke="#1C274C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </g>
        </svg>
    </div>
</div>
<div class="customer_info relative flex justify-between">
    <ul class="w-1/2">
        <li class="text-sm">
            نام :
            <span id="name_inventory"></span>
        </li>
        <li class="text-sm">
            شماره تماس :
            <span id="phone_inventory"></span>
        </li>
    </ul>
    <ul class="w-1/2">
        <li class="text-xs">
            نشانی :
            <span id="userAddress_inventory"></span>
        </li>
        <!-- <li class="text-xs">
            ماشین :
            <span id="car_inventory"></span>
        </li> -->
    </ul>
    <div class="text-xs flex items-center gap-2">
        <img class="rounded-full w-9 h-9 mt-2" src="<?= $profile ?>" alt="">
        <div>
            زمان ثبت:
            <span id="time_inventory"></span>
            <br>
            زمان پرینت:
            <span><?= date('H:i'); ?></span>
        </div>
    </div>
</div>
<script>
    function displayInventoryBill() {
        const inventory_bill_footer = document.getElementById('inventory_bill_footer');
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

            template += `
                    <tr class="even:bg-gray-100">
                        <td style="padding-block:10px !important;" class="text-sm text-center">
                            <span>${counter}</span>
                        </td>
                        <td style="padding-block:10px !important" class="text-sm ${specialClass}" colspan="2">
                            <span>${nameParts[0]}
                            ${nameParts[1] ? ` - <span class="${excludeClass}">${nameParts[1]}</span>` : ''}
                            </span>
                            <table style="direction:ltr !important; border:none !important" id="${item.id}" class="float-left">
                            </table>
                            <span class="float-left" id="des_${item.id}"></span>
                        </td>
                        <td style="padding:15px 0 !important; width:10px !important; border-left: 0px !important;" class="text-sm ${item.quantity != 1 ? 'font-semibold' : ''}">
                            <span>${item.quantity}</span>
                        </td>
                    </tr>`;
            counter++;
        }

        inventory_bill_footer.innerHTML = template;
    }

    function displayInventoryCustomer() {
        // Retrieve display name from local storage
        const displayName = sessionStorage.getItem("displayName");

        // Update customer information if display name is available
        if (displayName !== null && displayName !== undefined) {
            // Update customer information if display name is available
            customerInfo.name = displayName;
        }

        // Display customer information on the webpage
        const nameElement = document.getElementById("name_inventory");
        const phoneElement = document.getElementById("phone_inventory");
        const addressElement = document.getElementById("userAddress_inventory");
        // const car_inventory = document.getElementById("car_inventory");

        nameElement.innerHTML =
            customerInfo.name + (customerInfo.family ? " " + customerInfo.family : "");
        phoneElement.innerHTML = customerInfo.phone;
        if (customerInfo.address && customerInfo.address != "null")
            addressElement.innerHTML = customerInfo.address;
        // if (customerInfo.car && customerInfo.car != "null")
        //     car_inventory.innerHTML = customerInfo.car;
    }

    function displayInventoryBillDetails() {
        document.getElementById("billNO_inventory").innerHTML = BillInfo.bill_number;
        document.getElementById("date_inventory").innerHTML = BillInfo.bill_date.replace(
            /-/g,
            "/"
        );
        // document.getElementById("quantity_inventory").innerHTML = BillInfo.quantity;
        // document.getElementById("totalPrice_inventory").innerHTML = formatAsMoney(
        //     BillInfo.total
        // );
        // document.getElementById("totalPrice2_inventory").innerHTML = formatAsMoney(
        //     Number(BillInfo.total) - Number(BillInfo.discount)
        // );
        // document.getElementById("discount_inventory").innerHTML = BillInfo.discount;
        // document.getElementById("total_in_word_inventory").innerHTML = numberToPersianWords(
        //     BillInfo.total
        // );
        document.getElementById("time_inventory").innerHTML = now;
        if (document.getElementById("description_inventory") && BillInfo.description !== null)
            document.getElementById("description_inventory").innerHTML =
            BillInfo.description.replace(/\n/g, "<br>");
    }
</script>