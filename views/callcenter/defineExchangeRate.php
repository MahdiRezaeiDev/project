<?php
$pageTitle = "تعریف دلار جدید";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../app/controller/callcenter/DollarRateController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php'; ?>

<div class="bg-white rounded-lg shadow-md m-5 lg:w-1/2 mx-auto p-5">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            درصد تغییر قیمت دلار
            <i class="material-icons font-semibold text-orange-400">attach_money</i>
        </h2>
        <button onclick="toggleFormDisplay('create_block')" class="bg-gray-600 text-white text-sm rounded-sm px-5 py-2 my-2"> دلار جدید</button>
    </div>
    <div class="">
        <table class="text-sm min-w-full">
            <thead class="font-medium border border-gray-600">
                <tr class="bg-gray-600">
                    <th scope="col" class="text-white p-2">
                        شماره
                    </th>
                    <th scope="col" class="text-white p-2">
                        در صد تغیر
                    </th>
                    <th scope="col" class="text-white p-2">
                        در صد تخفیف
                    </th>
                    <th scope="col" class="text-white p-2">
                        اعمال تا تاریخ
                    </th>
                    <th scope="col" class="text-white p-2">
                        عملیات
                    </th>
                </tr>
            </thead>
            <tbody class="border border-dashed border-gray-600" id="results">
                <?php
                foreach ($dollarRate as $counter => $rate) : ?>
                    <tr class="odd:bg-gray-200">
                        <td class='p-2 text-center'>
                            <?= ++$counter ?>
                        </td>
                        <td class='p-2 text-center'>
                            <?= $rate['rate'] ?>
                        </td>
                        <td class='p-2 text-center'>
                            <?= $rate['discount'] ?>
                        </td>
                        <td class='p-2 text-center '>
                            <?= $rate['created_at'] ?>
                        </td>
                        <td class='p-2 text-center'>
                            <?php $id = $rate['id'];
                            if ($rate['status']) : ?>
                                <button onclick="toggleActivation(<?= $id ?>, 0)" class="shadow bg-red-500 hover:bg-red-400 focus:shadow-outline focus:outline-none text-xs text-white font-bold py-2 px-4 rounded">
                                    غیر فعال سازی
                                </button>
                            <?php else : ?>
                                <button onclick="toggleActivation(<?= $id ?>, 1)" class="shadow bg-green-500 hover:bg-green-400 focus:shadow-outline focus:outline-none text-xs text-white font-bold py-2 px-4 rounded">
                                    فعال سازی
                                </button>
                            <?php endif; ?>
                            <button onclick="editItem(<?= $id ?>)" class="shadow bg-blue-500 hover:bg-blue-400 focus:shadow-outline focus:outline-none text-xs text-white font-bold py-2 px-4 rounded">
                                ویرایش
                            </button>
                        </td>
                    </tr>
                <?php
                endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="edit_block" class="hidden my-5 bg-white rounded-lg shadow-md lg:w-1/2 mx-auto p-5">
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                ویرایش درصد تغییر قیمت دلار
                <i class="material-icons font-semibold text-indigo-400">create</i>
            </h2>
        </div>
        <div class="flex flex-wrap mb-6">

            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                <input type="hidden" name="id" id="edit_id">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="edit_rate">
                    درصد دلار
                </label>
                <input id="edit_rate" name="rate" value="" class="block w-full text-gray-700 border-2 border-gray-300 py-3 px-4 focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="درصد دلار" required>
            </div>
            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="edit_discount">
                    درصد تخفیف
                </label>
                <input id="edit_discount" name="discount" value="" class="block w-full text-gray-700 border-2 border-gray-300 py-3 px-4 focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="درصد دلار" required>
            </div>
            <div class="w-full md:w-1/2 px-3">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="edit_date">
                    بازه اول
                </label>
                <input id="edit_date" name="date" value="" class="block w-full text-gray-700 border-2 border-gray-300 py-3 px-4 focus:outline-none focus:bg-white focus:border-gray-500 mb-3" type="date" placeholder="اعمال تا تاریخ" required>
            </div>
            <div class="w-full md:w-1/2 px-3 mt-5">
                <button onclick="completeEdit()" class="text-xs shadow bg-gray-500 hover:bg-gray-400 focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4" type="submit">
                    ویرایش
                </button>
            </div>
        </div>
        </form>
    </div>
</div>
<div id="create_block" class="my-5 bg-white rounded-lg shadow-md lg:w-1/2 mx-auto p-5">
    <div>
        <div class=" flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                تعریف دلار جدید
                <i class="material-icons font-semibold text-indigo-400">create</i>
            </h2>
        </div>
        <div class="flex flex-wrap mb-6">

            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="create_rate">
                    درصد دلار
                </label>
                <input id="create_rate" name="rate" value="" class="block w-full text-gray-700 border-2 border-gray-300 py-3 px-4 mb-3 focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="درصد دلار" required>
            </div>
            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="create_discount">
                    درصد تخفیف
                </label>
                <input id="create_discount" name="create_discount" value="" class="block w-full text-gray-700 border-2 border-gray-300 py-3 px-4 mb-3 focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="درصد دلار" required>
            </div>
            <div class="w-full md:w-1/2 px-3">
                <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="create_date">
                    بازه اول
                </label>
                <input id="create_date" name="date" value="" class="block w-full text-gray-700 border-2 border-gray-300 py-3 px-4 focus:outline-none focus:bg-white focus:border-gray-500" type="date" placeholder="اعمال تا تاریخ" required>
            </div>
            <div class="w-full md:w-1/2 px-3 mt-5">
                <button onclick="saveDollarRate()" class=" shadow bg-gray-500 hover:bg-gray-400 text-sm focus:shadow-outline focus:outline-none text-white font-bold py-5 px-7" type="submit">
                    ثبت نرخ ارز
                </button>
            </div>
        </div>
        </form>
    </div>
</div>
<script>
    const endPointAddress = "../../app/api/callcenter/DollarRateApi.php";

    function toggleActivation(id, type) {
        const params = new URLSearchParams();
        params.append("toggleActivation", "toggleActivation");
        params.append("type", type);
        params.append("rate_id", id);

        axios
            .post(endPointAddress, params)
            .then(function(response) {
                if (response.data == true) {
                    window.location.reload();
                }
            })
            .catch(function(error) {});
    }

    function toggleFormDisplay(form) {
        if (form == "create_block") {
            document.getElementById('create_block').classList.remove("hidden");
            document.getElementById('edit_block').classList.add("hidden");
        } else {
            document.getElementById('create_block').classList.add("hidden");
            document.getElementById('edit_block').classList.remove("hidden");
        }
    }

    function editItem(id) {
        toggleFormDisplay("edit_block");
        const params = new URLSearchParams();
        params.append("getItem", "getItem");
        params.append("rate_id", id);

        axios
            .post(endPointAddress, params)
            .then(function(response) {
                $item = response.data;
                document.getElementById('edit_id').value = $item.id;
                document.getElementById('edit_rate').value = $item.rate;
                document.getElementById('edit_discount').value = $item.discount;
                document.getElementById('edit_date').value = $item.created_at;
            })
            .catch(function(error) {});
    }

    function completeEdit() {
        const id = document.getElementById('edit_id').value;
        const rate = document.getElementById('edit_rate').value;
        const discount = document.getElementById('edit_discount').value;
        const date = document.getElementById('edit_date').value;

        const params = new URLSearchParams();
        params.append("updateItem", "updateItem");
        params.append("id", id);
        params.append("rate", rate);
        params.append("discount", discount);
        params.append("date", date);

        axios
            .post(endPointAddress, params)
            .then(function(response) {
                window.location.reload();
            })
            .catch(function(error) {});
    }

    function saveDollarRate() {
        const rate = document.getElementById('create_rate').value;
        const discount = document.getElementById('create_discount').value;
        const date = document.getElementById('create_date').value;

        const params = new URLSearchParams();
        params.append("createItem", "createItem");
        params.append("rate", rate);
        params.append("discount", discount);
        params.append("date", date);

        axios
            .post(endPointAddress, params)
            .then(function(response) {
                window.location.reload();
            })
            .catch(function(error) {});
    }
</script>
<?php
require_once './components/footer.php';
