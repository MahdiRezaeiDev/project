<?php
require_once  './config/constants.php';
require_once './database/db_connect.php'; // Make sure PDO_CONNECTION is defined here

$apiUrl = "http://wsrest.hoseinparts.ir/ShowList";
$pageLength = 50;
$startRecord = 0;
$hasMore = true;

while ($hasMore) {
    $payload = [
        'menuID'        => 87,
        'userName'      => 'string',
        'pagelenth'     => $pageLength,
        'order_column'  => 1,
        'order_dir'     => 'string',
        'startRecord'   => $startRecord,
        'userCondition' => '$APP$Stock > 0'
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: */*",
        "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWQiOiJKV1RTZXJ2aWNlQWNjZXNzVG9rZW4iLCJqdGkiOiI1M2M0YWFmZi01MTk2LTRlMGUtYWJmMC04ZGViOWY3ZWM0YjMiLCJpYXQiOiI5LzIyLzIwMjUgNjowNzozNiBQTSIsIlVzZXJOYW1lIjoid3N1c2VyIiwiZXhwIjoxNzU4NTY1MDU2LCJpc3MiOiJKV1RBdXRoZW50aWNhdGlvblNlcnZlciIsImF1ZCI6IkpXVFNlcnZpY2VDbGllbnQifQ.bWVVrwrVvhFJjWKASVUz83xPj2EnlJDg9z_bDz-JNW8",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        break;
    }
    curl_close($ch);

    $data = json_decode($response, true);

    if (!isset($data['result']) || empty($data['result'])) {
        $hasMore = false;
        break;
    }

    // Decode the nested JSON string inside 'result'
    $resultData = json_decode($data['result'], true);

    if (!isset($resultData['Data']) || empty($resultData['Data'])) {
        $hasMore = false;
        break;
    }

    // Prepare insert/update statement
    $stmt = PDO_CONNECTION->prepare("
        INSERT INTO hoseinparts_products 
        (api_id, property_code, similar_code, brand, offer_price, online_price, instant_offer_price, stock, last_sale_price) 
        VALUES 
        (:api_id, :property_code, :similar_code, :brand, :offer_price, :online_price, :instant_offer_price, :stock, :last_sale_price)
        ON DUPLICATE KEY UPDATE
            api_id = VALUES(api_id),
            similar_code = VALUES(similar_code),
            brand = VALUES(brand),
            offer_price = VALUES(offer_price),
            online_price = VALUES(online_price),
            instant_offer_price = VALUES(instant_offer_price),
            stock = VALUES(stock),
            last_sale_price = VALUES(last_sale_price),
            last_update = CURRENT_TIMESTAMP
    ");

    // Loop through each product
    foreach ($resultData['Data'] as $item) {
        $stmt->execute([
            ':api_id'              => $item['ID'] ?? 0,
            ':property_code'       => $item['PropertyCode'] ?? '',
            ':similar_code'        => $item['SimilarCode'] ?? null,
            ':brand'               => $item['Brand'] ?? null,
            ':offer_price'         => $item['OfferPrice'] ?? 0,
            ':online_price'        => $item['onlineprice'] ?? 0,
            ':instant_offer_price' => $item['InstantOfferPrice'] ?? 0,
            ':stock'               => $item['Stock'] ?? 0,
            ':last_sale_price'     => $item['LastSalePrice'] ?? 0
        ]);
    }

    $startRecord += $pageLength;

    if (count($resultData['Data']) < $pageLength) {
        $hasMore = false;
    }
}

echo "All products inserted/updated successfully.\n";
