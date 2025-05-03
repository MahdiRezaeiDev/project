const address = "../../app/api/callcenter/TelegramPartnerApi.php";
const externalAddressPoint = "http://partners.yadak.center/";

let localData = [];
function getInitialData() {
  fetchLocalPartnersData().then(function (data) {
    localData = data;
  });
}

getInitialData();

/* ------------------- Related to the send Message Section   ----------------------- */
function sendMessage() {
  const message_content = document.getElementById("message_content").value;

  const categories = document.querySelectorAll(".target_partner");
  const data = [];
  const names = [];

  for (const node of categories) {
    const authority = node.getAttribute("data_id");
    const name = node.innerText.split("\n")[0];
    names.push(name);
    data.push(authority);
  }

  const receivers = data.filter((item, index, self) => {
    return self.indexOf(item) === index;
  });

  if (message_content.length > 0 && receivers.length > 0) {
    const params = new URLSearchParams();
    params.append("action", "sendMessage");
    params.append("message_content", message_content);
    params.append("data", JSON.stringify(receivers));

    axios
      .post(externalAddressPoint, params)
      .then(function (response) {
        console.log(response.data);
      })
      .catch(function (error) {});

    const logParams = new URLSearchParams();
    logParams.append("logAction", "log");
    logParams.append("message_content", message_content);
    logParams.append("receivers", JSON.stringify(names));

    axios
      .post(address, logParams.toString())
      .then(function (response) {
        document.getElementById("message_content").value = null;
        const message = document.getElementById("success");

        const target_partners = document.querySelectorAll(".target_partner");

        const category_identifier = document.querySelectorAll(
          ".category_identifier"
        );
        for (const node of target_partners) {
          node.parentNode.removeChild(node);
        }
        for (const node of category_identifier) {
          node.checked = false;
        }

        message.classList.remove("hidden");
        setTimeout(() => {
          message.classList.add("hidden");
        }, 2000);
      })
      .catch(function (error) {
        console.log(error);
        // window.location.reload();
      });
  } else {
    const message = document.getElementById("error");
    message.classList.remove("hidden");
    setTimeout(() => {
      message.classList.add("hidden");
    }, 2000);
  }
  // window.location.reload();
}

// update the contacts based on the selected categories
function updateCategory(element) {
  const categories = document.querySelectorAll(".category_identifier");
  const data = {};

  for (const node of categories) {
    const category = node.getAttribute("name");
    const isChecked = node.checked;
    data[category] = isChecked;
  }

  for (let brand in data) {
    if (brand !== "all") {
      const category = document.getElementById(brand + "_result");
      category.innerHTML = null;
    }
  }

  const params = new URLSearchParams();
  params.append("getCategories", "getCategories");
  params.append("data", JSON.stringify(data));

  axios
    .post(address, params)
    .then(function (response) {
      const data = response.data;
      for (let brand in data) {
        const category = document.getElementById(brand + "_result");
        category.innerHTML = null;
        for (let item of data[brand]) {
          category.innerHTML += `
                    <span class="text-sm flex justify-between target_partner items-center rounded-sm bg-gray-700 hover:bg-gray-900 text-white p-1 m-1" data_id ="${
                      item.chat_id
                    }">
                    ${item.name.toLowerCase()}
                    <i class="cursor-pointer material-icons text-red-600 pr-1 text-sm" onclick="removePartner(this)" title="حذف از گروه ارسالی پیام">close</i>
                    </span>`;
        }
      }
      attachPartners(response.data);
    })
    .catch(function (error) {});
}

// remove partners from the list
function removePartner(element) {
  const parentElement = element.parentElement;
  parentElement.remove();
}

// this function displays the list of previously selected partners
function displayLocalData() {
  fetchLocalPartnersData().then(function (data) {
    const initial_data = document.getElementById("initial_data");
    let template = "";
    let counter = 1;
    const partners = data["partners"];
    const categories = data["categories"];
    if (partners.length > 0) {
      for (let user of partners) {
        const related_cats = user.category_names.split(",");
        // Substring to be replaced
        var search = "http://partners.yadak.center/";

        // Replace the substring
        var newUrl = user.profile.replace(search, externalAddressPoint);
        template += `
        <tr class="even:bg-gray-100" 
            data-operation='update'
            data-chat="${user.chat_id}" 
            data-name=" ${user.telegram_partner_name}" 
            data-username="${user.username}" 
            data-profile="${user.profile}">
                <td class="p-2 text-center">${counter} </td>
                <td class="p-2 text-center">${user.telegram_partner_name}</td>
                <td class="p-2 text-center" style="text-decoration:ltr">${user.username}</td>
                <td class="p-2 text-center"> <img class="w-8 h-8 rounded-full mx-auto d-block" src='${newUrl}' /> </td>`;
        for (let cat of categories) {
          template += ` 
                  <td class="p-2 text-center">
                      <input ${
                        related_cats.includes(cat.name) == 1 ? "checked" : ""
                      } data-section="exist" class="cursor-pointer 
                      exist user-${user.chat_id}" data-user="${
            user.chat_id
          }" type="checkbox" name="${cat.id}" 
                      onclick="addPartner(this)" />
                  </td>
                  `;
        }
        template += "  </tr>";
        counter += 1;
      }
    } else {
      template = `<tr>
      <td colspan="7" class="text-center py-3 text-red-500">
      موردی برای نمایش وجود ندارد.
      </td>
      </tr>`;
    }
    initial_data.innerHTML = template;
  });
}

/** ------------------ Related to the Fetching new Contact from the Telegram section --------------- */

const contact = document.getElementById("results_new"); // Result box for displaying data coming from Telegram
let isLoadedTelegramContacts = false; // Whether the contact has been loaded

// Define a function to fetch local partners data
async function fetchLocalPartnersData() {
  const params = new URLSearchParams();
  params.append("getInitialData", "getInitialData");

  try {
    const response = await axios.post(address, params);
    return response.data;
  } catch (error) {
    console.log(error);
    return null;
  }
}

// Define a function to display the contact data
function displayTelegramData(data) {
  let template = ``;
  let counter = 1;

  fetchLocalPartnersData().then(function (items) {
    // Convert chat_id to integers
    const partners = items["partners"];
    const categories = items["categories"];

    data.forEach(function (user) {
      const foundObject = partners.find((item) => item.chat_id == user.chat_id);
      template += `
        <tr class="even:bg-gray-100" 
          data-chat="${user.chat_id}"
          data-name="${user.title}"
          data-username="${user.username}"
          data-profile="${user.profile_path}"
          data-operation="check">
          <td class="p-2 text-center">${counter}</td>
          <td class="p-2 text-center">${user.title}</td>
          <td class="p-2 text-center" style="text-decoration:ltr">${user.username}</td>
          <td class="p-2 text-center"><img class="w-8 h-8 rounded-full mx-auto d-block" src="${user.profile_path}" /></td>`;
      for (let cat of categories) {
        related_cats = foundObject ? foundObject.category_names.split(",") : [];
        template += ` 
                    <td class="p-2 text-center">
                        <input ${
                          related_cats.includes(cat.name) == 1 ? "checked" : ""
                        } data-section="exist" class="cursor-pointer 
                        exist user-${user.chat_id}" data-user="${
          user.chat_id
        }" type="checkbox" name="${cat.id}" 
                        onclick="addPartner(this)" />
                    </td>
                    `;
      }

      template += `</tr>`;
      counter += 1;
    });
    // Assuming you have an element with the ID 'contact' to display the data
    const contact = document.getElementById("contact");
    if (contact) {
      contact.innerHTML = template;
    }
  });
}

// Define a function to get and display contacts
async function getContacts() {
  if (!isLoadedTelegramContacts) {
    // Assuming you have an element with the ID 'contact' to display the loading animation
    const contact = document.getElementById("contact");
    if (contact) {
      contact.innerHTML = `
        <tr>
          <td colspan="9" class="py-5">
            <img class='block w-10 mx-auto h-auto' src="./assets/img/loading.png" />
          </td>
        </tr>
      `;
    }

    try {
      // Make the Axios request to fetch contact data
      const params = new URLSearchParams();
      params.append("getContacts", "getContacts");
      const response = await axios.post(externalAddressPoint, params);
      displayTelegramData(response.data);
      isLoadedTelegramContacts = true;
    } catch (error) {
      console.log(error);
      // Display an error message
      if (contact) {
        contact.innerHTML = `
          <tr>
            <td colspan="9" class="py-5">
              <p class="text-center text-bold text-red-500 ">اطلاعاتی دریافت نشد, لطفا لحظاتی بعد تلاش نمایید</p>
            </td>
          </tr>`;
      }
    }
  }
}

// Define a function to fetch new contacts from telegram account
function hardRefresh() {
  isLoadedTelegramContacts = false;
  getContacts();
}

// Define a function which adds partner contacts to the list
function addPartner(element) {
  // the target URL to send the ajax request

  const closestTr = element.closest("tr");
  const section = element.getAttribute("data-section");

  const chat_id = closestTr.getAttribute("data-chat");
  const name = closestTr.getAttribute("data-name");
  const username = closestTr.getAttribute("data-username");
  const profile = closestTr.getAttribute("data-profile");
  const operation = closestTr.getAttribute("data-operation");

  const authorityList = document.querySelectorAll(
    "." + section + ".user-" + chat_id
  );

  const data = {};

  for (const node of authorityList) {
    const authority = node.getAttribute("name");
    const isChecked = node.checked;
    data[authority] = isChecked;
  }

  const params = new URLSearchParams();
  params.append("operation", operation);
  params.append("chat_id", chat_id);
  params.append("name", name);
  params.append("username", username);
  params.append("profile", profile);
  params.append("data", JSON.stringify(data));

  axios
    .post(address, params)
    .then(function (response) {
      const message = document.getElementById("success");
      message.classList.remove("hidden");
      setTimeout(() => {
        message.classList.add("hidden");
      }, 2000);
      displayLocalData();
    })
    .catch(function (error) {
      console.log(error);
    });
}

// Define a function to display existing categories
function displayCategories() {
  getExistingCategories().then(function (data) {
    let counter = 1;
    let template = "";
    const resultBox = document.getElementById("category_data");
    for (const item of data) {
      template += `
      <tr class="even:bg-gray-100 border-none" data-cat="${item.id}">
        <td class="p-2 text-center font-semibold ">${counter}</td>
        <td class="p-2 text-center font-semibold " id="target-${item.id}">${item.name}</td>
        <td class="p-2 text-center">
        <i title="حذف کتگوری" data-cat-id="${item.id}" data-value="${item.name}" onclick="deleteCategory(this)" class="cursor-pointer material-icons font-semibold text-red-600">delete</i>
        <i title="ویرایش کتگوری" data-cat-id="${item.id}" data-value="${item.name}" onclick="editCategory(this)" class="cursor-pointer material-icons font-semibold text-blue-400">edit</i>
        </td>
      </tr>
      `;
      counter += 1;
    }

    resultBox.innerHTML = template;
  });
}

// this function fetches the existing categories
async function getExistingCategories() {
  const params = new URLSearchParams();
  params.append("getExistingCategories", "getExistingCategories");

  try {
    const response = await axios.post(address, params);
    return response.data;
  } catch (error) {
    console.log(error);
    throw error; // Rethrow the error to be caught by the caller
  }
}

let previous_id = null;

function editCategory(element) {
  const id = element.getAttribute("data-cat-id");
  const value = element.getAttribute("data-value");

  const editForm = document.getElementById("edit_category");
  const saveForm = document.getElementById("save_category");

  if (previous_id !== id) {
    previous_id = id;
    editForm.classList.remove("hidden");
    saveForm.classList.add("hidden");
    document.getElementById("edit_category_name").value = value;
    document.getElementById("category_id").value = id;
  } else {
    if (editForm.classList.contains("hidden")) {
      editForm.classList.remove("hidden");
      document.getElementById("edit_category_name").value = value;
      document.getElementById("category_id").value = id;
      saveForm.classList.add("hidden");
    } else {
      editForm.classList.add("hidden");
      saveForm.classList.remove("hidden");
    }
  }
}

// this function runs while submitting the edit category form
// and edits the specified category name
function editCategoryForm() {
  event.preventDefault();
  const id = document.getElementById("category_id").value;
  const value = document.getElementById("edit_category_name").value;

  const params = new URLSearchParams();
  params.append("editCategory", "editCategory");
  params.append("id", id);
  params.append("value", value);

  try {
    const response = axios.post(address, params).then((response) => {
      document.getElementById("success_edit").style.opacity = 1;
      document.getElementById("target-" + id).innerHTML = value;

      setTimeout(() => {
        document.getElementById("success_edit").style.opacity = 0;
      }, 1000);
    });
  } catch (error) {
    console.log(error);
    return null;
  }
}

// this function runs while submitting the create category form
// and creates a new partner category
function createCategoryForm() {
  event.preventDefault();
  const value = document.getElementById("category_name").value;

  const params = new URLSearchParams();
  params.append("createCategory", "createCategory");
  params.append("value", value);

  try {
    const response = axios.post(address, params).then((response) => {
      document.getElementById("success_create").style.opacity = 1;
      displayCategories();

      setTimeout(() => {
        document.getElementById("success_edit").style.opacity = 0;
      }, 1000);
    });
  } catch (error) {
    console.log(error);
    return null;
  }
}

/**
 *
 * @param {DOM ELEMENT} element
 * @returns NULL
 * this function deletes the specified category
 */
function deleteCategory(element) {
  // Display a confirmation dialog
  const isConfirmed = confirm(
    "آیا مطمين هستید که میخواهید این دسته بندی را حذف کنید ؟"
  );

  // Check the user's choice
  if (isConfirmed) {
    const id = element.getAttribute("data-cat-id");
    const params = new URLSearchParams();
    params.append("delete_category", "delete_category");
    params.append("id", id);

    try {
      axios.post(address, params).then((response) => {
        displayCategories();
        setTimeout(() => {
          document.getElementById("success_edit").style.opacity = 0;
        }, 1000);
      });
    } catch (error) {
      console.log(error);
      return null;
    }
  }
}
