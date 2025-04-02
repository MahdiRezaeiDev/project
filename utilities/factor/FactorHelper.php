<?php

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

function getItemPrice($givenPrice)
{
    if ($givenPrice != 'موجود نیست') {
        $pricesParts = array_map('trim', explode('/', $givenPrice));
        foreach ($pricesParts as $part) {
            $spaceIndex = strpos($part, ' ');
            if ($spaceIndex !== false) {
                $priceSubStr = substr($part, 0, $spaceIndex);
                $brandSubStr = substr($part, $spaceIndex + 1); // Skip the space
                $brand = trim(explode('(', $brandSubStr)[0]);
                $complexBrands = explode(' ', $brand)[0];
                if ($complexBrands == 'GEN' || $complexBrands == 'MOB') {
                    return $priceSubStr * 10000;
                }
                if (count($pricesParts) == 1) {
                    return is_numeric($priceSubStr) ? $priceSubStr * 10000 : 0;
                }
            } else {
                return is_numeric($part) ? $part * 10000 : 0;
            }
        }
    }
    return 0;
}

function createBill($billInfo)
{
    try {
        $sql = "INSERT INTO factor.bill 
                (customer_id, bill_number, quantity, discount, tax, withdraw, total, bill_date, user_id, status, partner) 
                VALUES (:customer_id, :bill_number, :quantity, :discount, :tax, :withdraw, :total, :bill_date, :user_id, :status, :partner)";

        $status = 0;
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':customer_id', $billInfo['customer_id'], PDO::PARAM_INT);
        $stmt->bindParam(':bill_number', $billInfo['bill_number'], PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $billInfo['quantity'], PDO::PARAM_INT);
        $stmt->bindParam(':discount', $billInfo['discount'], PDO::PARAM_STR);
        $stmt->bindParam(':tax', $billInfo['tax'], PDO::PARAM_STR);
        $stmt->bindParam(':withdraw', $billInfo['withdraw'], PDO::PARAM_STR);
        $stmt->bindParam(':total', $billInfo['total'], PDO::PARAM_STR);
        $stmt->bindParam(':bill_date', $billInfo['date'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':partner', $billInfo['partner'], PDO::PARAM_INT);

        $stmt->execute();

        $lastInsertedId = PDO_CONNECTION->lastInsertId();
        $stmt->closeCursor();

        return $lastInsertedId;
    } catch (PDOException $e) {
        return false;
    }
}

function createBillItemsTable($billId, $billItems)
{
    try {
        $sql = "INSERT INTO factor.bill_details (bill_id, billDetails) VALUES (?, ?)";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->execute([$billId, $billItems]);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        // Handle exception here, if needed
    }
}

function convertPersianToEnglish($string)
{
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persianDigits, $englishDigits, $string);
}

function getFinalPriceBrand($price)
{
    $brandsPrice = [];
    $addedBrands = [];

    if (empty($price) || $price == 'موجود نیست') {
        return $brandsPrice;
    }

    $pricesParts = explode('/', $price);
    $pricesParts = array_map('trim', $pricesParts);
    $pricesParts = array_map('strtoupper', $pricesParts);

    foreach ($pricesParts as $part) {
        $spaceIndex = strpos($part, ' ');
        if ($spaceIndex !== false) {
            $priceSubStr = substr($part, 0, $spaceIndex);
            $brandSubStr = substr($part, $spaceIndex + 1); // Skip the space
            $brand = trim(explode('(', $brandSubStr)[0]);
            $complexBrands = explode(' ', $brand)[0];

            if (!in_array($brand, $addedBrands) && !empty($brand)) {
                $addedBrands[] = $complexBrands;
                if ($complexBrands == 'MOB' || $complexBrands == 'GEN') {
                    $brandsPrice['اصلی'] = $priceSubStr;
                    continue;
                }
                $brandsPrice[$complexBrands] = $priceSubStr;
            }
        } else {
            $brandsPrice['اصلی'] = $part;
        }
    }
    return $brandsPrice;
}
