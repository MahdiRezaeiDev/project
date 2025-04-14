<?php
$pageTitle = "تعریف رابطه مشتری های";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../app/controller/callcenter/relationshipController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$qualified = ['mahdi', 'babak', 'niyayesh'];
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
?>
<link rel="stylesheet" href="./assets/css/select2.css">
<script src="./assets/js/select2.js"></script>
<div class=" grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-6 px-4">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-900 h-28">
            <div class="flex items-center justify-between p-3 ">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="material-icons font-semibold text-orange-400">search</i>
                    جستجوی مشتری
                </h2>
            </div>
            <div class="flex justify-center px-3">
                <input onkeyup="search(this.value)" type="text" name="serial" id="serial"
                    class="p-2 w-full border-2 text-sm bg-transparent border-white outline-none text-white"
                    placeholder="شماره تماس یا اسم مشتری را وارد نمایید..." />
            </div>
        </div>
        <div id="search_result" class="p-3">
            <!-- Search Results are going to be appended here -->
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-900 h-28">
            <div class="flex items-center justify-between p-3">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="material-icons text-green-600">beenhere</i>
                    مشتری های انتخاب شده
                </h2>
                <button class="flex items-center bg-red-500 hover:bg-red-600 text-white rounded px-4 py-1 text-xs" onclick="clearAll()">
                    <i class="material-icons hover:cursor-pointer">delete</i>
                </button>
            </div>
            <p class="px-3 mb-4 text-white text-sm leading-relaxed">
                <span class="text-red-500">*</span>
                لیست مشتری های انتخاب شده برای افزودن به رابطه!
            </p>
        </div>
        <p id="select_box_error" class="px-3 tiny-text text-red-500 hidden">
            لیست مشتری های انتخاب شده برای افزودن به رابطه خالی بوده نمیتواند!
        </p>
        <p id="duplicate_relation" class="px-3 tiny-text text-red-500 hidden">
            شما همزمان نمی توانید ۲ رابطه را بارگذاری نمایید.(شما میتوانید با حذف همه رابطه جدید را وارد نمایید)
        </p>
        <div id="selected_box" class="p-3">
            <!-- selected items are going to be added here -->
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-900 h-28">
            <div class="p-3">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="material-icons font-semibold text-blue-500">save</i>
                    ثبت رابطه در سیستم
                </h2>
            </div>

            <p class="px-3 py-1 mb-4 text-white text-sm leading-relaxed">
                برای ثبت رابطه در سیستم فورم ذیل را با دقت پر نمایید.
            </p>
        </div>
        <div class="p-3">
            <form action="" method="post" onsubmit="event.preventDefault();createRelation()" id="myForm">
                <input id="mode" type="text" name="operation" value="create" hidden>
                <div class="col-span-12 sm:col-span-4 mb-3">
                    <label for="customer_name" class="block font-medium text-sm text-gray-700">
                        اسم مشتری
                    </label>
                    <input dir="rtl" name="customer_name" value="" class="border-2 outline-none text-sm mt-1 w-full border-gray-300 shadow-sm px-3 py-2" required id="customer_name" type="text" />
                    <p class="mt-2"></p>
                </div>
                <?php if (in_array($_SESSION['username'], $qualified)): ?>
                    <div class="col-span-12 sm:col-span-4 mb-3">
                        <div class="flex gap-2">
                            <label for="is_verified" class="block font-medium text-sm text-gray-700 cursor-pointer">
                                وضعیت تایید مالی
                            </label>
                            <input name="is_verified" id="is_verified" type="checkbox" />
                        </div>
                        <p class="mt-2"></p>
                    </div>
                <?php endif; ?>
                <div class="col-span-12 sm:col-span-4 mb-3">
                    <label for="admin_des" class="block font-medium text-sm text-gray-700">
                        توضیحات مدیریت
                    </label>
                    <textarea class="border-2 p-2 text-sm mt-1 block outline-none w-full border-gray-300 shadow-sm" id="admin_des" rows="5"></textarea>
                </div>
                <div class="col-span-12 sm:col-span-4 mb-3">
                    <label for="finance_des" class="block font-medium text-sm text-gray-700">
                        توضیحات مالی
                    </label>
                    <textarea class="border-2 p-2 text-sm mt-1 block outline-none w-full border-gray-300 shadow-sm" id="finance_des" rows="5"></textarea>
                </div>
        </div>

        <div class="flex items-center justify-between px-4 py-3  text-right sm:px-6">
            <button type="type" class="inline-flex items-center px-4 py-2 bg-gray-800 rounded font-semibold text-xs text-white">
                <i class="px-2 material-icons hover:cursor-pointer">save</i>
                ذخیره سازی
            </button>
            <p id="form_success" class="px-3 py-2 text-xs bg-green-700 hidden text-white rounded ">
                موفقانه در پایگاه داده ثبت شد!
            </p>
            <p id="form_error" class="px-3 py-2 text-xs bg-red-700 hidden text-white rounded">
                ذخیره سازی اطلاعات ناموفق بود!
            </p>
        </div>
        </form>
        <div id="output"></div>
    </div>
</div>
</div>
<script>
    // Container Global Variables
    let serial = null;
    let relation_id = null;
    selected_customers = [];
    let relation_active = false;

    //Global Elements Container 
    const selected_box = document.getElementById('selected_box');
    const resultBox = document.getElementById("search_result");
    const error_message = document.getElementById('select_box_error');
    const duplicate_relation = document.getElementById('duplicate_relation');
    const form_success = document.getElementById('form_success');
    const form_error = document.getElementById('form_error');

    // search for customers to define their relationship
    function search(pattern) {
        serial = pattern;
        if (pattern.length >= 3) {
            error_message.classList.add('hidden');
            duplicate_relation.classList.add('hidden');
            pattern = pattern.replace(/-/g, "");
            pattern = pattern.replace(/_/g, "");

            resultBox.innerHTML = `<tr class=''>
                                        <div class='w-full h-96 flex justify-center items-center'>
                                            <img class=' block w-10 mx-auto h-auto' src='../../public/img/loading.png' alt='google'>
                                        </div>
                                    </tr>`;
            var params = new URLSearchParams();
            params.append('searchCustomers', 'searchCustomers');
            params.append('pattern', pattern);
            axios.post("../../app/api/callcenter/CustomersApi.php", params)
                .then(function(response) {
                    resultBox.innerHTML = response.data;
                })
                .catch(function(error) {
                    console.log(error);
                });
        } else {
            resultBox.innerHTML = "";
        }
    };

    // A function to add a customer to the relation box
    function add(element) {
        duplicate_relation.classList.add('hidden');
        const id = element.getAttribute("data-id");
        const phone = element.getAttribute("data-phone");
        const name = element.getAttribute("data-name");
        const family = element.getAttribute("data-family");
        selected_customers = selected_customers.filter((customer) => {
            return customer.id !== id;
        });

        selected_customers.push({
            id: id,
            phone: phone,
            name: name
        });
        error_message.classList.add('hidden');
        remove(id);
        displaySelectedCustomers();
    };

    // A function to remove added customers from relation box
    function remove(id) {
        const item = document.getElementById("search-" + id);
        if (item) {
            item.remove();
        }
    }

    // A function to remove an specific item from selected items list
    function remove_selected(id) {
        selected_customers = selected_customers.filter((item) => {
            return item.id != id;
        });
        displaySelectedCustomers();
    };

    //A function to clear all selected items
    function clearAll() {
        selected_customers = [];
        relation_active = false;
        displaySelectedCustomers();
        duplicate_relation.classList.add('hidden');
        document.getElementById('mode').value = 'create';

        var form = document.getElementById('myForm');

        for (var i = 0; i < form.elements.length; i++) {
            var element = form.elements[i];

            // Check if the element is an input element (text, password, email, etc.)
            if (element.tagName === 'INPUT' && element.type !== 'button' && element.type !== 'submit') {
                element.value = ''; // Set the value to an empty string
            } else if (element.tagName === 'TEXTAREA') {
                element.value = ''; // Clear textarea values
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = -1; // Clear selected option in a dropdown
            }
        }
    }

    // A function to display the selected customers in the relation box
    function displaySelectedCustomers() {
        let template = '';
        for (const customer of selected_customers) {
            if (customer.relation_name && customer.relation_name.length > 0) {
                document.getElementById('customer_name').value = customer.relation_name;
            } else {
                document.getElementById('customer_name').value = customer.name;
            }
            template += `
            <div class="w-full flex justify-between items-center shadow-md hover:shadow-lg rounded-md px-4 py-3 mb-2 border border-gray-300">
                <p class="text-sm font-semibold text-gray-600">
                    ` + customer.name + `
                </p>
                <p class="text-sm font-semibold text-gray-600">
                    ` + customer.phone + `
                </p>
                    <i data-id="` + customer.id + `" data-partNumber="` + customer.name + `" onclick="remove_selected(` +
                customer.id + `)"
                            class="material-icons add text-red-600 cursor-pointer rounded-circle hover:bg-gray-200">do_not_disturb_on
                    </i>
                </div>
            `;
        }
        selected_box.innerHTML = template;
    }

    function getSelectedItems(id) {
        let selected = [];
        for (var option of document.getElementById(id).options) {
            if (option.selected) {
                selected.push(option.value);
            }
        }

        return selected;
    }

    // A function to create the relationship
    function createRelation() {
        duplicate_relation.classList.add('hidden');
        // Accessing the form fields to get thier value for an ajax store operation
        const customer_name = document.getElementById('customer_name').value;
        const is_verified = document.getElementById("is_verified").checked;
        const admin_des = document.getElementById("admin_des").value;
        const finance_des = document.getElementById("finance_des").value;
        // Defining a params instance to be attached to the axios request
        const params = new URLSearchParams();
        params.append('store_customers_phone', 'store_customers_phone');
        params.append('selected_customers', JSON.stringify(selected_customers));
        params.append('customer_name', customer_name);
        params.append('is_verified', is_verified);
        params.append('admin_des', admin_des);
        params.append('finance_des', finance_des);
        params.append('relation_id', relation_id);
        params.append('mode', mode.value);

        if (selected_customers.length > 0) {
            axios.post("../../app/api/callcenter/CustomersApi.php", params)
                .then(function(response) {
                    if (response.data == true) {
                        form_success.classList.remove('hidden');
                        setTimeout(() => {
                            form_success.classList.add('hidden');
                            location.reload();
                        }, 2000)
                    } else {
                        form_error.classList.remove('hidden');
                        setTimeout(() => {
                            form_error.classList.add('hidden');
                            location.reload();
                        }, 2000)
                    }
                })
                .catch(function(error) {

                });
        } else {
            error_message.classList.remove('hidden');
        }
    }

    // A function to load all the relationships for the selected relationship
    function load(element) {
        const pattern = element.getAttribute("data-pattern");
        const is_verified = element.getAttribute("data-verified");
        const customer_name = element.getAttribute("data-name").trim();
        const admin_des = element.getAttribute("data-admin_des");
        const finance_des = element.getAttribute("data-finance_des");

        document.getElementById('mode').value = 'update';
        if (!relation_active) {

            duplicate_relation.classList.add('hidden');
            relation_active = true;
            relation_id = pattern;

            const params = new URLSearchParams();
            params.append('load_relation', 'load_relation');
            params.append('relation_id', relation_id);

            axios.post("../../app/api/callcenter/CustomersApi.php", params)
                .then(function(response) {
                    if (is_verified != 0) {
                        document.getElementById('is_verified').checked = true;
                    }
                    document.getElementById('admin_des').value = admin_des;
                    document.getElementById('finance_des').value = finance_des;
                    push_data(response.data);
                    displaySelectedCustomers();
                })
                .catch(function(error) {

                });
        } else {
            duplicate_relation.classList.remove('hidden');
        }
    }

    //This function helps to add all relations of a relationship into the selected items list
    const push_data = (data) => {
        for (const item of data) {
            remove(item.id);
            selected_customers.push({
                id: item.id,
                name: item.name + " " + item.family,
                phone: item.phone,
                relation_name: item.relation_name ?? ''
            });
        }
    };

    <?php if (isset($_GET['search'])): ?>
        search("<?= $search; ?>");
        document.getElementById('serial').value = "<?= $search; ?>";
    <?php endif; ?>
</script>
<?php
require_once './components/footer.php';
