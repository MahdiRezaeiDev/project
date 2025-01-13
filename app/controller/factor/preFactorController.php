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
    $quantity = getGoodItemAmount($partNumber);
    $factorItems[] = [
        "id" => $goodDetail['goods']['id'],
        "partName" => getItemName($goodDetail['goods'], getFinalPriceBrands($goodDetail['finalPrice'])),
        "price_per" => getItemPrice($goodDetail['finalPrice']),
        "quantity" => $quantity,
        "max" => "undefined",
        "partNumber" => $partNumber,
        "original_price" => $goodDetail['finalPrice'],
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
    'partner' => $_GET['partner'],
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

function getGoodItemAmount($partNumber)
{
    $quantity = 1;

    // Exact part numbers with fixed quantities (exceptions)
    $exceptionCodes = [
        '2102025150' => 1
    ];

    // Exact complete codes with fixed quantities
    $completeCodes = [
        '1884111051' => 4,
        '2741023700' => 6,
    ];

    // Specific substrings-based quantities
    $specificItemsQuantity = [
        '51712' => 2,
        '54813' => 2,
        '55513' => 2,
        '58411' => 2,
        '230602' => 4,
        '222242' => 16,
        '222243' => 24,
        '234102' => 4,
        '210203' => 4,
        '230412' => 4,
        '210202' => 5,
        '273012' => 4,
        '273013' => 6,
        '230603' => 6,
        '234103' => 6,
        '230413' => 6,
        '273002' => 4,
        '2730137' => 1, // Longer code
        '2730103' => 4,
        '230603F' => 8,
        '210203F' => 4,
        '18858100' => 4,
    ];

    // Regular expression-based patterns and their corresponding quantities
    $patternQuantities = [
        '/^23060[0-9]9$/' => 1,  // Matches "23060-9"
        '/^21020[0-9]9$/' => 1,  // Matches "21020-9"
    ];

    // STEP 1: Check for exact matches in exceptions
    if (array_key_exists($partNumber, $exceptionCodes)) {
        return $exceptionCodes[$partNumber];
    }

    // STEP 2: Check for exact matches in complete codes
    if (array_key_exists($partNumber, $completeCodes)) {
        return $completeCodes[$partNumber];
    }

    // STEP 3: Check for specific substring-based matches
    // Sort specificItemsQuantity keys by length in descending order to prioritize longer keys
    $sortedSpecificItemsQuantity = $specificItemsQuantity;
    uksort($sortedSpecificItemsQuantity, function ($a, $b) {
        return strlen($b) - strlen($a); // Longer keys first
    });

    $ICN = substr($partNumber, 0, 5);  // First 5 characters
    $ICN_BIG = substr($partNumber, 0, 6);  // First 6 characters

    foreach ($sortedSpecificItemsQuantity as $key => $value) {
        if (strpos($partNumber, $key) === 0) { // Match from the start of the part number
            return $value;
        }
    }

    // STEP 4: Check for pattern-based matches using regular expressions
    foreach ($patternQuantities as $pattern => $value) {
        if (preg_match($pattern, $partNumber)) {
            return $value;
        }
    }

    // STEP 5: Default quantity if no match is found
    return $quantity;
}

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


header('location: /views/factor/checkIncompleteSell.php?factor_number=' . $incompleteBillId);
// header('location: /yadakshop-app/views/factor/incomplete.php?factor_number=' . $incompleteBillId);
