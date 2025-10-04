<!-- ENd the code info section -->
<div style="direction: ltr !important;" class="w-full bg-white <?= $existingSize ?> overflow-auto shadow-md relative py-2">
    <table style="direction: ltr !important;" class="w-full text-left text-sm font-light custom-table">
        <thead class="font-medium bg-gray-700">
            <tr>
                <th scope="col" class="px-3 py-3 text-white text-center">
                    شماره فنی
                </th>
                <th scope="col" class="px-3 py-3 text-white text-center">
                    موجودی
                </th>
                <th scope="col" class="px-3 py-3 text-white text-center">
                    قیمت به اساس نرخ ارز
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($sorted as $index => $element) :
                $YADAK_SHOP = [];
            ?>
                <tr>
                    <td class="relative px-1 hover:cursor-pointer" data-part="<?= $goods[$index]['partnumber'] ?>" onmouseleave="hideToolTip(this)" onmouseover="showToolTip(this)">
                        <div class="relative">
                            <?php
                            $not_registered = is_registered($goods[$index]['partnumber']);
                            $user = $_SESSION['username']; ?>
                            <p onclick="copyPartNumber(this, '<?= strtoupper($goods[$index]['partnumber']) ?>')" class="text-center bold bg-gray-600 <?= $not_registered ? 'text-white' : 'text-green-500' ?>  px-2 py-3">
                                <?php
                                echo strtoupper($goods[$index]['partnumber']);

                                // Calculate initial price and weight
                                $price = floatval($goods[$index]['price'] ?? 0);
                                $avgPrice = round(($price * 110) / 243.5);

                                // Convert mobis and korea to floats
                                $mobis = floatval($goods[$index]['mobis'] ?? 0);
                                $korea = floatval($goods[$index]['korea'] ?? 0);

                                // Determine status based on mobis
                                $status = null;
                                switch ($mobis) {
                                    case 0.00:
                                        $status = "NO-Price";
                                        break;
                                    case "-":
                                        $status = "NO-Mobis";
                                        break;
                                    case NULL:
                                        $status = "Request";
                                        break;
                                    default:
                                        $status = "YES-Mobis";
                                        break;
                                }

                                // Calculate basePrice and tenPercent for avgPrice
                                $basePrice = round($avgPrice * 1.1);
                                $tenPercent = round($avgPrice * 1.2);

                                // Calculate mobis and mobisTenPercent
                                $mobisAvgPrice = round(($mobis * 110) / 243.5);
                                $mobisTenPercent = round($mobisAvgPrice * 1.1);

                                // Calculate korea and koreaTenPercent
                                $koreaAvgPrice = round(($korea * 110) / 243.5);
                                $koreaTenPercent = round($koreaAvgPrice * 1.1);

                                // Assign updated values to mobis and korea
                                $mobis = $mobisAvgPrice;
                                $korea = $koreaAvgPrice;
                                ?>
                            </p>
                            <div class="ordered-price-tooltip2" id="<?= $goods[$index]['partnumber'] . '-google' ?>">
                                <div>
                                    <div>
                                        <a class="flex items-center gap-2 mb-2" target='_blank' href='https://www.google.com/search?tbm=isch&q=<?= $goods[$index]['partnumber'] ?>'>
                                            <img class="w-4 h-auto" src="../../public/img/google.png" alt="google">
                                            <p class="text-white text-xs">
                                                جستجو در گوگل
                                            </p>
                                        </a>
                                    </div>
                                    <div>
                                        <a class="flex items-center gap-2 mb-2" target='_blank' href='https://partsouq.com/en/search/all?q=<?= $goods[$index]['partnumber'] ?>'>
                                            <img class="w-4 h-auto" src="../../public/img/part.png" alt="part">
                                            <p class="text-white text-xs">
                                                جستجو پارت
                                            </p>
                                        </a>
                                    </div>
                                    <div>
                                        <a class="flex items-center gap-2 mb-2" title="بررسی تک آیتم" target='_blank' href='../inventory/singleItemReport.php?code=<?= $goods[$index]['partnumber'] ?>'>
                                            <img src="../../public/img/singleItem.svg" class="w-4 h-auto" alt="">
                                            <p class="text-white text-xs">
                                                بررسی تک آیتم
                                            </p>
                                        </a>
                                    </div>
                                    <div>
                                        <a class="flex items-center gap-2 mb-2" title="گزارش تقاضای بازار" target='_blank' href='../telegram/requests.php?type=hour&code=<?= $goods[$index]['partnumber'] ?>'>
                                            <img src="./assets/img/chart.svg" class="w-4 h-auto" alt="">
                                            <p class="text-white text-xs">
                                                گزارش بازار
                                            </p>
                                        </a>
                                    </div>
                                    <div>
                                        <a title="گزارش دلار "
                                            class="flex items-center gap-2 mb-2"
                                            onclick="openDollarModal(
                                                    '<?= $basePrice ?>',
                                                    '<?= $tenPercent ?>',
                                                    '<?= $mobis ?>',
                                                    '<?= $mobisTenPercent ?>',
                                                    '<?= $korea ?>',
                                                    '<?= $koreaTenPercent ?>',
                                                    )">
                                            <img src="./assets/img/information.svg" class="w-4 h-auto" alt="">
                                            <p class="text-white text-xs">
                                                گزارش دلار
                                            </p>
                                        </a>
                                    </div>
                                    <div>
                                        <?php
                                        if ($user == 'niyayesh' || $user == 'mahdi') {
                                            if ($not_registered) { ?>

                                                <a class="flex items-center gap-2 mb-2" title="افزودن به لیست پیام خودکار" onclick="addSelectedGood('<?= $goods[$index]['partnumber'] ?>', this)">
                                                    <img src="./assets/img/add_good.svg" class="w-4 h-auto" alt="">
                                                    <p class="text-white text-xs">
                                                        لیست پیام
                                                    </p>
                                                </a>
                                            <?php } else { ?>
                                                <a class="flex items-center gap-2 mb-2" title="حذف از لیست پیام خودکار" onclick="deleteGood('<?= $goods[$index]['partnumber'] ?>', this)">
                                                    <img src="./assets/img/deleteBill.svg" class="w-4 h-auto" alt="">
                                                    <p class="text-white text-xs">
                                                        لیست پیام
                                                    </p>
                                                </a>
                                            <?php }
                                            ?>
                                            <a class="flex items-center gap-2 mb-2" title="حذف از لیست پیام خودکار"
                                                target="_blank"
                                                href="./priceSearchDetails.php?type=week&code=<?= $goods[$index]['partnumber'] ?>">
                                                <img src="./assets/img/time.svg" class="w-4 h-auto" alt="">
                                                <p class="text-white text-xs">
                                                    گزارش جستجو
                                                </p>
                                            </a>
                                        <?php
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-1 pt-2 pb-10">
                        <table class="w-full text-sm font-light p-2">
                            <thead class="font-medium">
                                <tr>
                                    <?php
                                    if (abs(array_sum($exist[$index])) > 0) {
                                        foreach ($exist[$index] as $brand => $amount) {
                                            if ($amount > 0) { ?>
                                                <th onclick="appendBrand(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $brand ?>" data-part="<?= $partNumber ?>" scope="col" class="<?= $brand == 'GEN' || $brand == 'MOB' ? $brand : 'brand-default' ?> text-white text-sm text-center py-2 relative hover:cursor-pointer" data-key="<?= $index ?>" data-part="<?= $partNumber ?>" data-brand="<?= $brand ?>" onmouseover="seekExist(this)" onmouseleave="closeSeekExist(this)">
                                                    <?= $brand ?>
                                                    <div class="ordered-price-tooltip" id="<?= $index . '-' . $brand ?>">
                                                        <table class="w-full text-sm font-light p-2">
                                                            <thead class="font-medium bg-violet-800">
                                                                <tr>
                                                                    <th class="text-right p-2 text-xs">فروشنده</th>
                                                                    <th class="text-right p-2 text-xs"> موجودی</th>
                                                                    <th class="text-right p-2 text-xs">تاریخ</th>
                                                                    <th class="text-right p-2 text-xs">
                                                                        <img src="./assets/img/time.svg" alt="clock icon">
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                foreach ($stockInfo[$index] as $item) {
                                                                    if ($item !== 0 && $item['brandName'] === $brand && $item['remaining_qty'] > 0) {

                                                                        if ($item['stockId'] == 9) {
                                                                            // Check if the brandName exists, and add to it; otherwise, initialize it
                                                                            if (isset($YADAK_SHOP[$item['brandName']])) {
                                                                                $YADAK_SHOP[$item['brandName']] += $item['remaining_qty'];
                                                                            } else {
                                                                                $YADAK_SHOP[$item['brandName']] = $item['remaining_qty'];
                                                                            }
                                                                        } else {
                                                                            // If the stockId is not 9 and the brandName is set but its value is 0, explicitly set it to 0
                                                                            if (!isset($YADAK_SHOP[$item['brandName']])) {
                                                                                $YADAK_SHOP[$item['brandName']] = 0;
                                                                            }
                                                                        }
                                                                ?>
                                                                        <tr class="<?= in_array($item['seller_name'], $excludedSellers) ? 'bg-red-500' : 'bg-gray-600' ?>">
                                                                            <td class="p-2 text-xs text-right"><?= $item['seller_name'] ?></td>
                                                                            <td class="p-2 text-xs text-right"><?= $item['remaining_qty'] ?></td>
                                                                            <td class="p-2 text-xs text-right"><?= (explode(' ', $item['invoice_date'])[0]) ?></td>
                                                                            <td class="p-2 text-xs text-right"><?= displayTimePassed($item['invoice_date']) ?></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                <?php
                                                                } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </th>
                                    <?php }
                                        }
                                    } else {
                                        echo '<p class="text-rose-500 text-sm text-center font-bold">  موجود نیست </p>';
                                    } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="py-3">
                                    <?php
                                    foreach ($exist[$index] as $brand => $amount) :
                                        $count = 0;
                                        if ($amount > 0) : ?>
                                            <td class="<?= $brand == 'GEN' || $brand == 'MOB' ? $brand : 'brand-default' ?> whitespace-nowrap text-white px-3 py-2 text-center relative">
                                                <?= $amount; ?>
                                                <?php
                                                if (isset($YADAK_SHOP[$brand]) && $YADAK_SHOP[$brand] != 0):
                                                ?>
                                                    <span class="text-xs font-semibold absolute top-full left-1/2 transform -translate-x-1/2 rounded-full bg-green-600 px-2 py-1 flex justify-center items-center">
                                                        <?php
                                                        if (isset($YADAK_SHOP[$brand]) && $YADAK_SHOP[$brand] == $amount) {
                                                        ?>
                                                            <svg width="10px" height="10px" viewBox="0 -1.5 11 11" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000">
                                                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                                                <g id="SVGRepo_iconCarrier">
                                                                    <title>done_mini [#ffffff]</title>
                                                                    <desc>Created with Sketch.</desc>
                                                                    <defs> </defs>
                                                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                        <g id="Dribbble-Light-Preview" transform="translate(-304.000000, -366.000000)" fill="#ffffff">
                                                                            <g id="icons" transform="translate(56.000000, 160.000000)">
                                                                                <polygon id="done_mini-[#ffffff]" points="259 207.6 252.2317 214 252.2306 213.999 252.2306 214 248 210 249.6918 208.4 252.2306 210.8 257.3082 206"> </polygon>
                                                                            </g>
                                                                        </g>
                                                                    </g>
                                                                </g>
                                                            </svg>
                                                        <?php
                                                        } else {
                                                            echo $YADAK_SHOP[$brand];
                                                        }
                                                        ?>
                                                    </span>
                                            </td>
                                <?php endif;
                                            endif;
                                        endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="px-1 pt-2 pb-10">
                        <table style="direction: ltr !important;" class="w-full text-left text-sm font-light">
                            <thead class="font-medium">
                                <tr>
                                    <?php foreach ($rates as $rate) : ?>
                                        <th class="text-white text-center py-2 <?= $rate['status'] !== 'N' ? $rate['status'] : 'bg-green-700' ?>">
                                            <?= $rate['amount'] ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="py-3">
                                    <?php foreach ($rates as $rate) :
                                        $price = doubleval($goods[$index]['price']);
                                        $price = str_replace(",", "", $price);
                                        $avgPrice = round(($price * 110) / 243.5);
                                        $finalPrice = round($avgPrice * $rate['amount'] * 1.2 * 1.2 * 1.3); ?>
                                        <td class="text-bold whitespace-nowrap px-3 py-2 text-center hover:cursor-pointer <?= $rate['status'] !== 'N' ? $rate['status'] : 'bg-gray-200' ?>" onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPrice ?>" data-part="<?= $partNumber ?>">
                                            <?= $finalPrice ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php if ($goods[$index]['mobis'] > 0 && $goods[$index]['mobis'] !== '-') : ?>
                                    <tr class="bg-neutral-300">
                                        <?php foreach ($rates as $rate) :
                                            $price = doubleval($goods[$index]['mobis']);
                                            $price = str_replace(",", "", $price);
                                            $avgPrice = round(($price * 110) / 243.5);
                                            $finalPrice = round($avgPrice * $rate['amount'] * 1.25 * 1.3); ?>
                                            <td class="text-bold whitespace-nowrap px-3 text-center py-2 hover:cursor-pointer" onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPrice ?>" data-part="<?= $partNumber ?>">
                                                <?= $finalPrice ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>

                                <?php if ($goods[$index]['korea'] > 0 && $goods[$index]['mobis'] !== '-') : ?>
                                    <tr class="bg-amber-600">
                                        <?php foreach ($rates as $rate) :
                                            $price = doubleval($goods[$index]['korea']);
                                            $price = str_replace(",", "", $price);
                                            $avgPrice = round(($price * 110) / 243.5);
                                            $finalPrice = round($avgPrice * $rate['amount'] * 1.25 * 1.3); ?>
                                            <td class="text-bold whitespace-nowrap px-3 text-center py-2 hover:cursor-pointer" onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPrice ?>" data-part="<?= $partNumber ?>">
                                                <?= $finalPrice ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?php $hussin_part = get_hussain_parts($goods[$index]['partnumber']);
                        if ($hussin_part) : ?>
                            <table dir="ltr" class="w-full text-left text-sm font-light custom-table mt-2">
                                <thead class="font-medium bg-gray-700 text-white">
                                    <tr>
                                        <th class="text-xs px-2 text-left">قیمت</th>
                                        <th class="text-xs px-2 text-left">موجودی</th>
                                        <th class="text-xs px-2 text-left">30%</th>
                                        <th class="text-xs px-2 text-left">last_sale_price</th>
                                        <th class="text-xs px-2 text-left">instant_offer_price</th>
                                        <th class="text-xs px-2 text-left">online_price</th>
                                        <th class="text-xs px-2 text-left">offer_price</th>
                                        <th class="text-xs px-2 text-left">برند</th>
                                        <th class="text-xs px-2 text-left">کد فنی</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr class="text-xs bg-gray-200 odd:bg-purple-400">

                                        <td class="p-2 text-left font-semibold">
                                            <span><?= (int) ($hussin_part['yadakprice'] / 10000) ?></span>
                                        </td>

                                        <td class="p-2 text-left font-semibold">
                                            <span><?= (int) $hussin_part['stock'] ?></span>
                                        </td>

                                        <td class="p-2 text-left font-semibold">
                                            <span><?= (int) (($hussin_part['online_price'] * 1.3) / 10000); ?></span>
                                        </td>

                                        <td class="p-2 text-left font-semibold">
                                            <span><?= $hussin_part['last_sale_price'] ?></span>
                                        </td>
                                        <td class="p-2 text-left font-semibold">
                                            <span><?= $hussin_part['instant_offer_price'] ?></span>
                                        </td>
                                        <td class="p-2 text-left font-semibold">
                                            <span><?= $hussin_part['online_price'] ?></span>
                                        </td>
                                        <td class="p-2 text-left font-semibold">
                                            <span><?= $hussin_part['offer_price'] ?></span>
                                        </td>
                                        <td class="p-2 text-left font-semibold">
                                            <span><?= $brandMap[$hussin_part['brand']] ?? $hussin_part['brand'] ?></span>
                                        </td>
                                        <td class="p-2 text-left font-semibold">
                                            <span><?= $hussin_part['property_code'] ?></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($limit_id && $_SESSION['username'] === 'niyayesh' || $limit_id && $_SESSION['username'] === 'mahdi') :
        $fraction = explode('-', $limit_id);
        $id = $fraction[0];
        $type = $fraction[1];

        $overall = overallSpecification($id, $type);
        $inventory = inventorySpecification($id, $type);
        $mode = 'create';

        if ($overall) :
            $mode = 'update';
        else :
            $overall = ['original_all' => 0, 'fake_all' => 0];
            $inventory = ['original' => 0, 'fake' => 0];
        endif; ?>
        <div class="px-1 mt-4 mb-1">
            <form id="f-<?= $partNumber ?>" action="" class="bg-gray-200 rounded-md p-3" method="post">
                <input id="id" type="hidden" name="id" value="<?= $id ?>" />
                <input id="type" type="hidden" name="type" value="<?= $type ?>" />
                <input id="operation" type="hidden" name="operation" value="<?= $mode ?>" />
                <div class="flex gap-2">
                    <fieldset class="flex-grow">
                        <legend class="my-3 font-semibold"> هشدار موجودی انبار یدک شاپ:</legend>
                        <div class="col-span-12 sm:col-span-4 mb-3 flex flex-wrap gap-2 ">
                            <div class="flex-grow">
                                <label for="original" class="block font-medium text-sm text-gray-700">
                                    مقدار اصلی
                                </label>
                                <input name="original" value="<?= $inventory['original'] ? $inventory['original'] : 0 ?>" style="direction:ltr !important;" class="border border-2 text-sm outline-none border-gray-300 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="original" type="number" min='0' />
                            </div>
                            <div class="flex-grow">
                                <label for="fake" class="block font-medium text-sm text-gray-700">
                                    مقدار غیر اصلی
                                </label>
                                <input name="fake" value="<?= $inventory['fake'] ? $inventory['fake'] : 0 ?>" style="direction:ltr !important;" class="border border-2 text-sm outline-none border-gray-300 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="fake" type="number" min='0' />
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="flex-grow">
                        <legend class="my-3 font-semibold"> هشدار موجودی کلی:</legend>
                        <div class="col-span-12 sm:col-span-4 mb-3 flex flex-wrap gap-2 ">
                            <div class="flex-grow">
                                <label for="original" class="block font-medium text-sm text-gray-700">
                                    مقدار اصلی
                                </label>
                                <input name="original_all" value="<?= $overall['original_all'] ? $overall['original_all'] : 0 ?>" style="direction:ltr !important;" class="border border-2 text-sm outline-none border-gray-300 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="original_all" type="number" min='0' />
                            </div>
                            <div class="flex-grow">
                                <label for="fake" class="block font-medium text-sm text-gray-700">
                                    مقدار غیر اصلی
                                </label>
                                <input name="fake_all" value="<?= $overall['fake_all'] ? $overall['fake_all'] : 0 ?>" style="direction:ltr !important;" class="border border-2 text-sm outline-none border-gray-300 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="fake_all" type="number" min='0' />
                            </div>
                        </div>
                    </fieldset>
                </div>
                <button onclick="setLimitAlert(event)" data-form="<?= $partNumber ?>" class="text-xs bg-blue-500 hover:bg-blue-600 font-semibold px-5 py-2 rounded text-white" type="submit">ذخیره</button>
            </form>
        </div>
    <?php endif; ?>

</div>