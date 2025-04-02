<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/callcenter/DollarRateHelper.php';
require_once '../../../utilities/callcenter/GivenPriceHelper.php';
require_once '../../../utilities/inventory/ExistingHelper.php';

if (isset($_POST['getSimilarCodes'])) {
    $completeCode = trim($_POST['partNumber']);
    $fullCode = trim($_POST['fullCode']);
    $allowedBrands = array_unique(array_map('trim', explode(',', $_POST['allowedBrands'])));

    $similarCodes = setup_loading($completeCode);

    $relatedGoods =  $similarCodes['existing'] ? current(current($similarCodes['existing'])) : [];

    if ($relatedGoods) {
        $stockInfo = $relatedGoods['relation']['stockInfo'];

        $existingGoods = array_filter($stockInfo, function ($item) {
            return count($item) > 0;
        });

        $MatchedGoods = getCodesWithInfo($existingGoods, $allowedBrands, $completeCode, $fullCode);

        echo json_encode($MatchedGoods);
    } else {
        echo json_encode(null);
    }
} else {
    echo "Not defined";
}

function getCodesWithInfo($existingGoods, $allowedBrands, $completeCode, $returnFullCode = false)
{
    $CODES_INFORMATION = [
        'goods' => [],
        'codes' => []
    ];

    $specifiedCode = [];
    $similarCodes = [];

    foreach ($existingGoods as $key => $code) {
        foreach ($code as $item) {
            if (in_array(trim($item['brandName']), $allowedBrands)) {
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

    if ($returnFullCode) {
        return $CODES_INFORMATION;
    }

    // Return unique codes instead of array keys
    return array_unique($CODES_INFORMATION['codes']);
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
    ]);
}
