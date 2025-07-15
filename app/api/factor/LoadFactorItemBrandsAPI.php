<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/callcenter/DollarRateHelper.php';
require_once '../../../utilities/callcenter/TemporaryHelper.php';

if (isset($_POST['completeCode'])) {
    $completeCode = $_POST['completeCode'];
    $brandsPrices = getDetails($completeCode);
    echo json_encode($brandsPrices);
}

function getDetails($completeCode)
{
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

    foreach ($goodDetails as $parentCode => &$children) {
        // Skip if not array or empty
        if (!is_array($children) || empty($children)) continue;

        // Separate givenPrice for appending later
        $givenPrice = $children['givenPrice'] ?? null;
        unset($children['givenPrice']); // Temporarily remove it

        // Get the first child
        $firstKey = array_key_first($children);
        if ($firstKey && (empty($children[$firstKey]['givenPrice']) || !is_array($children[$firstKey]['givenPrice']))) {
            // Remove first element and push it to the end
            $element = array_shift($children);
            $children[$firstKey] = $element;
        }

        // Restore givenPrice if it existed
        if ($givenPrice !== null) {
            $children['givenPrice'] = $givenPrice;
        }

        // Assign back to parent
        $goodDetails[$parentCode] = $children;
    }

    $finalGoods = [];
    foreach ($goodDetails as $good) {
        foreach ($good as $key => $item) {
            $finalGoods[$key] = $item;
            break;
        }
    }

    $goodDetails = $finalGoods;

    foreach ($goodDetails as $parentCode => &$children) {
        // Skip if not array or empty
        if (!is_array($children) || empty($children)) continue;

        // Separate givenPrice for appending later
        $givenPrice = $children['givenPrice'] ?? null;
        unset($children['givenPrice']); // Temporarily remove it

        // Get the first child
        $firstKey = array_key_first($children);
        if ($firstKey && (empty($children[$firstKey]['givenPrice']) || !is_array($children[$firstKey]['givenPrice']))) {
            // Remove first element and push it to the end
            $element = array_shift($children);
            $children[$firstKey] = $element;
        }

        // Restore givenPrice if it existed
        if ($givenPrice !== null) {
            $children['givenPrice'] = $givenPrice;
        }

        // Assign back to parent
        $goodDetails[$parentCode] = $children;
    }

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


    $brandsPrices = [];

    foreach ($goodDetails as $partNumber => $goodDetail) {
        $brandsPrices[$partNumber]['prices'] = getFinalPriceBrands($goodDetail['finalPrice']);
        $brandsPrices[$partNumber]['partName'] = getItemName($goodDetail['goods'], $brandsPrices[$partNumber]['prices']);
        $brandsPrices[$partNumber]['original'] = $goodDetail['finalPrice'];
    }

    return $brandsPrices;
}


function getFinalPriceBrands($price)
{
    $brandsPrice = [];
    $addedBrands = [];

    // Return empty array if price is not available or is 'موجود نیست'
    if (empty($price) || $price == 'موجود نیست') {
        return $brandsPrice;
    }

    // Split the price string into parts by '/'
    $pricesParts = explode('/', $price);
    $pricesParts = array_map('trim', $pricesParts);
    $pricesParts = array_map('strtoupper', $pricesParts);

    // Create a mapping for complex brand names
    $complexBrandMap = [
        'HI' => 'HI Q',
        'HYUNDAI' => 'HYUNDAI BRAKE',
        'MB' => 'MB KOREA',
        'OE' => 'OE MAX',
        'SAM' => 'SAM YUNG',
        'GEO' => 'GEO SUNG',
        'FAKE' => 'FAKE MOB',
        'YOUNG' => 'YOUNG SHIN'
    ];

    foreach ($pricesParts as $part) {
        $spaceIndex = strpos($part, ' ');
        if ($spaceIndex !== false) {
            $priceSubStr = substr($part, 0, $spaceIndex); // Extract price part
            $brandSubStr = substr($part, $spaceIndex + 1); // Extract brand part
            $brand = trim(explode('(', $brandSubStr)[0]); // Handle '('
            $complexBrands = explode(' ', $brand)[0]; // Extract main brand word

            // Map complex brands if needed
            if (isset($complexBrandMap[$complexBrands])) {
                $complexBrands = $complexBrandMap[$complexBrands];
            }

            // Add brand and price if it hasn't been added yet
            if (!in_array($complexBrands, $addedBrands) && !empty($complexBrands)) {
                $addedBrands[] = $complexBrands;
                $priceValue = is_numeric($priceSubStr) ? $priceSubStr * 10000 : 0;

                // Special case for 'MOB' and 'GEN' brands
                if ($complexBrands == 'MOB' || $complexBrands == 'GEN') {
                    $brandsPrice['اصلی'] = $priceValue;
                    continue;
                }

                // Add price for other brands
                $brandsPrice[$complexBrands] = $priceValue;
            }
        } else {
            // Handle case where no space is found (assume it's only the price)
            $brandsPrice['اصلی'] = is_numeric($part) ? $part * 10000 : 0;
        }
    }

    return $brandsPrice;
}

function getItemName($good, $brands)
{
    $brands = array_keys($brands);
    $name = $good['partnumber'];

    if ($good['partName']) {
        $name .= " (" . $good['partName'] . ")";
    }

    if (in_array('اصلی', $brands)) {
        $name .= ' - اصلی';
    } else if (count($brands) == 1) {
        $name .= ' - ' . $brands[0];
    }

    return $name;
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
