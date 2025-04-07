<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/jdf.php';
require_once '../../../utilities/inventory/InventoryHelpers.php';
function convertPersianToEnglish($string)
{
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persianDigits, $englishDigits, $string);
}

// Helper function to check if a POST value is 'null' and return null if so
function getPostValue($key)
{
    return (isset($_POST[$key]) && $_POST[$key] !== 'null') ? trim($_POST[$key]) : null;
}

// Retrieve POST values
$partNumber = getPostValue('partNumber');
$seller_id = getPostValue('seller');
$brand_id = getPostValue('brand');
$pos1 = getPostValue('pos1');
$pos2 = getPostValue('pos2');
$stock_id = getPostValue('stock');
$user_id = getPostValue('user');
$invoice_number = getPostValue('invoice_number');
$invoice_date = getPostValue('invoice_time');
$exit_time = getPostValue('exit_time');

global $stock;

// Base SQL query
$sql = "SELECT 
        qtybank.id AS purchase_id,
        qtybank.des AS purchase_description,
        qtybank.qty AS purchase_quantity,
        qtybank.pos1 AS purchase_position1,
        qtybank.pos2 AS purchase_position2,
        qtybank.create_time AS purchase_time,
        qtybank.anbarenter AS purchase_isEntered,
        qtybank.invoice AS purchase_hasBill,
        qtybank.invoice_number,
        qtybank.invoice_date,
        nisha.partnumber,
        nisha.price AS good_price,
        seller.id AS seller_id,
        seller.name AS seller_name,
        brand.name AS brand_name,
        deliverer.name AS deliverer_name,
        users.username AS username,
        stock.name AS stock_name
        FROM $stock.qtybank
        INNER JOIN nisha ON qtybank.codeid = nisha.id
        INNER JOIN brand ON qtybank.brand = brand.id
        LEFT JOIN seller ON qtybank.seller = seller.id
        LEFT JOIN deliverer ON qtybank.deliverer = deliverer.id
        LEFT JOIN users ON qtybank.user = users.id
        LEFT JOIN stock ON qtybank.stock_id = stock.id 
        WHERE is_transfered = 0";

// Initialize parameters array
$params = [];

// Dynamically append conditions
if ($partNumber !== null) {
    $sql .= " AND nisha.partnumber LIKE :partNumber";
    $params[':partNumber'] = $partNumber . '%';
}
if ($seller_id !== null) {
    $sql .= " AND qtybank.seller = :seller_id";
    $params[':seller_id'] = $seller_id;
}
if ($brand_id !== null) {
    $sql .= " AND brand.id = :brand_id";
    $params[':brand_id'] = $brand_id;
}
if ($pos1 !== null) {
    $sql .= " AND qtybank.pos1 = :pos1";
    $params[':pos1'] = $pos1;
}
if ($pos2 !== null) {
    $sql .= " AND qtybank.pos2 = :pos2";
    $params[':pos2'] = $pos2;
}
if ($stock_id !== null) {
    $sql .= " AND qtybank.stock_id = :stock_id";
    $params[':stock_id'] = $stock_id;
}
if ($user_id !== null) {
    $sql .= " AND qtybank.user = :user_id";
    $params[':user_id'] = $user_id;
}
if ($invoice_number !== null) {
    $sql .= " AND qtybank.invoice_number = :invoice_number";
    $params[':invoice_number'] = $invoice_number;
}
if ($invoice_date !== null && $exit_time !== null) {
    $sql .= " AND qtybank.invoice_date >= :invoice_date AND qtybank.invoice_date <= :exit_time";
    $params[':invoice_date'] = $invoice_date;
    $params[':exit_time'] = $exit_time;
} elseif ($invoice_date !== null) {
    $sql .= " AND qtybank.invoice_date = :invoice_date";
    $params[':invoice_date'] = $invoice_date;
}

// Append the ORDER BY clause
$sql .= " ORDER BY qtybank.create_time DESC";

try {
    // Prepare the SQL statement
    $stmt = $pdo->prepare($sql);

    // Bind parameters dynamically
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    // Execute the statement
    $stmt->execute();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$purchaseList = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <tr class="border border-b-0 border-x-2 border-y-0 border-gray-800 even:bg-sky-100">
            <td class="text-xs text-center w-2.5"><?= $counter ?></td>
            <td class="p-2 text-center text-lg text-white font-semibold uppercase bg-sky-500">
                <?= '&nbsp;&#8203;' . $item["partnumber"] . PHP_EOL ?>
            </td>
            <td class="p-2 text-center text-sm text-white <?= getBrandBackground(strtoupper($item["brand_name"])) ?> font-semibold uppercase"><?= $item["brand_name"] ?></td>
            <td class="p-2 text-xs text-gray-700 text-center font-semibold"><?= $item["purchase_description"] ?></td>
            <td class="p-2 text-center text-sm font-semibold"><?= $item["purchase_quantity"] ?></td>
            <td class="p-2 text-center text-sm"><?= $item["purchase_position1"] ?></td>
            <td class="p-2 text-center text-sm"><?= $item["purchase_position2"] ?></td>
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
                    <a href="./editPurchase.php?record=<?= $item["purchase_id"] ?>" data-target="<?= $item["purchase_id"] ?>" class="cursor-pointer">
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