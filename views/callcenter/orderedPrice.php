<?php
$pageTitle = "قیمت دستوری";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../utilities/callcenter/DollarRateHelper.php';
require_once '../../utilities/callcenter/GivenPriceHelper.php';
require_once '../../utilities/inventory/ExistingHelper.php';
require_once '../../app/controller/callcenter/OrderedPriceController.php';
require_once '../../utilities/inventory/InventoryHelpers.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
$AvailableBrands = getBrands();
$brandsEnglishName = array_column($AvailableBrands, 'name');
$brandsPersianName = array_column($AvailableBrands, 'persian_name');
$customBrands = json_encode(array_combine($brandsEnglishName, $brandsPersianName));

if ($isValidCustomer) :
    if ($finalResult) :
        $explodedCodes = $finalResult['explodedCodes'];
        $not_exist = $finalResult['not_exist'];
        $existing = $finalResult['existing'];
        $customer = $finalResult['customer'];
        $completeCode = $finalResult['completeCode'];
        $notification = $finalResult['notification'];
        $rates = $finalResult['rates'];
        $relation_ids = $finalResult['relation_id'];
?>
        <link href="./assets/css/report.css" rel="stylesheet" />
        <section class="flex gap-8 justify-between">
            <div class="hidden sm:block m-2 bg-gray-700 p-2 sm:w-96">
                <table class="col-6 text-sm border border-white font-light mb-2 w-full h-full">
                    <thead class="font-medium bg-gray-700 border-b border-white">
                        <tr>
                            <th scope="col" class="p-3 text-white text-right">
                                نام
                            </th>
                            <th scope="col" class="px-3 text-white text-right">
                                نام خانوادگی
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-white">
                        <tr>
                            <td class="px-1">
                                <p class="text-right font-semibold px-2 py-3">
                                    <?= $customer_info['name'] ?>
                                </p>
                            </td>
                            <td class=" px-1">
                                <p class="text-right font-semibold px-2 py-3">
                                    <?= $customer_info['family'] ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="hidden sm:block m-2 p-2 bg-gray-700  sm:w-96">
                <form class="h-full border border-white flex flex-col gap-2 p-2 " target="_blank" action="./orderedPrice.php" method="post">
                    <div class="h-full">
                        <input type="text" name="givenPrice" value="givenPrice" id="form" hidden>
                        <input type="text" name="user" value="<?= $_SESSION["id"] ?>" hidden>
                        <input type="text" name="customer" value="1" id="target_customer" hidden>
                        <textarea style="direction: ltr !important;" onchange="filterCode(this)" id="code" name="code" required class="h-full bg-transparent w-full p-2 text-white placeholder-white outline-none focus:border-white" placeholder="لطفا کد های مورد نظر خود را در خط های مجزا قرار دهید"></textarea>
                    </div>
                    <span class="flex justify-between">
                        <div class="flex gap-2 items-center">
                            <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=0" class="cursor-pointer  text-white rounded bg-sky-600 hover:bg-sky-500 px-3 py-2 text-xs">پیش فاکتور مصرف کننده</button>
                            <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=1" class="cursor-pointer bg-green-600 hover:bg-green-700 text-white rounded px-3 py-2 text-xs">پیش فاکتور همکار</button>
                        </div>
                        <button type="type" class="inline-flex self-end p-2 bg-indigo-500 border-indigo-700 font-semibold text-xs text-white hover:bg-indigo-700">
                            جستجو اقلام
                        </button>
                    </span>
                </form>
            </div>
            <div class="m-2 p-2 bg-gray-700  sm:w-4/12 relative">
                <table style="direction: ltr !important;" class="w-full h-full text-sm p-2">
                    <thead class="font-medium">
                        <tr class="border">
                            <th class="text-left px-3 py-2 w-24">کد فنی</th>
                            <th class="text-left px-3 py-2">قیمت</th>
                            <th class="text-left px-3 py-2 flex items-center justify-between gap-2" onclick="closeTab()">
                                <span class="flex gap-1">
                                    <i id="copy_all_with_price" title="کاپی کردن مقادیر دارای قیمت" onclick="copyItemsWith(this)" class="text-sm material-icons hover:cursor-pointer text-green-500">content_copy</i>
                                    <img class="inline cursor-pointer" id="copy_all" title="کاپی کردن مقادیر" onclick="copyPrice(this)" src="./assets/img/all.svg" alt="copy all items">
                                </span>
                                <i id="copy_all" title="کپی توضیحات فارسی" onclick="copyPriceDetails(this)" class="mr-7 text-sm material-icons hover:cursor-pointer text-sky-500">content_copy</i>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="priceReport">
                        <?php
                        $isDisplayAllowed = false;
                        $persianName = '';
                        $codes = null;
                        foreach ($explodedCodes as $code) {
                            $relation_id =  array_key_exists($code, $relation_ids) ? $relation_ids[$code] : 'xxx';
                            $max = 0;
                            $finalPrice = '';
                            $codes .= $code . PHP_EOL;
                            if (array_key_exists($code, $existing)) {
                                foreach ($existing[$code] as $item) {
                                    $max += $item['relation']['existingQuantity'];
                                }

                                if (isset($existing[$code]) && count($existing[$code]) > 0) {
                                    foreach (current($existing[$code])['relation']['goods'] as $key => $value) {
                                        if ($value['partName'] != '') {
                                            $persianName = $value['partName'];
                                            break;
                                        } else {
                                            $persianName = '';
                                        }
                                    }
                                }
                            } ?>
                            <tr class="border">
                                <?php
                                if (in_array($code, $not_exist)) {
                                ?>
                                    <td data-persianName="<?= strtoupper($code) ?>" class="px-3 py-2 text-left text-white hover:cursor-pointer" data-move="<?= $code ?>" onclick="onScreen(this)"><?= strtoupper($code) ?></td>
                                <?php
                                } else {
                                ?>
                                    <td data-persianName="<?= $persianName ?>" class="px-3 py-2 text-left text-white hover:cursor-pointer" data-move="<?= $code ?>" onclick="onScreen(this)"><?= strtoupper($code) ?></td>
                                <?php } ?>
                                <td class="px-3 py-2 text-left text-white">
                                    <?php
                                    if (in_array($code, $not_exist)) {
                                        echo "<p class ='text-red-600' data-relation='" . $relation_id . "' id='" . $code . '-append' . "'>کد اشتباه</p>";
                                        echo "<span data-description='کد اشتباه'></span>";
                                    } else {
                                        if ($max && current($existing[$code])['givenPrice']) {

                                            $target = current(current($existing[$code])['givenPrice']);
                                            $priceDate = $target['created_at'];

                                            $rawPrice = current(current($existing[$code])['givenPrice']);

                                            $existing_brands = getExistingBrands(current($existing[$code])['relation']['stockInfo']);
                                            $finalPrice = getFinalSanitizedPrice([$rawPrice], $existing_brands);

                                            if (!$finalPrice) {
                                                $finalPrice = 'موجود نیست';
                                            }

                                            if (!$isDisplayAllowed && $finalPrice != 'موجود نیست') {
                                                $isDisplayAllowed = true;
                                            }
                                    ?>
                                            <p style='direction: ltr !important;' data-relation='<?= $relation_id ?>' id='<?= $code ?>-append' class="<?= $finalPrice !== 'موجود نیست' ? '' : 'text-yellow-400' ?>">
                                                <?= $finalPrice !== 'موجود نیست' ? $finalPrice : 'نیاز به بررسی' ?>
                                            </p>
                                        <?php
                                        } else if ($max) {
                                            echo "<p style='direction: ltr !important;' data-relation='" . $relation_id . "' id='" . $code . '-append' . "'class ='text-green-400'>نیاز به قیمت</p>";
                                        } else if ($max == 0) {
                                            echo "<p style='direction: ltr !important;' data-relation='" . $relation_id . "' id='" . $code . '-append' . "'>" . 'موجود نیست' . "</p>";
                                        }

                                        ?>
                                        <span data-description="<?= $finalPrice ?>"></span>
                                </td>
                                <td class="text-left py-2" onclick="closeTab()">
                                    <i title="کاپی کردن مقادیر" onclick="copyItemPrice(this)" class="px-4 text-white text-sm material-icons hover:cursor-pointer">content_copy</i>
                                </td>
                            <?php
                                    }
                            ?>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <form class="flex flex-col absolute bottom-3 right-3" target="_blank" action="./orderedPrice.php" method="post">
                    <div class="hidden">
                        <input type="text" name="givenPrice" value="givenPrice" id="form" hidden>
                        <input type="text" name="user" value="<?= $_SESSION["id"] ?>" hidden>
                        <input type="text" name="customer" value="1" id="target_customer" hidden>
                        <textarea style="direction: ltr !important;" onchange="filterCode(this)" id="code" name="code" required class="h-full bg-transparent w-full p-2 text-white placeholder-white outline-none focus:border-white hidden" placeholder="لطفا کد های مورد نظر خود را در خط های مجزا قرار دهید"><?= $codes; ?></textarea>
                    </div>
                    <span class="hidden sm:flex justify-between">
                        <div class="flex gap-1 items-center">
                            <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=0" class="cursor-pointer  text-white rounded bg-sky-600 hover:bg-sky-500 px-1 py-1 text-xs">مصرف کننده</button>
                            <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=1" class="cursor-pointer bg-green-600 hover:bg-green-700 text-white rounded px-1 py-1 text-xs">همکار</button>
                        </div>
                    </span>
                </form>
            </div>
            <?php
            if (!$isDisplayAllowed):
            ?>
                <script>
                    document.getElementById('copy_all_with_price').style.display = 'none';
                </script>
            <?php
            endif;
            ?>
        </section>
        <section class="accordion mb-10">
            <?php foreach ($explodedCodes as $code_index => $code) {

                $relation_id =  array_key_exists($code, $relation_ids) ? $relation_ids[$code] : 'xxx';
                $max = 0;
                if (array_key_exists($code, $existing)) {
                    foreach ($existing[$code] as $item) {
                        $max += $item['relation']['existingQuantity'];
                    }
                }
            ?>
                <div style="direction: ltr !important;" id="<?= $code ?>" class="accordion-header bg-cyan-800">
                    <p class="flex text-left items-center gap-2">
                        <span class='text-white font-semibold uppercase'><?= $code; ?></span>
                        <?php if (in_array($code, $not_exist)) {
                            echo '<i class="material-icons text-neutral-400">block</i>';
                        } else if ($max > 0) {
                            echo '<i class="material-icons text-green-500">check_circle</i>';
                        } else {
                            echo '<i class="material-icons text-red-600">do_not_disturb_on</i>';
                        } ?>

                    </p>
                </div>
                <div style="<?= $max > 0 ? 'max-height: 1000vh' : 'max-height: 0vh' ?>" class="accordion-content overflow-hidden bg-grey-lighter">
                    <?php
                    if (array_key_exists($code, $existing)) {
                        foreach ($existing[$code] as $index => $item) {
                            $partNumber = $index;
                            $information = $item['information'];
                            $relation = $item['relation'];
                            $goods =  $relation['goods'];
                            $exist =  $relation['existing'];
                            $sorted =  $relation['sorted'];
                            $stockInfo =  $relation['stockInfo'];
                            $givenPrice =  $item['givenPrice'];
                            $limit_id = $relation['limit_alert'];
                            $existing_brands = getExistingBrands($stockInfo);
                    ?>
                            <div style="direction: ltr !important;" class="grid grid-cols-1 gap-6 lg:grid-cols-11 lg:gap-2 mb-7">
                                <?php
                                $infoSize = 'lg:col-span-2';
                                $existingSize = 'lg:col-span-6';
                                $priceSize = 'lg:col-span-3';
                                require './components/givenPrice/info.php';
                                require './components/givenPrice/existing.php';
                                require './components/givenPrice/price.php';
                                ?>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="bg-white rounded-lg overflow-auto mb-3 py-4">
                            <p class="text-center">کد مد نظر در سیستم موجود نیست</p>
                        </div>
                    <?php } ?>
                </div>
            <?php
            }
            ?>
            <p id="form_success" class="custom-alert success px-3 tiny-text">
                موفقانه در پایگاه داده ثبت شد!
            </p>
            <p id="form_error" class=" custom-alert error px-3 tiny-text">
                ذخیره سازی اطلاعات ناموفق بود!
            </p>
        </section>
        <div onclick="toggleDollarModal()" id="dollarContainerModal" class="hidden fixed flex inset-0 bg-gray-900/75 justify-center items-center">
            <div class="bg-white p-4 rounded min-w-96">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl mb-2">گزارش دلار پایه و 10%</h2>
                    <img class="cursor-pointer" src="./assets/img/close.svg" alt="close icon">
                </div>
                <table class="w-full">
                    <thead class="bg-green-500">
                        <tr>
                            <th class="p-2">دلار ۱۰٪</th>
                            <th class="p-2">دلار پایه</th>
                            <th class="p-2">شاخص</th>
                        </tr>
                    </thead>
                    <tbody id="Modal">
                        <!-- Results will be appended here async -->
                    </tbody>
                </table>
            </div>
        </div>
        <a class="toTop" href="#">
            <i class="material-icons">arrow_drop_up</i>
        </a>
        <p id="copied_message" style="display:none;position: fixed; top:50%; left:50%; transform: translate(-50%, -50%); font-size: 60px;font-weight: bold; color:seagreen">کد ها کاپی شدند</p>
        <script src="./assets/js/givePrice.js"></script>
        <script>
            const brandTranslations = <?= $customBrands ?>;
            const brandNames = Object.keys(brandTranslations);
            const brandPattern = new RegExp(`\\b(${brandNames.join('|')})\\b`, 'g');
            const dollarContainerModal = document.getElementById('dollarContainerModal');

            function replaceBrandsWithPersian(input) {
                const result = input.replace(brandPattern, (match) => {
                    return brandTranslations[match] || match;
                });
                return result;
            }

            function copyPriceDetails(element) {
                // Select all elements with data attributes for names and descriptions
                const names = document.querySelectorAll('[data-persianName]');
                const descriptions = document.querySelectorAll('[data-description]');
                const final = [];

                // Helper function to format digits as money
                function formatMoney(value) {
                    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' ';
                }

                // Loop through each name and corresponding description
                for (let index = 0; index < descriptions.length; index++) {
                    let persianName = '';

                    // Get Persian name and replace brands in the description
                    persianName = names[index].getAttribute('data-persianName');

                    if (persianName == '') {
                        continue;
                    }

                    let description = replaceBrandsWithPersian(descriptions[index].getAttribute('data-description'));
                    if (description == 'موجود نیست' || description == 'نیاز به بررسی') {
                        description = '-';
                    } else {
                        // Check if the description contains digits and format them as money
                        description = description.replace(/\d+/g, (match) => formatMoney(match));
                    }

                    // Append formatted text to the final array (without HTML tags)
                    final.push(`${persianName} :\n ${description}\n\nقیمت ها در واحد هزار تومان می باشد.\nتمام قطعات اصلی برند جنیون پارت می باشند.`);
                }

                // Copy the plain text to clipboard
                copyToClipboard(final.join('\n'));
                element.innerHTML = `done`;
                setTimeout(() => {
                    element.innerHTML = `content_copy`;
                }, 1500);
            }


            function openDollarModal(basePrice, tenPercent, mobis, mobisTenPercent, korea, koreaTenPercent, ) {
                const container = document.getElementById('Modal');
                toggleDollarModal();
                container.innerHTML = '';

                container.innerHTML += `
                        <tr class="bg-sky-200">
                        <td class="p-2 font-semibold text-xs text-center">${tenPercent}</td>
                        <td class="p-2 font-semibold text-xs text-center">${basePrice}</td>
                            <td class="p-2 font-semibold text-xs text-center">قیمت پایه</td>
                        </tr>`;
                if (mobis != 0) {
                    container.innerHTML += `
                        <tr class="bg-gry-200">
                        <td class="p-2 font-semibold text-xs text-center">${mobisTenPercent}</td>
                        <td class="p-2 font-semibold text-xs text-center">${mobis}</td>
                            <td class="p-2 font-semibold text-xs text-center">قیمت موبیز</td>
                        </tr>`;
                }

                if (korea != 0) {
                    container.innerHTML += `
                        <tr class="bg-sky-200">
                        <td class="p-2 font-semibold text-xs text-center">${koreaTenPercent}</td>
                        <td class="p-2 font-semibold text-xs text-center">${korea}</td>
                            <td class="p-2 font-semibold text-xs text-center">قیمت کورآ</td>
                        </tr>`;
                }

            }

            function toggleDollarModal() {
                dollarContainerModal.classList.toggle('hidden');
            }
        </script>
<?php
    endif;
else :
    echo "<p class='col-6 mx-auto flex items-center justify-center h-full'>کاربر درخواست دهنده و یا مشتری مشخص شده معتبر نمی باشد</p>";
endif;
require_once './components/footer.php';
