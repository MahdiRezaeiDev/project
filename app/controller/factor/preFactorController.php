<?php
$completeCode = $_POST['code'];
$dateTime = convertPersianToEnglish(jdate('Y/m/d'));
$explodedCodes = explode("\n", $completeCode);

$nonExistingCodes = [];

$explodedCodes = array_filter($explodedCodes, function ($code) {
    return strlen($code) > 6;
});

// Cleaning and filtering codes
$sanitizedCodes = array_map(function ($code) {
    return strtoupper(preg_replace('/[^a-z0-9]/i', '', $code));
}, $explodedCodes);

// Remove duplicate codes
$explodedCodes = array_unique($sanitizedCodes);

$existing_code = []; // This array will hold the id and partNumber of the existing codes in DB

// Prepare SQL statement outside the loop for better performance
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
        $nonExistingCodes[] = $code;
    }
}

$goodDetails = [];
$relation_id = [];
$codeRelationId = [];
foreach ($explodedCodes as $code) {
    if (!in_array($code, $nonExistingCodes)) {
        foreach ($existing_code[$code] as $item) {
            $relation_exist = isInRelation($item['id']);

            if ($relation_exist) {
                if (!in_array($relation_exist, $relation_id)) {
                    array_push($relation_id, $relation_exist);
                    $goodDescription = relations($relation_exist, true);
                    $goodDetails[$code][$item['partnumber']]['goods'] = getIdealGood($goodDescription['goods'], $item['partnumber']);
                    $goodDetails[$code][$item['partnumber']]['existing'] = $goodDescription['existing'];
                    $goodDetails[$code][$item['partnumber']]['sorted'] = $goodDescription['sorted'];
                    $goodDetails[$code][$item['partnumber']]['givenPrice'] = givenPrice(array_keys($goodDescription['goods']), $relation_exist);
                }
            } else {
                $goodDescription = relations($item['partnumber'], false);
                $goodDetails[$code][$item['partnumber']]['goods'] = $goodDescription['goods'][$item['partnumber']];
                $goodDetails[$code][$item['partnumber']]['existing'] = $goodDescription['existing'];
                $goodDetails[$code][$item['partnumber']]['sorted'] = $goodDescription['sorted'];
                $goodDetails[$code][$item['partnumber']]['givenPrice'] = givenPrice(array_keys($goodDescription['goods']));
            }
        }
    }
}

// Custom comparison function to sort inner arrays by values in descending order
function customSort($a, $b)
{
    $sumA = array_sum($a['sorted']); // Calculate the sum of values in $a
    $sumB = array_sum($b['sorted']); // Calculate the sum of values in $b

    // Compare the sums in descending order
    if ($sumA == $sumB) {
        return 0;
    }
    return ($sumA > $sumB) ? -1 : 1;
}


foreach ($goodDetails as &$record) {
    uasort($record, 'customSort'); // Sort the inner array by values
}

$finalGoods = [];
foreach ($goodDetails as $good) {
    foreach ($good as $key => $item) {
        $finalGoods[$key] = $item;
        break;
    }
}

$goodDetails = $finalGoods;

foreach ($goodDetails as $partNumber => $goodDetail) {
    $brands = [];
    foreach ($goodDetail['existing'] as $item) {
        if (count($item)) {
            array_push($brands, array_keys($item));
        }
    }
    $brands = [...array_unique(array_merge(...$brands))];
    $goodDetails[$partNumber]['brands'] = addRelatedBrands($brands);
    $goodDetails[$partNumber]['finalPrice'] = getFinalSanitizedPrice($goodDetail['givenPrice'], $goodDetails[$partNumber]['brands']);
}

$factorItems = [];
foreach ($goodDetails as $partNumber => $goodDetail) {
    $factorItems[] = [
        "id" => $goodDetail['goods']['id'],
        "partName" => getItemName($goodDetail['goods'], getFinalPriceBrands($goodDetail['finalPrice'])),
        "price_per" => getItemPrice($goodDetail['finalPrice']),
        "quantity" => 1,
        "max" => "undefined",
        "partNumber" => $partNumber
    ];
}

$factorInfo = [
    'customer_id' => 0,
    'bill_number' => 0,
    'quantity' => 0,
    'discount' => 0,
    'tax' => 0,
    'withdraw' => 0,
    'total' => 0,
    'date' => $dateTime,
    'partner' => 1,
    'totalInWords' => null
];

foreach ($factorItems as $item) {
    $factorInfo['quantity'] += $item['quantity'];
    $factorInfo['total'] += $item['price_per'] * $item['quantity'];
}

$incompleteBillId = createBill($factorInfo);

$incompleteBillDetails = createBillItemsTable(
    $incompleteBillId,
    json_encode($factorItems)
);


function getIdealGood($goods, $partNumber)
{
    if (empty($goods[$partNumber]['partName'])) {
        foreach ($goods as $key => &$good) {
            if (!empty($good['partName'])) {
                $good['partnumber'] = $partNumber;
                return $good;
            }
        }
    }
    return $goods[$partNumber];
}

// header('location: /views/factor/checkIncompleteSell.php?factor_number=' . $incompleteBillId);
header('location: /yadakshop-app/views/factor/checkIncompleteSell.php?factor_number=' . $incompleteBillId);