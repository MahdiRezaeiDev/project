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
        <input class="border-2 border-gray-300 focus:border-gray-500 py-2 px-3 text-sm outline-none" type="search" name="search" id="search" placeholder="جستجو....">
    </div>
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

    function sendToContact() {
        const param = new URLSearchParams();
        param.append('contacts', JSON.stringify(allCustomers));

        axios.post('https://contacts.yadak.center/contactsAPI.php', param)
            .then((response) => {
                console.log(response.data);
                if (response.data.success) {
                    const data = new URLSearchParams();
                    data.append('SYNC', 'SYNC');
                    axios.post('../../app/api/callcenter/CustomersApi.php', data).then((response) => {
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

        const data = axios.post('https://contacts.yadak.center/contactsAPI.php', param).then((response) => {

            const contacts = response.data;

            const data = new URLSearchParams();
            data.append('sveContacts', JSON.stringify(contacts));

            axios.post('../../app/api/callcenter/CustomersApi.php', data).then((response) => {
                console.log(response.data);

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
</script>
<?php
require_once './components/footer.php';
