<?php
function getSimilarGoods($factorItems, $billId, $customer, $factorNumber, $factorType, $totalPrice, $date, $isComplete, $SMS_Status)
{
    $selectedGoods = [];
    $lowQuantity = [];
    $trackingQuantity = [];

    foreach ($factorItems as $item) {
        $brandSeparator = strripos($item->partName, '-');
        $factorItemParts = explode('-', $item->partName);
        $persianPartNumber = '';

        if (count($factorItemParts) > 1) {
            $goodNameBrand = trim(substr($item->partName, $brandSeparator + 1));
            $goodNamePart = trim(explode(' ', $factorItemParts[0])[0]);
            $persianPartNumber = trim(implode(' ', array_slice(explode(' ', $factorItemParts[0]), 1)));
        } else {
            $goodNameBrand = 'اصلی';
            $goodNamePart = trim(explode(' ', $factorItemParts[0])[0]);
            $persianPartNumber = trim(implode(' ', array_slice(explode(' ', $factorItemParts[0]), 1)));
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
            case 'HYUNDAI':
                $ALLOWED_BRANDS[] = 'HYUNDAI BRAKE';
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
                    'MB',
                    'PH',
                    'CAP',
                    'BRG',
                    'GMB',
                    'KGC',
                    'GATES'
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

        if (!isset($item->original_price) || empty($item->original_price) || $item->original_price == 'موجود نیست') {
            array_push($lowQuantity, [...[
                'quantityId' => $item->id,
                'id' => $item->id,
                'goodId' => $item->id,
                'partNumber' => $goodNamePart,
                'persianPartNumber' => $persianPartNumber,
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

        $goods = getGoodsSpecification($goodNamePart, $ALLOWED_BRANDS);

        $inventoryGoods = isset($goods['goods']) ? $goods['goods'] : [];

        $inventoryGoods = array_filter($inventoryGoods, function ($good) {
            return $good['seller_name'] !== 'کاربر دستوری';
        });

        $relatesCodes = isset($goods['codes']) ? $goods['codes'] : [];

        if (empty($relatesCodes) || count($inventoryGoods) == 0) {
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
                    addToBillItems($good, $sellQuantity, $selectedGoods, $item->id, $totalQuantity, $persianPartNumber);
                } else {
                    $good['quantity'] = $totalQuantity;
                    $good['id'] = $item->id;
                    $lowQuantityItem = $good;
                    $lowQuantityItem['required'] = $billItemQuantity - $totalQuantity;
                    $lowQuantityItem['persianName'] = $persianPartNumber;
                    array_push($lowQuantity, $lowQuantityItem);
                    break;
                }
            }
        }
    }

    if (!empty($selectedGoods) || !empty($lowQuantity)) {
        sendSalesReport($customer, $factorNumber, $factorType, $selectedGoods, $lowQuantity, $billId, $isComplete);
    }

    $selectedGoods = [...$selectedGoods, ...$lowQuantity];

    if (hasPreSellFactor($billId)) {
        update_pre_bill($billId, json_encode($selectedGoods), json_encode([]), $factorNumber, $SMS_Status);
    } else {
        save_pre_bill($billId, json_encode($selectedGoods), json_encode([]), $factorNumber, $SMS_Status);
    }
}

function sendSalesReport($customer, $factorNumber, $factorType, $selectedGoods, $lowQuantity, $billId, $isComplete)
{
    $name = $_SESSION['user']['name'] ?? '';
    $family = $_SESSION['user']['family'] ?? '';
    $fullName = $name . ' ' . $family;
    $edited = $isComplete ? ' ویرایش شده' : '*';

    // Construct the link URL
    $destinationPage = $factorType == 0 ? 'complete.php' : 'complete.php';
    $factorLink = "http://192.168.9.14/YadakShop-APP/views/factor/" . $destinationPage . "?factor_number=" . $billId;
    // Build the header message
    if ($isComplete) {
        $header = sprintf(
            "%s %s\nکاربر : %s\nشماره فاکتور : <a href='%s'>%s</a>\n<span style='color:red'> ❌%s❌</span>\n",
            htmlspecialchars($customer->displayName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($customer->family, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($factorLink, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($factorNumber, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($edited, ENT_QUOTES, 'UTF-8')
        );
    } else {
        $header = sprintf(
            "%s %s\nکاربر : %s\nشماره فاکتور : <a href='%s'>%s</a>\n",
            htmlspecialchars($customer->displayName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($customer->family, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($factorLink, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($factorNumber, ENT_QUOTES, 'UTF-8')
        );
    }


    if ($isComplete) {
        $destination = "http://sells.yadak.center/";
    } else {
        $destination = $factorNumber % 2 == 0 ? "http://sells.yadak.center" : "http://sells.yadak.center";
    }

    sendSellsReportMessage($header, $factorType, $selectedGoods, $lowQuantity, $destination, $isComplete);
    sendPurchaseReportMessage($lowQuantity);
}

function sendPurchaseReportMessage($lowQuantity)
{
    $postData = array(
        "sendMessage" => "PurchaseReport",
        "lowQuantity" => json_encode($lowQuantity),
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "http://delivery.yadak.center/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $result = curl_exec($ch);

    // Close cURL session
    curl_close($ch);
}

function sendSellsReportMessage($header, $factorType, $selectedGoods, $lowQuantity, $destination, $isComplete)
{
    if (!$isComplete) {
        $typeID = $factorType == 0 ? 3516 : 3514;
    } else {
        $typeID = 17815;
    }

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
    // Normalize allowed brands
    $allowedBrands = array_map(fn($brand) => strtoupper(trim($brand)), $allowedBrands);
    $primaryBrand = $allowedBrands[0] ?? null; // The first brand in the allowed list

    $CODES_INFORMATION = [
        'goods' => [],
        'codes' => []
    ];

    $specifiedCode = [];
    $similarCodes = [];

    foreach ($existingGoods as $partNumber => $items) {
        foreach ($items as $item) {
            $processedBrand = strtoupper(trim($item['brandName']));

            if (in_array($processedBrand, $allowedBrands)) {
                $item['brandName'] = $processedBrand;
                $item['partNumber'] = $partNumber;

                if ($partNumber === $completeCode) {
                    $specifiedCode[] = $item;
                } else {
                    $similarCodes[] = $item;
                }

                $CODES_INFORMATION['codes'][] = $partNumber;
            }
        }
    }

    // Remove duplicate codes
    $CODES_INFORMATION['codes'] = array_unique($CODES_INFORMATION['codes']);

    // Helper function to segregate inventory
    $prioritizeInventory = function ($goods) {
        $yadakShop = [];
        $other = [];

        foreach ($goods as $good) {
            if ($good['stockId'] == 9) {
                $yadakShop[] = $good;
            } else {
                $other[] = $good;
            }
        }

        return array_merge($yadakShop, $other);
    };

    // Prioritize inventories for specified and similar codes
    $specifiedCode = $prioritizeInventory($specifiedCode);
    $similarCodes = $prioritizeInventory($similarCodes);

    // Merge inventories
    $mergedGoods = array_merge($specifiedCode, $similarCodes);

    // Final sort based on primary brand
    if ($primaryBrand) {
        usort($mergedGoods, function ($a, $b) use ($primaryBrand) {
            $isPrimaryA = $a['brandName'] === $primaryBrand;
            $isPrimaryB = $b['brandName'] === $primaryBrand;

            // Prioritize primary brand
            if ($isPrimaryA && !$isPrimaryB) {
                return -1;
            } elseif (!$isPrimaryA && $isPrimaryB) {
                return 1;
            }

            // Retain original order otherwise
            return 0;
        });
    }

    $CODES_INFORMATION['goods'] = $mergedGoods;

    return $CODES_INFORMATION;
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

function addToBillItems($good, $quantity, &$selectedGoods, $index, $remaining, $persianName)
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
                'pos2' => $good['pos2'],
                'remaining_qty' => $remaining,
                'persianName' => $persianName,
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
            'pos2' => $good['pos2'],
            'remaining_qty' => $remaining,
            'persianName' => $persianName,
        ];
    }
}

function save_pre_bill($billId, $billItems, $billItemsDescription, $factorNumber, $SMS_Status)
{
    try {
        // Prepare the SQL statement with correct placeholders
        $sql = "INSERT INTO factor.pre_sell(bill_id, selected_items, details) 
                VALUES (:billId, :billItems, :billItemsDescription)";

        $stmt = PDO_CONNECTION->prepare($sql);

        // Ensure the data is serialized if necessary
        $billItems = json_encode($billItems, JSON_UNESCAPED_UNICODE);
        $billItemsDescription = json_encode($billItemsDescription, JSON_UNESCAPED_UNICODE);

        // Bind values to the placeholders
        $stmt->bindValue(":billId", $billId);
        $stmt->bindValue(":billItems", $billItems);
        $stmt->bindValue(":billItemsDescription", $billItemsDescription);

        // Execute the statement and handle the result
        if ($stmt->execute()) {
            echo json_encode([

                'status' => 'success',
                'message' => 'Bill updated successfully',
                'factorNumber' => $factorNumber,
                'SMS_Status' => $SMS_Status
            ]);
        } else {
            echo json_encode([

                'status' => 'error',
                'message' => 'Failed to update bill'
            ]);
        }
    } catch (\Throwable $th) {
        echo json_encode(array('status' => 'error', 'message' => 'An error occurred: ' . $th->getMessage()));
    }
}

function update_pre_bill($billId, $billItems, $billItemsDescription, $factorNumber, $SMS_Status)
{
    try {
        // Prepare the SQL statement with correct placeholders
        $sql = "UPDATE factor.pre_sell SET selected_items = :billItems, details = :billItemsDescription WHERE bill_id = :billId";

        $stmt = PDO_CONNECTION->prepare($sql);

        // Ensure the data is serialized if necessary
        $billItems = json_encode($billItems, JSON_UNESCAPED_UNICODE);
        $billItemsDescription = json_encode($billItemsDescription, JSON_UNESCAPED_UNICODE);

        // Bind values to the placeholders
        $stmt->bindValue(":billId", $billId);
        $stmt->bindValue(":billItems", $billItems);
        $stmt->bindValue(":billItemsDescription", $billItemsDescription);

        // Execute the statement and handle the result
        if ($stmt->execute()) {
            echo json_encode([

                'status' => 'success',
                'message' => 'Bill updated successfully',
                'factorNumber' => $factorNumber,
                'SMS_Status' => $SMS_Status
            ]);
        } else {
            echo json_encode([

                'status' => 'error',
                'message' => 'Failed to update bill'
            ]);
        }
    } catch (\Throwable $th) {
        echo json_encode([

            'status' => 'error',
            'message' => 'An error occurred: ' . $th->getMessage()
        ]);
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
