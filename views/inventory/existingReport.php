<?php
$pageTitle = "گزارش موجودی کالا";
$iconUrl = 'stock.svg';
require_once './components/header.php';
require_once '../../app/controller/inventory/ExistingController.php';
require_once '../../utilities/inventory/InventoryHelpers.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php'; ?>
<script>
    const endpointAddress = "../../app/api/inventory/ExistingGoodsApi.php";

    function searchGoods(pattern) {
        const resultBox = document.getElementById('existing_result_box');
        pattern = pattern.trim().replace(/\s+/g, '');
        if (pattern.length < 7) {
            return;
        }

        resultBox.innerHTML = `<tr>
                                    <td colspan="9" class="text-center text-white p-5 font-semibold">
                                        <img class="w-12 h-12 mx-auto" src="../../public/img/loading.png" />
                                    </td>
                                </tr>`;
        var params = new URLSearchParams();
        params.append('searchGoods', 'searchGoods');
        params.append('pattern', pattern);
        axios.post(endpointAddress, params)
            .then(function(response) {
                let goods = Object.values(response.data);
                resultBox.innerHTML = '';
                resultBox.innerHTML = response.data;
            })
            .catch(function(error) {
                console.log(error);
            });

    }
</script>
<section class="px-5">
    <div class="flex justify-between items-center bg-gray-200 rounded shadow p-5 mb-5">
        <input class="p-2 border-2 border-gray-400 text-sm w-72 outline-gray-500 uppercase" id="search" onkeyup="searchGoods(this.value)" type="text" placeholder="جستجو به اساس کدفنی">
        <a href="./existingExcel.php" class="text-white text-sm font-semibold bg-green-800 rounded px-5 py-2">اکسل</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-800">
                <th class="p-3 text-white text-sm font-semibold">#</th>
                <th class="p-3 text-white text-sm font-semibold">شماره فنی</th>
                <th class="p-3 text-white text-sm font-semibold">برند</th>
                <th class="p-3 text-white text-sm font-semibold">تعداد موجود</th>
                <th class="p-3 text-white text-sm font-semibold">فروشنده</th>
                <th class="p-3 text-white text-sm font-semibold">راهرو</th>
                <th class="p-3 text-white text-sm font-semibold">قفسه</th>
                <th class="p-3 text-white text-sm font-semibold">توضیحات</th>
                <th class="p-3 text-white text-sm font-semibold">انبار</th>
            </tr>
        </thead>
        <tbody id="existing_result_box">
            <?php if (!empty($purchasedGoods)) :
                // insertIntoNewQtyBank($purchasedGoods);
            ?>
                <?php foreach ($purchasedGoods as $index => $good) :
                    if ($good['remaining_qty'] > 0) :
                ?>
                        <tr class="<?= $good['is_transfered'] ? 'bg-orange-400' : 'even:bg-sky-100' ?>">
                            <td class="text-xs text-center w-2.5"><?= $index + 1 ?></td>
                            <td class="p-2 text-center text-lg text-white font-semibold uppercase <?= $item['sellerName'] == 'کاربر دستوری' ? 'bg-orange-400' : 'bg-sky-500' ?>">
                                &nbsp;<?= $good['sellerName'] == 'کاربر دستوری' ? '
                                    <img class=" absolute right-12 shake" src="./assets/icons/warning.svg" />'
                                            : '' ?>
                                <?= $good["partNumber"] . ' '; ?>
                            </td>
                            <td class="p-2 text-center text-sm text-white <?= getBrandBackground(strtoupper($good["brandName"])) ?> font-semibold uppercase"><?= $good["brandName"] ?></td>
                            <td class="p-2 text-center text-sm font-bold bg-yellow-100"><?= $good['remaining_qty'] ?></td>
                            <td class="p-2 text-sm uppercase text-center font-semibold <?= $item["seller_id"] == 61 ? 'text-white bg-red-600' : 'bg-yellow-500' ?>"><?= $good["sellerName"] ?></td>
                            <td class="p-2 text-center text-sm font-bold uppercase"><?= $good["pos1"] ?></td>
                            <td class="p-2 text-center text-sm font-bold uppercase"><?= $good["pos2"] ?></td>
                            <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $good["quantityDescription"] ?></td>
                            <td class="p-2 text-xs text-gray-700 text-center font-semibold">
                                <?php
                                $stock = trim($good["stockName"]);
                                $theme = '';
                                if ($stock == "خاوران") {
                                    $theme = 'bg-red-500';
                                } elseif ($stock == 'یدک شاپ') {
                                    $theme = 'bg-green-700';
                                } elseif ($stock == 'انبار مشترک') {
                                    $theme = 'bg-cyan-700';
                                } elseif ($stock == 'فرشاد') {
                                    $theme = 'bg-blue-500';
                                } elseif ($stock == 'دوبی') {
                                    $theme = 'bg-gray-800';
                                } elseif ($stock == 'انبار 2') {
                                    $theme = 'bg-sky-600';
                                } elseif ($stock == 'لنتور') {
                                    $theme = 'bg-cyan-500';
                                } elseif ($stock == 'چین') {
                                    $theme = 'bg-cyan-500';
                                } elseif ($stock == 'واردات') {
                                    $theme = 'bg-sky-600"';
                                } else {
                                    $theme = 'bg-cyan-500';
                                }
                                ?>
                                <p class='text-xs py-2 mx-2 rounded text-white <?= $theme ?>'> <?= $stock ?> </p>
                            </td>
                        </tr>
                <?php
                    endif;
                endforeach;
            else : ?>
                <tr>
                    <td colspan="9" class="text-center bg-red-500 text-sm text-white p-4 font-semibold">
                        <p>اطلاعاتی موجود نیست</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<?php require_once './components/footer.php'; ?>