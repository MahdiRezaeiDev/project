<?php

/**
 * @param Connection to the database
 * @return array of rates selected to be used in the goods report table
 */
function getSelectedRates()
{

    $sql = "SELECT amount, status FROM shop.rates WHERE selected = '1' ORDER BY amount ASC";
    $result = PDO_CONNECTION->query($sql);
    $rates = $result->fetchAll(PDO::FETCH_ASSOC);
    return $rates;
}

/**
 * @param Connection to the database
 * @param int $id is the id of the good to check if it has a relationship
 * @return int if the good has a relationship return the id of the relationship
 */
function isInRelation($id)
{
    $sql = "SELECT pattern_id FROM shop.similars WHERE nisha_id = :nisha_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':nisha_id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result['pattern_id'];
    }

    return false;
}

/**
 * @param Connection to the database
 * @param int $id is the id of specified good
 * @return int $relation_exist
 * @return array of information about the good
 */
function info($relation_exist = null)
{
    $info = false;
    $cars = [];

    if ($relation_exist) {
        // Get pattern info
        $sql = "SELECT * FROM shop.patterns WHERE id = :relation_exist";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':relation_exist', $relation_exist, PDO::PARAM_INT);
        $stmt->execute();
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($info && $info['status_id'] !== 0) {
            $sql = "SELECT patterns.*, status.name AS status_name 
                    FROM shop.patterns 
                    INNER JOIN shop.status ON status.id = patterns.status_id 
                    WHERE patterns.id = :relation_exist";
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':relation_exist', $relation_exist, PDO::PARAM_INT);
            $stmt->execute();
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Get cars info
        $sql = "SELECT cars.name 
                FROM shop.patterncars 
                INNER JOIN shop.cars ON cars.id = patterncars.car_id 
                WHERE patterncars.pattern_id = :relation_exist";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':relation_exist', $relation_exist, PDO::PARAM_INT);
        $stmt->execute();
        $cars = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return $info ? ['relationInfo' => $info, 'cars' => $cars] : false;
}

function relations($id, $condition)
{
    $relations = [];
    $limit_id = '';
    $excludedSellers = [
        'کاربر دستوری',
        'کاربر دستوری معیوب',
        'کاربر دستوری مفقود'
    ];

    if ($condition) {
        $sql = "SELECT nisha.* 
                FROM yadakshop.nisha 
                INNER JOIN shop.similars ON similars.nisha_id = nisha.id 
                WHERE similars.pattern_id = :id";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $limit_id = $id . '-r';
    } else {
        $sql = "SELECT * FROM yadakshop.nisha WHERE partnumber = :id";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $limit_id = end($relations)['id'] . '-s';
    }

    $existing = [];
    $stockInfo = [];
    $sortedGoods = [];
    $unique_goods = [];

    foreach ($relations as $relation) {
        if (!array_key_exists($relation['partnumber'], $unique_goods)) {
            $unique_goods[$relation['partnumber']] = [$relation['id']];
        } else {
            $unique_goods[$relation['partnumber']][] = $relation['id'];
        }
        $sortedGoods[$relation['partnumber']] = $relation;
    }

    foreach ($unique_goods as $key => $relation_ids) {
        $data = exist($relation_ids);
        $existing[$key] = $data['brands_info'];
        $stockInfo[$key] = $data['stockInfo'];
    }

    $existingQuantity = 0;
    foreach ($stockInfo as $key => $stock) {
        foreach ($stock as $item) {
            if (!in_array($item['seller_name'], $excludedSellers)) {
                $existingQuantity += intval($item['remaining_qty']);
            }
        }
    }

    arsort($existing);
    $sorted = [];

    $max = 0;
    foreach ($existing as $key => $value) {
        $sorted[$key] = getMax($value);
        $max += $sorted[$key];
    }

    arsort($sorted);

    return [
        'goods' => $sortedGoods,
        'existing' => $existing,
        'sorted' => $sorted,
        'stockInfo' => $stockInfo,
        'limit_alert' => $limit_id,
        'existingQuantity' => $existingQuantity
    ];
}

function givenPrice($codes, $relation_exist = null)
{
    // Filter and lowercase the codes
    $codes = array_map('strtolower', array_filter($codes));
    $ordered_price = [];

    if ($relation_exist) {
        // Query to get the price and created_at for the given pattern
        $sql = "SELECT price, created_at FROM shop.patterns WHERE id = :relation_exist";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':relation_exist', $relation_exist, PDO::PARAM_INT);
        $stmt->execute();
        $ordered_price = $stmt->fetch(PDO::FETCH_ASSOC);
        $ordered_price['ordered'] = true;
    }

    // Query to get prices based on the provided codes
    $placeholders = implode(',', array_fill(0, count($codes), '?'));
    $sql = "SELECT prices.id, prices.price, prices.partnumber, customer.name, customer.id AS customerID, 
                   customer.family, users.id AS userID, prices.created_at
            FROM shop.prices
            INNER JOIN callcenter.customer ON customer.id = prices.customer_id
            INNER JOIN yadakshop.users ON users.id = prices.user_id
            WHERE partnumber IN ($placeholders)
            ORDER BY created_at DESC LIMIT 7";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute($codes);
    $givenPrices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure givenPrices is not empty
    $givenPrices = array_filter($givenPrices);

    // Prepare final data array
    $final_data = $relation_exist ? array_merge([$ordered_price], $givenPrices) : $givenPrices;

    // Sort final data by created_at if relation_exist is true
    if ($relation_exist) {
        usort($final_data, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }

    // Filter out items without price
    $filtered_data = array_filter($final_data, function ($item) {
        return isset($item['price']) && $item['price'] !== '';
    });
    return $filtered_data;
}

function out($id)
{
    global $stock;

    // Initialize result
    $result = 0;

    // Prepare the SQL query
    $out_sql = "SELECT SUM(qty) AS total FROM {$stock}.exitrecord WHERE qtyid = :id";
    $stmt = PDO_CONNECTION->prepare($out_sql);

    // Bind the parameter
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch all rows
    $out = $stmt->fetch(PDO::FETCH_ASSOC);

    return $out['total'];
}

function stockInfo($id, $brand)
{
    global $stock;

    // Retrieve the brand ID
    $brand_id = null;
    $brand_sql = "SELECT id FROM yadakshop.brand WHERE brand.name = :brand";
    $stmt_brand = PDO_CONNECTION->prepare($brand_sql);
    $stmt_brand->bindParam(':brand', $brand, PDO::PARAM_STR);
    $stmt_brand->execute();
    $brand_row = $stmt_brand->fetch(PDO::FETCH_ASSOC);
    if ($brand_row) {
        $brand_id = $brand_row['id'];
    }

    // Fetch stock information
    $qtybank_sql = "SELECT qtybank.id, qtybank.qty, seller.name
                    FROM {$stock}.qtybank 
                    INNER JOIN yadakshop.seller ON qtybank.seller = seller.id 
                    WHERE codeid = :id AND brand = :brand_id";
    $stmt_qtybank = PDO_CONNECTION->prepare($qtybank_sql);
    $stmt_qtybank->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_qtybank->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
    $stmt_qtybank->execute();
    $result = $stmt_qtybank->fetchAll(PDO::FETCH_ASSOC);

    // Initialize arrays to store results
    $existing_record = [];
    $customers = [];

    // Process each row of the result
    foreach ($result as $item) {
        // Retrieve exit quantity
        $out_data = out($item['id']);
        $out = $out_data ? (int)$out_data : 0;
        // Calculate available quantity
        $item['qty'] = (int)$item['qty'] - $out;
        // Add the item to existing record
        $existing_record[] = $item;
        // Add customer to list
        $customers[] = $item['name'];
    }

    // Get unique customers
    $customers = array_unique($customers);

    // Calculate total quantity for each customer
    $final_result = [];
    foreach ($customers as $customer) {
        $total = 0;
        foreach ($existing_record as $record) {
            if ($customer === $record['name']) {
                $total += $record['qty'];
            }
        }
        $final_result[$customer] = $total;
    }

    return $final_result;
}

function exist($ids)
{
    global $stock;

    // Prepare the base SQL query with LEFT JOIN to exitrecord and necessary calculations
    $base_sql = "SELECT qtybank.id AS quantityId, codeid AS goodId, brand.name AS brandName, qtybank.qty AS quantity,
                    create_time AS invoice_date, seller.name AS seller_name, brand.id AS brandId, stock.id AS stockId,
                    qtybank.des AS purchase_Description, qtybank.pos1, qtybank.pos2,
                    stock.name AS stockName, IFNULL(SUM(exitrecord.qty), 0) AS total_sold,
                    qtybank.qty - IFNULL(SUM(exitrecord.qty), 0) AS remaining_qty
                FROM {$stock}.qtybank
                INNER JOIN yadakshop.brand ON brand.id = qtybank.brand
                LEFT JOIN yadakshop.stock ON stock.id = qtybank.stock_id
                LEFT JOIN yadakshop.seller ON seller.id = qtybank.seller
                LEFT JOIN $stock.exitrecord ON qtybank.id = exitrecord.qtyid";

    // Append the condition based on the number of IDs
    if (count($ids) == 1) {
        $data_sql = $base_sql . " WHERE codeid = :id
        AND seller.name NOT IN ('کاربر دستوری', 'کاربر دستوری معیوب', 'کاربر دستوری مفقود')
                                  GROUP BY qtybank.id, codeid, brand.name, qtybank.qty, create_time, seller.name, brand.id
                                  HAVING remaining_qty > 0";

        // Prepare and execute the SQL statement
        $stmt = PDO_CONNECTION->prepare($data_sql);
        $stmt->bindParam(':id', $ids[0], PDO::PARAM_INT);
    } else {
        // Prepare placeholders for multiple IDs
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $data_sql = $base_sql . " WHERE codeid IN ($placeholders)
        AND seller.name NOT IN ('کاربر دستوری', 'کاربر دستوری معیوب', 'کاربر دستوری مفقود')
                                  GROUP BY qtybank.id, codeid, brand.name, qtybank.qty, create_time, seller.name, brand.id
                                  HAVING remaining_qty > 0";

        // Prepare and execute the SQL statement
        $stmt = PDO_CONNECTION->prepare($data_sql);
        // Bind each ID to the corresponding placeholder
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }
    }

    try {
        // Execute the statement
        $stmt->execute();
        $incoming = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter and process the incoming data
        $existingGoods = ($incoming);

        // Calculate brand totals
        $brands_info = [];
        foreach ($existingGoods as $item) {
            $brandName = $item['brandName'];
            $remainingQuantity = $item['remaining_qty'];
            if (isset($brands_info[$brandName])) {
                $brands_info[$brandName] += $remainingQuantity;
            } else {
                $brands_info[$brandName] = $remainingQuantity;
            }
        }

        // Sort brands by total quantity
        arsort($brands_info);

        return ['stockInfo' => $existingGoods, 'brands_info' => $brands_info];
    } catch (PDOException $e) {
        // Handle any errors
        echo 'Error: ' . $e->getMessage();
        return [];
    }
}

function getMax($array)
{
    $max = 0;
    foreach ($array as $k => $v) {
        $max = $max < $v ? $v : $max;
    }
    return $max;
}

function sortArrayByNumericPropertyDescending($array, $property)
{
    usort($array, function ($a, $b) use ($property) {
        return $b->$property - $a->$property;
    });
    return $array;
}

function inventorySpecification($id, $type)
{
    // Define the SQL query based on the type
    $sql = '';
    switch ($type) {
        case 'r':
            $sql = "SELECT original, fake FROM shop.good_limit_inventory WHERE pattern_id = :id";
            break;
        case 's':
            $sql = "SELECT original, fake FROM shop.good_limit_inventory WHERE nisha_id = :id";
            break;
    }

    // Prepare and execute the SQL statement
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the result
    $limit = $stmt->fetch(PDO::FETCH_ASSOC);
    $yadakLimit = !empty($limit) ? $limit : false;

    return $yadakLimit;
}

function overallSpecification($id, $type)
{
    // Define the SQL query based on the type
    $sql = '';
    switch ($type) {
        case 'r':
            $sql = "SELECT original AS original_all, fake AS fake_all FROM shop.good_limit_all WHERE pattern_id = :id";
            break;
        case 's':
            $sql = "SELECT original AS original_all, fake AS fake_all FROM shop.good_limit_all WHERE nisha_id = :id";
            break;
    }

    // Prepare and execute the SQL statement
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the result
    $limit_all = $stmt->fetch(PDO::FETCH_ASSOC);
    $allLimit = !empty($limit_all) ? $limit_all : false;

    return $allLimit;
}

function is_hussain_enabled()
{
    // Fetch the status from hussain_api (assuming only one row)
    $stmt = PDO_CONNECTION->prepare("SELECT status FROM hussain_api LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    $status = intval($config['status'] ?? 0);

    return $status === 1;
}

function get_hussain_parts($partNumber)
{
    // Fetch dynamic percentages from hussain_api (assuming only one row)
    $stmt2 = PDO_CONNECTION->prepare("SELECT percent, benefit, status FROM hussain_api LIMIT 1");
    $stmt2->execute();
    $config = $stmt2->fetch(PDO::FETCH_ASSOC);

    $percent = (float)($config['percent'] ?? 30); // default 30%
    $benefit = (float)($config['benefit'] ?? 3);  // default 3%

    // Fetch the part from hoseinparts_products
    $stmt = PDO_CONNECTION->prepare("
        SELECT 
            property_code, 
            brand, 
            stock, 
            offer_price, 
            id, 
            similar_code,
            online_price,
            instant_offer_price,
            last_sale_price,
            last_update
        FROM hoseinparts_products 
        WHERE property_code = :partnumber 
        LIMIT 1
    ");
    $stmt->bindParam(':partnumber', $partNumber);
    $stmt->execute();
    $part = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$part) {
        return false; // part not found
    }

    // Convert numeric fields safely
    $offerPrice          = (float)($part['offer_price'] ?? 0);
    $instant_offer_price = (float)($part['instant_offer_price'] ?? 0);
    $last_sale_price     = (float)($part['last_sale_price'] ?? 0);
    $online_price        = (float)($part['online_price'] ?? 0);

    // Step 1: add dynamic percent to online price
    $online_priceWithPercent = $online_price * (1 + $percent / 100);

    // Step 2: find the max among all
    $maxPrice = max($offerPrice, $instant_offer_price, $last_sale_price, $online_priceWithPercent);

    // Step 3: add dynamic benefit
    $yadakPrice = $maxPrice * (1 + $benefit / 100);

    $part['yadakprice'] = round($yadakPrice, 2); // rounded to 2 decimals

    return $part;
}

function lastActiveRate()
{
    $stmt = PDO_CONNECTION->query("SELECT * FROM shop.dollarrate WHERE status = 1 ORDER BY id DESC LIMIT 1");
    $lastRate = $stmt->fetch(PDO::FETCH_ASSOC);

    return $lastRate['created_at'];
}
