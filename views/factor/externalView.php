<?php
$pageTitle = "فاکتور یدک شاپ";
$iconUrl = 'factor.svg';
$logo = "./assets/img/logo.png";
$title = 'فاکتور فروش یدک شاپ';
$subTitle = 'لوازم یدکی هیوندای و کیا';
$factorType = 'yadak';

require_once './components/header.php';
require_once '../../app/controller/factor/DisplayFactorController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

$user_id = $BillInfo['user_id'];
$profile = "../../public/userimg/$user_id.jpg";

if (!file_exists($profile)) {
    $profile = "../../public/userimg/default.png";
}

// Decode JSON once
$billItemsArray = json_decode($billItems, true);
$preBillItemsArray = $preSellFactor ? (json_decode($preSellFactorItems, true) ?? []) : [];
$billItemsDescription = $preSellFactor ? (json_decode($preSellFactorItemsDescription, true) ?? []) : [];

($preBillItemsArray = json_decode($preBillItemsArray, true));
?>
<style>
    .special {
        box-shadow: 7px 0px 0px 0px black inset;
    }
</style>

<div id="bill_body_pdf" class="p-5 m-5 bg-white shadow-lg rounded-lg" dir="rtl">
    <div class="flex justify-between items-center mb-3 p-5">
        <div>
            <div class="rounded-lg border border-gray-800 overflow-hidden grid grid-cols-2 gap-0 w-44">
                <div class="text-xs font-semibold px-3 py-2 bg-gray-200">شماره</div>
                <div class="text-xs font-semibold px-3 py-2"><?= htmlspecialchars($BillInfo['bill_number']); ?></div>
                <div class="text-xs font-semibold px-3 py-2 bg-gray-200">تاریخ</div>
                <div class="text-xs font-semibold px-3 py-2"><?= htmlspecialchars($BillInfo['bill_date']); ?></div>
            </div>
        </div>
        <div class="text-center">
            <h2 class="text-lg font-semibold">مشخصات فاکتور فروش</h2>
            <h2 style="margin-bottom: 7px;">لوازم یدکی هیوندای کیا</h2>
        </div>
        <div class="log_section">
            <svg width="64px" height="64px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                <path fill="#444" d="M12 6v-6h-8v6h-4v7h16v-7h-4zM7 12h-6v-5h2v1h2v-1h2v5zM5 6v-5h2v1h2v-1h2v5h-6zM15 12h-6v-5h2v1h2v-1h2v5z"></path>
                <path fill="#444" d="M0 16h3v-1h10v1h3v-2h-16v2z"></path>
            </svg>
        </div>
    </div>

    <div class="p-5">
        <div class="bg-gray-100 rounded-lg border border-gray-300 py-5 px-2 flex flex-col md:flex-row md:justify-between gap-4">
            <!-- Left Info -->
            <div class="flex-1">
                <ul class="space-y-1 text-sm text-gray-800">
                    <li><strong>نام:</strong> <span><?= htmlspecialchars($customerInfo['name']) ?></span></li>
                    <li><strong>شماره تماس:</strong> <span><?= htmlspecialchars($customerInfo['phone']) ?></span></li>
                </ul>
            </div>

            <!-- Right Info -->
            <div class="flex-1">
                <ul class="space-y-1 text-sm text-gray-800">
                    <li><strong>نشانی:</strong> <span><?= htmlspecialchars($customerInfo['address']) ?></span></li>
                </ul>
            </div>

            <!-- Profile & Timestamps -->
            <div class="flex items-center gap-3 rounded-md p-2">
                <img class="rounded-full w-10 h-10 object-cover" src="<?= htmlspecialchars($profile) ?>" alt="تصویر">
                <div class="text-xs text-gray-700 leading-relaxed">
                    <div><strong>زمان ثبت:</strong> <span><?= htmlspecialchars($BillInfo['bill_date']) ?></span></div>
                    <div><strong>زمان پرینت:</strong> <span><?= date('H:i'); ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4 px-5">
        <div class="overflow-x-auto rounded-lg border border-gray-300 shadow-sm">
            <table class="w-full text-sm text-right border-collapse border border-gray-300" dir="rtl" style="border-collapse: collapse;">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2 whitespace-nowrap">ردیف</th>
                        <th class="border border-gray-300 px-3 py-2 whitespace-nowrap">نام قطعه</th>
                        <th class="border border-gray-300 py-2 whitespace-nowrap text-left px-8">جزئیات</th>
                        <th class="border border-gray-300 py-2 whitespace-nowrap text-left px-8">کسری</th>
                        <th class="border border-gray-300 px-3 py-2 whitespace-nowrap text-center">گزارش فنی</th>
                        <th class="border border-gray-300 px-3 py-2 text-center whitespace-nowrap">تعداد</th>
                        <th class="border border-gray-300 px-3 py-2 text-center whitespace-nowrap">قیمت واحد</th>
                        <th class="border border-gray-300 px-3 py-2 text-center whitespace-nowrap">قیمت مجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    $totalPrice = 0;
                    $totalQuantity = 0;

                    $brands = ["شرکتی", "کره ای", "کره", "چین", "چینی", "متفرقه"];
                    $excludeBrands = ["اصلی", "GEN", "MOB"];
                    $brandPattern = '/\b(' . implode('|', $excludeBrands) . ')\b/u';

                    foreach ($billItemsArray as $item):
                        $payPrice = (int)$item['quantity'] * (int)$item['price_per'];
                        $totalPrice += $payPrice;
                        $totalQuantity += (int)$item['quantity'];

                        $isBrand = false;
                        foreach ($brands as $brand) {
                            if (mb_stripos($item['partName'], $brand) !== false) {
                                $isBrand = true;
                                break;
                            }
                        }
                        $specialClass = $isBrand ? 'special' : '';

                        $nameParts = explode('-', $item['partName']);
                        $excludeClass = '';

                        if (!empty($nameParts[1])) {
                            $brand = trim($nameParts[1]);
                            if ($brand !== "اصلی" && !preg_match($brandPattern, $brand)) {
                                $excludeClass = 'exclude';
                            }
                        }

                        $price_difference = $item['price_per'] - $item['actual_price'];
                        $price_template = '';
                        if ($price_difference > 0 && $price_difference !=  $item['price_per']) {
                            $price_template = "* " . number_format(abs($price_difference) / 10000, 0) . '+';
                        } elseif ($price_difference < 0) {
                            $price_template = "* " . number_format(abs($price_difference) / 10000, 0) . '-';
                        }

                        $LRTemplate = '';
                        if (isset($item['original_price']) && mb_strpos($item['original_price'], '(LR)') !== false) {
                            $LRTemplate = '●';
                        }

                        // Find pre-sell items related to this bill item by matching IDs or partNumbers
                        $relatedPreSellItems = [];
                        foreach ($preBillItemsArray as $preItem) {
                            if (isset($preItem['id']) && $preItem['id'] == $item['id']) {
                                $relatedPreSellItems[] = $preItem;
                            } elseif (isset($preItem['partNumber']) && strpos($item['partName'], $preItem['partNumber']) !== false) {
                                $relatedPreSellItems[] = $preItem;
                            }
                        }
                    ?>
                        <tr class="even:bg-gray-100">
                            <td class="border border-gray-300 text-sm text-center py-2"><?= $counter ?></td>
                            <td class="border border-gray-300 text-sm <?= $specialClass ?> py-2">
                                <?= htmlspecialchars($nameParts[0]) ?>
                                <?php if (!empty($nameParts[1])): ?>
                                    - <span class="<?= $excludeClass ?>"><?= htmlspecialchars($nameParts[1]) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="flex justify-end px-5 border-l border-gray-300 text-sm py-2">
                                <table style="direction:ltr !important; width: 100%;">
                                    <tbody>
                                        <?php foreach ($relatedPreSellItems as $preItem):
                                            $specialBrandClass = in_array($preItem['brandName'], ['MOB', 'GEN', 'اصلی']) ? '' : 'text-white bg-gray-700 p-1 rounded-sm';
                                        ?>
                                            <tr>
                                                <td style="width: 120px; padding: 3px; font-size: 13px; text-align: left;">
                                                    <?= htmlspecialchars($preItem['partNumber']) ?>
                                                </td>
                                                <td style="width: 50px; padding: 3px; text-align: left;">
                                                    <p class="text-xs text-center <?= $specialBrandClass ?>">
                                                        <?= htmlspecialchars($preItem['brandName']) ?>
                                                    </p>
                                                </td>
                                                <td style="width: 30px; padding: 3px; font-size: 13px; text-align: left;">
                                                    <p class="text-xs text-center bg-gray-300 px-1 py-1 rounded-sm">
                                                        <?= (int)$preItem['quantity'] ?>
                                                    </p>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="pl-3 text-xs mt-1" style="white-space: pre-line; text-align: left; direction: ltr;">
                                    <?php
                                    if (!empty($billItemsDescription[$item['id']])) {
                                        echo htmlspecialchars($billItemsDescription[$item['id']]);
                                    }
                                    ?>
                                </div>
                            </td>
                            <td style="width: 30px; padding: 3px; font-size: 13px; text-align: left;">
                                <div class="flex gap-2 items-center justify-center">
                                    <?php if (!empty($preItem['required'])): ?>
                                        <p style="width: 20px; padding: 3px; font-size: 13px; text-align: left;">
                                            <?= htmlspecialchars($preItem['required'] ?? '') ?>
                                        </p>
                                        <svg width="20px" height="20px" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg" fill="#ea5a47">
                                            <path d="m58.14 21.78-7.76-8.013-14.29 14.22-14.22-14.22-8.013 8.013 14.36 14.22-14.36 14.22 8.014 8.013 14.22-14.22 14.29 14.22 7.76-8.013-14.22-14.22z" />
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="border border-gray-300 text-sm text-center py-2">
                                <span>
                                    <?= $LRTemplate ?>
                                    <?= $price_template ?>
                                </span>
                            </td>
                            <td class="border border-gray-300 text-sm <?= ($item['quantity'] != 1) ? 'font-semibold' : '' ?> text-center py-2">
                                <?= (int)$item['quantity'] ?>
                            </td>
                            <td class="border border-gray-300 text-sm text-center py-2 text-green-600 font-medium">
                                <?= number_format($item['price_per']) ?>
                            </td>
                            <td class="border border-gray-300 text-sm text-center py-2 text-green-600 font-medium">
                                <?= number_format((int)$item['quantity'] * $item['price_per']) ?> ریال
                            </td>
                        </tr>
                    <?php
                        $counter++;
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-semibold text-gray-800 border-t border-gray-300">
                        <td colspan="5" class="py-3 pr-3">
                            <div class="flex gap-5 items-center">
                                <span>جمع فاکتور</span>
                                <span id="total_in_word_owner"></span>
                            </div>
                        </td>
                        <td class="border border-gray-300 text-center py-3"><?= $totalQuantity ?></td>
                        <td colspan="2" class="border border-gray-300 text-center"><span class="text-green-700"><?= number_format($totalPrice) ?> ریال</span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        const total = <?= json_encode($totalPrice) ?>;
        document.getElementById("total_in_word_owner").innerHTML = numberToPersianWords(
            total
        );
    </script>

    <!-- Description section -->
    <div class="p-5 text-sm border border-gray-300 rounded-lg mt-6">
        <div class="font-semibold bg-gray-100 p-2 rounded-md mb-2 text-center">توضیحات فاکتور</div>
        <div class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">
            <?= nl2br(htmlspecialchars($BillInfo['description'] ?? '')) ?>
        </div>
    </div>
</div>

<?php require_once './components/footer.php'; ?>