<?php
$pageTitle = "لیست مشتریان";
$iconUrl = 'favicon.ico';
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
require_once './components/header.php';
require_once '../../app/controller/callcenter/CustomersListController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

// Get the current page number
$totalPages = ceil($customersCount / $fetchLimit);
?>
<div class="px-4">
    <div class="w-4/5 mx-auto flex justify-between items-center mb-3">
        <h2 class="text-xl font-semibold">لیست مشتریان</h2>
        <button class="bg-sky-400 rounded text-white p-3 py-2" onclick="sendToContact()">انتقال مخاططبین به حساب گوگل</button>
        <button class="bg-rose-400 rounded text-white p-3 py-2" onclick="getContacts()">بارگیری مخاطبین از حساب گوگل</button>
        <div>
            <input class="border-2 border-gray-300 focus:border-gray-500 py-2 px-3 text-sm outline-none" type="search" name="search" placeholder="جستجو....">
            <button id="searchButton" class="bg-sky-600 text-sm text-white rounded-e px-4 py-2">جستجو</button>
        </div>
    </div>
    <div class="w-4/5 mx-auto flex justify-end items-center my-3">
        <div>
            <input type="text" name="phone_number" id="phone_number" placeholder="شماره تماس مشتری"
                class="border-2 border-gray-300 focus:border-gray-500 py-2 px-3 text-sm outline-none">
            <button type="button" id="saveCustomerBtn"
                class="bg-sky-600 text-sm text-white rounded-e px-4 py-2">
                ذخیره مشتری
            </button>
        </div>
    </div>

    <script>
        document.getElementById('saveCustomerBtn').addEventListener('click', function() {
            const phoneInput = document.getElementById('phone_number');
            const phone = encodeURIComponent(phoneInput.value.trim());

            if (phone) {
                // Replace '/your-route' with your actual route
                window.location.href = `./main.php?phone=${phone}`;
            } else {
                alert("لطفاً شماره تلفن را وارد کنید.");
            }
        });
    </script>

    <table class="w-4/5 mx-auto">
        <thead>
            <tr class="bg-gray-800 border border-gray-800">
                <th class="p-3 text-sm text-right text-white font-semibold">#</th>
                <th class="p-3 text-sm text-right text-white font-semibold">نام</th>
                <th class="p-3 text-sm text-right text-white font-semibold">فامیلی</th>
                <th class="p-3 text-sm text-right text-white font-semibold">تلفن</th>
                <th class="p-3 text-sm text-right text-white font-semibold">شماره شاسی</th>
                <th class="p-3 text-sm text-right text-white font-semibold">ماشین</th>
                <th class="p-3 text-sm text-right text-white font-semibold">نوع</th>
                <th class="p-3 text-sm text-right text-white font-semibold">آدرس</th>
                <th class="p-3 text-sm text-right text-white font-semibold">توضیحات</th>
                <th class="p-3 text-sm text-right text-white font-semibold">تعریف رابطه شماره</th>
            </tr>
        </thead>
        <tbody class="border border-dashed border-gray-600">
            <?php if (count($customers) > 0) :
                $counter = $fetchLimit * ($current_page - 1) + 1;
                foreach ($customers as $customer) : ?>
                    <tr class="even:bg-gray-200">
                        <td class="p-3 text-sm"><?= $counter ?></td>
                        <td class="p-3 text-sm"><?= $customer['name'] ?></td>
                        <td class="p-3 text-sm"><?= $customer['family'] ?></td>
                        <td class="p-3 text-sm text-blue-600 font-semibold hover:underline">
                            <a target="_blank" href="./main.php?phone=<?= $customer['phone']; ?>"><?= $customer['phone']; ?></a>
                        </td>
                        <td class="p-3 text-sm uppercase"><?= $customer['vin'] ?></td>
                        <td class="p-3 text-sm"><?= $customer['car']; ?></td>
                        <td class="p-3 text-sm"><?= $customer['kind'] != 'null' ? $customer['kind'] : '' ?></td>
                        <td class="p-3 text-sm"><?= $customer['address']; ?></td>
                        <td class="p-3 text-sm"><?= $customer['des'] ?></td>
                        <td class="p-3 text-sm">
                            <a href="./customersPhone.php?search=<?= $customer['name']; ?>" class="bg-rose-600 text-xs text-white rounded px-3 py-2">تعریف</a>
                        </td>
                    </tr>
                <?php
                    $counter += 1;
                endforeach;
            else :
                ?>
                <tr class="">
                    <td colspan="9" scope="col" class="text-rose-600 p-3 text-center font-semibold">
                        موردی برای نمایش وجود ندارد !!
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    // Calculate the offset for the SQL query
    $offset = ($current_page - 1) * $fetchLimit;

    // Display pagination links
    echo '<div class="flex justify-center items-center p-5">';
    echo '<span class="bg-gray-600 rounded-md flex justify-center items-center text-white px-3 h-8 m-1 ">صحفه  ' . $current_page . ' از ' . $totalPages . '</span>';

    // Previous page link
    if ($current_page > 1) {
        echo '<a class="prev bg-rose-600 text-white rounded-md px-3 h-8 flex justify-center items-center" href="?page=' . ($current_page - 1) . '">قبلی</a>';
    }

    // Page links with limited visibility
    $startPage = max(1, $current_page - 2);
    $endPage = min($totalPages, $startPage + 4);

    for ($i = $startPage; $i <= $endPage; $i++) {
        echo '<a class="' . ($i == $current_page ? 'bg-gray-900' : 'bg-gray-600') . ' rounded-md flex justify-center items-center text-white w-8 h-8 m-1" href="?page=' . $i . '">' . $i . '</a>';
    }

    // Next page link
    if ($current_page < $totalPages) {
        echo '<a class="next bg-rose-600 text-white rounded-md px-3 h-8 flex justify-center items-center" href="?page=' . ($current_page + 1) . '">بعدی</a>';
    }

    echo '</div>';
    ?>
</div>
<script>
    const allCustomers = <?= json_encode($allCustomers) ?>;
    const customersAPI = '../../app/api/callcenter/CustomersApi.php';
    const contactsAPI = 'https://contacts.yadak.center/contactsAPI.php';

    function sendToContact() {
        const param = new URLSearchParams();
        param.append('contacts', JSON.stringify(allCustomers));

        axios.post(contactsAPI, param)
            .then((response) => {
                if (response.data.success) {
                    const data = new URLSearchParams();
                    data.append('SYNC', 'SYNC');
                    axios.post(contactsAPI, data).then((response) => {
                        window.open('https://contacts.yadak.center/', '_blank');
                    })
                }
            }).catch((error) => {
                console.log(error);
            });
    }

    function getContacts() {
        const param = new URLSearchParams();
        param.append('getContacts', 'getContacts');

        const data = axios.post(contactsAPI, param).then((response) => {

            const contacts = response.data;

            const data = new URLSearchParams();
            data.append('sveContacts', JSON.stringify(contacts));

            axios.post(contactsAPI, data).then((response) => {
                if (response.data.success) {
                    if (response.data.message == "0 contacts saved successfully.") {
                        alert("Already Upto date")
                    } else {
                        alert(response.data.message)
                    }
                }
            })
        })
    }

    function searchCustomers() {
        const searchValue = document.getElementById('search').value;
        const params = new URLSearchParams();
        params.append('pattern', searchValue);
        params.append('getContacts', 'getContacts');

        if (searchValue.length == 0) {
            window.location.reload();
        }

        if (searchValue.length >= 3) {
            axios.post(customersAPI, params)
                .then((response) => {
                    const filteredCustomers = response.data;
                    renderCustomers(filteredCustomers);
                })
                .catch((error) => {
                    console.error('Error fetching customers:', error);
                });
        }
    }

    function renderCustomers(customers) {
        const tableBody = document.querySelector('tbody');
        tableBody.innerHTML = ''; // Clear existing rows

        if (customers.length > 0) {
            customers.forEach((customer, index) => {
                const row = document.createElement('tr');
                row.className = 'even:bg-gray-200';
                row.innerHTML = `
                    <td class="p-3 text-sm">${index + 1}</td>
                    <td class="p-3 text-sm">${customer.name}</td>
                    <td class="p-3 text-sm">${customer.family}</td>
                    <td class="p-3 text-sm text-blue-600 font-semibold hover:underline">
                        <a target="_blank" href="./main.php?phone=${customer.phone}">${customer.phone}</a>
                    </td>
                    <td class="p-3 text-sm uppercase">${customer.vin}</td>
                    <td class="p-3 text-sm">${customer.car}</td>
                    <td class="p-3 text-sm">${customer.kind != 'null' ? customer.kind : ''}</td>
                    <td class="p-3 text-sm">${customer.address}</td>
                    <td class="p-3 text-sm">${customer.des}</td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            const row = document.createElement('tr');
            row.innerHTML = `<td colspan="9" scope="col" class="text-rose-600 p-3 text-center font-semibold">موردی برای نمایش وجود ندارد !!</td>`;
            tableBody.appendChild(row);
        }
    }
    document.getElementById('searchButton').addEventListener('click', searchCustomers);
</script>
<?php
require_once './components/footer.php';
