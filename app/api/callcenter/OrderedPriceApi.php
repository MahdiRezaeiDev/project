<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/callcenter/DollarRateHelper.php';

if (isset($_POST['delete_price'])) :

    $id = $_POST['id'];
    $partNumber = $_POST['partNumber'];
    $existing_brands = json_decode($_POST['brands'], true);
    $customer_id = $_POST['customer_id'];
    $notification_id = $_POST['notification_id'];
    $relation_id = $_POST['relation_id'];
    $code = $_POST['code'];

    $sql = "DELETE FROM shop.prices WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $sql = "SELECT id, partnumber FROM yadakshop.nisha WHERE partnumber = :partNumber";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':partNumber', $partNumber, PDO::PARAM_STR);
    $stmt->execute();
    $good = $stmt->fetch(PDO::FETCH_ASSOC);

    $relation_exist = isInRelation($good['id']);
    $relations = relations($good['id']);
    $relations = array_keys($relations['goods']);
    $givenPrice = givenPrice($relations, $relation_exist);
    $sanitizedPrice = getFinalSanitizedPrice($givenPrice, $existing_brands);

    if (count($givenPrice) > 0) :
        if ($sanitizedPrice) :
            $target = current($givenPrice); ?>
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
                    <?= $target['price'] === null ? 'ندارد' :  $sanitizedPrice ?>
                </td>
                <?php if ($_SESSION['username'] == 'mahdi' || $_SESSION['username'] = 'niyayesh') : ?>
                    <td>
                    </td>
                <?php endif; ?>
            </tr>
        <?php
        endif;
        $target = current($givenPrice);
        $priceDate = $target['created_at'] ?? '';
        if (checkDateIfOkay($applyDate, $priceDate) && $target['price'] !== 'موجود نیست') :
            $rawGivenPrice = $target['price'];
            $finalPriceForm = (applyDollarRate($rawGivenPrice, $priceDate)); ?>
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
                <?php if ($_SESSION['username'] == 'mahdi' || $_SESSION['username'] = 'niyayesh') : ?>
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
                    <?php if ($_SESSION['username'] == 'mahdi' || $_SESSION['username'] = 'niyayesh') : ?>
                        <td data-part="<?= $partNumber ?>" data-code="<?= $code ?>" onclick="deleteGivenPrice(this)" data-brands='<?= json_encode($existing_brands) ?>' data-del='<?= $price['id'] ?>' data-target="<?= $relation_id ?>" scope="col" class="text-sm text-left px-2 py-1 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : '' ?>">
                            <i id="deleteGivenPrice" class="material-icons" title="حذف قیمت">close</i>
                        </td>
                    <?php endif; ?>
                </tr>
                <tr class="w-full mb-1 border-b-2 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'bg-red-500' : 'bg-indigo-300' ?>" data-price='<?= $price['price'] ?>'>
                    <td class="<?php array_key_exists("ordered", $price) ? 'text-white' : '' ?> text-gray-800  py-1 px-2 tiny-text" colspan="<?= ($_SESSION['username'] == 'mahdi' || $_SESSION['username'] = 'niyayesh') ? 4 : 3 ?>" scope="col">
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
        <tr class="min-w-full mb-4 border-b-2 border-white">
            <td colspan="5" scope="col" class="text-gray-800 py-2 text-center bg-indigo-300">
                !! موردی برای نمایش وجود ندارد
            </td>
        </tr>
        <?php
    endif;
endif;

if (isset($_POST['store_price'])) :
    $partNumber = $_POST['partNumber'];
    $existing_brands = json_decode($_POST['brands'], true);
    $price = $_POST['price'];
    $customer_id = $_POST['customer_id'];
    $notification_id = $_POST['notification_id'];
    $relation_id = $_POST['relation_id'];
    $code = $_POST['code'];
    store($partNumber, $price, $customer_id, $notification_id);

    $sql = "SELECT id, partnumber FROM yadakshop.nisha WHERE partnumber = :partNumber";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':partNumber', $partNumber, PDO::PARAM_STR);
    $stmt->execute();
    $good = $stmt->fetch(PDO::FETCH_ASSOC);

    $relation_exist = isInRelation($good['id']);
    $relations = relations($good['id']);
    $relations = array_keys($relations['goods']);
    $givenPrice = givenPrice($relations, $relation_exist);
    $sanitizedPrice = getFinalSanitizedPrice($givenPrice, $existing_brands);

    if ($givenPrice !== null) {
        if ($sanitizedPrice) :
            $target = current($givenPrice); ?>
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
                    <?= $target['price'] === null ? 'ندارد' :  $sanitizedPrice ?>
                </td>
                <?php if ($_SESSION['username'] == 'mahdi' || $_SESSION['username'] = 'niyayesh') : ?>
                    <td>
                    </td>
                <?php endif; ?>
            </tr>
        <?php
        endif;
        $target = current($givenPrice);
        $priceDate = $target['created_at'];
        if (checkDateIfOkay($applyDate, $priceDate) && $target['price'] !== 'موجود نیست') :
            $rawGivenPrice = $target['price'];
            $finalPrice = applyDollarRate($rawGivenPrice, $priceDate); ?>
            <tr class="min-w-full mb-1  bg-cyan-400 hover:cursor-pointer">
                <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPrice ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left px-2 py-1 rtl <?= array_key_exists("ordered", $target) || $target['customerID'] == 1 ? 'text-white' : '' ?>">
                    <?php if (!array_key_exists("ordered", $target)) : ?>
                        <img class="userImage" src="../../public/userimg/<?= $target['userID'] ?>.jpg" alt="userimage">
                    <?php endif; ?>
                </td>
                <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPrice ?>" data-part="<?= $partNumber ?>" class="text-sm text-left <?= array_key_exists("ordered", $target) || $target['customerID'] == 1 ? 'text-white' : '' ?> ">
                    <?= array_key_exists("partnumber", $target) ? $target['partnumber'] : '' ?>
                </td>
                <td onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPrice ?>" data-part="<?= $partNumber ?>" scope="col" class="text-sm text-left px-2 py-1 rtl <?= array_key_exists("ordered", $target) || $target['customerID'] == 1 ? 'text-white' : '' ?>">
                    افزایش قیمت <?= $appliedRate ?> در صد
                </td>
                <td style='direction: ltr !important;' onclick="setPrice(this)" data-target="<?= $relation_id ?>" data-code="<?= $code ?>" data-price="<?= $finalPrice ?>" data-part="<?= $partNumber ?>" scope="col" class="relative text-sm text-left px-2 py-1 <?= array_key_exists("ordered", $target) || $target['customerID'] == 1 ? 'text-white' : '' ?>">
                    <?= $target['price'] === null ? 'ندارد' :  $finalPrice ?>
                </td>
                <td>
                </td>
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
                    <?php if ($_SESSION['username'] == 'mahdi' || $_SESSION['username'] = 'niyayesh') : ?>
                        <td data-part="<?= $partNumber ?>" data-code="<?= $code ?>" onclick="deleteGivenPrice(this)" data-brands='<?= json_encode($existing_brands) ?>' data-del='<?= $price['id'] ?>' data-target="<?= $relation_id ?>" scope="col" class="text-sm text-left px-2 py-1 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : '' ?>">
                            <i id="deleteGivenPrice" class="material-icons" title="حذف قیمت">close</i>
                        </td>
                    <?php endif; ?>
                </tr>
                <tr class="w-full mb-1 border-b-2 <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'bg-red-500' : 'bg-indigo-300' ?>" data-price='<?= $price['price'] ?>'>
                    <td class="<?php array_key_exists("ordered", $price) ? 'text-white' : '' ?> text-gray-800  py-1 px-2 tiny-text" colspan="<?= ($_SESSION['username'] == 'mahdi' || $_SESSION['username'] = 'niyayesh') ? 4 : 3 ?>" scope="col">
                        <div class="flex items-center w-full <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : 'text-gray-800' ?>">
                            <i class="px-1 material-icons tiny-text <?= array_key_exists("ordered", $price) || $price['customerID'] == 1 ? 'text-white' : 'text-gray-800' ?>">access_time</i>
                            <?= timeFormatter($price['created_at']); ?>
                        </div>
                    </td>
                    <td></td>
                </tr>
        <?php endif;
        endforeach;
    } else { ?>
        <tr class="min-w-full mb-4 border-b-2 border-white">
            <td colspan="3" scope="col" class="text-gray-800 py-2 text-center bg-indigo-300">
                !! موردی برای نمایش وجود ندارد
            </td>
        </tr>
    <?php } ?>
<?php
endif;

if (isset($_POST['askPrice'])) {
    $partnumber = $_POST['partNumber'];
    $customer_id = $_POST['customer_id'];
    $user_id = $_POST['user_id'];
    $created_at = date("Y-m-d H:i:s");

    askPrice($conn, $partnumber, $customer_id, $user_id, $created_at);
}

function store($partnumber, $price, $customer_id, $notification_id)
{
    $pattern_sql = "INSERT INTO shop.prices (partnumber, price, user_id, customer_id, created_at, updated_at)
                    VALUES (:partNumber, :price , :user_id ,:customer_id, NOW(), NOW())";
    $stmt = PDO_CONNECTION->prepare($pattern_sql);
    $stmt->bindParam(':partNumber', $partnumber, PDO::PARAM_STR);
    $stmt->bindParam(':price', $price, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($notification_id) {
        // Prepare the SQL statement
        $sql = "UPDATE ask_price SET status = 'done', notify = 'received', price = :price WHERE id = :notification_id";

        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':notification_id', $notification_id);

        // Execute the statement
        $stmt->execute();
    }
}

function askPrice($conn, $partnumber, $customer_id, $user_id, $created_at)
{
    $pattern_sql = "INSERT INTO shop.ask_price (customer_id, user_id, code, status, notify, created_at)
            VALUES ('" . $customer_id . "', '" . $user_id . "', '" . $partnumber . "', 'pending', 'send' , NOW())";

    if ($conn->query($pattern_sql) === TRUE) {
        echo 'true';
    }
}

function relations($id)
{
    try {
        $sql = "SELECT pattern_id FROM shop.similars WHERE nisha_id = :similars_id";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':similars_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $isInRelation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($isInRelation) {
            $sql = "SELECT yadakshop.nisha.* FROM yadakshop.nisha
                    INNER JOIN shop.similars ON similars.nisha_id = nisha.id 
                    WHERE similars.pattern_id = :pattern_id";
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':pattern_id', $isInRelation['pattern_id'], PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM yadakshop.nisha WHERE id = :id";
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sortedGoods = [];
        foreach ($relations as $relation) {
            $sortedGoods[$relation['partnumber']] = $relation;
        }

        return ['goods' => $sortedGoods];
    } catch (PDOException $e) {
        // Handle the error gracefully
        return ['error' => $e->getMessage()];
    }
}

function givenPrice($codes, $relation_exist = null)
{
    $codes = array_filter($codes, function ($item) {
        return strtolower($item);
    });

    $ordered_price = [];


    if ($relation_exist) {
        // Prepare the SQL statement
        $out_sql = "SELECT patterns.price, patterns.created_at FROM shop.patterns WHERE id = :relation_exist";

        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($out_sql);

        // Bind parameter
        $stmt->bindParam(':relation_exist', $relation_exist);

        // Execute the statement
        $stmt->execute();

        // Check if there are any rows returned
        if ($stmt->rowCount() > 0) {
            // Fetch the result
            $ordered_price = $stmt->fetch(PDO::FETCH_ASSOC);

            // Add the 'ordered' key
            $ordered_price['ordered'] = true;
        }
    }


    $givenPrices = [];

    // Prepare the SQL statement
    $sql = "SELECT prices.id, prices.price, prices.partnumber, customer.name, customer.id AS customerID, customer.family, users.id AS userID, prices.created_at
        FROM ((shop.prices 
        INNER JOIN callcenter.customer ON customer.id = prices.customer_id)
        INNER JOIN yadakshop.users ON users.id = prices.user_id)
        WHERE partnumber IN (" . implode(',', array_fill(0, count($codes), '?')) . ")
        ORDER BY created_at DESC LIMIT 7";

    // Prepare the statement
    $stmt = PDO_CONNECTION->prepare($sql);

    // Bind parameters
    foreach ($codes as $index => $code) {
        $stmt->bindValue($index + 1, $code);
    }

    // Execute the statement
    $stmt->execute();

    // Fetch results
    $givenPrices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter out null or empty items
    $givenPrices = array_filter($givenPrices, function ($item) {
        return $item !== null && count($item) > 0;
    });

    $unsortedData = [];

    // Copy the items to unsortedData
    foreach ($givenPrices as $item) {
        $unsortedData[] = $item;
    }

    // Push the ordered_price to unsortedData
    $unsortedData[] = $ordered_price;

    if ($relation_exist) {
        // Sort the data by created_at in descending order
        usort($unsortedData, function ($a, $b) {
            return strtotime($a['created_at']) < strtotime($b['created_at']);
        });
    }

    // Assign final_data based on whether relation_exist is true or not
    $final_data = $relation_exist ? $unsortedData : $givenPrices;

    return $final_data;
}

/**
 * @param Connection to the database
 * @param int $id is the id of the good to check if it has a relationship
 * @return int if the good has a relationship return the id of the relationship
 */
function isInRelation($id)
{
    $sql = "SELECT pattern_id FROM shop.similars WHERE nisha_id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (($result))
        return $result['pattern_id'];
    else
        return false;
}

if (isset($_POST['changeName'])) {
    $id = $_POST['id'];
    $name = $_POST['value'];

    echo json_encode(changeGoodName($id, $name));
}

function changeGoodName($id, $name)
{
    $stmt = PDO_CONNECTION->prepare("UPDATE nisha SET partName= :name WHERE id= :id");
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":id", $id);
    return $stmt->execute();
}

if (isset($_POST['changeDescription'])) {
    $id = $_POST['id'];
    $description = $_POST['value'];

    $relations = [...allRelations($id), $id]; // should return array of related IDs (can be empty)

    echo json_encode(changeGoodDescription($id, $relations, $description));
}

/**
 * Update description for an item and all its relations
 */
function changeGoodDescription($id, $relations, $description)
{
    try {
        // Always build the list of IDs to update
        $ids = !empty($relations) && is_array($relations) ? $relations : [$id];

        // Ensure no duplicates
        $ids = array_unique($ids);

        // Build placeholders (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = PDO_CONNECTION->prepare("
            UPDATE nisha 
            SET description = ? 
            WHERE id IN ($placeholders)
        ");

        // First param = description, then IDs
        $params = array_merge([$description], $ids);
        $result = $stmt->execute($params);

        return [
            'success' => $result,
            'updated_ids' => $ids
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}


function allRelations($id)
{
    $sql = "SELECT pattern_id FROM shop.similars WHERE nisha_id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $pattern_id = $result['pattern_id'];
        $sql = "SELECT nisha_id FROM shop.similars WHERE pattern_id = :pattern_id AND nisha_id != :id";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':pattern_id', $pattern_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $related_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $related_ids ?: [];
    }

    return [];
}
