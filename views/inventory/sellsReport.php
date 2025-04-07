<?php
$pageTitle = "گزارش خروج کالا";
$iconUrl = 'purchase.svg';
require_once './components/header.php';
require_once '../../app/controller/inventory/SellsReportController.php';
require_once '../../utilities/inventory/InventoryHelpers.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php';
?>
<section class="px-3">
    <?php require_once './components/reports/filter.php'; ?>
    <table id="reportTable" class="w-full">
        <thead>
            <tr class="bg-gray-800 text-white border-2 border-gray-800">
                <th class="text-xs p-3 font-bold" title="">#</th>
                <th class="text-xs p-3 font-bold" title="شماره فنی">شماره فنی</th>
                <th class="text-xs p-3 font-bold" title="برند">برند</th>
                <th class="text-xs p-3 font-bold" title="توضیحات ورود">توضیحات و</th>
                <th class="text-xs p-3 font-bold" title="توضیحات خروج">توضیحات خ</th>
                <th class="text-xs p-3 font-bold" title="تعداد">تعداد</th>
                <th class="text-xs p-3 font-bold" title="فروشنده">فروشنده</th>
                <th class="text-xs p-3 font-bold" title="خریدار">خریدار</th>
                <th class="text-xs p-3 font-bold" title="تحویل گیرنده">تحویل گیرنده</th>
                <th class="text-xs p-3 font-bold" title="جمع کننده">جمع کننده</th>
                <th class="text-xs p-3 font-bold" title="زمان خروج">زمان خ</th>
                <th class="text-xs p-3 font-bold" title="تاریخ خروج">تاریخ خ</th>
                <th class="text-xs p-3 font-bold" title="شماره فاکتور خروج">ش ف خروج</th>
                <th class="text-xs p-3 font-bold" title="تاریخ فاکتور خروج">تاریخ ف خ</th>
                <th class="text-xs p-3 font-bold" title="ورود به انبار">ورود به انبار</th>
                <th class="text-xs p-3 font-bold" title="شماره فاکتور ورود">ش ف و</th>
                <th class="text-xs p-3 font-bold" title="تاریخ فاکتور ورود">تاریخ ف و</th>
                <th class="text-xs p-3 font-bold" title="انبار">انبار</th>
                <th class="text-xs p-3 font-bold" title="کاربر">کاربر</th>
                <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                    <th class="text-xs p-3 font-bold" class="operation" title="عملیات">
                        <img class="w-8" src="./assets/icons/setting.svg" alt="settings icon">
                    </th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody id="resultBox">
            <?php
            $counter = 1;
            $billItemsCount = 0;
            if (count($soldItemsList) > 0) :
                $invoice_number = $soldItemsList[0]['sold_invoice_number'] ?? 'x';
                foreach ($soldItemsList as $item) :
                    $date = $item["sold_time"];
                    $array = explode(' ', $date);
                    list($year, $month, $day) = explode('-', $array[0]);
                    list($hour, $minute, $second) = explode(':', $array[1]);
                    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
                    $purchaseTime = jdate("H:i", $timestamp, "", "Asia/Tehran", "en");
                    $purchaseDate = jdate("Y/m/d", $timestamp, "", "Asia/Tehran", "en");

                    if ($invoice_number !== $item["sold_invoice_number"]) :
                        $invoice_number = $item["sold_invoice_number"];
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
                    $billItemsCount += $item["sold_quantity"]; ?>
                    <tr class="border-b-0 border-x-2 border-y-0 border-gray-800 even:bg-sky-100">
                        <td class="text-xs text-center w-2.5"><?= $counter ?></td>
                        <td class="p-2 text-center text-lg text-white font-semibold uppercase bg-sky-500"><?= '&nbsp;' . $item["partnumber"] . " " ?></td>
                        <td class="p-2 text-center text-sm text-white <?= getBrandBackground(strtoupper($item["brand_name"])) ?> font-semibold uppercase"><?= $item["brand_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["purchase_description"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["sold_description"] ?></td>
                        <td class="p-2 text-center text-sm font-semibold"><?= $item["sold_quantity"] ?></td>
                        <td class="p-2 text-sm uppercase text-center font-semibold <?= $item["seller_id"] == 61 ? 'text-white bg-red-600' : 'bg-yellow-500' ?>"><?= $item["seller_name"] ?></td>
                        <td class="p-2 text-center text-sm font-semibold"><?= $item["sold_customer"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["getter_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["jamkon"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $purchaseTime ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $purchaseDate ?></td>
                        <td class="p-2 text-sm text-red-500 text-center font-semibold">
                            <?= $item["sold_invoice_number"] ?>
                        </td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= strtoupper($item["sold_invoice_date"]) ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold">
                            <?php if ($item["purchase_isEntered"] == 1) : ?>
                                <img class="block mx-auto" src="./assets/icons/tick.svg" alt="TICK ICON">
                            <?php else : ?>
                                <img class="block mx-auto" src="./assets/icons/cross.svg" alt="CROSS ICON">
                            <?php endif; ?>
                        </td>
                        <td class="p-2 text-sm text-red-500 text-center font-semibold"><?= $item['qty_invoice_number'] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item['qty_invoice_date'] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["stock_name"] ?></td>
                        <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["username"] ?></td>
                        <?php if ($_SESSION["financialYear"] == convertPersianToEnglish(jdate('Y'))): ?>
                            <td style="display: flex; justify-content: center; margin-block: 15px;" class="operation">
                                <a onclick="displayModal(this)" data-target="<?= $item["sold_id"] ?>" class="cursor-pointer">
                                    <img class="mx-auto w-5 h-5" src="./assets/icons/edit.svg" alt="edit icon">
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>

                    <?php
                    if ($counter == count($soldItemsList)) : ?>
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
                    <td colspan="20" class="bg-rose-500 text-sm text-white text-center font-semibold border-x-2 border-t-0 border-rose-500 py-2">
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
        <div class="w-full h-full">
            <iframe id="editPage" style="height:100%" class="w-full" src="" frameborder="0"></iframe>
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

    const defaultValues = {
        seller: 'انتخاب فروشنده',
        brand: 'انتخاب برند جنس',
        stock: 'انتخاب انبار',
        user: 'انتخاب کاربر'
    };

    function getElementValue(id) {
        const element = document.getElementById(id);
        const defaultValue = defaultValues[id] || '';
        return element && element.value !== '' && element.value !== defaultValue ? element.value : null;
    }

    function filterReport() {
        const values = elements.reduce((acc, id) => {
            acc[id] = getElementValue(id);
            return acc;
        }, {});

        // values.dateType = document.querySelector('input[name="dateType"]:checked').value;
        // console.log(values.dateType);

        filter(values);
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

        axios.post("../../app/api/inventory/SellsReportApi.php", urlParams)
            .then(response => {
                resultBox.innerHTML = response.data;
            })
            .catch(error => {
                console.error(error);
            });
    }

    function clearFilter() {
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = defaultValues[id] || '';
            }
        });
    }


    function displayModal(element) {
        const iframe = document.getElementById('editPage');
        const targetElement = element.getAttribute('data-target');

        // Show the modal
        editModal.style.display = 'flex';

        // Clear the iframe src and show a loading message or indicator
        iframe.src = '';

        // Optionally, display a loading message or indicator
        iframe.contentWindow.document.open();
        iframe.contentWindow.document.write('<p style="font-size:40px; text-align:center; text:red">... لطفا صبور باشید</p>');
        iframe.contentWindow.document.close();

        // Set the new src after a slight delay to ensure the iframe has been cleared
        setTimeout(() => {
            iframe.src = './editSoled.php?record=' + targetElement;
        }, 100);
    }


    function closeModal() {
        editModal.style.display = 'none';
    }

    var modal = document.getElementById("editModal");

    $(function() {
        $(".exportToExcel").click(function(e) {
            var table = document.getElementById('report-table');
            if (table) {
                var workbook = XLSX.utils.table_to_book(table, {
                    sheet: "Sheet1"
                });
                var wbout = XLSX.write(workbook, {
                    bookType: 'xlsx',
                    type: 'binary'
                });

                function s2ab(s) {
                    var buf = new ArrayBuffer(s.length);
                    var view = new Uint8Array(buf);
                    for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
                    return buf;
                }

                var filename = "Exit_Report_" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xlsx";
                saveAs(new Blob([s2ab(wbout)], {
                    type: "application/octet-stream"
                }), filename);
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
</script>
<?php
require_once './components/footer.php';
?>