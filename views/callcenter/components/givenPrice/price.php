<!-- Given Price section -->
<?php
$sanitizedPrice = getFinalSanitizedPrice($givenPrice, $existing_brands, $discount);
?>
<div class="w-full bg-white <?= $priceSize; ?> overflow-auto shadow-md p-2">
    <table class="w-full text-sm font-light">
        <thead>
            <tr class="w-full bg-gray-700">
                <td class="text-white bold text-right text-sm py-3 px-2">کاربر</td>
                <td class=" text-white bold text-left text-sm py-3 px-2">کد فنی</td>
                <td class="text-white bold text-left text-sm py-3 px-2">مشتری</td>
                <td class="text-white bold text-left text-sm py-3 px-2 w-28">قیمت</td>
                <?php if (in_array($_SESSION['username'], ['mahdi', 'niyayesh', 'babak'])) : ?>
                    <td class=" text-white bold text-left text-sm py-3 px-2"></td>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody id="price-<?= $partNumber ?>">
            <?php
            $target = current($givenPrice);
            if ($target) : ?>
                <tr class="bg-green-500 hover:cursor-pointer text-sm">
                    <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $sanitizedPrice ?>" data-part="<?= $partNumber ?>" scope="col">
                        <?php if (!array_key_exists("ordered", $target)) : ?>
                            <img class="w-7 h-7 rounded-full mx-auto" src="../../public/userimg/<?= $target['userID'] ?>.jpg" alt="userimage">
                        <?php endif; ?>
                    </td>
                    <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $sanitizedPrice ?>" data-part="<?= $partNumber ?>" class="text-sm text-left text-white">
                        <?= array_key_exists("partnumber", $target) ? $target['partnumber'] : '' ?>
                    </td>
                    <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $sanitizedPrice ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left text-white px-1 py-1">
                        قیمت نهایی
                    </td>
                    <td style='direction: ltr !important;' onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $sanitizedPrice ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left text-white px-2 py-2">
                        <?= $sanitizedPrice == '' ? 'نیاز به بررسی' :  $sanitizedPrice ?>
                    </td>
                    <?php if (in_array($_SESSION['username'], ['mahdi', 'niyayesh', 'babak'])) : ?>
                        <td>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php
            endif;
            $finalPriceForm = '';
            if ($givenPrice !== null && count($givenPrice) > 0) :
                $target = current($givenPrice);
                $priceDate = $target['created_at'];
                if (checkDateIfOkay($applyDate, $priceDate) && $target['price'] !== 'موجود نیست') :
                    $rawGivenPrice = $target['price'];
                    $finalPriceForm = strtoupper(applyDollarRate($rawGivenPrice, $priceDate)); ?>
                    <tr class="bg-cyan-400 hover:cursor-pointer text-sm">
                        <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPriceForm ?>" data-part="<?= $partNumber ?>" scope="col">
                            <?php if (!array_key_exists("ordered", $target)) : ?>
                                <img class="w-7 h-7 rounded-full mx-auto" src="../../public/userimg/<?= $target['userID'] ?>.jpg" alt="userimage">
                            <?php endif; ?>
                        </td>
                        <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPriceForm ?>" data-part="<?= $partNumber ?>" class="text-sm text-left text-white">
                            <?= array_key_exists("partnumber", $target) ? $target['partnumber'] : '' ?>
                        </td>
                        <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPriceForm ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left text-white px-1 py-1">
                            افزایش قیمت <?= $appliedRate ?>%
                        </td>
                        <td style='direction: ltr !important;' onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPriceForm ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left text-white px-2 py-2">
                            <?= $target['price'] === null ? 'ندارد' :  $finalPriceForm ?>
                        </td>
                        <?php if (in_array($_SESSION['username'], ['mahdi', 'niyayesh', 'babak'])) : ?>
                            <td>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php
                endif;
                foreach ($givenPrice as $price) :
                    if ($price['price'] !== null && $price['price'] !== '') : ?>
                        <tr class="w-full mb-1 hover:cursor-pointer  text-sm <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'bg-red-400' : 'bg-indigo-200'; ?>">
                            <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= strtoupper($price['price']) ?>" data-part="<?= $partNumber ?>" scope="col" class="text-center text-gray-800 px-2 py-1 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : '' ?>">
                                <?php if (!array_key_exists("ordered", $price)) : ?>
                                    <img class="userImage" src="../../public/userimg/<?= $price['userID'] ?>.jpg" alt="userimage">
                                <?php endif; ?>
                            </td>
                            <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= strtoupper($price['price']) ?>" data-part="<?= $partNumber ?>" class="text-sm text-left <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : '' ?> ">
                                <?= array_key_exists("partnumber", $price) ? $price['partnumber'] : '' ?>
                            </td>
                            <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= strtoupper($price['price']) ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left px-2 py-1 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : '' ?>">
                                <?= array_key_exists("ordered", $price) ? 'قیمت دستوری' : $price['name'] . ' ' . $price['family']; ?>
                            </td>
                            <td style="direction: ltr !important;" onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $price['price'] ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left px-2 py-1 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : '' ?>">
                                <?= $price['price'] === null ? 'ندارد' : strtoupper($price['price']); ?>
                            </td>
                            <?php if (in_array($_SESSION['username'], ['mahdi', 'niyayesh', 'babak'])) : ?>
                                <td data-part="<?= $partNumber ?>" data-code="<?= $code ?>" onclick="deleteGivenPrice(this)" data-brands='<?= json_encode($existing_brands) ?>' data-del='<?= $price['id'] ?>' data-target="<?= $relation_id ?>" scope="col" class="text-sm text-left px-2 py-1 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : '' ?>">
                                    <i id="deleteGivenPrice" class="material-icons" title="حذف قیمت">close</i>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <tr class="w-full mb-1 border-b-2 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'bg-red-500' : 'bg-indigo-300' ?>" data-price='<?= $price['price'] ?>'>
                            <td class="<?php array_key_exists("ordered", $price) ? 'text-white' : '' ?> text-gray-800  py-1 px-2 tiny-text" colspan="<?= (in_array($_SESSION['username'], ['mahdi', 'niyayesh', 'babak'])) ? 4 : 3 ?>" scope="col">
                                <div class="flex items-center w-full <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : 'text-gray-800' ?>">
                                    <i class="px-1 material-icons tiny-text <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : 'text-gray-800' ?>">access_time</i>
                                    <?= timeFormatter($price['created_at']); ?>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                <?php endif;
                endforeach;
            else : ?>
                <tr class="w-full mb-4 border-b-2 border-white">
                    <td colspan="5" scope="col" class="text-gray-800 py-2 text-center bg-indigo-300">
                        !! موردی برای نمایش وجود ندارد
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <br>
    <?php
    if (in_array($_SESSION['username'], ['mahdi', 'niyayesh', 'babak'])): ?>
        <div class="bg-gray-200 rounded-md m-1">
            <form class="px-2 py-4" action="" method="post" onsubmit="event.preventDefault()">
                <?php
                date_default_timezone_set("Asia/Tehran"); ?>
                <input type="text" hidden name="store_price" value="store_price">
                <input type="text" hidden name="partNumber" value="<?= $partNumber ?>">
                <input type="text" hidden id="customer_id" name="customer_id" value="<?= $customer ?>">
                <input type="text" hidden id="notification_id" name="notification_id" value="<?= $notification_id ?>">
                <div class="col-span-6 sm:col-span-4">
                    <label class="block text-sm text-gray-700" for="<?= $partNumber ?>-price">
                        قیمت
                    </label>
                    <?php
                    $value = null;
                    if ($finalPriceForm) {
                        $value = $finalPriceForm;
                    } else if (current($givenPrice)) {
                        $value = current($givenPrice)['price'];
                    }
                    ?>
                    <input style="direction: ltr !important;" value="<?= $value ?>" onkeyup="update_price(this)" data-target="<?= $relation_id ?>" name="price" class="text-sm price-input-custome mt-1 block w-full border-2 border-gray-300 px-3 py-2 outline-none" id="<?= $partNumber ?>-price" data-code="<?= $code ?>" type="text" />
                    <p class="mt-2"></p>
                </div>

                <div class="flex gap-2">
                    <button onclick=" createRelation(this)" data-brands='<?= json_encode($existing_brands) ?>' data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-part="<?= $partNumber ?>" type="submit" class="disabled:cursor-not-allowed  disabled:bg-gray-500 tiny-txt inline-flex items-center bg-gray-700 border border-transparent rounded-md text-sm text-white uppercase tracking-widest hover:bg-gray-700 px-2 py-2">
                        ثبت قیمت
                    </button>
                    <button onclick="donotHave(this)" data-brands='<?= json_encode($existing_brands) ?>' data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-part="<?= $partNumber ?>" type="submit" class="disabled:cursor-not-allowed  disabled:bg-gray-500 tiny-txt inline-flex items-center bg-gray-700 border border-transparent rounded-md text-sm text-white uppercase tracking-widest hover:bg-gray-700 px-2 py-2">
                        موجود نیست
                    </button>
                    <button onclick="askPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-user="<?= $_SESSION['user_id'] ?>" data-part="<?= $partNumber ?>" type="button" class="disabled:cursor-not-allowed  disabled:bg-gray-500 tiny-txt inline-flex items-center bg-gray-700 border border-transparent rounded-md text-sm text-white uppercase tracking-widest hover:bg-gray-700 px-2 py-2">
                        ارسال به نیایش
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>