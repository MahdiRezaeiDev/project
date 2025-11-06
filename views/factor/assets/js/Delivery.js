async function displayDeliveryModal(element) {
    const billNumber = element.dataset.bill;


    if (!billNumber) {
        showToast("شماره فاکتور نامعتبر است", "error");
        return;
    }

    showToast("در حال بارگذاری اطلاعات فاکتور و ارسال...", "success");


    const deliveryForm = document.getElementById('deliveryForm');
    if (deliveryForm) {
        deliveryForm.reset();
    }

    const billInput = document.getElementById('deliveryBillNumber');
    if (billInput) {
        billInput.value = '';
    }

    const factorInfo = document.getElementById('factorInfo');
    if (factorInfo) {
        factorInfo.innerHTML = '';
    }

    const financialInfo = document.getElementById('financialInfo');
    if (financialInfo) {
        financialInfo.innerHTML = '';
    }


    try {
        const response = await fetch('../../app/api/factor/GetDelivery.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `billNumber=${billNumber}`
        });

        const textResponse = await response.text();
        //test
        //console.log("🧾 پاسخ خام از سرور:", textResponse);

        let jsonParts;
        try {
            jsonParts = JSON.parse(textResponse);
        } catch (e) {
            console.error("🚨 خطا در parse JSON:", e, textResponse);
            showToast("خطا در پردازش داده‌های سرور", "error");
            return;
        }

        // JSON PARTS
        const deliveryData = jsonParts.find(j => j.type === 'delivery');
        const factorData = jsonParts.find(j => j.type === 'factor');
        const paymentsData = jsonParts.find(j => j.type === 'payments');
        const mergedData = jsonParts.find(j => j.type === 'merged');

        window.currentfactorData = factorData.data;
        window.currentdeliveryData = deliveryData.data;
        window.currentpaymentsData = paymentsData.data;
        window.currentallData = mergedData.data;



        if (deliveryData && deliveryData.status === "success" && deliveryData.data) {
            const {
                total,
                paid,
                remaining
            } = calculateFinancials(deliveryData.data, paymentsData);
            fillFinancialInfo(total, paid, remaining);
            fillDeliveryForm(deliveryData.data);
            window.currentDeliveryData = deliveryData.data;

        } else if (factorData && factorData.data) {
            const total = parseFloat(factorData.data.total) || 0;
            const paid = paymentsData && paymentsData.data ? parseFloat(paymentsData.data) || 0 : 0;
            const remaining = Math.max(0, total - paid);

            fillFinancialInfo(total, paid, remaining);
        }

        if (factorData && factorData.data) fillFactorInfo(factorData.data);


        showDeliveryModal();
        showToast("اطلاعات بارگذاری شد ✅", "success");
    } catch (error) {
        console.error("❌ خطا در دریافت اطلاعات:", error);
        showToast("خطا در ارتباط با سرور", "error");
    }

    enablePrintButton()
    const addressInput = document.getElementById('address');

    const address =
        (window.currentallData && window.currentallData.destination) ||
        element.getAttribute('data-address') ||
        '';


    if (addressInput) {
        addressInput.value = address;
    } else {
        console.warn('⚠️ المان addressInput پیدا نشد!');
    }
    window.dataaddress = element.getAttribute('data-address');
}


function calculateFinancials(deliveryData, paymentsData) {
    if (!deliveryData || !deliveryData.items) {
        console.warn("⚠️ هیچ آیتمی برای محاسبه وجود ندارد");
        return {
            total: 0,
            paid: 0,
            remaining: 0
        };
    }

    const items = deliveryData.items;


    const total = items.reduce((sum, item) => {
        const price = parseFloat(item.price_per) || 0;
        const qty = parseFloat(item.quantity) || 0;
        return sum + (price * qty);
    }, 0);

    const paid = paymentsData && paymentsData.data ?
        parseFloat(paymentsData.data) || 0 :
        0;

    const remaining = Math.max(0, total - paid);

    return {
        total,
        paid,
        remaining
    };
}

function fillFinancialInfo(total, paid, remaining) {
    document.getElementById('display_total').innerText =
        new Intl.NumberFormat().format(total) + ' ریال';
    document.getElementById('display_paid').innerText =
        new Intl.NumberFormat().format(paid) + ' ریال';
    document.getElementById('display_remaining').innerText =
        new Intl.NumberFormat().format(remaining) + ' ریال';
}


/* ===============================
    پر کردن اطلاعات فاکتور
   =============================== */
function fillFactorInfo(f) {
    const billEl = document.getElementById('display_billNumber');
    if (billEl) billEl.innerText = f.bill_number || '';

}

/* ===============================
     پر کردن فرم  
   =============================== */
function fillDeliveryForm(d) {
    document.getElementById('deliveryBillNumber').value = d.bill_number || '';
    document.getElementById('deliveryType').value = d.type || '';

    document.getElementById('peymentother').value = d.peymentother || '';


    const deliverycostSelect = document.getElementById('deliverycost');
    const otherCostInput = document.getElementById('deliverycostother');
    if (['70', '100', '150', '200'].includes(String(parseInt(d.delivery_cost)))) {
        deliverycostSelect.value = String(parseInt(d.delivery_cost));
        otherCostInput.style.display = 'none';
        otherCostInput.value = '';
    } else if (d.delivery_cost && d.delivery_cost !== '') {
        deliverycostSelect.value = 'other';
        otherCostInput.style.display = 'block';
        otherCostInput.value = parseInt(d.delivery_cost);
    } else {
        deliverycostSelect.value = '';
        otherCostInput.style.display = 'none';
        otherCostInput.value = '';
    }

    const courierSelect = document.getElementById('courier');
    const courierOther = document.getElementById('courierother');
    if (['اقای امیردوست', 'آقای عباسی'].includes(String(d.courier_name))) {
        courierSelect.value = String(d.courier_name);
        courierOther.style.display = 'none';
        courierOther.value = '';
    } else if (d.courier_name && d.courier_name !== '') {
        courierSelect.value = 'other';
        courierOther.style.display = 'block';
        courierOther.value = d.courier_name;
    } else {
        courierSelect.value = '';
        courierOther.style.display = 'none';
        courierOther.value = '';
    }

    document.getElementById('explain').value = d.description || '';


    const needCallCheckbox = document.getElementById('needcall');
    needCallCheckbox.checked = d.need_call === 'YES';
}


function showDeliveryModal() {
    const modal = document.getElementById('deliveryModal');
    if (modal) modal.classList.remove('hidden');

}



//delivery
document.getElementById('deliverycost').addEventListener('change', function () {
    const otherInput = document.getElementById('deliverycostother');
    if (this.value === 'other') {
        otherInput.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
});

document.getElementById('courier').addEventListener('change', function () {
    const otherInput = document.getElementById('courierother');
    if (this.value === 'other') {
        otherInput.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const peymentField = document.getElementById("peymentother");
    const explainField = document.getElementById("explain");
    const star = document.getElementById("requiredfild");

    peymentField.readOnly = true;
    peymentField.classList.add("bg-gray-100", "cursor-pointer");

    peymentField.addEventListener("click", function (event) {
        event.stopPropagation();
        event.preventDefault();

        if (this.readOnly) {
            this.readOnly = false;
            this.classList.remove("bg-gray-100");
            this.classList.add("bg-white", "border-blue-400");
            showToast("💡 مبلغ قابل ویرایش شد.", "success");
        }
    });

    peymentField.addEventListener("change", function () {
        explainField.required = true;
        explainField.classList.add("border-red-500");
        if (star) star.style.display = "inline";
        showToast("⚠️ لطفاً دلیل تغییر مبلغ را در توضیحات بنویسید.", "warning");
    });


});

async function saveDelivery(event) {
    event.preventDefault();
    const d = window.currentfactorData;

    if (!d) {
        showToast("لطفا تمام بخش ها را تکمیل بکنید", "error");
        return;
    }
    const billNumber = d.bill_number;
    const deliveryType = document.getElementById('deliveryType').value.trim();
    const deliveryCost = document.getElementById('deliverycost').value;
    const deliveryCostOther = document.getElementById('deliverycostother').value.trim();
    const courierSelect = document.getElementById('courier').value;
    const courierOther = document.getElementById('courierother').value.trim();
    const address = document.getElementById('address').value.trim();
    const description = document.getElementById('explain').value.trim();
    const peymentother = document.getElementById('peymentother').value.trim();
    const needCall = document.getElementById('needcall').checked ? 'YES' : 'NO';
    const finalCost = deliveryCost === 'other' ? deliveryCostOther : deliveryCost;
    const finalCourier = courierSelect === 'other' ? courierOther : courierSelect;

    if (!billNumber) {
        showToast('شماره فاکتور یافت نشد.', 'error');
        return;
    }
    if (!address) {
        showToast('لطفا آدرس مقصد را وارد کنید.', 'error');
        return;
    }


    const formData = new FormData();
    formData.append('saveDelivery', '1');
    formData.append('billNumber', billNumber);
    formData.append('deliveryType', deliveryType);
    formData.append('deliverycost', finalCost);
    formData.append('courier_name', finalCourier);
    formData.append('address', address);
    formData.append('description', description);
    formData.append('need_call', needCall);
    formData.append('peymentother', peymentother);

    try {

        const response = await axios.post(
            '../../app/api/factor/DeliveryApi.php',
            formData
        );

        if (response.data.status === 'success') {
            showToast('اطلاعات ارسال با موفقیت ذخیره شد ✅', 'success');

        } else {
            showToast('❌ ' + response.data.message, 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('خطا در ارتباط با سرور.', 'error');
    }
}

function printDeliveryInfo() {

    const d = window.currentfactorData || window.currentdeliveryData;
    const all = window.currentallData;


    if (!d) {
        showToast("لطفا تمام بخش ها را تکمیل بکنید", "error");
        return;
    }

    const address = all.destination || window.dataaddress;
    const buyer = all.kharidar || "——";
    const operator = all.user_family || "——";
    const phone = all.phone || "——";
    const billNumber = all.bill_number || "——";
    const courier = all.courier_name || "——";
    const deliveryCost = all.delivery_cost ?
        new Intl.NumberFormat().format(all.delivery_cost) + "هزار تومان" :
        "——";
    const description = all.description || " ";
    const needCallText =
        all.need_call === "YES" ? "⚠️ حتماً قبل از ارسال با مشتری تماس گرفته شود" : "";

    const f = window.currentfactorData || {};
    const total = f.total ? parseFloat(f.total) : 0;

    const payments = window.currentpaymentsData;
    let paid = 0;

    if (typeof payments === "number") {
        paid = payments;
    } else if (payments && typeof payments === "object") {

        paid = parseFloat(payments.paid || payments.data || 0);
    } else {
        paid = parseFloat(f.paid || 0);
    }

    let remaining = total - paid;

    const newRemainingInput = document.getElementById("peymentother");
    if (newRemainingInput && newRemainingInput.value.trim() !== "") {
        remaining = parseFloat(newRemainingInput.value) || remaining;
    }

    let financialStatus = "";
    if (remaining <= 0) {
        financialStatus = "<div style='color:green; font-weight:bold;'>✅ پرداخت شده است</div>";
    } else {
        financialStatus = `
        <div style="margin-top:5px;">
            <strong>مبلغ باقی‌مانده:</strong>
            ${new Intl.NumberFormat().format(remaining)} ریال
        </div>
    `;
    }

    const statusEl = document.getElementById("display_status");
    if (statusEl) statusEl.innerHTML = financialStatus;

    const printContent = `
    <div id="deliveryPrint" dir="rtl" style="font-family: tahoma;  line-height: 1.8;  margin-top: -50px; width:100%">
                    <h2 style="text-align: center; margin-bottom: 10px;"> اطلاعات ارسال یدک شاپ  </h2>
                    <div style="border:1px solid #ccc; border-radius:8px; padding:15px;text-align: right;">

              
                        <div class="w-full border-gray-300 font-size:17px"><strong>آدرس مقصد:</strong> <span style=" font-size:18px">${address}<span></div>
                        
                        <div class="w-full flex">

  <div class=" w-1/2 bg-gray-50 p-4 rounded-2xl  " style="font-size:17px">
    <p ><strong>نام خریدار:</strong> ${buyer}</p>
    <p><strong>شماره موبایل:</strong> ${phone}</p>
  </div>


  <div class="w-1/2 bg-gray-50 p-4 rounded-2xl " >
    <p style="font-size:14px">شماره فاکتور: ${billNumber}</p>
    <p style="font-size:14px">کاربر ثبت‌کننده: ${operator}</p>
    <p style="font-size:16px"><strong>نام پیک: ${courier}</strong> </p>
    <p style="font-size:16px"><strong>هزینه ارسال:  ${deliveryCost}</strong></p>
  </div>
</div>
<div style="font-size:17px">${financialStatus}</div>

                       

                        ${ needCallText
                        ? `<div style="color:red; border:1px dashed red; padding:5px; margin-top:2px; border-radius:6px; font-size:13px">
                            ${needCallText}
                        </div>`
                        : ""
                        }
 <div class="mt-3 p-3 border-2 border-gray-300 rounded-xl bg-gray-50 text-gray-800 font-size:15px" >
                            <strong class="text-gray-700">توضیحات:</strong>
                            <div class="mt-1 text-sm leading-relaxed" style="font-size:19px">${description || "—"}</div>
                        </div>
                        <div class="grid grid-cols-4 border border-gray-200 bg-gray-50 rounded-md p-4">
                            <div class="col-span-3 flex">
                                <div class="w-8 mr-2 flex flex-col items-center justify-start space-y-2">
                                    <img src="./assets/img/phone-rounded.svg" class="w-4 h-4" alt="phone">
                                    <img src="./assets/img/iphone.svg" class="w-4 h-4" alt="mobile">
                                    <img src="./assets/img/location.svg" class="w-4 h-4" alt="location">
                                </div>
                                <div class="text-xs leading-relaxed">
                                    <p>
                                        <span>021-36619432</span> &nbsp;|&nbsp;
                                        <span>021-36619809</span>
                                    </p>
                                    <p>
                                        <span>0912-0733545</span> &nbsp;|&nbsp;
                                        <span>0912-7204134</span>
                                    </p>
                                    <p class="mt-1">
                                        تهران، مترو بهارستان، میدان بهارستان، خیابان مصطفی خمینی،
                                        <br>کوچه نظامیه، بن‌بست ویژه، پلاک ۴
                                    </p>
                                </div>
                            </div>

                            <div class="col-span-1 flex justify-end items-center space-x-3">
                                <img src="./assets/img/qrcode-factor.svg" class="w-20 h-20 rounded-md border" alt="QR">
                                <img src="./assets/img/YadakShop-logo.png" class="w-20 h-20 rounded-md border" alt="Logo">
                            </div>
                        </div>
                    </div>
                </div>
    `;

    const printArea = document.getElementById("printArea");
    printArea.innerHTML = printContent;
    printArea.style.display = "block";



}

function closeDelivery() {
    const dialog = document.getElementById('dialog');
    const modal = document.getElementById('deliveryModal');
    const printArea = document.getElementById('printArea');
    const form = document.getElementById('deliveryForm');
    const billInput = document.getElementById('deliveryBillNumber');
    const factorInfo = document.getElementById('factorInfo');
    const financialInfo = document.getElementById('financialInfo');

    if (dialog && !dialog.classList.contains('hidden')) {
        dialog.classList.add('hidden');

        if (printArea) {
            printArea.innerHTML = '';
            printArea.style.display = 'none';
        }

    } else if (modal && !modal.classList.contains('hidden')) {
        modal.classList.add('hidden');

        if (form) form.reset();
        if (billInput) billInput.value = '';
        if (factorInfo) factorInfo.innerHTML = '';
        if (financialInfo) financialInfo.innerHTML = '';


    }

    window.currentDeliveryData = null;
}

//active print btn
const form = document.getElementById('deliveryForm');
const printBtn = document.getElementById('printBtn');
const printIcon = document.getElementById('printIcon');
form.addEventListener('input', () => {
    printBtn.disabled = true;
    printIcon.style.opacity = '0.5';
    printIcon.style.cursor = 'not-allowed';
    printIcon.onclick = null; // حذف عملکرد چاپ
});

function enablePrintButton() {
    printBtn.disabled = false;
    printIcon.style.opacity = '1';
    printIcon.style.cursor = 'pointer';
    printIcon.onclick = function () {
        document.getElementById('dialog').classList.remove('hidden');
        printDeliveryInfo();
    };
}
//active print btn

