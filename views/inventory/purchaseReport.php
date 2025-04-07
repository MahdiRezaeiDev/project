<?php
$pageTitle = "گزارش ورود کالا";
$iconUrl = 'purchase.svg';
require_once './components/header.php';
require_once '../../app/controller/inventory/PurchaseController.php';
require_once '../../utilities/inventory/InventoryHelpers.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php'; ?>
<section class="px-3">
    <?php require_once './components/reports/filter.php'; ?>
    <table id="reportTable" class="w-full">
        <thead>
            <tr class="bg-gray-800 text-white border-2 border-gray-800">
                <th class="text-xs p-3 font-bold">#</th>
                <th class="text-xs p-3 font-bold">شماره فنی</th>
                <th class="text-xs p-3 font-bold">برند</th>
                <th class="text-xs p-3 font-bold w-52">توضیحات</th>
                <th class="text-xs p-3 font-bold">تعداد</th>
                <th class="text-xs p-3 font-bold">راهرو</th>
                <th class="text-xs p-3 font-bold">قفسه</th>
                <th class="text-xs p-3 font-bold">فروشنده</th>
                <th class="text-xs p-3 font-bold">زمان ورود</th>
                <th class="text-xs p-3 font-bold">تاریخ ورود</th>
                <th class="text-xs p-3 font-bold">تحویل دهنده</th>
                <th class="text-xs p-3 font-bold">فاکتور</th>
                <th class="text-xs p-3 font-bold">شماره فاکتور</th>
                <th class="text-xs p-3 font-bold">تاریخ فاکتور</th>
                <th class="text-xs p-3 font-bold">ورود به انبار</th>
                <th class="text-xs p-3 font-bold">انبار</th>
                <th class="text-xs p-3 font-bold">کاربر</th>
                <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                    <th class="operation">
                        <img src="./assets/icons/setting.svg" alt="settings icon">
                    </th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody id="resultBox">
            <?php
            $counter = 1;
            $billItemsCount = 0;
            if (count($purchaseList) > 0) :
                $invoice_number = $purchaseList[0]['invoice_number'] ?? 'x';
                foreach ($purchaseList as $item) :
                    $date = $item["purchase_time"];
                    $array = explode(' ', $date);
                    list($year, $month, $day) = explode('-', $array[0]);
                    list($hour, $minute, $second) = explode(':', $array[1]);
                    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
                    $purchaseTime = jdate("H:i", $timestamp, "", "Asia/Tehran", "en");
                    $purchaseDate = jdate("Y/m/d", $timestamp, "", "Asia/Tehran", "en");

                    if ($invoice_number !== $item["invoice_number"]) :
                        $invoice_number = $item["invoice_number"];
                        if ($counter > 1) : ?>
                            <tr class="bg-gray-300 border-t-0 border-2 border-gray-800">
                                <td class="py-2 text-center text-sm font-semibold" colspan="20">
                                    مجموع اقلام <?= $billItemsCount ?>
                                </td>
                            </tr>
                            <tr class="py-2 border-2 border-gray-800 border-x-0">
                                <td class="py-5" colspan="20"></td>
                            </tr>
                    <?php
                        endif;

                        $billItemsCount = 0; // Reset for the new bill
                    endif;
                    $billItemsCount += $item["purchase_quantity"]; ?>
                    <tr class="border-b-0 border-x-2 border-y-0 border-gray-800 even:bg-sky-100">
                        <td class="text-xs text-center w-2.5"><?= $counter ?></td>
                        <td class="p-2 text-center text-lg text-white font-semibold uppercase bg-sky-500">
                            <?= '&nbsp;&#8203;' . $item["partnumber"] . PHP_EOL ?>
                        </td>
                        <td class="p-2 text-center text-sm text-white <?= getBrandBackground(strtoupper($item["brand_name"])) ?> font-semibold uppercase"><?= $item["brand_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold" ondblclick="makeEditable(this)" data-field="des" data-id="<?= $item['purchase_id'] ?>"><?= $item["purchase_description"] ?></td>
                        <td class=" p-2 text-center text-sm font-semibold"><?= $item["purchase_quantity"] ?></td>
                        <td class="p-2 text-center text-sm uppercase font-semibold" ondblclick="makeEditable(this)" data-field="pos1" data-id="<?= $item['purchase_id'] ?>">
                            <?= $item["purchase_position1"] ?>
                        </td>
                        <td class="p-2 text-center text-sm uppercase font-semibold" ondblclick="makeEditable(this)" data-field="pos2" data-id="<?= $item['purchase_id'] ?>">
                            <?= $item["purchase_position2"] ?>
                        </td>
                        <td class="p-2 text-sm uppercase text-center font-semibold <?= $item["seller_id"] == 61 ? 'text-white bg-red-600' : 'bg-yellow-500' ?>"><?= $item["seller_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $purchaseTime ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $purchaseDate ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["deliverer_name"] ?></td>
                        <td class="p-2 text-center">
                            <?php if ($item["purchase_hasBill"] == 1) : ?>
                                <img class="block mx-auto" src="./assets/icons/tick.svg" alt="TICK ICON">
                            <?php else : ?>
                                <img class="block mx-auto" src="./assets/icons/cross.svg" alt="CROSS ICON">
                            <?php endif; ?>
                        </td>
                        <td class="p-2 text-sm text-center font-semibold text-rose-500"><?= $item["invoice_number"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= substr($item["invoice_date"], 5) ?></td>
                        <td class="p-2 text-center">
                            <?php if ($item["purchase_isEntered"] == 1) : ?>
                                <img class="block mx-auto" src="./assets/icons/tick.svg" alt="TICK ICON">
                            <?php else : ?>
                                <img class="block mx-auto" src="./assets/icons/cross.svg" alt="CROSS ICON">
                            <?php endif; ?>
                        </td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["stock_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["username"] ?></td>
                        <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                            <td class="text-xs w-8">
                                <a href="./editPurchase.php?record=<?= $item["purchase_id"] ?>" target="_blank" class="cursor-pointer">
                                    <img class="mx-auto w-5 h-5" src="./assets/icons/edit.svg" alt="edit icon">
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>

                    <?php
                    if ($counter == count($purchaseList)) : ?>
                        <tr class="bg-gray-300 border-t-0 border-2 border-gray-800">
                            <td class="py-2 text-center text-sm font-semibold" colspan="20">
                                مجموع اقلام <?= $billItemsCount ?>
                            </td>
                        </tr>
                <?php
                    endif;
                    $counter++;
                endforeach;
            else : ?>
                <tr class="">
                    <td colspan="18" class="bg-rose-500 text-sm text-white text-center font-semibold border-x-2 border-t-0 border-rose-500 py-2">
                        متاسفانه موردی پیدا نشد
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
<div id="editModal" class="fixed right-0 left-0 top-0 bottom-0 bg-gray-900 bg-opacity-90 flex items-center">
    <div class="bg-white rounded-lg w-full h-5/6 p-5 mx-20 mt-5">
        <div class="flex justify-between items-center py-3">
            <h2 class="text-xl font-semibold">ویرایش فاکتور ورودی</h2>
            <img title="بستن" onclick="closeModal()" class="cursor-pointer" src="./assets/icons/cross.svg" alt="close icon">
        </div>
        <div id="edit_purchase" class="w-full h-full">
            <!-- data to be placed -->
        </div>
    </div>
</div>
<script>
    const elements = [
        'partNumber',
        'seller',
        'brand',
        'pos1',
        'pos2',
        'stock',
        'user',
        'invoice_number',
        'invoice_time',
        'exit_time'
    ];

    function getElementValue(id, defaultValue = null) {
        const element = document.getElementById(id);
        return element && element.value !== '' && element.value !== defaultValue ? element.value : null;
    }

    function filterReport() {
        const values = elements.reduce((acc, id) => {
            acc[id] = getElementValue(id, id.includes('seller') ? 'انتخاب فروشنده' :
                id.includes('brand') ? 'انتخاب برند جنس' :
                id.includes('stock') ? 'انتخاب انبار' :
                id.includes('user') ? 'انتخاب کاربر' : '');
            return acc;
        }, {});

        filter(values);
    }

    function clearFilter() {
        elements.forEach(id => {
            const defaultValue = id.includes('seller') ? 'انتخاب فروشنده' :
                id.includes('brand') ? 'انتخاب برند جنس' :
                id.includes('stock') ? 'انتخاب انبار' :
                id.includes('user') ? 'انتخاب کاربر' : '';
            document.getElementById(id).value = defaultValue;
        });
    }

    function filter(params) {
        const urlParams = new URLSearchParams({
            submit_filter: 'submit_filter',
            ...params
        });

        const resultBox = document.getElementById('resultBox');
        resultBox.innerHTML = `
        <tr>
            <td colspan='18' class='py-5'>
                <img class='w-16 h-16 block mx-auto' src='../../public/img/loading.png' alt='loading'>
                <p class="pt-2 text-gray-700 text-center">لطفا صبور باشید</p>
            </td>
        </tr>`;

        axios.post("../../app/api/inventory/PurchaseReportApi.php", urlParams)
            .then(response => {
                resultBox.innerHTML = response.data;
            })
            .catch(error => {
                console.error(error);
            });
    }

    function displayModal(element) {
        editModal.style.display = 'flex';
        const record_id = element.getAttribute('data-target');

        const urlParams = new URLSearchParams({
            edit_purchase: 'edit_purchase',
            record: record_id
        });

        const resultBox = document.getElementById('edit_purchase');
        resultBox.innerHTML = `
        <tr>
            <td colspan='18' class='py-5'>
                <img class='w-16 h-16 block mx-auto' src='../../public/img/loading.png' alt='loading'>
                <p class="pt-2 text-gray-700 text-center">لطفا صبور باشید</p>
            </td>
        </tr>`;

        axios.post("./editPurchase.php", urlParams)
            .then(response => {
                resultBox.innerHTML = response.data;
            })
            .catch(error => {
                console.error(error);
            });
    }

    function closeModal() {
        editModal.style.display = 'none';
    }

    var modal = document.getElementById("editModal");

    $(function() {
        $(".exportToExcel").click(function(e) {
            var table = $('#report-table');
            if (table && table.length) {
                var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
                $(table).table2excel({
                    exclude: ".noExl",
                    name: "Exit Report",
                    filename: "Exit Report " + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xlsx",
                    fileext: ".xlsx",
                    exclude_img: true,
                    exclude_links: true,
                    exclude_inputs: true,
                    preserveColors: preserveColors
                });
            }
        });

    });

    $(function() {
        $("#invoice_time").persianDatepicker({
            formatDate: "YYYY/0M/0D",
        });
        $("#exit_time").persianDatepicker({
            formatDate: "YYYY/0M/0D",
        });
    });

    function matchCustom(params, data) {
        // If there are no search terms, return all of the data
        if ($.trim(params.term) === '') {
            return data;
        }

        // Do not display the item if there is no 'text' property
        if (typeof data.text === 'undefined') {
            return null;
        }

        // `params.term` should be the term that is used for searching
        // `data.text` is the text that is displayed for the data object
        if (data.text.indexOf(params.term) > -1) {
            var modifiedData = $.extend({}, data, true);
            modifiedData.text += '';

            // You can return modified objects from here
            // This includes matching the `children` how you want in nested data sets
            return modifiedData;
        }

        // Return `null` if the term should not be displayed
        return null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[ondblclick="makeEditable(this)"]').forEach(cell => {
            cell.addEventListener('blur', function() {
                const id = this.getAttribute('data-id');
                const field = this.getAttribute('data-field');
                const newValue = this.textContent;

                if (confirm('آیا از ذخیره اطلاعات اطمینان دارید؟')) {
                    // Send the updated data to the server
                    updateDatabase(id, field, newValue);
                } else {
                    // Restore the original value
                    this.textContent = this.getAttribute('data-original-value');
                    this.removeAttribute('contenteditable');
                }
            });
        });
    });

    function makeEditable(cell) {
        cell.setAttribute('contenteditable', 'true');
        cell.setAttribute('data-original-value', cell.textContent); // Store the original value
        cell.focus();
    }

    function updateDatabase(id, field, value) {
        const params = new URLSearchParams();
        params.append('edit_purchase', 'edit');
        params.append('id', id);
        params.append('field', field);
        params.append('value', value);

        axios.post('../../app/api/inventory/PurchaseEditApi.php', params)
            .then(response => {
                // Optionally, remove contenteditable attribute after update
                document.querySelector(`[data-id="${id}"][data-field="${field}"]`).removeAttribute('contenteditable');
            })
            .catch(error => {
                console.error('Error updating record:', error);
            });
    }
</script>
<?php require_once './components/footer.php'; ?>