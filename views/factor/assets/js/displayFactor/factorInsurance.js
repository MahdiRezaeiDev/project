displayBill();
displayCustomer();
displayBillDetails();

function displayBill() {
  let counter = 1;
  let template = ``;
  let totalPrice = 0;

  const excludeBrands = ["اصلی", "GEN", "MOB"];

  for (const item of billItems) {
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

    template += `
        <tr style="padding: 10px !important;" class="even:bg-gray-100">
            <td class="text-sm text-center">
                <span>${counter}</span>
            </td>
            <td class="text-sm">`;

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

    if (factorType == "partner") {
      template += `<span>${nameParts[0]}
                      ${
                        nameParts[1]
                          ? ` - <span class="${excludeClass}">${nameParts[1]}</span>`
                          : ""
                      } 
                 </span>`;
    } else {
      template += `<span>${result}</span>`;
    }

    template += `</td> <td class="text-sm border-r border-gray-800" style="border-left:0px !important;">
                <span>${item.quantity}</span>
            </td>
        </tr> `;
    counter++;
  }
  bill_body.innerHTML = template;
}

function displayCustomer() {
  // Retrieve display name from local storage
  const displayName = sessionStorage.getItem("displayName");

  // Update customer information if display name is available
  if (displayName !== null && displayName !== undefined) {
    // Update customer information if display name is available
    customerInfo.name = displayName;
  }

  // Display customer information on the webpage
  const nameElement = document.getElementById("name");
  const phoneElement = document.getElementById("phone");
  const addressElement = document.getElementById("userAddress");
  // const user_car = document.getElementById("user_car");

  nameElement.innerHTML =
    customerInfo.name + (customerInfo.family ? " " + customerInfo.family : "");
  phoneElement.innerHTML = customerInfo.phone;
  if (customerInfo.address && customerInfo.address != "null")
    addressElement.innerHTML = customerInfo.address;

  // if (customerInfo.car && customerInfo.car != "null")
  //   user_car.innerHTML = customerInfo.car;
}

function displayBillDetails() {
  document.getElementById("billNO").innerHTML = BillInfo.bill_number;
  document.getElementById("date").innerHTML = BillInfo.bill_date.replace(
    /-/g,
    "/"
  );
  // document.getElementById("quantity").innerHTML = BillInfo.quantity;
  // document.getElementById("totalPrice").innerHTML = formatAsMoney(
  //   BillInfo.total
  // );
  // document.getElementById("totalPrice2").innerHTML = formatAsMoney(
  //   Number(BillInfo.total) - Number(BillInfo.discount)
  // );
  // document.getElementById("discount").innerHTML = BillInfo.discount;
  // document.getElementById("total_in_word").innerHTML = numberToPersianWords(
  //   BillInfo.total
  // );
  if (document.getElementById("description") && BillInfo.description != null)
    document.getElementById("description").innerHTML =
      BillInfo.description.replace(/\n/g, "<br>");
}

function copyInfo(element) {
  const info = document.getElementById("name").innerHTML;
  const billNo = document.getElementById("billNO").innerHTML;
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

function handleSaveAsPdfClick() {
  const content = document.getElementById("bill_body_pdf");
  const opt = {
    filename:
      BillInfo.billNO + "-" + customerInfo.name + " " + customerInfo.family ??
      "" + ".pdf",
    image: {
      type: "jpeg",
      quality: 0.98,
    },
    html2canvas: {
      scale: 2,
    },
    jsPDF: {
      unit: "in",
      format: "letter",
      orientation: "portrait",
    },
  };
  html2pdf().set(opt).from(content).save();
}

document.addEventListener("keydown", function (event) {
  if (
    (event.ctrlKey || event.metaKey) &&
    (event.key === "p" || event.keyCode === 80)
  ) {
    event.preventDefault();
  }
});
