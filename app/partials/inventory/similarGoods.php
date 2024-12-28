<?php
function getSimilarGoods($factorItems, $billId, $customer, $factorNumber, $factorType, $totalPrice, $date)
{
    $selectedGoods = [];
    $lowQuantity = [];
    $trackingQuantity = [];

    foreach ($factorItems as $item) {

        $brandSeparator = strripos($item->partName, '-');
        $factorItemParts = explode('-', $item->partName);

        if (count($factorItemParts) > 1) {
            $goodNameBrand = trim(substr($item->partName, $brandSeparator + 1));
            $goodNamePart = trim(explode(' ', $factorItemParts[0])[0]);
        } else {
            $goodNameBrand = 'اصلی';
            $goodNamePart = trim(explode(' ', $factorItemParts[0])[0]);
        }

        $ALLOWED_BRANDS = [];

        // Add related brands based on the current brand
        switch ($goodNameBrand) {
            case 'اصلی':
            case 'GEN':
            case 'MOB':
                $ALLOWED_BRANDS = array_merge($ALLOWED_BRANDS, ['GEN', 'MOB']);
                break;
            case 'شرکتی':
                $ALLOWED_BRANDS[] = 'IRAN';
                break;
            case 'متفرقه':
            case 'چین':
                $ALLOWED_BRANDS[] = 'CHINA';
                break;
            case 'کره':
            case 'کره ای':
                $ALLOWED_BRANDS[] = 'KOREA';
                break;
            default:
                $ALLOWED_BRANDS[] = $goodNameBrand;
        }

        if ($goodNameBrand == 'HIQ' || $goodNameBrand == 'HI') {
            $brands = [
                'HIQ' => ['HI Q'],
                'HI' => ['HI Q'],
            ];
            $ALLOWED_BRANDS = [...$brands[$goodNameBrand], $goodNameBrand];
        }

        if ($goodNameBrand == 'KOREA' || $goodNameBrand == 'CHINA') {
            $brands = [
                'KOREA' => [
                    'YONG',
                    'YONG HOO',
                    'OEM',
                    'ONNURI',
                    'GY',
                    'MIDO',
                    'MIRE',
                    'CARDEX',
                    'MANDO',
                    'OSUNG',
                    'DONGNAM',
                    'HYUNDAI BRAKE',
                    'SAM YUNG',
                    'BRC',
                    'GEO SUNG',
                    'YULIM',
                    'CARTECH',
                    'HSC',
                    'KOREA STAR',
                    'DONI TEC',
                    'ATC',
                    'VALEO',
                    'MB KOREA',
                    'FAKE MOB',
                    'FAKE GEN',
                    'IACE',
                    'MB'
                ],
                'CHINA' => [
                    'OEMAX',
                    'JYR',
                    'RB2',
                    'Rb2',
                    'IRAN',
                    'FAKE MOB',
                    'FAKE GEN',
                    'OEMAX',
                    'OE MAX',
                    'MAXFIT',
                    'ICBRI',
                    'HOH'
                ],
            ];
            $ALLOWED_BRANDS = [...$brands[$goodNameBrand], $goodNameBrand];
        } else {
            $ALLOWED_BRANDS = addRelatedBrands($ALLOWED_BRANDS);
        }

        $goods = getGoodsSpecification($goodNamePart, $ALLOWED_BRANDS);

        $inventoryGoods = isset($goods['goods']) ? $goods['goods'] : [];

        $inventoryGoods = array_filter($inventoryGoods, function ($good) {
            return $good['seller_name'] !== 'کاربر دستوری';
        });

        $relatesCodes = isset($goods['codes']) ? $goods['codes'] : [];

        if (empty($relatesCodes)) {
            array_push($lowQuantity, [...[
                'quantityId' => $item->id,
                'id' => $item->id,
                'goodId' => $item->id,
                'partNumber' => $goodNamePart,
                'stockId' => null,
                'purchase_Description' => '',
                'stockName' => '',
                'brandName' => mb_convert_encoding($goodNameBrand, 'UTF-8', 'UTF-8'),
                'sellerName' => '',
                'quantity' => $item->quantity,
                'pos1' => '',
                'pos2' => '',
            ], 'required' => $item->quantity]);
            continue;
        }

        $billItemQuantity = $item->quantity;
        $totalQuantity = getTotalQuantity($inventoryGoods, $ALLOWED_BRANDS);

        foreach ($inventoryGoods as $good) {
            if ($billItemQuantity == 0) {
                break;
            }

            // Check if the good's brand is allowed
            if (in_array(strtoupper($good['brandName']), $ALLOWED_BRANDS)) {
                if ($totalQuantity >= $billItemQuantity) {
                    // Use the global tracker for remaining quantities
                    if (array_key_exists($good['quantityId'], $trackingQuantity)) {
                        // Skip goods with no remaining quantity
                        if ($trackingQuantity[$good['quantityId']] <= 0) {
                            continue;
                        }

                        // Update the good's remaining quantity from the tracker
                        $good['remaining_qty'] = $trackingQuantity[$good['quantityId']];
                    } else {
                        // Initialize tracker with the good's original remaining quantity
                        $trackingQuantity[$good['quantityId']] = $good['remaining_qty'];
                    }

                    // Determine how much can be allocated
                    $sellQuantity = min($billItemQuantity, $good['remaining_qty']);

                    // Deduct the allocated quantity from both the bill item and tracker
                    $billItemQuantity -= $sellQuantity;
                    $trackingQuantity[$good['quantityId']] -= $sellQuantity;

                    // Add the allocated good to the selected goods
                    addToBillItems($good, $sellQuantity, $selectedGoods, $item->id);
                } else {
                    $good['quantity'] = $totalQuantity;
                    $good['id'] = $item->id;
                    array_push($lowQuantity, [...$good, 'required' => $billItemQuantity - $totalQuantity]);
                    break;
                }
            }
        }
    }

    if (!empty($selectedGoods) || !empty($lowQuantity)) {
        sendSalesReport($customer, $factorNumber, $factorType, $selectedGoods, $lowQuantity, $billId);
    }

    if ($factorType == 0) {
        sendPurchaseMessageToCustomer($customer, $factorNumber, $totalPrice, $date);
    }

    $selectedGoods = [...$selectedGoods, ...$lowQuantity];

    if (hasPreSellFactor($billId)) {
        update_pre_bill($billId, json_encode($selectedGoods), json_encode([]), $factorNumber);
    } else {
        save_pre_bill($billId, json_encode($selectedGoods), json_encode([]), $factorNumber);
    }
}

function sendPurchaseMessageToCustomer($customer, $factorNumber, $totalPrice, $date)
{
    $fullName = $customer->name . ' ' . $customer->family;
    $phone = $customer->phone;
    $date = explode('/', $date);
    $current = $date[1] . '/' . $date[2];
    // Construct the message
    $message = "$fullName عزیز\n" .
        "خرید شما با مبلغ $totalPrice ریال در تاریخ $current با موفقیت ثبت شد \n" .
        "شماره فاکتور : $factorNumber \n" .
        "یدک شاپ از انتخاب شما سپاسگزار است و خوشحالیم که همراه شما هستیم. \n" .
        "www.yadak.shop";

    // URL-encode the message
    $encodedMessage = rawurlencode($message);

    // Construct the full URL
    $url = "http://192.168.9.16/cgi/WebCGI?1500101=account=test&password=test1028&port=1&destination=" . $phone . "&content=" . $encodedMessage;

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute request
    $response = curl_exec($ch);

    // Check for errors
    // if ($response === false) {
    //     echo "cURL Error: " . curl_error($ch);
    // } else {
    //     echo "Message sent successfully: " . htmlspecialchars($response);
    // }

    // Close cURL
    curl_close($ch);
}


function sendSalesReport($customer, $factorNumber, $factorType, $selectedGoods, $lowQuantity, $billId)
{
    $name = $_SESSION['user']['name'] ?? '';
    $family = $_SESSION['user']['family'] ?? '';
    $fullName = $name . ' ' . $family;

    // Construct the link URL
    $destinationPage = $factorType == 0 ? 'complete.php' : 'complete.php';
    $factorLink = "http://192.168.9.14/YadakShop-APP/views/factor/" . $destinationPage . "?factor_number=" . $billId;
    // Build the header message
    $header = sprintf(
        "%s %s\nکاربر : %s\nشماره فاکتور : <a href='%s'>%s</a>\n",
        htmlspecialchars($customer->displayName, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($customer->family, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($factorLink, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($factorNumber, ENT_QUOTES, 'UTF-8')
    );


    $destination = $factorNumber % 2 == 0 ? "http://sells.yadak.center/" : "http://sells2.yadak.center/";

    sendSellsReportMessage($header, $factorType, $selectedGoods, $lowQuantity, $destination);
}

function sendSellsReportMessage($header, $factorType, $selectedGoods, $lowQuantity, $destination)
{
    $typeID = $factorType == 0 ? 3516 : 3514;

    $postData = array(
        "sendMessage" => "sellsReportTest",
        "header" => $header,
        "topic_id" => $typeID,
        "selectedGoods" => json_encode($selectedGoods),
        "lowQuantity" => json_encode($lowQuantity),
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $destination);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $result = curl_exec($ch);

    // Close cURL session
    curl_close($ch);
}

function getGoodsSpecification($completeCode, $allAllowedBrands)
{
    $similarCodes = setup_loading($completeCode);

    $relatedGoods =  $similarCodes['existing'] ? current(current($similarCodes['existing'])) : [];

    if ($relatedGoods) {
        $stockInfo = $relatedGoods['relation']['stockInfo'];


        $existingGoods = array_filter($stockInfo, function ($item) {
            return count($item) > 0;
        });

        $MatchedGoods = fetchCodesWithInfo($existingGoods, $allAllowedBrands, $completeCode);

        return $MatchedGoods;
    } else {
        return null;
    }
}

function setup_loading($completeCode)
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

    foreach ($explodedCodes as $code) {
        $sql = "SELECT id, partnumber FROM yadakshop.nisha WHERE partnumber LIKE :partNumber";
        $stmt = PDO_CONNECTION->prepare($sql);
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

    foreach ($itemDetails as &$record) {

        uasort($record, 'resultCustomSort'); // Sort the inner array by values
    }

    return ([
        'explodedCodes' => $explodedCodes,
        'not_exist' => $results_array['not_exist'],
        'existing' => $itemDetails,
    ]);
}

// Custom comparison function to sort inner arrays by values in descending order
function resultCustomSort($a, $b)
{
    $sumA = array_sum($a['relation']['sorted']); // Calculate the sum of values in $a
    $sumB = array_sum($b['relation']['sorted']); // Calculate the sum of values in $b

    // Compare the sums in descending order
    if ($sumA == $sumB) {
        return 0;
    }
    return ($sumA > $sumB) ? -1 : 1;
}

function fetchCodesWithInfo($existingGoods, $allowedBrands, $completeCode)
{
    $allowedBrands = array_map('trim', $allowedBrands);
    $allowedBrands = array_map('strtoupper', $allowedBrands);

    $CODES_INFORMATION = [
        'goods' => [],
        'codes' => []
    ];

    $specifiedCode = [];
    $similarCodes = [];

    foreach ($existingGoods as $key => $code) {
        foreach ($code as $item) {
            $processedBrand = strtoupper(trim($item['brandName']));

            if (in_array($processedBrand, $allowedBrands)) {
                $item['brandName'] = strtoupper(trim($item['brandName']));
                $allowedBrands = array_map('strtoupper', $allowedBrands);
                $item['partNumber'] = $key;
                if ($key == $completeCode) {
                    $specifiedCode[] = $item;
                } else {
                    $similarCodes[] = $item;
                }
                $CODES_INFORMATION['codes'][] = $key;
                $CODES_INFORMATION['codes'] = array_unique($CODES_INFORMATION['codes']);
            }
        }
    }

    $YadakShopInventory = [];
    $otherInventory = [];

    foreach ($specifiedCode as $good) {
        if ($good['stockId'] == 9) {
            $YadakShopInventory[] = $good;
        } else {
            $otherInventory[] = $good;
        }
    }

    $specifiedCode = array_merge($YadakShopInventory, $otherInventory);

    $YadakShopInventory = [];
    $otherInventory = [];

    foreach ($similarCodes as $good) {
        if ($good['stockId'] == 9) {
            $YadakShopInventory[] = $good;
        } else {
            $otherInventory[] = $good;
        }
    }

    // Merge inventories
    $CODES_INFORMATION['goods'] = array_merge($specifiedCode, $YadakShopInventory, $otherInventory);

    // No need to filter empty goods, all goods will have data
    $CODES_INFORMATION['goods'] = array_filter($CODES_INFORMATION['goods'], function ($item) {
        return !empty($item); // Ensuring that goods are non-empty
    });

    // Return unique codes instead of array keys
    return ($CODES_INFORMATION);
}

function getTotalQuantity($goods = [], $brandsName = [])
{
    $totalQuantity = 0;

    foreach ($goods as $good) {
        if (in_array($good['brandName'], $brandsName)) {
            $totalQuantity = (int)($totalQuantity) +  (int)($good['remaining_qty']);
        }
    }
    return $totalQuantity;
}

function addToBillItems($good, $quantity, &$selectedGoods, $index)
{
    // Check if the item already exists in billItems
    if (array_key_exists($good['goodId'], $selectedGoods)) {
        if ($selectedGoods[$good['goodId']]['brandName'] == $good['brandName']) {

            // If the item exists, sum the quantities
            $selectedGoods[$good['goodId']]['quantity'] += $quantity;
        } else {
            array_push($selectedGoods, [
                'quantityId' => $good['quantityId'],
                'id' => $index,
                'goodId' => $good['goodId'],
                'partNumber' => $good['partNumber'],
                'stockId' => $good['stockId'],
                'purchase_Description' => $good['purchase_Description'],
                'stockName' => $good['stockName'],
                'brandName' => $good['brandName'],
                'sellerName' => $good['seller_name'],
                'quantity' => $quantity,
                'pos1' => $good['pos1'],
                'pos2' => $good['pos2']
            ]);
        }
    } else {
        // If the item does not exist, add it to billItems
        $selectedGoods[$good['goodId']] = [
            'quantityId' => $good['quantityId'],
            'id' => $index,
            'goodId' => $good['goodId'],
            'partNumber' => $good['partNumber'],
            'stockId' => $good['stockId'],
            'purchase_Description' => $good['purchase_Description'],
            'stockName' => $good['stockName'],
            'brandName' => $good['brandName'],
            'sellerName' => $good['seller_name'],
            'quantity' => $quantity,
            'pos1' => $good['pos1'],
            'pos2' => $good['pos2']
        ];
    }
}

function save_pre_bill($billId, $billItems, $billItemsDescription, $factorNumber)
{
    try {
        // Prepare the SQL statement with correct placeholders
        $sql = "INSERT INTO factor.pre_sell(bill_id, selected_items, details) 
                VALUES (:billId, :billItems, :billItemsDescription)";

        $stmt = PDO_CONNECTION->prepare($sql);

        // Ensure the data is serialized if necessary
        $billItems = json_encode($billItems);
        $billItemsDescription = json_encode($billItemsDescription);

        // Bind values to the placeholders
        $stmt->bindValue(":billId", $billId);
        $stmt->bindValue(":billItems", $billItems);
        $stmt->bindValue(":billItemsDescription", $billItemsDescription);

        // Execute the statement and handle the result
        if ($stmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Bill saved successfully', 'factorNumber' => $factorNumber));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to save bill'));
        }
    } catch (\Throwable $th) {
        echo json_encode(array('status' => 'error', 'message' => 'An error occurred: ' . $th->getMessage()));
    }
}

function update_pre_bill($billId, $billItems, $billItemsDescription, $factorNumber)
{
    try {
        // Prepare the SQL statement with correct placeholders
        $sql = "UPDATE factor.pre_sell SET selected_items = :billItems, details = :billItemsDescription WHERE bill_id = :billId";

        $stmt = PDO_CONNECTION->prepare($sql);

        // Ensure the data is serialized if necessary
        $billItems = json_encode($billItems);
        $billItemsDescription = json_encode($billItemsDescription);

        // Bind values to the placeholders
        $stmt->bindValue(":billId", $billId);
        $stmt->bindValue(":billItems", $billItems);
        $stmt->bindValue(":billItemsDescription", $billItemsDescription);

        // Execute the statement and handle the result
        if ($stmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Bill updated successfully', 'factorNumber' => $factorNumber));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to update bill'));
        }
    } catch (\Throwable $th) {
        echo json_encode(array('status' => 'error', 'message' => 'An error occurred: ' . $th->getMessage()));
    }
}

function hasPreSellFactor($factorId)
{
    $sql = "SELECT * FROM factor.pre_sell WHERE bill_id = :billId";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":billId", $factorId);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}
