<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$isValidCustomer = false;
$customer_info = null;
$finalResult = null;
$brands = [];
$givenPrices = [];
$discount = isset($_POST['discount']) ? (int)$_POST['discount'] : 0;

if (filter_has_var(INPUT_POST, 'givenPrice') && filter_has_var(INPUT_POST, 'user')) {
    // check if a customer is already specified or not !!!! 1 is the ID of the ordered customer!!!
    $customer = empty($_POST['customer']) ? 1 : $_POST['customer'];

    // remove all the special characters from the user input
    $code = htmlspecialchars($_POST['code']);

    // Setting the user ID who have submitted the form
    $_SESSION["user_id"] = $_POST['user'];

    // Check if the requested is coming from the notification page
    $notification_id = filter_has_var(INPUT_POST, 'notification') ? $_POST['notification'] : null;

    $customer_sql = "SELECT * FROM callcenter.customer WHERE id = :customer_id";
    $stmt = PDO_CONNECTION->prepare($customer_sql);
    $stmt->bindParam(':customer_id', $customer, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->execute()) {
        $isValidCustomer = true;
        $customer_info = $stmt->fetch(PDO::FETCH_ASSOC);
        $completeCode = $code;
        $finalResult = (setup_loading($customer, $completeCode, $notification_id));
    }
}

function setup_loading($customer, $completeCode, $notification = null)
{
    $explodedCodes = explode("\n", $completeCode);

    $results_array = [
        'not_exist' => [],
        'existing' => [],
    ];

    $explodedCodes = array_map(function ($code) {
        if (strlen($code) > 0) {
            return  strtoupper(preg_replace('/[^a-z0-9]/i', '', $code));
        }
    }, $explodedCodes);

    $explodedCodes = array_filter($explodedCodes, function ($code) {
        if (strlen($code) > 6) {
            return  $code;
        }
    });

    // Remove duplicate codes from results array
    $explodedCodes = array_unique($explodedCodes);

    $existing_code = []; // this array will hold the id and partNumber of the existing codes in DB

    $sql = "SELECT id, partnumber FROM yadakshop.nisha WHERE partnumber LIKE :partNumber";
    $stmt = PDO_CONNECTION->prepare($sql);

    foreach ($explodedCodes as $code) {
        $param = $code . '%';
        $stmt->bindParam(':partNumber', $param, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $existing_code[$code] = $result;
        } else {
            $results_array['not_exist'][] = $code; // Adding nonexisting codes to the final result array's not_exist index
        }
    }

    $itemDetails = [];
    $relation_id = [];
    $codeRelationId = [];
    foreach ($explodedCodes as $code) {
        if (!in_array($code, $results_array['not_exist'])) {
            $itemDetails[$code] = [];
            foreach ($existing_code[$code] as $item) {
                $relation_exist = isInRelation($item['id']);

                if ($relation_exist) {
                    $codeRelationId[$code] =  $relation_exist;
                    if (!in_array($relation_exist, $relation_id)) {
                        array_push($relation_id, $relation_exist); // if a new relation exists -> put it in the result array

                        $itemDetails[$code][$item['partnumber']]['information'] = info($relation_exist);
                        $itemDetails[$code][$item['partnumber']]['relation'] = relations($relation_exist, true);
                        $itemDetails[$code][$item['partnumber']]['givenPrice'] = givenPrice(array_keys($itemDetails[$code][$item['partnumber']]['relation']['goods']), $relation_exist);
                    }
                } else {
                    $codeRelationId[$code] =  'not' . rand();
                    $itemDetails[$code][$item['partnumber']]['information'] = info();
                    $itemDetails[$code][$item['partnumber']]['relation'] = relations($item['partnumber'], false);
                    $itemDetails[$code][$item['partnumber']]['givenPrice'] = givenPrice(array_keys($itemDetails[$code][$item['partnumber']]['relation']['goods']));
                }
            }
        }
    }

    // Custom comparison function to sort inner arrays by values in descending order
    function customSort($a, $b)
    {
        $sumA = array_sum($a['relation']['sorted']); // Calculate the sum of values in $a
        $sumB = array_sum($b['relation']['sorted']); // Calculate the sum of values in $b

        // Compare the sums in descending order
        if ($sumA == $sumB) {
            return 0;
        }
        return ($sumA > $sumB) ? -1 : 1;
    }

    foreach ($itemDetails as &$record) {

        uasort($record, 'customSort'); // Sort the inner array by values
    }

    return ([
        'explodedCodes' => $explodedCodes,
        'not_exist' => $results_array['not_exist'],
        'existing' => $itemDetails,
        'customer' => $customer,
        'completeCode' => $completeCode,
        'notification' => $notification,
        'rates' => getSelectedRates(),
        'relation_id' => $codeRelationId
    ]);
}
