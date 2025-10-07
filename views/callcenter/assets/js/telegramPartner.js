const address = "../../app/api/callcenter/TelegramPartnerApi.php";
const externalAddressPoint = "http://partners.yadak.center/";

let localData = [];

/* ------------------- Initialization ----------------------- */
function getInitialData() {
  fetchLocalPartnersData().then(function (data) {
    localData = data;
    displayLocalData();
  });
}

getInitialData();

/* ------------------- SEND MESSAGE SECTION ----------------------- */
function sendMessage() {
  const message_content = document
    .getElementById("message_content")
    .value.trim();
  const categories = document.querySelectorAll(".target_partner");
  const receivers = [];
  const names = [];

  categories.forEach((node) => {
    const id = node.getAttribute("data_id");
    const name = node.innerText.split("\n")[0];
    if (id) {
      receivers.push(id);
      names.push(name);
    }
  });

  const uniqueReceivers = [...new Set(receivers)];

  if (!message_content || uniqueReceivers.length === 0) {
    showMessage("error");
    return;
  }

  const params = new URLSearchParams();
  params.append("action", "sendMessage");
  params.append("message_content", message_content);
  params.append("data", JSON.stringify(uniqueReceivers));

  axios
    .post("http://partners.yadak.center/api/message", params)
    .then(() => {
      const logParams = new URLSearchParams();
      logParams.append("logAction", "log");
      logParams.append("message_content", message_content);
      logParams.append("receivers", JSON.stringify(names));

      return axios.post(address, logParams.toString());
    })
    .then(() => {
      document.getElementById("message_content").value = "";
      document
        .querySelectorAll(".target_partner")
        .forEach((node) => node.remove());
      document
        .querySelectorAll(".category_identifier")
        .forEach((node) => (node.checked = false));
      showMessage("success");
    })
    .catch((err) => console.error(err));
}

function showMessage(type) {
  const message = document.getElementById(type);
  message.classList.remove("hidden");
  setTimeout(() => message.classList.add("hidden"), 2000);
}

/* ------------------- UPDATE CATEGORY ----------------------- */
function updateCategory() {
  const categories = document.querySelectorAll(".category_identifier");
  const data = {};

  categories.forEach((node) => {
    const category = node.getAttribute("name");
    data[category] = node.checked;
  });

  for (let brand in data) {
    if (brand !== "all") {
      document.getElementById(brand + "_result").innerHTML = "";
    }
  }

  const params = new URLSearchParams();
  params.append("getCategories", "getCategories");
  params.append("data", JSON.stringify(data));

  axios
    .post(address, params)
    .then((response) => {
      const data = response.data;
      for (let brand in data) {
        const category = document.getElementById(brand + "_result");
        if (!category) continue;
        category.innerHTML = data[brand]
          .map(
            (item) => `
              <span class="text-sm flex justify-between target_partner items-center rounded-sm bg-gray-700 hover:bg-gray-900 text-white p-1 m-1" data_id="${
                item.chat_id
              }">
                ${item.name.toLowerCase()}
                <i class="cursor-pointer material-icons text-red-600 pr-1 text-sm" onclick="removePartner(this)" title="حذف از گروه">close</i>
              </span>
            `
          )
          .join("");
      }
      attachPartners(data);
    })
    .catch((error) => console.error(error));
}

/* ------------------- REMOVE PARTNER ----------------------- */
function removePartner(element) {
  element.parentElement.remove();
}

/* ------------------- DISPLAY LOCAL DATA ----------------------- */
function displayLocalData() {
  fetchLocalPartnersData().then((data) => {
    const table = document.getElementById("initial_data");
    if (!table) return;

    const partners = data?.partners || [];
    const categories = data?.categories || [];
    let html = "";

    if (partners.length === 0) {
      table.innerHTML = `
        <tr><td colspan="7" class="text-center py-3 text-red-500">موردی برای نمایش وجود ندارد.</td></tr>
      `;
      return;
    }

    partners.forEach((user, index) => {
      const related_cats = user.category_names.split(",");
      const imgUrl = user.profile.replace(
        "http://partners.yadak.center/",
        externalAddressPoint
      );

      html += `
        <tr class="even:bg-gray-100"
          data-operation="update"
          data-chat="${user.chat_id}"
          data-name="${user.telegram_partner_name}"
          data-username="${user.username}"
          data-profile="${user.profile}">
          <td class="p-2 text-center">${index + 1}</td>
          <td class="p-2 text-center">${user.telegram_partner_name}</td>
          <td class="p-2 text-center">${user.username}</td>
          <td class="p-2 text-center"><img class="w-8 h-8 rounded-full mx-auto" src="${imgUrl}" /></td>
      `;

      categories.forEach((cat) => {
        const checked = related_cats.includes(cat.name) ? "checked" : "";
        html += `
          <td class="p-2 text-center">
            <input ${checked} data-section="exist" class="cursor-pointer exist user-${user.chat_id}"
              data-user="${user.chat_id}" type="checkbox" name="${cat.id}"
              onclick="addPartner(this)" />
          </td>`;
      });

      html += "</tr>";
    });

    table.innerHTML = html;
  });
}

/* ------------------- FETCH LOCAL PARTNERS ----------------------- */
async function fetchLocalPartnersData() {
  const params = new URLSearchParams();
  params.append("getInitialData", "getInitialData");

  try {
    const response = await axios.post(address, params);
    return response.data;
  } catch (error) {
    console.error(error);
    return { partners: [], categories: [] };
  }
}

/* ------------------- DISPLAY TELEGRAM CONTACTS ----------------------- */
function displayTelegramData(data) {
  fetchLocalPartnersData().then((items) => {
    const partners = items.partners || [];
    const categories = items.categories || [];
    const table = document.getElementById("contact");
    if (!table) return;

    let html = "";
    data.forEach((user, index) => {
      const existing = partners.find((p) => p.chat_id == user.chat_id);
      const related_cats = existing ? existing.category_names.split(",") : [];

      html += `
        <tr class="even:bg-gray-100"
          data-chat="${user.chat_id}"
          data-name="${user.title}"
          data-username="${user.username}"
          data-profile="${user.profile_path}"
          data-operation="check">
          <td class="p-2 text-center">${index + 1}</td>
          <td class="p-2 text-center">${user.title}</td>
          <td class="p-2 text-center">${user.username}</td>
          <td class="p-2 text-center"><img class="w-8 h-8 rounded-full mx-auto" src="${
            user.profile_path
          }" /></td>
      `;

      categories.forEach((cat) => {
        const checked = related_cats.includes(cat.name) ? "checked" : "";
        html += `
          <td class="p-2 text-center">
            <input ${checked} data-section="exist" class="cursor-pointer exist user-${user.chat_id}"
              data-user="${user.chat_id}" type="checkbox" name="${cat.id}"
              onclick="addPartner(this)" />
          </td>`;
      });

      html += "</tr>";
    });

    table.innerHTML = html;
  });
}

/* ------------------- FETCH TELEGRAM CONTACTS ----------------------- */
let isLoadedTelegramContacts = false;

async function getContacts() {
  const contact = document.getElementById("contact");
  if (!contact) return;

  if (!isLoadedTelegramContacts) {
    contact.innerHTML = `
      <tr><td colspan="9" class="py-5">
        <img class="block w-10 mx-auto h-auto" src="./assets/img/loading.png" />
      </td></tr>
    `;
  }

  try {
    const params = new URLSearchParams();
    params.append("getContacts", "getContacts");

    const response = await axios.post(
      "http://partners.yadak.center/api/contacts",
      params
    );
    displayTelegramData(response.data.data);
    isLoadedTelegramContacts = true;
  } catch (error) {
    console.error(error);
    contact.innerHTML = `
      <tr><td colspan="9" class="py-5 text-center text-red-500">
        اطلاعاتی دریافت نشد، لطفا بعدا تلاش نمایید.
      </td></tr>
    `;
  }
}

function hardRefresh() {
  isLoadedTelegramContacts = false;
  getContacts();
}

/* ------------------- ADD PARTNER ----------------------- */
function addPartner(element) {
  const tr = element.closest("tr");
  if (!tr) return;

  const section = element.getAttribute("data-section");
  const chat_id = tr.getAttribute("data-chat");
  const name = tr.getAttribute("data-name");
  const username = tr.getAttribute("data-username");
  const profile = tr.getAttribute("data-profile");
  const operation = tr.getAttribute("data-operation");

  const relatedBoxes = document.querySelectorAll(`.${section}.user-${chat_id}`);
  const data = {};

  relatedBoxes.forEach((box) => {
    const categoryId = box.getAttribute("name");
    data[categoryId] = box.checked;
  });

  const params = new URLSearchParams();
  params.append("operation", operation);
  params.append("chat_id", chat_id);
  params.append("name", name);
  params.append("username", username);
  params.append("profile", profile);
  params.append("data", JSON.stringify(data));

  axios
    .post(address, params)
    .then(() => {
      showMessage("success");
      displayLocalData(); // Refresh after saving
    })
    .catch((error) => {
      console.error("addPartner failed:", error);
      showMessage("error");
    });
}
