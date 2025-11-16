// Global controllers for operations messages
const form_success = document.getElementById("form_success");
const form_error = document.getElementById("form_error");
// Global price variable
let price = null;

function copyToClipboard(text) {
  if (window.clipboardData && window.clipboardData.setData) {
    // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
    return window.clipboardData.setData("Text", text);
  } else if (
    document.queryCommandSupported &&
    document.queryCommandSupported("copy")
  ) {
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

// A function to update the global price while typing in the input field
function update_price(element) {
  price = element.value;
  const partNumber = element.getAttribute("data-code").split("-")[0];

  const targetRelation = element.getAttribute("data-target");

  // Step 2: Select all elements with the same data-relation attribute
  const elementsWithSameDataRelation = document.querySelectorAll(
    `[data-relation="${targetRelation}"]`
  );

  // Step 3: Iterate through the selected elements and update their innerHTML
  elementsWithSameDataRelation.forEach((element) => {
    // Update the innerHTML as needed
    element.innerHTML = price; // Replace with your desired content
  });
}

// A function to set the price to we don't have
function donotHave(element) {
  element.disabled = true;
  element.innerHTML = "ثبت شد";

  price = "موجود نیست";
  part = element.getAttribute("data-part");
  const input = document.getElementById(part + "-price");
  input.value = price;
  const partNumber = element.getAttribute("data-code").split("-")[0];
  const target = document.getElementById(partNumber + "-append");
  target.innerHTML = price;
  createRelation(element, "not");
  input.value = null;
}

// A function to send a request in order to ask the price for specific code
function askPrice(element) {
  element.disabled = true;
  element.innerHTML = "ارسال شد";

  setTimeout(() => {
    element.disabled = false;
    element.innerHTML = `
        ارسال به نیایش
        <i class="material-icons text-green-500 font-sm px-1">check_circle</i>
        `;
  }, 5000);

  // Accessing the form fields to get thier value for an ajax store operation
  const partNumber = element.getAttribute("data-part");
  const user_id = element.getAttribute("data-user");
  const customer_id = document.getElementById("customer_id").value;

  const params = new URLSearchParams();
  params.append("askPrice", "askPrice");
  params.append("partNumber", partNumber);
  params.append("customer_id", customer_id);
  params.append("user_id", user_id);

  axios
    .post("../../app/api/callcenter/OrderedPriceApi.php", params)
    .then(function (response) {
      if (response.data == true) {
        form_success.style.bottom = "10px";
        setTimeout(() => {
          form_success.style.bottom = "-300px";
        }, 2000);
      } else {
        form_error.style.bottom = "10px";
        setTimeout(() => {
          form_error.style.bottom = "-300px";
        }, 2000);
      }
    })
    .catch(function (error) {});
}

// A function to create the relationship
function createRelation(e, button = null) {
  e.disabled = true;
  if (button) {
    setTimeout(() => {
      e.disabled = false;
      e.innerHTML = `
        موجود نیست
        <i class="material-icons text-green-500 font-sm px-1">check_circle</i>
        `;
    }, 5000);
  } else {
    setTimeout(() => {
      e.disabled = false;
      e.innerHTML = `
            ثبت قیمت
            <i class="material-icons text-green-500 font-sm px-1">check_circle</i>`;
    }, 5000);
  }

  // Accessing the form fields to get thier value for an ajax store operation
  const partNumber = e.getAttribute("data-part");
  const brands = e.getAttribute("data-brands");
  const customer_id = document.getElementById("customer_id").value;
  const notification_id = document.getElementById("notification_id").value;
  const relation_id = e.getAttribute("data-target");
  const code = e.getAttribute("data-code");

  let goodPrice = document.getElementById(partNumber + "-price").value;
  const resultBox = document.getElementById("price-" + partNumber);

  goodPrice = goodPrice.replace(/\\/g, "/");

  // Defining a params instance to be attached to the axios request
  const params = new URLSearchParams();
  params.append("store_price", "store_price");
  params.append("partNumber", partNumber);
  params.append("brands", brands);
  params.append("customer_id", customer_id);
  params.append("notification_id", notification_id);
  params.append("relation_id", relation_id);
  params.append("price", goodPrice);
  params.append("code", code);

  axios
    .post("../../app/api/callcenter/OrderedPriceApi.php", params)
    .then(function (response) {
      if (response.data) {
        form_success.style.bottom = "10px";
        goodPrice.value = null;
        setTimeout(() => {
          form_success.style.bottom = "-300px";
          resultBox.innerHTML = response.data;
        }, 2000);
      } else {
        form_error.style.bottom = "10px";
        setTimeout(() => {
          form_error.style.bottom = "-300px";
          location.reload();
        }, 2000);
      }
    })
    .catch(function (error) {});
}

// A function to set the price while clicking on the prices table
function setPrice(element) {
  newPrice = element.getAttribute("data-price");
  part = element.getAttribute("data-part");
  const input = document.getElementById(part + "-price");
  input.value = newPrice;
  price = newPrice;

  const targetRelation = element.getAttribute("data-target");

  // Step 2: Select all elements with the same data-relation attribute
  const elementsWithSameDataRelation = document.querySelectorAll(
    `[data-relation="${targetRelation}"]`
  );

  // Step 3: Iterate through the selected elements and update their innerHTML
  elementsWithSameDataRelation.forEach((element) => {
    // Update the innerHTML as needed
    element.innerHTML = price; // Replace with your desired content
  });
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
      if (tdElements[i].textContent.trim() !== "content_copy") {
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
      text += item.join(" : ");
      text += "\n";
    }
    copyToClipboard(text.trim());
    // Alert the copied text
    elem.src = `./assets/img/complete.svg`;
    setTimeout(() => {
      elem.src = `./assets/img/all.svg`;
    }, 1500);
  } catch (e) {
    console.log(e);
  }
}

// A function to copy content to clipboard
function copyItemsWith(elem) {
  try {
    // Get the text field
    let parentElement = document.getElementById("priceReport");

    let tdElements = parentElement.getElementsByTagName("td");
    let tdTextContent = [];

    const elementLength = tdElements.length;

    const dash = ["موجود نیست", "نیاز به بررسی"];
    const space = ["کد اشتباه", "نیاز به قیمت"];

    for (let i = 0; i < elementLength; i++) {
      if (tdElements[i].textContent.trim() !== "content_copy") {
        let text = "";
        if (dash.includes(tdElements[i].textContent.trim())) {
          text = "skip";
        } else if (space.includes(tdElements[i].textContent.trim())) {
          text = "skip";
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

    const filteredResult = finalResult.filter((item) => item[1] !== "skip");

    // Copy the text inside the text field
    let text = "";
    for (let item of filteredResult) {
      text += item.join(" : ");
      text += "\n";
    }
    copyToClipboard(text.trim());
    // Alert the copied text
    elem.innerHTML = `done`;
    setTimeout(() => {
      elem.innerHTML = `content_copy`;
    }, 1500);
  } catch (e) {
    console.log(e);
  }
}

// A function to copy content to clipboard
function copyItemsWithout(elem) {
  try {
    // Get the table parent element
    const parentElement = document.getElementById("priceReport");

    if (!parentElement) {
      console.error("Element with ID 'priceReport' not found.");
      return;
    }

    const tdElements = parentElement.getElementsByTagName("td");
    const tdTextContent = [];
    const forbiddenPrices = ["موجود نیست", "نیاز به بررسی", "نیاز به قیمت"];

    // Extract text content from table cells
    for (let td of tdElements) {
      const elementText = td.textContent.trim();
      if (elementText && elementText !== "content_copy") {
        tdTextContent.push(elementText);
      }
    }

    // Create chunks of two elements (code and price)
    const chunkSize = 2;
    const chunks = [];
    for (let i = 0; i < tdTextContent.length; i += chunkSize) {
      chunks.push(tdTextContent.slice(i, i + chunkSize));
    }

    // Filter chunks where the price matches forbidden prices
    const filteredCodes = chunks
      .filter(
        (chunk) => chunk.length === 2 && forbiddenPrices.includes(chunk[1])
      )
      .map((chunk) => chunk[0]); // Extract only the codes

    // Copy the filtered codes to clipboard
    const textToCopy = filteredCodes.join("\n");
    copyToClipboard(textToCopy.trim());

    // Change the icon to indicate success
    elem.src = `./assets/img/complete.svg`;
    setTimeout(() => {
      elem.src = `./assets/img/forbidden.svg`;
    }, 1500);
  } catch (error) {
    console.error("An error occurred:", error);
  }
}

function copyItemPrice(elem) {
  // Get the parent <td> element
  var parentTd = elem.parentNode;

  // Get the siblings <td> elements
  var sibling1 = parentTd.previousElementSibling; // price
  var sibling2 = sibling1.previousElementSibling; // code

  // Retrieve the text content of the sibling <td> elements (ignores HTML tags like <span>)
  var sibling1Text = sibling1.textContent.trim();
  var sibling2Text = sibling2.textContent.trim();

  const dash = ["موجود نیست", "نیاز به بررسی"];
  const space = ["کد اشتباه", "نیاز به قیمت"];

  let value = "";
  if (dash.includes(sibling1Text)) {
    value = "-";
  } else if (space.includes(sibling1Text)) {
    value = " ";
  } else {
    value = sibling1Text;
  }

  let text = sibling2Text + " : " + value;

  // Copy to clipboard
  copyToClipboard(text);

  // Visual feedback
  elem.innerHTML = `done`;
  setTimeout(() => {
    elem.innerHTML = `content_copy`;
  }, 1500);
}

function deleteGivenPrice(element) {
  const partNumber = element.getAttribute("data-part");
  const brands = element.getAttribute("data-brands");
  const id = element.getAttribute("data-del");
  const relation_id = element.getAttribute("data-target");
  // Accessing the form fields to get thier value for an ajax store operation
  const customer_id = document.getElementById("customer_id").value;
  const notification_id = document.getElementById("notification_id").value;
  const code = element.getAttribute("data-code");
  const resultBox = document.getElementById("price-" + partNumber);
  // Defining a params instance to be attached to the axios request

  const params = new URLSearchParams();
  params.append("delete_price", "delete_price");
  params.append("partNumber", partNumber);
  params.append("brands", brands);
  params.append("customer_id", customer_id);
  params.append("notification_id", notification_id);
  params.append("relation_id", relation_id);
  params.append("code", code);
  params.append("id", id);

  axios
    .post("../../app/api/callcenter/OrderedPriceApi.php", params)
    .then(function (response) {
      if (response.data) {
        resultBox.innerHTML = response.data;
      } else {
      }
    })
    .catch(function (error) {});
}

function closeTab() {
  // Set up a timeout to close the tab after 2 minutes (120,000 milliseconds)
  setTimeout(function () {
    // Try to close the window (tab)
    // This may not work if the window was not opened by a script or if the browser blocks the action.
    window.close();
  }, 60000);
}

function appendBrand(element) {
  const brand = element.getAttribute("data-brand");
  const part = element.getAttribute("data-part");
  const input = document.getElementById(part + "-price");
  input.value += " " + brand;

  const targetRelation = element.getAttribute("data-target");

  // Step 2: Select all elements with the same data-relation attribute
  const elementsWithSameDataRelation = document.querySelectorAll(
    `[data-relation="${targetRelation}"]`
  );

  // Step 3: Iterate through the selected elements and update their innerHTML
  elementsWithSameDataRelation.forEach((element) => {
    // Update the innerHTML as needed
    element.innerHTML += " " + brand; // Replace with your desired content
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const accordionHeaders = document.querySelectorAll(".accordion-header");

  accordionHeaders.forEach((header) => {
    const content = header.nextElementSibling;

    header.addEventListener("click", function () {
      if (content.style.maxHeight !== "1000vh") {
        content.style = "max-height:1000vh;";
      } else {
        content.style = "max-height:0vh;";
      }
    });
  });
});

function telegram(e) {
  setTimeout(() => {
    e.disabled = false;
    e.innerHTML = `
            ثبت قیمت
            <i class="material-icons text-green-500 font-sm px-1">check_circle</i>`;
  }, 5000);

  // Accessing the form fields to get thier value for an ajax store operation
  const partNumber = e.getAttribute("data-part");
  const customer_id = e.getAttribute("data-customer");
  const notification_id = document.getElementById("notification_id").value;
  const code = e.getAttribute("data-code");
  const relation_id = e.getAttribute("data-target");

  let goodPrice = document.getElementById(partNumber + "-price").value;
  const resultBox = document.getElementById("price-" + partNumber);

  // Defining a params instance to be attached to the axios request
  const params = new URLSearchParams();
  params.append("store_price", "store_price");
  params.append("partNumber", partNumber);
  params.append("customer_id", 2);
  params.append("notification_id", notification_id);
  params.append("price", goodPrice);
  params.append("code", code);
  params.append("relation_id", relation_id);
  goodPrice = goodPrice.replace(/\\/g, "/");

  axios
    .post("../../app/api/callcenter/OrderedPriceApi.php", params)
    .then(function (response) {
      if (response.data) {
        form_success.style.bottom = "10px";
        goodPrice.value = null;
        setTimeout(() => {
          form_success.style.bottom = "-300px";
          resultBox.innerHTML = response.data;
        }, 2000);
      } else {
        form_error.style.bottom = "10px";
        setTimeout(() => {
          form_error.style.bottom = "-300px";
          location.reload();
        }, 2000);
      }
    })
    .catch(function (error) {});
  sendMessage(customer_id, code, goodPrice);
}

function sendMessage(receiver, code, price) {
  // Defining a params instance to be attached to the axios request
  const params = new URLSearchParams();
  params.append("sendMessage", "sendMessage");
  params.append("receiver", receiver);
  params.append("code", code);
  params.append("price", price);

  axios
    .post("http://auto.yadak.center/", params)
    .then(function (response) {
      if (response.data) {
        form_success.style.bottom = "10px";
        setTimeout(() => {
          form_success.style.bottom = "-300px";
        }, 2000);
      } else {
      }
    })
    .catch(function (error) {});
}

function onScreen(element) {
  const section = document.getElementById(element.getAttribute("data-move"));
  window.scrollTo(0, section.getBoundingClientRect().top - 70);
}

const elementsWithDataTarget = document.querySelectorAll("[data-relation]");

// Get all elements with the data-relation attribute
const elementsWithDataRelation = document.querySelectorAll("[data-relation]");

// Create an object to store the HTML content by relation
const htmlByRelation = {};

// Loop through the selected elements
elementsWithDataRelation.forEach((element) => {
  const relation = element.getAttribute("data-relation");
  const innerHTML = element.innerHTML;

  if (!htmlByRelation[relation]) {
    // Store the inner HTML of the first element in the group
    htmlByRelation[relation] = innerHTML;
  } else {
    // Set the inner HTML of subsequent elements in the group
    element.innerHTML = htmlByRelation[relation];
  }
});

function addSelectedGood(partNumber, element) {
  element.style.display = "none";
  const params = new URLSearchParams();
  params.append("selectedGoodForMessage", "selectedGoodForMessage");
  params.append("partNumber", partNumber);

  axios
    .post("../../app/api/telegram/telegramApi.php", params)
    .then(function (response) {
      if (response.data) {
        form_success.style.bottom = "10px";
        setTimeout(() => {
          form_success.style.bottom = "-300px";
        }, 2000);
      } else {
        form_error.style.bottom = "10px";
        setTimeout(() => {
          form_error.style.bottom = "-300px";
        }, 2000);
      }
    })
    .catch(function (error) {});
}

function deleteGood(partNumber, element) {
  element.style.display = "none";
  var params = new URLSearchParams();
  params.append("deleteGood", "deleteGood");
  params.append("partNumber", partNumber);

  axios
    .post("../../app/api/telegram/telegramApi.php", params)
    .then(function (response) {
      const data = response.data;
      console.log(data);

      if (response.data) {
        form_success.style.bottom = "10px";
        setTimeout(() => {
          form_success.style.bottom = "-300px";
        }, 2000);
      } else {
        form_error.style.bottom = "10px";
        setTimeout(() => {
          form_error.style.bottom = "-300px";
        }, 2000);
      }
    })
    .catch(function (error) {
      console.log(error);
    });
}

// A function to show the existing report of specific good report
const seekExist = (e) => {
  const element = e;
  if (element.hasAttribute("data-key")) {
    const partNumber = element.getAttribute("data-key");
    const brand = element.getAttribute("data-brand");

    const target = document.getElementById(partNumber + "-" + brand);
    target.style.display = "block";
  }
};

// A function to hide the existing report of specific good report
const closeSeekExist = (e) => {
  const element = e;
  if (element.hasAttribute("data-key")) {
    const partNumber = element.getAttribute("data-key");
    const brand = element.getAttribute("data-brand");

    const target = document.getElementById(partNumber + "-" + brand);
    target.style.display = "none";
  }
};

// A function to show the part number tooltip
function showToolTip(element) {
  partNumber = element.getAttribute("data-part");
  targetElement = document.getElementById(partNumber + "-google");

  targetElement.style.display = "flex";
  targetElement.style.gap = "5px";
}

// A function to hide the part number tooltip
function hideToolTip(element) {
  partNumber = element.getAttribute("data-part");
  targetElement = document.getElementById(partNumber + "-google");

  targetElement.style.display = "none";
  targetElement.style.gap = "5px";
}

// A function to set the limit for specific goods to display an alert
// for quantity less then specified limit
function setLimitAlert(e) {
  e.preventDefault();
  const formId = "f-" + e.target.getAttribute("data-form");
  const targetForm = document.getElementById(formId);

  const id = targetForm.querySelector("#id").value;
  const type = targetForm.querySelector("#type").value;
  const operation = targetForm.querySelector("#operation").value;
  const original = targetForm.querySelector("#original").value;
  const fake = targetForm.querySelector("#fake").value;
  const original_all = targetForm.querySelector("#original_all").value;
  const fake_all = targetForm.querySelector("#fake_all").value;

  const params = new URLSearchParams();
  params.append("id", id);
  params.append("type", type);
  params.append("operation", operation);
  params.append("original", original);
  params.append("fake", fake);
  params.append("original_all", original_all);
  params.append("fake_all", fake_all);

  axios
    .post("../../app/api/callcenter/GoodLimitAPI.php", params)
    .then(function (response) {
      if (response.data == true) {
        const form_success = document.getElementById("form_success");
        form_success.style.bottom = "10px";
        setTimeout(() => {
          form_success.style.bottom = "-300px";
        }, 2000);
      } else {
        const form_error = document.getElementById("form_error");
        form_error.style.bottom = "10px";
        setTimeout(() => {
          form_error.style.bottom = "-300px";
        }, 2000);
      }
    })
    .catch(function (error) {});
}

function copyPartNumber(element, partNumber) {
  element.classList.remove("bg-gray-800");
  element.classList.add("bg-green-800");
  copyToClipboard(partNumber);
}
