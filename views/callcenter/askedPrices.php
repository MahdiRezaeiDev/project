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
            <input onkeyup="updatePrice(this.value)" class="border p-1 py-2 min-w-full rounded-md" type="text" name="price" id="price">
        </div>
        <div class="py-5">
            <button onclick="confirmEdit()" class="border-4 border-blue-500/75 rounded-lg  bg-blue-500 text-white py-2 px-5">ویرایش</button>
            <button onclick="closeModal('editModal')" class=" border-4 border-red-500/75 rounded-lg bg-red-500 text-white py-2 px-5">انصراف</button>
        </div>
    </div>
</div>
<div class="bg-white px-5">
    <div class="flex items-center justify-between mb-5">
        <div class="relative flex items-center">
            <input class="searchBoxes border-2 border-gray-400 px-3 py-2 text-sm w-72" placeholder="جستجو ..." type="text" name="search" id="container1-search" onchange="searchBazar(this.value, this)">
            <i class="absolute left-2 material-icons text-red-500 hover:cursor-pointer" data-key="container1" onclick="searchByCustomer(this)" data-customer=''>close</i>
        </div>
        <div class="relative flex items-center">
            <input class="searchBoxes border-2 border-gray-400 px-3 py-2 text-sm w-72" placeholder="جستجو ..." type="text" name="search" id="container2-search" onchange="searchBazar(this.value, this)">
            <i class="absolute left-2 material-icons text-red-500 hover:cursor-pointer" data-key="container2" onclick="searchByCustomer(this)" data-customer=''>close</i>
        </div>
        <div class="relative flex items-center">
            <input class="searchBoxes border-2 border-gray-400 px-3 py-2 text-sm w-72" placeholder="جستجو ..." type="text" name="search" id="container3-search" onchange="searchBazar(this.value, this)">
            <i class="absolute left-2 material-icons text-red-500 hover:cursor-pointer" data-key="container3" onclick="searchByCustomer(this)" data-customer=''>close</i>
        </div>
        <h2 class="text-xl font-semibold">آخرین قیمت های گرفته شده از بازار</h2>
    </div>
    <div class="h-screen grid gap-2 grid-cols-1" id="parentContainer">
        <div class="overflow-y-auto" id="container1">
            <div id="container1-result"></div>
        </div>
        <div class="hidden overflow-y-auto" id="container2">
            <div id="container2-result"></div>
        </div>
        <div class="hidden overflow-y-auto" id="container3">
            <div id="container3-result"></div>
        </div>
    </div>
</div>
<script>
    const searchBoxes = document.getElementsByClassName('searchBoxes');
    const deleteModal = document.getElementById('deleteModal');
    const editModal = document.getElementById('editModal');

    const SinglePrice = "../../app/api/callcenter/AskedPricesApi.php";
    const MultiPrice = "../../app/api/callcenter/AskedPriceMultiApi.php";

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
            const parentContainer = document.getElementById('parentContainer');

            elementContainer.style.display = 'block';

            // Apply the correct height dynamically
            if (filled.length === 1) {
                parentContainer.classList.remove('grid-cols-1');
                parentContainer.classList.remove('grid-cols-2');
                parentContainer.classList.remove('grid-cols-3');
                parentContainer.classList.add('grid-cols-1');
            } else if (filled.length === 2) {
                parentContainer.classList.remove('grid-cols-1');
                parentContainer.classList.remove('grid-cols-2');
                parentContainer.classList.remove('grid-cols-3');
                parentContainer.classList.add('grid-cols-2');
            } else if (filled.length >= 3) {
                parentContainer.classList.remove('grid-cols-1');
                parentContainer.classList.remove('grid-cols-2');
                parentContainer.classList.remove('grid-cols-3');
                parentContainer.classList.add('grid-cols-3');
            }

            const resultContainer = document.getElementById(key + '-result');
            let destination = SinglePrice;

            if (filled.length > 1) {
                destination = MultiPrice;
            }
            getResults(key, value, resultContainer, destination);
        });
    }

    function getResults(key, pattern, container, destination) {
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

        axios.post(destination, params)
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
        priceInput.value = price;
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
                                                                                <input onkeyup="updatePrice(this.value)" class="border p-1 py-2 min-w-full rounded-md" type="text" name="price" id="price">
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

    getResults('container1', '', document.getElementById('container1-result'), SinglePrice);
</script>
<?php
require_once './components/footer.php';
