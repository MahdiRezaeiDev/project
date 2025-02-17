<?php
$pageTitle = "قیمت های گرفته شده";
$iconUrl = 'favicon.ico';
require_once './components/header.php';
require_once '../../app/controller/callcenter/AskedPricesController.php';
require_once '../../utilities/callcenter/DollarRateHelper.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<style>
    #deleteModal,
    #editModal {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.7);
        display: none;
    }
</style>

<div id="deleteModal" class="flex items-center justify-center">
    <div id="modalContent" style="width: 530px;" class="bg-white rounded-md shadow-ld w-54 p-5 flex flex-col items-center justify-center">
        <i class="material-icons text-4xl text-orange-600">warning</i>
        <h4 class=" text-2xl mb-3 font-bold">حذف معلومات</h4>
        <p class="text-center my-4">
            آیا مطمئن هستید میخواهید اطاعات انتخاب شده را حذف نمایید؟
            <br>
            اطلاعات مورد نظر بعد از حذف در درسترس نخواهد بود!
        </p>
        <div class="py-5">
            <button onclick="confirmDelete()" class="border-4 border-red-500/75 rounded-lg bg-red-500 text-white py-2 px-5">تایید و حذف</button>
            <button onclick="closeModal('deleteModal')" class=" border-4 border-indigo-500/75 rounded-lg bg-indigo-500 text-white py-2 px-5">انصراف</button>
        </div>
    </div>
</div>

<div id="editModal" class="flex items-center justify-center">
    <div id="editModalContent" style="width: 530px;" class="bg-white rounded-md shadow-ld w-54 p-5 flex flex-col items-center justify-center">
        <i class="material-icons text-4xl text-blue-600">dvr</i>
        <h4 class=" text-2xl mb-3 font-bold">ویرایش معلومات</h4>
        <p class="text-center my-4">
            برای ویرایش اطلاعات فورم ذیل را به دقت پر نمایید
        </p>
        <div class="py-3">
            <input onkeyup="updatePrice(this.value)" class="border p-2 min-w-full rounded-md" type="text" name="price" id="price">
        </div>
        <div class="py-5">
            <button onclick="confirmEdit()" class="border-4 border-blue-500/75 rounded-lg  bg-blue-500 text-white py-2 px-5">ویرایش</button>
            <button onclick="closeModal('editModal')" class=" border-4 border-red-500/75 rounded-lg bg-red-500 text-white py-2 px-5">انصراف</button>
        </div>
    </div>
</div>
<div class=" bg-white px-5">
    <div class="flex items-center justify-between mb-5">
        <div class="relative flex items-center">
            <input class="searchBoxes border-2 border-gray-400 px-3 py-2 text-sm w-72" placeholder="جستجو ..." type="text" name="search" id="container1-search" onkeyup="searchBazar(this.value, this)">
            <i class="absolute left-2 material-icons text-red-500 hover:cursor-pointer" data-key="container1" onclick="searchByCustomer(this)" data-customer=''>close</i>
        </div>
        <div class="relative flex items-center">
            <input class="searchBoxes border-2 border-gray-400 px-3 py-2 text-sm w-72" placeholder="جستجو ..." type="text" name="search" id="container2-search" onkeyup="searchBazar(this.value, this)">
            <i class="absolute left-2 material-icons text-red-500 hover:cursor-pointer" data-key="container2" onclick="searchByCustomer(this)" data-customer=''>close</i>
        </div>
        <div class="relative flex items-center">
            <input class="searchBoxes border-2 border-gray-400 px-3 py-2 text-sm w-72" placeholder="جستجو ..." type="text" name="search" id="container3-search" onkeyup="searchBazar(this.value, this)">
            <i class="absolute left-2 material-icons text-red-500 hover:cursor-pointer" data-key="container3" onclick="searchByCustomer(this)" data-customer=''>close</i>
        </div>
        <h2 class="text-xl font-semibold">آخرین قیمت های گرفته شده از بازار</h2>
    </div>
    <div class="h-screen">
        <div class="overflow-y-auto mb-5" id="container1">
            <table class="w-full">
                <tr class="bg-gray-600">
                    <th class="text-white text-right font-semibold p-3">کد فنی</th>
                    <th class="text-white text-right font-semibold p-3">فروشنده</th>
                    <th class="text-white text-right font-semibold p-3">قیمت</th>
                    <th class="text-white text-right font-semibold p-3">کاربر ثبت کننده</th>
                    <th class="text-white text-center font-semibold p-3">زمان ثبت</th>
                    <th class="text-white text-right font-semibold p-3">عملیات</th>
                </tr>
                <tbody id="container1-result">
                    <?php
                    $currentGroup = null;
                    $bgColors = ['rgb(254 243 199)', 'rgb(220 252 231)']; // Array of background colors for date groups
                    $bgColorIndex = 0;

                    foreach (getAskedPrices() as $row) :
                        $id = $row['id'];
                        $time = $row['time'];
                        $partNumber = $row['codename'];
                        $sellerName = $row['seller_name'];
                        $price = $row['price'];
                        $userId = $row['user_id'];
                        $username = $row['username'];

                        // Explode the time value to separate date and time
                        $dateTime = explode(' ', $time);
                        $date = $dateTime[0];

                        // Check if the group has changed
                        if ($date !== $currentGroup) :
                            // Update the current group
                            $currentGroup = $date;

                            // Get the background color for the current group
                            $bgColor = $bgColors[$bgColorIndex % count($bgColors)];
                            $bgColorIndex++;
                    ?>
                            <!-- // Display a row for the new group with the background color -->
                            <tr class="bg-sky-800">
                                <td class="text-white font-semibold p-3" colspan="6"><?= displayTimePassed($date) . ' - ' . jdate('Y/m/d', strtotime($date)) ?></td>
                            </tr>
                        <?php
                        endif;
                        // Display the row for current entry with the same background color as the group
                        ?>
                        <tr id="row-<?= $id ?>" style="background-color:<?= $bgColor ?>">
                            <td class="text-md font-semibold p-3 hover:cursor-pointer text-blue-400 uppercase" data-key="container1" onclick="searchByCustomer(this)" data-customer='<?= $partNumber ?>'><?= $partNumber ?></td>
                            <td class="text-md font-semibold p-3 hover:cursor-pointer text-blue-400" data-key="container1" onclick="searchByCustomer(this)" data-customer='<?= $sellerName ?>'><?= $sellerName ?></td>
                            <td class="text-md font-semibold p-3" id="price-<?= $id ?>"><?= $price ?></td>
                            <td>
                                <?php
                                $profile = "../../public/userimg/" . $row['user_id'] . ".jpg";
                                if (!file_exists($profile)) {
                                    $profile = "../../public/userimg/default.png";
                                }
                                ?>
                                <img title="<?= $row['name'] . ' ' . $row['family'] ?>" class="w-8 h-8 rounded-full" src="<?= $profile ?>" alt="user profile" />
                            </td>
                            <td class="text-md font-semibold p-3">
                                <p class="text-sm text-center font-semibold p-3" style="direction: ltr !important;">
                                    <?= jdate('Y/m/d   H:i', strtotime($time)); ?>
                                </p>
                            </td>
                            <td>
                                <i onclick="editItem(this)" data-price="<?= $price ?>" data-item='<?= $id ?>' class="material-icons hover:cursor-pointer text-indigo-600">edit</i>
                                <i onclick="deleteItem(this)" data-item='<?= $id ?>' class="material-icons hover:cursor-pointer text-red-600">delete</i>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="hidden overflow-y-auto mb-5" id="container2">
            <table class="w-full">
                <tr class="bg-gray-600">
                    <th class="text-white text-right font-semibold p-3">کد فنی</th>
                    <th class="text-white text-right font-semibold p-3">فروشنده</th>
                    <th class="text-white text-right font-semibold p-3">قیمت</th>
                    <th class="text-white text-right font-semibold p-3">کاربر ثبت کننده</th>
                    <th class="text-white text-center font-semibold p-3">زمان ثبت</th>
                    <th class="text-white text-right font-semibold p-3">عملیات</th>
                </tr>
                <tbody id="container2-result">
                </tbody>
            </table>
        </div>
        <div class="hidden overflow-y-auto mb-5" id="container3">
            <table class="w-full">
                <tr class="bg-gray-600">
                    <th class="text-white text-right font-semibold p-3">کد فنی</th>
                    <th class="text-white text-right font-semibold p-3">فروشنده</th>
                    <th class="text-white text-right font-semibold p-3">قیمت</th>
                    <th class="text-white text-right font-semibold p-3">کاربر ثبت کننده</th>
                    <th class="text-white text-center font-semibold p-3">زمان ثبت</th>
                    <th class="text-white text-right font-semibold p-3">عملیات</th>
                </tr>
                <tbody id="container3-result">
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    const searchBoxes = document.getElementsByClassName('searchBoxes');
    const deleteModal = document.getElementById('deleteModal');
    const editModal = document.getElementById('editModal');

    let toBeModified = null;
    let itemPrice = null;

    deleteModal.addEventListener('click', (e) => {
        if (e.target.id === 'deleteModal') {
            document.getElementById('deleteModal').style.display = 'none';
        }
    });

    editModal.addEventListener('click', (e) => {
        if (e.target.id === 'editModal') {
            document.getElementById('editModal').style.display = 'none';
        }
    })

    function searchByCustomer(element) {
        const customer_name = element.getAttribute('data-customer');
        const key = element.getAttribute('data-key');
        document.getElementById(key + '-search').value = customer_name;
        searchBazar(customer_name, key);
    }

    function searchBazar(pattern, element) {
        let counter = 1;
        let filled = [];

        for (let element of searchBoxes) {
            const value = element.value;
            const key = 'container' + counter;

            if (value.length) {
                filled.push({
                    key,
                    value
                });
                element.style.display = 'block'; // Show element
            } else {
                const containerElement = document.getElementById(key);
                containerElement.style.display = 'none'; // Hide element
            }
            counter += 1;
        }

        filled.forEach((item, index) => {
            const key = item.key;
            const value = item.value;
            const elementContainer = document.getElementById(key);

            elementContainer.style.display = 'block'; // Ensure it's visible

            // Reset any previous height styles
            elementContainer.style.height = '';

            // Apply the correct height dynamically
            if (filled.length === 1) {
                elementContainer.style.height = '100%';
            } else if (filled.length === 2) {
                elementContainer.style.height = '50%';
            } else if (filled.length >= 3) {
                elementContainer.style.height = '33.33%';
            }

            const resultContainer = document.getElementById(key + '-result');
            getResults(key, value, resultContainer);
        });

        if (filled.length == 0) {
            window.location.reload();
        }
    }


    function getResults(key, pattern, container) {
        pattern = pattern.replace(/\s/g, "");
        pattern = pattern.replace(/-/g, "");
        pattern = pattern.replace(/_/g, "");

        container.innerHTML = `<tr class=''>
                                <td colspan='14' class='py-10 text-center'> 
                                    <img class=' block w-10 mx-auto h-auto' src='./assets/img/loading.png' alt='loading'>
                                    </td>
                                </tr>`;

        var params = new URLSearchParams();
        params.append('pattern', pattern);
        params.append('key', key);

        axios.post("../../app/api/callcenter/AskedPricesApi.php", params)
            .then(function(response) {
                if (response.data.length)
                    container.innerHTML = response.data;
                else
                    container.innerHTML = `<tr class='bg-sky-100'>
                                                <td colspan='14' class='py-5 text-center text-rose-500 font-semibold'> 
                                                نتیجه ای پیدا نشد.
                                                </td>
                                            </tr>`;
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function editItem(element) {
        const id = element.getAttribute('data-item');
        const price = element.getAttribute('data-price');
        const priceInput = document.getElementById('price');

        toBeModified = id;
        editModal.style.display = 'flex';
        itemPrice = document.getElementById('price-' + toBeModified).innerHTML;
        priceInput.value = itemPrice;
    }

    function deleteItem(element) {
        const id = element.getAttribute('data-item');

        deleteModal.style.display = 'flex';
        toBeModified = id;
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function confirmDelete() {
        var params = new URLSearchParams();
        params.append('toBeDelete', toBeModified);
        params.append('operation', 'delete');

        axios.post("../../app/api/callcenter/AskedPricesApi.php", params)
            .then(function(response) {
                console.log(response.data);
                document.getElementById('modalContent').innerHTML = `<i class="material-icons text-6xl text-green-600 mb-4">check_circle</i>
                                                                    <h4 class=" text-2xl mb-3 font-bold">عملیات موفقیت آمیز</h4>
                                                                    <p class="text-center my-4">
                                                                        حذف اطلاعات موفقانه صورت گرفت!
                                                                    </p>‍‍`;
                setTimeout(() => {
                    document.getElementById('modalContent').innerHTML = `<i class="material-icons text-4xl text-orange-600">warning</i>
                                                                            <h4 class=" text-2xl mb-3 font-bold">حذف معلومات</h4>
                                                                            <p class="text-center my-4">
                                                                                آیا مطمئن هستید میخواهید اطاعات انتخاب شده را حذف نمایید؟
                                                                                <br>
                                                                                اطلاعات مورد نظر بعد از حذف در درسترس نخواهد بود!
                                                                            </p>
                                                                            <div class="py-5">
                                                                                <button onclick="confirmDelete()" class="border-4 border-red-500/75 rounded-lg bg-red-500 text-white py-2 px-5">تایید و حذف</button>
                                                                                <button onclick="closeModal('deleteModal')" class=" border-4 border-indigo-500/75 rounded-lg bg-indigo-500 text-white py-2 px-5">انصراف</button>
                                                                            </div>`;
                    deleteModal.style.display = 'none';
                    document.getElementById('row-' + toBeModified).remove();
                }, 1000)

            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function updatePrice(value) {
        itemPrice = value;
    }

    function confirmEdit() {
        var params = new URLSearchParams();
        params.append('editOperation', 'edit');
        params.append('toBeEdited', toBeModified);
        params.append('price', itemPrice);

        axios.post("../../app/api/callcenter/AskedPricesApi.php", params)
            .then(function(response) {
                document.getElementById('editModalContent').innerHTML = `<i class="material-icons text-6xl text-green-600 mb-4">check_circle</i>
                                                                    <h4 class=" text-2xl mb-3 font-bold">عملیات موفقیت آمیز</h4>
                                                                    <p class="text-center my-4">
                                                                        ویرایش اطلاعات موفقانه صورت گرفت!
                                                                    </p>‍‍`;
                setTimeout(() => {
                    document.getElementById('editModalContent').innerHTML = `<i class="material-icons text-4xl text-blue-600">dvr</i>
                                                                            <h4 class=" text-2xl mb-3 font-bold">ویرایش معلومات</h4>
                                                                            <p class="text-center my-4">
                                                                                برای ویرایش اطلاعات فورم ذیل را به دقت پر نمایید
                                                                            </p>
                                                                            <div class="py-3">
                                                                                <input onkeyup="updatePrice(this.value)" class="border p-2 min-w-full rounded-md" type="text" name="price" id="price">
                                                                            </div>
                                                                            <div class="py-5">
                                                                                <button onclick="confirmEdit()" class="border-4 border-blue-500/75 rounded-lg  bg-blue-500 text-white py-2 px-5">ویرایش</button>
                                                                                <button onclick="closeModal('editModal')" class=" border-4 border-red-500/75 rounded-lg bg-red-500 text-white py-2 px-5">انصراف</button>
                                                                            </div>`;
                    editModal.style.display = 'none';
                    document.getElementById('price-' + toBeModified).innerHTML = itemPrice;
                }, 1000)

            })
            .catch(function(error) {
                console.log(error);
            });
    }
</script>
<?php
require_once './components/footer.php';
