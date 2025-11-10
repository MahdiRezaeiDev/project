<?php
$pageTitle = "نمایش قیمت دلار";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

$sql = "SELECT * FROM shop.rates ORDER BY amount ASC";
$stmt = PDO_CONNECTION->prepare($sql);
$stmt->execute();
$rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link href="./assets/css/report.css" rel="stylesheet" />
<div class="rtl max-w-5xl mx-auto py-20 sm:px-6 lg:px-8">
    <div class="mb-3">
        <a href="./registerRates.php" class="flex items-center w-40 bg-sky-600 hover:bg-sky-700 text-sm rounded-md text-white px-4 py-2">
            <i class="px-1 material-icons hover:cursor-pointer">add_circle_outline</i>
            ثبت نرخ ارز جدید</a>
    </div>
    <table class="min-w-full text-left text-sm font-light">
        <thead class="font-medium border border-gray-700">
            <tr class="bg-gray-700">
                <th scope="col" class="px-3 py-3 text-white text-center">
                    نرخ ارز
                </th>
                <th scope="col" class="px-3 py-3 text-white text-center">
                    شاخص نرخ
                </th>
                <th scope="col" class="px-3 py-3 text-white text-center">
                    زنگ شاخص
                </th>
                <th scope="col" class="px-3 py-3 text-white text-center">
                    انتخاب به عنوان پیش فرض
                </th>
                <th scope="col" class="px-3 py-3 text-white text-center">
                    عملیات
                </th>
            </tr>
        </thead>
        <tbody class="border border-dashed border-gray-700" id="results">
            <?php
            if (count($rates) > 0) {
                foreach ($rates as $rate) { ?>
                    <tr class="even:bg-neutral-100 hover:bg-neutral-200">
                        <td class="whitespace-nowrap px-3 py-3 text-center font-bold">
                            <?= $rate['amount'] ?>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-center font-bold">
                            <?= $rate['status'] ?>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-center font-bold <?= $rate['status'] ?>">
                            <?php ?>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-center font-bold">
                            <input type="checkbox" name="selected" id="selected" class="cursor-pointer" <?php if ($rate['selected'] == 1) echo 'checked' ?> data-id="<?= $rate['id'] ?>" onclick="toggleSelected(this)">
                        </td>
                        <td class="whitespace-nowrap w-24">
                            <div class="flex justify-center gap-1 items-center px-2">
                                <a title="ویرایش نرخ" class="cursor-pointer" href="./registerRates.php?form=update&id=<?= $rate['id'] ?>">
                                    <i class="material-icons text-blue-500 hover:text-blue-700">create</i>
                                </a>
                                <a title="حذف نرخ" class="cursor-pointer" type="submit" onclick="confirmDeletion(this)" data-id="<?= $rate['id'] ?>">
                                    <i data-id="<?= $rate['id'] ?>" class="material-icons text-red-600 hover:text-red-800">delete_forever</i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php }
            } else {
                ?>
                <tr v-else class="transition duration-300 ease-in-out bg-neutral-200">
                    <td colspan="5" class="whitespace-nowrap px-3 py-3 text-center text-red-500 font-bold">
                        <i class="material-icons text-red-500">mood_bad</i>
                        <br />
                        !متاسفانه چیزی برای نمایش در پایگاه داده
                        موجود نیست
                    </td>
                </tr>
            <?php
            }
            ?>

        </tbody>
    </table>
</div>
<div class="hidden fixed top-0 right-0 bottom-0 left-0 bg-gray-900 bg-opacity-80">
    <div class="w-full h-full flex justify-center items-center">
        <div class="w-1/2 rounded-lg overflow-hidden">
            <div class="flex justify-between bg-gray-900 p-5">
                <div id="edit_header" class=" px-4 sm:px-0">
                    <h3 class="text-lg font-medium text-white">
                        ویرایش نرخ ارز
                    </h3>
                    <p class=" mt-1 text-sm text-white">
                        برای ویرایش نرخ ارز انتخاب شده اطلاعات ذیل را به دقت ویرایش نمایید.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2 bg-white">
                <form action="" method="post">
                    <div class="p-5">
                        <input type="text" name="form" value="update" hidden>
                        <div class="col-span-6 sm:col-span-4">
                            <label class="block font-medium text-sm text-gray-700">
                                نرخ ارز
                            </label>
                            <input name="rate_price" value="" class="border mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2" required id="serial" type="text" />
                            <p class="mt-2"></p>
                        </div>
                        <!-- Price -->
                        <div class="col-span-6 sm:col-span-4">
                            <label class="block font-medium text-sm text-gray-700" for="status">
                                <span>شاخص نرخ ارز</span></label>
                            <select name="status" class="border mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm px-3 py-2" id="status">
                                <option class="A" value="A">A</option>
                                <option class="B" value="B">B</option>
                                <option class="C" value="C">C</option>
                                <option class="D" value="D">D</option>
                                <option class="E" value="E">E</option>
                                <option class="F" value="F">F</option>
                                <option class="G" value="G">G</option>
                                <option class="N" value="N">N</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-start px-4 py-3 bg-gray-50 text-right">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white">
                            <i class="px-2 material-icons hover:cursor-pointer">import_export</i>
                            ویرایش
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<script>
    function toggleSelected(e) {
        const element = e;
        const id = element.getAttribute('data-id');
        var params = new URLSearchParams();
        params.append('update_selected_rate', 'update_selected_rate');
        params.append('element_id', id);
        params.append('element_value', element.checked);

        axios.post("../../app/api/callcenter/GoodsListApi.php", params)
            .then(function(response) {
                console.log(response);
            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function confirmDeletion(e) {
        const element = e;
        const deleteItem = element.getAttribute('data-id');

        var params = new URLSearchParams();
        params.append('delete_id', deleteItem);
        params.append('Delete_rate', 'Delete_rate');

        let text = "آیا مطمئن هستید که میخواهید عملیات حذف را انجام دهید؟";
        if (confirm(text) == true) {
            axios.post("../../app/api/callcenter/GoodsListApi.php", params)
                .then(function(response) {
                    if (response.data) {
                        location.reload();
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        }

    }
</script>
<?php
require_once './components/footer.php';
