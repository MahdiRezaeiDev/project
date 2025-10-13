/* ============================
   Telegram Partner Management
   Full script: includes categories CRUD + stable addPartner
   ============================ */

const address = "../../app/api/callcenter/TelegramPartnerApi.php";
const externalAddressPoint = "http://partners.yadak.center/";

let localData = [];

/* ------------------- Initialization ----------------------- */
function getInitialData() {
  fetchLocalPartnersData().then(function (data) {
    localData = data || { partners: [], categories: [] };
    displayLocalData();
    displayCategories(); // load categories list on init
  });
}
getInitialData();

/* ------------------- Utility: show message ---------------- */
function showMessage(type) {
  // expects elements with IDs: "success" and "error"
  const el = document.getElementById(type);
  if (!el) return;
  el.classList.remove("hidden");
  setTimeout(() => el.classList.add("hidden"), 2000);
}

/* ------------------- SEND MESSAGE SECTION ----------------------- */
function sendMessage() {
  const message_content =
    document.getElementById("message_content")?.value.trim() || "";
  const categories = document.querySelectorAll(".target_partner");
  const receivers = [];
  const names = [];

  categories.forEach((node) => {
    const id = node.getAttribute("data_id");
    const name = (node.innerText || "").split("\n")[0];
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
    .catch((err) => {
      console.error("sendMessage failed:", err);
      showMessage("error");
    });
}

/* ------------------- UPDATE CATEGORY (results area) ----------------------- */
function updateCategory() {
  const categories = document.querySelectorAll(".category_identifier");
  const data = {};

  categories.forEach((node) => {
    const category = node.getAttribute("name");
    data[category] = node.checked;
  });

  for (let brand in data) {
    if (brand !== "all") {
      const el = document.getElementById(brand + "_result");
      if (el) el.innerHTML = "";
    }
  }

  const params = new URLSearchParams();
  params.append("getCategories", "getCategories");
  params.append("data", JSON.stringify(data));

  axios
    .post(address, params)
    .then((response) => {
      const data = response.data || {};
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
              <i class="remove-partner cursor-pointer material-icons text-red-600 pr-1 text-sm" title="حذف از گروه">close</i>
            </span>
          `
          )
          .join("");
      }
      // no attachPartners required -- delegation handles clicks now
    })
    .catch((error) => console.error("updateCategory failed:", error));
}

/* ------------------- REMOVE PARTNER (delegated) ----------------------- */
function removePartner(element) {
  const parent = element.closest(".target_partner");
  if (parent) parent.remove();
}

/* ------------------- DISPLAY LOCAL DATA (partners table) ----------------------- */
function displayLocalData() {
  fetchLocalPartnersData()
    .then((data) => {
      const table = document.getElementById("initial_data");
      if (!table) return;

      const partners = data?.partners || [];
      const categories = data?.categories || [];
      if (!partners.length) {
        table.innerHTML = `<tr><td colspan="${Math.max(
          7,
          categories.length + 4
        )}" class="text-center py-3 text-red-500">موردی برای نمایش وجود ندارد.</td></tr>`;
        return;
      }

      let html = "";
      partners.forEach((user, index) => {
        const related_cats = (user.category_names || "")
          .split(",")
          .map((c) => c.trim());
        const imgUrl = (user.profile || "").replace(
          "http://telegram.yadak.center/",
          externalAddressPoint
        );

        html += `
          <tr class="even:bg-gray-100"
            data-operation="update"
            data-chat="${user.chat_id}"
            data-name="${escapeHtml(user.telegram_partner_name || "")}"
            data-username="${escapeHtml(user.username || "")}"
            data-profile="${escapeHtml(imgUrl)}">
            <td class="p-2 text-center">${index + 1}</td>
            <td class="p-2 text-center">${escapeHtml(
              user.telegram_partner_name || ""
            )}</td>
            <td class="p-2 text-center" style="text-decoration:ltr">${escapeHtml(
              user.username || ""
            )}</td>
            <td class="p-2 text-center"><img class="w-8 h-8 rounded-full mx-auto" src="${escapeHtml(
              imgUrl
            )}" /></td>
        `;

        categories.forEach((cat) => {
          const checked = related_cats.includes(cat.name) ? "checked" : "";
          // NOTE: do not include inline onclick; use delegation
          html += `
            <td class="p-2 text-center">
              <input ${checked} data-section="exist" data-user="${
            user.chat_id
          }" class="exist user-${user.chat_id}"
                type="checkbox" name="${escapeHtml(cat.id)}" />
            </td>`;
        });

        html += `</tr>`;
      });

      table.innerHTML = html;
    })
    .catch((err) => {
      console.error("displayLocalData failed:", err);
    });
}

/* ------------------- FETCH LOCAL PARTNERS ----------------------- */
async function fetchLocalPartnersData() {
  const params = new URLSearchParams();
  params.append("getInitialData", "getInitialData");

  try {
    const response = await axios.post(address, params);
    return response.data || { partners: [], categories: [] };
  } catch (error) {
    console.error("fetchLocalPartnersData error:", error);
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
      const related_cats = existing
        ? (existing.category_names || "").split(",").map((s) => s.trim())
        : [];
      const profile = user.profile_path || "";

      html += `
        <tr class="even:bg-gray-100"
          data-chat="${user.chat_id}"
          data-name="${escapeHtml(user.title || "")}"
          data-username="${escapeHtml(user.username || "")}"
          data-profile="${escapeHtml(profile)}"
          data-operation="check">
          <td class="p-2 text-center">${index + 1}</td>
          <td class="p-2 text-center">${escapeHtml(user.title || "")}</td>
          <td class="p-2 text-center" style="text-decoration:ltr">${escapeHtml(
            user.username || ""
          )}</td>
          <td class="p-2 text-center"><img class="w-8 h-8 rounded-full mx-auto" src="${escapeHtml(
            profile
          )}" /></td>
      `;

      categories.forEach((cat) => {
        const checked = related_cats.includes(cat.name) ? "checked" : "";
        html += `
          <td class="p-2 text-center">
            <input ${checked} data-section="exist" data-user="${
          user.chat_id
        }" class="exist user-${user.chat_id}"
              type="checkbox" name="${escapeHtml(cat.id)}" />
          </td>`;
      });

      html += `</tr>`;
    });

    table.innerHTML = html;
  });
}

/* ------------------- FETCH TELEGRAM CONTACTS API ----------------------- */
let isLoadedTelegramContacts = false;
async function getContacts() {
  const contact = document.getElementById("contact");
  if (!contact) return;

  if (!isLoadedTelegramContacts) {
    contact.innerHTML = `<tr><td colspan="9" class="py-5"><img class="block w-10 mx-auto h-auto" src="./assets/img/loading.png" /></td></tr>`;
  }

  try {
    const params = new URLSearchParams();
    params.append("getContacts", "getContacts");
    const response = await axios.post(
      "http://partners.yadak.center/api/contacts",
      params
    );
    displayTelegramData(response.data.data || []);
    isLoadedTelegramContacts = true;
  } catch (error) {
    console.error("getContacts error:", error);
    contact.innerHTML = `<tr><td colspan="9" class="py-5 text-center text-red-500">اطلاعاتی دریافت نشد، لطفا بعدا تلاش نمایید.</td></tr>`;
  }
}
function hardRefresh() {
  isLoadedTelegramContacts = false;
  getContacts();
}

/* ------------------- ADD PARTNER (robust) ----------------------- */
function addPartner(element) {
  try {
    if (!element) {
      console.warn("addPartner called without element");
      return;
    }
    const tr = element.closest("tr");
    if (!tr) {
      console.warn("addPartner: missing <tr> parent");
      return;
    }

    const section = element.getAttribute("data-section");
    const chat_id = tr.getAttribute("data-chat");
    const name = tr.getAttribute("data-name") || "";
    const username = tr.getAttribute("data-username") || "";
    const profile = tr.getAttribute("data-profile") || "";
    const operation = tr.getAttribute("data-operation") || "update";

    if (!chat_id) {
      console.warn("addPartner: missing chat_id");
      return;
    }

    // use data- attribute selector (robust)
    const authoritySelector = `input[data-section="${section}"][data-user="${chat_id}"]`;
    const authorityList = document.querySelectorAll(authoritySelector);

    const data = {};
    authorityList.forEach((node) => {
      const categoryId = node.getAttribute("name");
      if (categoryId) data[categoryId] = !!node.checked;
    });

    const params = new URLSearchParams();
    params.append("operation", operation);
    params.append("chat_id", chat_id);
    params.append("name", name);
    params.append("username", username);
    params.append("profile", profile);
    params.append("data", JSON.stringify(data));

    // disable the checkbox while request in progress
    const wasDisabled = element.disabled;
    element.disabled = true;

    axios
      .post(address, params)
      .then(() => {
        showMessage("success");
        // Refresh the local table to reflect server state
        setTimeout(displayLocalData, 300);
      })
      .catch((error) => {
        console.error("addPartner failed:", error);
        showMessage("error");
      })
      .finally(() => {
        element.disabled = wasDisabled;
      });
  } catch (err) {
    console.error("addPartner exception:", err);
  }
}

/* ------------------- EVENT DELEGATION ----------------------- */
// checkbox changes (category membership)
document.addEventListener("change", function (e) {
  const t = e.target;
  if (t && t.matches("input[data-section]")) {
    addPartner(t);
  }
});

// remove partner click (delegated)
document.addEventListener("click", function (e) {
  const t = e.target;
  if (!t) return;

  if (t.matches(".remove-partner")) {
    removePartner(t);
    return;
  }

  // category row actions (delete/edit) delegation
  if (t.matches(".cat-delete")) {
    deleteCategory(t);
    return;
  }
  if (t.matches(".cat-edit")) {
    editCategory(t);
    return;
  }
});

/* ------------------- CATEGORIES: display, create, edit, delete ----------------------- */

async function getExistingCategories() {
  const params = new URLSearchParams();
  params.append("getExistingCategories", "getExistingCategories");
  try {
    const response = await axios.post(address, params);
    return response.data || [];
  } catch (error) {
    console.error("getExistingCategories error:", error);
    return [];
  }
}

function displayCategories() {
  getExistingCategories()
    .then((data) => {
      const resultBox = document.getElementById("category_data");
      if (!resultBox) return;
      let template = "";
      let counter = 1;

      // data can be array or object - adapt
      const items = Array.isArray(data) ? data : data.categories || data;

      items.forEach((item) => {
        template += `
          <tr class="even:bg-gray-100 border-none" data-cat="${item.id}">
            <td class="p-2 text-center font-semibold">${counter}</td>
            <td class="p-2 text-center font-semibold" id="target-${
              item.id
            }">${escapeHtml(item.name)}</td>
            <td class="p-2 text-center">
              <i class="cat-delete cursor-pointer material-icons font-semibold text-red-600" data-cat-id="${
                item.id
              }" data-value="${escapeHtml(
          item.name
        )}" title="حذف کتگوری">delete</i>
              <i class="cat-edit cursor-pointer material-icons font-semibold text-blue-400 ml-3" data-cat-id="${
                item.id
              }" data-value="${escapeHtml(
          item.name
        )}" title="ویرایش کتگوری">edit</i>
            </td>
          </tr>
        `;
        counter++;
      });

      if (items.length === 0) {
        template = `<tr><td colspan="3" class="text-center py-3 text-red-500">کتگوری موجود نیست</td></tr>`;
      }

      resultBox.innerHTML = template;
    })
    .catch((err) => console.error("displayCategories failed:", err));
}

// Show edit UI or prompt if no form present
let previous_id = null;
function editCategory(element) {
  const id = element.getAttribute("data-cat-id");
  const value = element.getAttribute("data-value");

  // If DOM edit form exists, populate it
  const editForm = document.getElementById("edit_category");
  const saveForm = document.getElementById("save_category");

  if (
    editForm &&
    document.getElementById("edit_category_name") &&
    document.getElementById("category_id")
  ) {
    // toggle visibility and populate fields
    if (previous_id !== id) {
      previous_id = id;
      editForm.classList.remove("hidden");
      if (saveForm) saveForm.classList.add("hidden");
      document.getElementById("edit_category_name").value = value;
      document.getElementById("category_id").value = id;
    } else {
      // toggle
      if (editForm.classList.contains("hidden")) {
        editForm.classList.remove("hidden");
        if (saveForm) saveForm.classList.add("hidden");
        document.getElementById("edit_category_name").value = value;
        document.getElementById("category_id").value = id;
      } else {
        editForm.classList.add("hidden");
        if (saveForm) saveForm.classList.remove("hidden");
      }
    }
    return;
  }

  // Fallback: use prompt
  const newName = prompt("ویرایش نام کتگوری:", value);
  if (newName !== null) {
    const params = new URLSearchParams();
    params.append("editCategory", "editCategory");
    params.append("id", id);
    params.append("value", newName);

    axios
      .post(address, params)
      .then(() => {
        showMessage("success");
        displayCategories();
      })
      .catch((err) => {
        console.error("editCategory fallback failed:", err);
        showMessage("error");
      });
  }
}

// If you have a form that calls this onsubmit, it should pass event
function editCategoryForm(e) {
  if (e && typeof e.preventDefault === "function") e.preventDefault();
  const id = document.getElementById("category_id")?.value;
  const value = document.getElementById("edit_category_name")?.value;

  if (!id || !value) {
    showMessage("error");
    return;
  }

  const params = new URLSearchParams();
  params.append("editCategory", "editCategory");
  params.append("id", id);
  params.append("value", value);

  axios
    .post(address, params)
    .then(() => {
      showMessage("success");
      displayCategories();
      const editForm = document.getElementById("edit_category");
      if (editForm) editForm.classList.add("hidden");
    })
    .catch((err) => {
      console.error("editCategoryForm failed:", err);
      showMessage("error");
    });
}

function createCategoryForm(e) {
  if (e && typeof e.preventDefault === "function") e.preventDefault();
  const value = document.getElementById("category_name")?.value;
  if (!value) {
    showMessage("error");
    return;
  }

  const params = new URLSearchParams();
  params.append("createCategory", "createCategory");
  params.append("value", value);

  axios
    .post(address, params)
    .then(() => {
      showMessage("success");
      displayCategories();
      // clear form input if any
      const input = document.getElementById("category_name");
      if (input) input.value = "";
    })
    .catch((err) => {
      console.error("createCategoryForm failed:", err);
      showMessage("error");
    });
}

function deleteCategory(element) {
  const id = element.getAttribute("data-cat-id");
  const name = element.getAttribute("data-value") || "";
  const confirmed = confirm(
    `آیا مطمئن هستید که میخواهید دسته‌بندی "${name}" را حذف کنید؟`
  );
  if (!confirmed) return;

  const params = new URLSearchParams();
  params.append("delete_category", "delete_category");
  params.append("id", id);

  axios
    .post(address, params)
    .then(() => {
      showMessage("success");
      displayCategories();
    })
    .catch((err) => {
      console.error("deleteCategory failed:", err);
      showMessage("error");
    });
}

/* ------------------- Helpers ----------------------- */
function escapeHtml(str) {
  if (!str && str !== 0) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}
