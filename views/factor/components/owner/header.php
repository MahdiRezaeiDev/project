<div class="bill_header">
    <div class="bill_info">
        <div class="nisha-bill-info">
            <div class="A-main">
                <div class="A-1">شماره</div>
                <div class="A-2"><span id="billNO_owner"></span></div>
            </div>
            <div class="B-main">
                <div class="B-1">تاریخ</div>
                <div class="B-2"><span id="date_owner"></span></div>
            </div>
        </div>
    </div>
    <div class="headline">
        <h2 style="margin-bottom: 7px;">نسخه کارشناس فنی</h2>
        <h2 style="margin-bottom: 7px;"><?= $subTitle; ?></h2>
    </div>
    <div class="log_section">
        <?php if ($factorType == 'partner'): ?>
            <svg width="64px" height="64px" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path fill="#000000" d="M218 19c-1 0-2.76.52-5.502 3.107-2.742 2.589-6.006 7.021-9.191 12.76-6.37 11.478-12.527 28.033-17.666 45.653-4.33 14.844-7.91 30.457-10.616 44.601 54.351 24.019 107.599 24.019 161.95 0-2.706-14.144-6.286-29.757-10.616-44.601-5.139-17.62-11.295-34.175-17.666-45.653-3.185-5.739-6.45-10.171-9.191-12.76C296.76 19.52 295 19 294 19c-6.5 0-9.092 1.375-10.822 2.85-1.73 1.474-3.02 3.81-4.358 7.34-1.338 3.53-2.397 8.024-5.55 12.783C270.116 46.73 263.367 51 256 51c-7.433 0-14.24-4.195-17.455-8.988-3.214-4.794-4.26-9.335-5.576-12.881-1.316-3.546-2.575-5.867-4.254-7.315C227.035 20.37 224.5 19 218 19zm-46.111 124.334c-1.41 9.278-2.296 17.16-2.57 22.602 6.61 5.087 17.736 10.007 31.742 13.302C217.18 183.031 236.6 185 256 185s38.82-1.969 54.94-5.762c14.005-3.295 25.13-8.215 31.742-13.302-.275-5.443-1.161-13.324-2.57-22.602-55.757 23.332-112.467 23.332-168.223 0zM151.945 155.1c-19.206 3.36-36.706 7.385-51.918 11.63-19.879 5.548-35.905 11.489-46.545 16.57-5.32 2.542-9.312 4.915-11.494 6.57-.37.28-.247.306-.445.546.333.677.82 1.456 1.73 2.479 1.973 2.216 5.564 4.992 10.627 7.744 10.127 5.504 25.944 10.958 45.725 15.506C139.187 225.24 194.703 231 256 231s116.813-5.76 156.375-14.855c19.78-4.548 35.598-10.002 45.725-15.506 5.063-2.752 8.653-5.528 10.627-7.744.91-1.023 1.397-1.802 1.73-2.479-.198-.24-.075-.266-.445-.547-2.182-1.654-6.174-4.027-11.494-6.568-10.64-5.082-26.666-11.023-46.545-16.57-15.212-4.246-32.712-8.272-51.918-11.631.608 5.787.945 10.866.945 14.9v3.729l-2.637 2.634c-10.121 10.122-25.422 16.191-43.302 20.399C297.18 200.969 276.6 203 256 203s-41.18-2.031-59.06-6.238c-17.881-4.208-33.182-10.277-43.303-20.399L151 173.73V170c0-4.034.337-9.113.945-14.9zm1.094 88.205C154.558 308.17 200.64 359 256 359c55.36 0 101.442-50.83 102.96-115.695a748.452 748.452 0 0 1-19.284 2.013c-1.33 5.252-6.884 25.248-15.676 30.682-13.61 8.412-34.006 7.756-48 0-7.986-4.426-14.865-19.196-18.064-27.012-.648.002-1.287.012-1.936.012-.65 0-1.288-.01-1.936-.012-3.2 7.816-10.078 22.586-18.064 27.012-13.994 7.756-34.39 8.412-48 0-8.792-5.434-14.346-25.43-15.676-30.682a748.452 748.452 0 0 1-19.285-2.013zM137.4 267.209c-47.432 13.23-77.243 32.253-113.546 61.082 42.575 4.442 67.486 21.318 101.265 48.719l16.928 13.732-21.686 2.211c-13.663 1.393-28.446 8.622-39.3 17.3-5.925 4.738-10.178 10.06-12.957 14.356 44.68 5.864 73.463 10.086 98.011 20.147 18.603 7.624 34.81 18.89 53.737 35.781l5.304-23.576c-1.838-9.734-4.134-19.884-6.879-30.3-5.12-7.23-9.698-14.866-13.136-22.007C201.612 397.326 199 391 199 384c0-3.283.936-6.396 2.428-9.133a480.414 480.414 0 0 0-6.942-16.863c-29.083-19.498-50.217-52.359-57.086-90.795zm237.2 0c-6.87 38.436-28.003 71.297-57.086 90.795a480.521 480.521 0 0 0-6.942 16.861c1.493 2.737 2.428 5.851 2.428 9.135 0 7-2.612 13.326-6.14 20.654-3.44 7.142-8.019 14.78-13.14 22.01-2.778 10.547-5.099 20.82-6.949 30.666l5.14 23.42c19.03-17.01 35.293-28.338 53.974-35.994 24.548-10.06 53.33-14.283 98.011-20.147-2.78-4.297-7.032-9.618-12.957-14.355-10.854-8.679-25.637-15.908-39.3-17.3l-21.686-2.212 16.928-13.732c33.779-27.4 58.69-44.277 101.265-48.719-36.303-28.829-66.114-47.851-113.546-61.082zM256 377c-8 0-19.592.098-28.234 1.826-4.321.864-7.8 2.222-9.393 3.324-1.592 1.103-1.373.85-1.373 1.85s1.388 6.674 4.36 12.846c2.971 6.172 7.247 13.32 11.964 19.924 4.717 6.604 9.925 12.699 14.465 16.806 4.075 3.687 7.842 5.121 8.211 5.377.37-.256 4.136-1.69 8.21-5.377 4.54-4.107 9.749-10.202 14.466-16.806 4.717-6.605 8.993-13.752 11.965-19.924C293.612 390.674 295 385 295 384s.22-.747-1.373-1.85c-1.593-1.102-5.072-2.46-9.393-3.324C275.592 377.098 264 377 256 377zm0 61.953c-.042.03-.051.047 0 .047s.042-.018 0-.047zm-11.648 14.701L235.047 495h41.56l-9.058-41.285C264.162 455.71 260.449 457 256 457c-4.492 0-8.235-1.316-11.648-3.346z"></path>
                </g>
            </svg>
        <?php else: ?>
            <svg width="64px" height="64px" viewBox="0 0 1024 1024" fill="#000000" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path d="M908.008473 139.713707L529.299213 3.059374a51.005609 51.005609 0 0 0-17.397182-3.059374c-5.950311 0-11.855934 1.03125-17.534682 3.090312L117.015902 139.744645c-20.394681 7.397498-34.704989 26.774679-34.70499 48.472173v323.781463c0 72.287165 23.299368 144.584643 67.763417 214.87806 34.082802 53.875921 81.520288 106.71028 140.256832 157.045577 98.209345 84.156849 195.655565 132.70121 199.749627 134.725896a51.957797 51.957797 0 0 0 22.96593 5.352186c7.717185 0 15.485933-1.732499 22.608431-5.197498 4.094061-1.993749 101.495594-49.78186 199.797752-133.605272 58.794982-50.132485 105.520905-102.966844 139.627769-157.031827 44.446861-70.444666 66.601542-143.171831 66.601543-216.167122V188.216818c0.003437-21.731868-13.230933-41.133112-33.67374-48.503111zM164.810887 511.757656V226.768368c0-10.848747 6.648123-20.539056 16.847183-24.237805l320.759902-116.692777c2.859999-1.034687 6.142811-1.797812 9.580309-1.797812v427.961745h349.631456c0.020625 0 0.044687 0.464062 0.044688 0.759687-0.06875 93.255909-47.038736 185.679943-139.58652 275.670228-72.273415 70.279666-154.384953 120.436213-196.621503 144.096519-3.915311 2.196562-10.027184 3.231249-13.464683 3.231249V511.867656l-347.190832-0.11z"></path>
                </g>
            </svg>
        <?php endif; ?>
    </div>
</div>
<div class="customer_info relative flex justify-between">
    <ul class="w-1/2">
        <li class="text-sm">
            نام :
            <span id="name_owner"></span>
        </li>
        <li class="text-sm">
            شماره تماس :
            <span id="phone_owner"></span>
        </li>
    </ul>
    <ul class="w-1/2">
        <li class="text-xs">
            نشانی :
            <span id="userAddress_owner"></span>
        </li>
        <!-- <li class="text-xs">
            ماشین :
            <span id="car_owner"></span>
        </li> -->
    </ul>
    <div class="text-xs flex items-center gap-2">
        <img class="rounded-full w-9 h-9 mt-2" src="<?= $profile ?>" alt="">
        <p>
            زمان ثبت:
            <span id="time_owner"></span>
            <br>
            زمان پرینت:
            <span><?= date('H:i'); ?></span>
        </p>
    </div>
</div>
<style>
    .exclude {
        border-radius: 5px;
        background: #000000;
        padding: 0 5px;
        color: white;
    }
</style>

<script>
    function displayOwnerBill() {
        const owner_bill_body = document.getElementById('owner_bill_body');
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

            const price_difference = item.price_per - item.actual_price;

            let price_template = '';

            if (price_difference > 0) {
                price_template = "* " + Math.abs(price_difference) / 10000 + '+';
            }

            if (price_difference < 0) {
                price_template = "* " + Math.abs(price_difference) / 10000 + '-';
            }

            let originalPrice = item.original_price; // assuming `item` is a JS object
            let LRTemplate = '';

            if (originalPrice.includes('(LR)')) {
                LRTemplate = '●';
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
                            <table style="direction:ltr !important; border:none !important" id="owner_${item.id}" class="float-left">
                            </table>
                            <span class="float-left" id="des_${item.id}"></span>
                            <span class="float-left" id="template_${item.id}">
                            ${LRTemplate}
                            ${price_template}

                            </span>
                        </td>
                        <td style="padding:15px 0 !important; width:10px !important" class="text-sm ${item.quantity != 1 ? 'font-semibold' : ''}">
                            <span>${item.quantity}</span>
                        </td>
                        <td class="text-sm text-center">
                            <span>${formatAsMoney((Number(item.price_per)))}</span>
                        </td>
                        <td class="text-sm text-center">
                            <span>${formatAsMoney(payPrice)}</span>
                        </td>
                    </tr>`;
            counter++;
        }
        owner_bill_body.innerHTML = template;
    }

    function displayOwnerCustomer() {
        // Retrieve display name from local storage
        const displayName = sessionStorage.getItem("displayName");

        // Update customer information if display name is available
        if (displayName !== null && displayName !== undefined) {
            // Update customer information if display name is available
            customerInfo.name = displayName;
        }

        // Display customer information on the webpage
        const nameElement = document.getElementById("name_owner");
        const phoneElement = document.getElementById("phone_owner");
        const addressElement = document.getElementById("userAddress_owner");
        // const car_owner = document.getElementById("car_owner");

        nameElement.innerHTML =
            customerInfo.name + (customerInfo.family ? " " + customerInfo.family : "");
        phoneElement.innerHTML = customerInfo.phone;
        if (customerInfo.address && customerInfo.address != "null")
            addressElement.innerHTML = customerInfo.address;
        // if (customerInfo.car && customerInfo.car != "null")
        //     car_owner.innerHTML = customerInfo.car;
    }

    function displayOwnerBillDetails() {
        document.getElementById("billNO_owner").innerHTML = BillInfo.bill_number;
        document.getElementById("date_owner").innerHTML = BillInfo.bill_date.replace(
            /-/g,
            "/"
        );
        document.getElementById("quantity_owner").innerHTML = BillInfo.quantity;
        document.getElementById("totalPrice_owner").innerHTML = formatAsMoney(
            BillInfo.total
        );
        document.getElementById("totalPrice2_owner").innerHTML = formatAsMoney(
            Number(BillInfo.total) - Number(BillInfo.discount)
        );
        document.getElementById("discount_owner").innerHTML = BillInfo.discount;
        document.getElementById("total_in_word_owner").innerHTML = numberToPersianWords(
            BillInfo.total
        );
        document.getElementById("time_owner").innerHTML = now;
        if (document.getElementById("description_owner"))
            document.getElementById("description_owner").innerHTML =
            BillInfo.description.replace(/\n/g, "<br>");
    }
</script>