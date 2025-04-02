<?php
$pageTitle = "برررسی تک آیتم";
$iconUrl = 'adjust.svg';
require_once './components/header.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../app/controller/inventory/SingleItemController.php';
require_once '../../layouts/inventory/sidebar.php';
if (isset($_GET['code'])) {
    $code = $_GET['code'] ?? '';
}
?>
<style>
    .A {
        background-color: brown;
    }

    .B {
        background-color: red;
    }

    .C {
        background-color: #b68f3a;
    }

    .D {
        background-color: #b63a95;
    }

    .E {
        background-color: #3a86b6;
    }

    .F {
        background-color: #de6ffa;
    }

    .G {
        background-color: #fc8ea0;
    }

    td.A {
        background-color: rgb(224, 84, 3);
    }

    td.B {
        background-color: rgb(255, 145, 0);
    }

    td.C {
        background-color: #d8a943;
    }

    td.D {
        background-color: #e449ba;
    }

    td.E {
        background-color: #47b0f1;
    }

    td.F {
        background-color: #e58ffa;
    }

    td.G {
        background-color: #f8b6c1;
    }

    .mobis {
        background-color: rgb(190, 187, 187);
    }
</style>
<!-- Search Input Section -->
<section class="flex justify-center">
    <input class="p-3 border-2 border-gray-400 w-72 text-sm outline-none" type="text" name="code" id="code" onkeyup="convertToEnglish(this);
    search(this.value);
    searchGoods(this.value);
    filterExport(this.value);" placeholder="کد مد نظر خود را بصورت کامل وارد کنید">
</section>

<!-- goods search section -->
<section class="p-5 mb-4 bg-white rounded-lg mx-5" id="price">
    <h2 class="text-xl font-bold mb-3">قیمت قطعه</h2>
    <table class="w-full">
        <thead class="font-medium">
            <tr class="bg-gray-900">
                <th class="text-sm text-white font-semibold p-3 w-52 text-center">
                    شماره فنی
                </th>
                <th class="text-right text-sm text-white font-semibold p-3 w-20">
                    دلار پایه
                </th>
                <th class="text-right text-sm text-white font-semibold p-3 border-black border-l-2">
                    +10%
                </th>
                <?php if (count($rates) > 0) :
                    // output data of each row
                    foreach ($rates as $rate) : ?>
                        <th class="<?= $rate['status'] ?> text-center text-sm text-white font-semibold p-3">
                            <?= $rate['amount'] ?>
                        </th>
                <?php
                    endforeach;
                endif;
                ?>
                <th class="text-right text-sm text-white font-semibold p-3 text-white w-32 text-center">
                    عملیات
                </th>
                <th class="text-right text-sm text-white font-semibold p-3 text-white">
                    وزن
                </th>
            </tr>
        </thead>
        <tbody id="results">
        </tbody>
    </table>
</section>

<!-- good existing section -->
<section class="p-5 mb-4 bg-white rounded-lg mx-5" id="existing">
    <h2 class="text-xl font-bold mb-3">موجودی انبار</h2>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-900">
                <th class="text-white text-sm p-3">#</th>
                <th class="text-white text-sm p-3">شماره فنی</th>
                <th class="text-white text-sm p-3">برند</th>
                <th class="text-white text-sm p-3">تعداد موجود</th>
                <th class="text-white text-sm p-3">فروشنده</th>
                <th class="text-white text-sm p-3">راهرو</th>
                <th class="text-white text-sm p-3">قفسه</th>
                <th class="text-white text-sm p-3">توضیحات</th>
                <th class="text-white text-sm p-3">انبار</th>
            </tr>
        </thead>
        <tbody id="existingResult" style="background-color: white;">
        </tbody>
    </table>
</section>

<!-- purchase and sells section -->
<section class="p-5 mb-4 bg-white rounded-lg mx-5" id="export">
    <h2 class="text-xl font-bold mb-3">گزارش ورود / خروج</h2>
    <table class="w-full">
        <thead>
            <tr class="bg-gray-900">
                <th class="p-3 text-sm text-white" title="">#</th>
                <th class="p-3 text-sm text-white" title="شماره فنی">شماره فنی</th>
                <th class="p-3 text-sm text-white" title="برند">برند</th>
                <th class="p-3 text-sm text-white" title="توضیحات ورود">توضیحات</th>
                <th class="p-3 text-sm text-white" title="تعداد">تعداد</th>
                <th class="p-3 text-sm text-white" title="فروشنده">فروشنده</th>
                <th class="p-3 text-sm text-white" title="خریدار">خریدار</th>
                <th class="p-3 text-sm text-white" title="تاریخ خروج">تاریخ</th>
                <th class="p-3 text-sm text-white" title="شماره فاکتور خروج">شماره فاکتور</th>
                <th class="p-3 text-sm text-white" title="انبار">انبار</th>
                <th class="p-3 text-sm text-white" title="کاربر">کاربر</th>
                <th class="p-3 text-sm text-white" title="کاربر">نوعیت</th>
            </tr>
        </thead>
        <tbody id="filterExportResultBox">
        </tbody>
    </table>
</section>

<!-- scripts related to searching -->
<script>
    let result = null;
    const search = (val) => {
        let pattern = val;
        const resultBox = document.getElementById("results");
        if (pattern.length >= 10) {
            pattern = pattern.replace(/\s/g, "");
            pattern = pattern.replace(/-/g, "");
            pattern = pattern.replace(/_/g, "");

            resultBox.innerHTML = `<tr class=''>
            <td colspan='14' class='py-10 text-center'> 
                <img style='width: 60px; margin-block:30px' class=' block w-10 mx-auto h-auto' 
                src='../../public/img/loading.png' alt='loading'>
                </td>
            </tr>`;
            var params = new URLSearchParams();
            params.append('pattern', pattern);
            params.append('pattern', pattern);

            axios.post("../../app/api/callcenter/SearchGoodsApi.php", params)
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

    function searchGoods(pattern) {
        if (pattern.length >= 10) {
            pattern = pattern.replace(/\s/g, "");
            pattern = pattern.replace(/-/g, "");
            pattern = pattern.replace(/_/g, "");
            const resultBox = document.getElementById('existingResult');
            resultBox.innerHTML = `<tr class=''>
            <td colspan='14' class='py-10 text-center'> 
                <img style='width: 60px; margin-block:30px' class=' block w-10 mx-auto h-auto' 
                src='../../public/img/loading.png' alt='loading'>
                </td>
            </tr>`;
            var params = new URLSearchParams();
            params.append('searchGoods', 'searchGoods');
            params.append('pattern', pattern);

            axios.post("../../app/api/inventory/ExistingGoodsApi.php", params)
                .then(function(response) {
                    resultBox.innerHTML = response.data;
                })
                .catch(function(error) {
                    console.log(error);
                });
        }
    }

    function filterExport(partNumber_value = null) {

        if (partNumber_value.length >= 10) {
            var params = new URLSearchParams();
            params.append('submit_filter', 'submit_filter');
            params.append('partNumber', partNumber_value);
            const resultBox = document.getElementById('filterExportResultBox');
            resultBox.innerHTML = `
                                <tr class=''>
                                    <td colspan='14' class='py-10 text-center'> 
                                        <img style='width: 60px; margin-block:30px' class=' block w-10 mx-auto h-auto' 
                                        src='../../public/img/loading.png' alt='loading'>
                                    </td>
                                </tr>`;
            axios.post("../../app/api/inventory/SingleItemReportApi.php", params)
                .then(function(response) {
                    const data = response.data;
                    resultBox.innerHTML = '';
                    let counter = 1;
                    if (data.length > 0) {
                        for (item of data) {
                            if (item.import) {
                                resultBox.innerHTML += `
                                    <tr style="background-color:lightblue">
                                        <td class="text-center text-sm font-semibold">${ counter }</td>
                                        <td class="text-center text-sm font-semibold">${ (item.partnumber) }</td>
                                        <td class="text-center text-sm font-semibold">${ item.brandName }</td>
                                        <td class="text-center text-xs font-semibold">${ item.description }</td>
                                        <td class="text-center text-sm font-semibold">${ item.quantity }</td>
                                        <td class="text-center text-sm font-semibold">${ item.sellerName }</td>
                                        <td class="text-center text-sm font-semibold">-</td>
                                        <td class="text-center text-sm font-semibold">${ item.invoice_date }</td>
                                        <td  class="text-center text-sm font-semibold">${item.invoice_number}</td>
                                        <td class="text-center text-sm font-semibold">${ item.stockName }</td>
                                        <td class="text-center text-sm font-semibold">${ item.username }</td>
                                        <td class="text-center text-sm font-semibold">
                                            <p class="bg-green-600 text-xs text-white rounded py-2">
                                            ورود
                                            </p>
                                        </td>
                                    </tr>
                                    `;
                            } else {
                                resultBox.innerHTML += `
                                    <tr style="background-color:lightyellow">
                                        <td class="text-center">${ counter }</td>
                                        <td class="text-center text-sm font-semibold">${ (item.partnumber) }</td>
                                        <td class="text-center text-sm font-semibold">${ item.brandName }</td>
                                        <td class="text-center text-xs font-semibold">${ item.description }</td>
                                        <td class="text-center text-sm font-semibold">${ item.quantity }</td>
                                        <td class="text-center text-sm font-semibold">${ item.sellerName }</td>
                                        <td class="text-center text-sm font-semibold">${ item.customer }</td>
                                        <td class="text-center text-sm font-semibold">${ item.invoice_date }</td>
                                        <td class="text-center text-sm font-semibold">${ item.invoice_number }</td>
                                        <td class="text-center text-sm font-semibold">${ item.stockName }</td>
                                        <td class="text-center text-sm font-semibold">${ item.username }</td>
                                        <td class="text-center text-sm font-semibold">
                                            <p class="bg-red-600 text-xs text-white rounded py-2">
                                                خروج
                                            </p>
                                        </td>
                                    </tr>
                                    `;
                            }
                            counter++;
                        }
                    } else {
                        resultBox.innerHTML += `
                                        <tr style="background-color:lightyellow">
                                            <td colspan="12" class="text-center">نتیجه ای دریافت نشد</td>
                                        </tr>`;
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        }
    }

    <?php
    if (isset($code)) {
        echo "search('$code');";
        echo "searchGoods('$code');";
        echo "filterExport('$code');";
    }
    ?>
</script>
<?php
require_once './components/footer.php';
?>