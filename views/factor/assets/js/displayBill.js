let bill_number = null;

if (factorInfo.partner !== 0) {
  changeLayout("partner");
}

function getBillData(type) {
  document.getElementById("billNO_bill").innerHTML =
    factorInfo.billNO == 0 ? "پیش فاکتور" : factorInfo.billNO;
  previewBill(type);
  displayCustomer();
  displayBillDetails();
}

function previewBill(type) {
  let counter = 1;
  let template = ``;
  let totalPrice = 0;
  const excludeBrands = ["اصلی", "GEN", "MOB"];

  for (const item of factorItems) {
    const payPrice = Number(item.quantity) * Number(item.price_per);
    totalPrice += payPrice;

    const nameParts = item.partName.split("-");

    let excludeClass = "";

    const brandPattern = new RegExp(`\\b(${excludeBrands.join("|")})\\b`, "gu");

    if (nameParts[1]) {
      if (nameParts[1].trim() != "اصلی") {
        const brand = nameParts[1].trim();

        if (!brand.match(brandPattern)) {
          excludeClass = "exclude";
        }
      }
    }

    // Extract the part inside parentheses
    const input = item.partName;
    // Extract the part inside parentheses
    const match = input.match(/\((.*?)\)/);
    const insideParentheses = match ? match[1] : null;

    // Extract the part after the last dash
    const afterLastDash = input.split("-").pop().trim();

    // Determine the result
    const result = insideParentheses
      ? `${insideParentheses} ${customerInfo.car} - ${afterLastDash}`
      : input;

    template += `
            <tr style="padding: 10px !important;" class="even:bg-gray-100">
            <td class="text-sm text-center">
                <span>${counter}</span>
            </td>
            <td class="text-sm">`;

    if (displayLayout == "partner") {
      template += `<span>${nameParts[0]}
                      ${
                        nameParts[1]
                          ? ` - <span class="${excludeClass}">${nameParts[1]}</span>`
                          : ""
                      } 
                 </span>`;
    } else {
      if (type == "yadak" || type == "korea") {
        template += `<span>${result}</span>`;
      } else {
        template += `<span>${item.partName}</span>`;
      }
    }

    template += `</td> <td class="text-sm border-r border-l-2 border-gray-800">
                <span>${item.quantity}</span>
            </td>
            <td class="text-sm">
                <span>${formatAsMoney(Number(item.price_per))}</span>
            </td>
            <td class="text-sm">
                <span>${formatAsMoney(payPrice)}</span>
            </td>
        </tr> `;
    counter++;
  }
  bill_body_result.innerHTML = template;
}

function displayCustomer() {
  document.getElementById("name_bill").innerHTML =
    customerInfo.displayName + " " + customerInfo.family ?? "";
  document.getElementById("phone_bill").innerHTML = customerInfo.phone;
  if (customerInfo.address) {
    document.getElementById("userAddress").innerHTML = customerInfo.address;
  }

  // if (customerInfo.car) {
  //   document.getElementById("car_bill").innerHTML = customerInfo.car;
  // }
}

function displayBillDetails() {
  document.getElementById("date_bill").innerHTML = factorInfo.date.replace(
    /-/g,
    "/"
  );

  document.getElementById("quantity_bill").innerHTML = factorInfo.quantity;
  document.getElementById("totalPrice_bill").innerHTML = formatAsMoney(
    factorInfo.totalPrice
  );

  document.getElementById("totalPrice2").innerHTML = formatAsMoney(
    Number(factorInfo.totalPrice) - Number(factorInfo.discount)
  );

  document.getElementById("discount_bill").innerHTML = factorInfo.discount;
  document.getElementById("total_in_word_bill").innerHTML =
    factorInfo.totalInWords;
}

function displayAsMoney(inputInstance) {
  // Get the original value from the input and remove non-digit characters
  let originalValue = inputInstance.value.trim().replace(/\D/g, "");
  originalValue = originalValue.replace(/^0+/, "");

  // Use regex to insert commas every three digits
  let formattedValue = originalValue.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

  // Update the input value with the formatted value
  inputInstance.value = formattedValue;
}

function copyInfo(element) {
  const info = document.getElementById("name_bill").innerHTML.trim();
  const billNo = document.getElementById("billNO_bill").innerHTML.trim();
  const total_in_word = document.getElementById("totalPrice2").innerHTML.trim();

  const combinedText = `مشتری : ${info} \nشماره فاکتور : ${billNo} \n مبلغ قابل پرداخت: ${total_in_word} ریال`;

  const textarea = document.createElement("textarea");
  textarea.value = combinedText;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand("copy");
  document.body.removeChild(textarea);

  element.src = "./assets/img/complete.svg";

  setTimeout(() => {
    element.src = "./assets/img/copy.svg";
  }, 2000);
}

function changeLayout(layout) {
  const logo_element = document.getElementById("factor_logo");
  const yadak_logo = "./assets/img/logo.png";
  const insurance_logo = "./assets/img/insurance.png";
  const partner_logo = "./assets/img/partner.jpg";
  const korea_logo = "./assets/img/korea.jpg";
  const logo_fiduciary = "./assets/img/fiduciary.webp";
  displayLayout = layout;

  switch (layout) {
    case "yadak":
      logo_element.src = yadak_logo;
      document.getElementById("factor_heading").innerHTML =
        "پیش فاکتور یدک شاپ";
      document.getElementById("factor_description").style.display = "block";
      document.getElementById("factor_address").style.display = "block";
      document.getElementById(
        "factor_phone"
      ).innerHTML = `<span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۷ ۹۳ ۷۰
                                                              </span>
                                                              <span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۴ ۶۷ ۸۸
                                                              </span>
                                                              <span style="direction: ltr !important;">
                                                                  ۰۹۱۲ - ۰۸۱ ۸۳ ۵۵
                                                              </span>`;
      previewBill(layout);
      break;
    case "insurance":
      logo_element.src = insurance_logo;
      document.getElementById("factor_heading").innerHTML = "شرق یدک";
      document.getElementById("factor_description").style.display = "none";
      document.getElementById("factor_address").style.display = "none";
      document.getElementById("factor_phone").innerHTML = `<span>
                                                                  ۷۷۵۴۸۹۴۶ - ۰۲۱
                                                              </span>`;
      previewBill(layout);
      break;
    case "partner":
      logo_element.src = partner_logo;
      document.getElementById("factor_heading").innerHTML = "فاکتور فروش همکار";
      document.getElementById("factor_description").style.display = "block";
      document.getElementById("factor_address").style.display = "block";
      document.getElementById(
        "factor_phone"
      ).innerHTML = `<span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۲
                                                              </span>
                                                              <span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۳
                                                              </span>
                                                              <span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۴
                                                              </span>`;
      previewBill(layout);
      break;
    case "fiduciary":
      logo_element.src = logo_fiduciary;
      document.getElementById("factor_heading").innerHTML = "امانت نامه";
      document.getElementById("factor_description").style.display = "block";
      document.getElementById("factor_address").style.display = "block";
      document.getElementById(
        "factor_phone"
      ).innerHTML = `<span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۲
                                                              </span>
                                                              <span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۳
                                                              </span>
                                                              <span style="direction: ltr !important;">
                                                                  ۰۲۱ - ۳۳ ۹۸ ۷۲ ۳۴
                                                              </span>`;
      previewBill(layout);
      break;
    case "korea":
      logo_element.src = korea_logo;
      document.getElementById("factor_heading").innerHTML =
        "بازرگانی کره اتوپارت";
      document.getElementById("factor_description").style.display = "block";
      document.getElementById("factor_address").style.display = "block";
      document.getElementById("factor_phone").innerHTML = `<span>
                                                                  ۰۲۱ - ۳۳ ۹۲ ۵۴ ۱۱
                                                              </span>
                                                              <span>
                                                                  ۰۹۳۰ - ۳۱۵ ۰۶ ۹۴
                                                              </span>`;
      previewBill(layout);
      break;
  }
}
