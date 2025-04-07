<?php
$pageTitle = "گزارش انتقالات به انبار";
$iconUrl = 'transfer.svg';
require_once './components/header.php';
require_once '../../app/controller/inventory/TransferReportController.php';
require_once '../../utilities/inventory/InventoryHelpers.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php'; ?>
<section class="px-5">
    <table id="report-table" class="w-full">
        <thead>
            <tr class="bg-gray-900">
                <th class="p-3 text-white text-sm font-semibold">#</th>
                <th class="p-3 text-white text-sm font-semibold">شماره فنی</th>
                <th class="p-3 text-white text-sm font-semibold">برند</th>
                <th class="p-3 text-white text-sm font-semibold">توضیحات</th>
                <th class="p-3 text-white text-sm font-semibold">راهرو</th>
                <th class="p-3 text-white text-sm font-semibold">قفسه</th>
                <th class="p-3 text-white text-sm font-semibold">انبار مبدا</th>
                <th class="p-3 text-white text-sm font-semibold"> تعداد قبلی</th>
                <th class="p-3 text-white text-sm font-semibold">انبار مقصد</th>
                <th class="p-3 text-white text-sm font-semibold">تعداد منتقل شده</th>
                <th class="p-3 text-white text-sm font-semibold">فروشنده</th>
                <th class="p-3 text-white text-sm font-semibold">تحویل گیرنده</th>
                <th class="p-3 text-white text-sm font-semibold">تاریخ انتقال </th>
                <th class="p-3 text-white text-sm font-semibold">کاربر</th>
                <th class="p-3 text-white text-sm font-semibold" style="color: red;"> &#10084;</th>
                <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                    <th class="p-3 text-white text-sm font-semibold" style="color: red;">
                        <i class="fa fa-cos" aria-hidden="true"></i>
                    </th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody id="resultBox">
            <tr class="bg-cyan-400">
                <td class="p-3 text-center text-md font-bold" colspan="16">عملیات روزهای امروز</td>
            </tr>
            <?php
            if (count($todays_records)) :
                foreach ($todays_records as $index => $result) : ?>
                    <tr class="even:bg-gray-100">
                        <td class="text-xs text-center font-semibold"><?= $index + 1 ?></td>
                        <td class="p-2 text-center text-lg text-white font-semibold uppercase bg-sky-500"><?= $result["partnumber"] ?></td>
                        <td class="p-2 text-center text-sm text-white <?= getBrandBackground(strtoupper($result["brand_name"])) ?> font-semibold uppercase"><?= $result["brand_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $result["des"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $result["pos1"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $result["pos2"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= getStockName($result["stock_id"]) ?></td>
                        <td class="p-2 text-center text-sm font-bold bg-yellow-100"><?= $result["prev_quantity"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= getStockName($result["stock"]) ?></td>
                        <td class="p-2 text-center text-sm font-bold bg-yellow-100 "><?= $result["quantity"] ?></td>
                        <td class="p-2 text-sm uppercase text-center font-semibold <?= $item["seller_id"] == 61 ? 'text-white bg-red-600' : 'bg-yellow-500' ?>"><?= $result["seller_name"] ?></td>
                        <td class="p-2 text-sm uppercase text-center font-semibold <?= $item["seller_id"] == 61 ? 'text-white bg-red-600' : 'bg-orange-400' ?>"><?= $result["getter_name"] ?></td>
                        <td class="text-xs text-center font-semibold"><?= explode(' ', $result["transfer_date"])[0] ?></td>
                        <td class="text-xs text-center font-semibold"><?= $result["user_name"] ?></td>
                        <td class="text-xs text-center font-semibold" style="width:5px">
                            <input type="checkbox" name="select for print" id="select">
                        </td>
                        <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                            <td>
                                <a onclick="displayModal(this)" id="<?= $result['qtybanck_id'] ?>" class="edit-rec2">
                                    <img src="./assets/icons/edit.svg" alt="edit icon">
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
            <?php endforeach;
            else :
                echo "<tr class='bg-red-300'>
                        <td colspan='16'>
                            <p class='text-center text-white p-3'> موردی پیدا نشد</p>
                        </td>
                    </tr>";
            endif; ?>
            <tr style="background-color: transparent;">
                <td class="py-4" colspan="16"></td>
            </tr>
            <tr style="background-color: transparent;">
                <td class="py-4" colspan="16"></td>
            </tr>
            <tr class="bg-cyan-400">
                <td class="p-3 text-center text-md font-bold" colspan="16">عملیات روزهای قبل</td>
            </tr>
            <?php
            if (count($previous_records)) :
                foreach ($previous_records as $index => $result) : ?>
                    <tr class="even:bg-gray-100">
                        <td class="text-xs text-center font-semibold"><?= $index + 1 ?></td>
                        <td class="p-2 text-center text-lg text-white font-semibold uppercase bg-sky-500"><?= $result["partnumber"] ?></td>
                        <td class="p-2 text-center text-sm text-white <?= getBrandBackground(strtoupper($result["brand_name"])) ?> font-semibold uppercase"><?= $result["brand_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $result["des"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $result["pos1"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $result["pos2"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= getStockName($result["stock_id"]) ?></td>
                        <td class="p-2 text-center text-sm font-bold bg-yellow-100"><?= $result["prev_quantity"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= getStockName($result["stock"]) ?></td>
                        <td class="p-2 text-center text-sm font-bold bg-yellow-100 "><?= $result["quantity"] ?></td>
                        <td class="p-2 text-sm uppercase text-center font-semibold <?= $item["seller_id"] == 61 ? 'text-white bg-red-600' : 'bg-yellow-500' ?>"><?= $result["seller_name"] ?></td>
                        <td class="p-2 text-sm uppercase text-center font-semibold <?= $item["seller_id"] == 61 ? 'text-white bg-red-600' : 'bg-orange-400' ?>"><?= $result["getter_name"] ?></td>
                        <td class="text-xs text-center font-semibold"><?= explode(' ', $result["transfer_date"])[0] ?></td>
                        <td class="text-xs text-center font-semibold"><?= $result["user_name"] ?></td>
                        <td class="text-xs text-center font-semibold" style="width:5px">
                            <input type="checkbox" name="select for print" id="select">
                        </td>
                        <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                            <td>
                                <a onclick="displayModal(this)" id="<?= $result['qtybanck_id'] ?>" class="edit-rec2">
                                    <img src="./assets/icons/edit.svg" alt="edit icon">
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
            <?php endforeach;
            else :
                echo "<tr class='bg-red-300'>
                        <td colspan='16'>
                            <p class='text-center text-white p-3'> موردی پیدا نشد</p>
                        </td>
                    </tr>";
            endif; ?>
        </tbody>
    </table>
</section>
<div id="updateModal" class="fixed inset-0 bg-gray-900 hidden justify-center items-center p-5">
    <div class="w-full bg-white rounded-lg shadow p-5 my-10" style="height: 85% !important;">
        <div class="flex justify-between">
            <h2 class="text-xl font-bold">ویرایش انتقال به انبار </h2>
            <img onclick="closeModal()" src="./assets/icons/cross.svg" alt="close icon">
        </div>
        <div class="bg-green h-full">
            <iframe class="w-full" style="height: 100% !important;" id="updateModalIframe" src="./editPurchase.php" frameborder="0"></iframe>
        </div>
    </div>
</div>
<div class="fixed w-full bottom-0 p-4 bg-gray-300 shadow-t">
    <button class="bg-sky-600 text-white px-5 py-1 rounded " onclick="makePreview()">فاکتور گیری</button>
</div>

<script>
    const partNumber = document.getElementById('partNumber');
    const seller = document.getElementById('seller');
    const brand = document.getElementById('brand');
    const pos1 = document.getElementById('pos1');
    const pos2 = document.getElementById('pos2');
    const stock = document.getElementById('stock');
    const user = document.getElementById('user');
    const invoice_number = document.getElementById('invoice_number');
    const invoice_time = document.getElementById('invoice_time');
    const updateModal = document.getElementById('updateModal');

    function filterReport() {
        const partNumber_value = partNumber.value === '' ? null : partNumber.value;
        const seller_value = seller.value === 'انتخاب فروشنده' ? null : seller.value;
        const brand_value = brand.value === 'انتخاب برند جنس' ? null : brand.value;
        const pos1_value = pos1.value === '' ? null : pos1.value;
        const pos2_value = pos2.value === '' ? null : pos2.value;
        const stock_value = stock.value === 'انتخاب انبار' ? null : stock.value;
        const user_value = user.value === 'انتخاب کاربر' ? null : user.value;
        const invoice_number_value = invoice_number.value === '' ? null : invoice_number.value;
        const invoice_time_value = invoice_time.value === '' ? null : invoice_time.value;

        filter(partNumber_value, seller_value, brand_value, pos1_value, pos2_value,
            stock_value, user_value, invoice_number_value, invoice_time_value);
    }

    function clearFilter() {
        partNumber.value = '';
        seller.value = 'انتخاب فروشنده';
        brand.value = 'انتخاب برند جنس';
        pos1.value = '';
        pos2.value = '';
        stock.value = 'انتخاب انبار';
        user.value = 'انتخاب کاربر';
        invoice_number.value = '';
        invoice_time.value = '';
        document.getElementById('select2-seller-container').innerHTML = 'انتخاب فروشنده';
        document.getElementById('select2-brand-container').innerHTML = 'انتخاب برند جنس';
        document.getElementById('select2-stock-container').innerHTML = 'انتخاب انبار';
        document.getElementById('select2-user-container').innerHTML = 'انتخاب کاربر';
    }

    function displayModal(element) {
        const id = element.getAttribute('id');

        // Clear the iframe src and show a loading message or indicator
        updateModalIframe.src = '';
        updateModal.style.display = 'flex';

        // Optional: Display a loading message or indicator
        updateModalIframe.contentWindow.document.open();
        updateModalIframe.contentWindow.document.write('<p style="font-size:40px; text-align:center; text:red">... لطفا صبور باشید</p>');
        updateModalIframe.contentWindow.document.close();

        // Set the new src after a slight delay to ensure the iframe has been cleared
        setTimeout(() => {
            updateModalIframe.src = './editTransfer.php?record=' + id;
        }, 100);
    }

    function closeModal() {
        updateModal.style.display = 'none';
    }
    var modal = document.getElementById("updateModal");
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

<?php
require_once './components/footer.php';
?>