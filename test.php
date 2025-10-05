<?php
require_once './config/constants.php';
require_once './database/db_connect.php';

$apiUrl = "http://wsrest.hoseinparts.ir/ShowList";
$pageLength = 50;
$startRecord = 0;
$allProducts = [];

// Step 1: Fetch all products from the API
while (true) {
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
    $resultData = json_decode($data['result'] ?? '', true);

    if (empty($resultData['Data'])) {
        break;
    }

    $allProducts = array_merge($allProducts, $resultData['Data']);
    if (count($resultData['Data']) < $pageLength) {
        break;
    }

    $startRecord += $pageLength;
}

// Step 1b: Remove duplicates strictly by PropertyCode
$uniqueProducts = [];
foreach ($allProducts as $product) {
    $code = isset($product['PropertyCode']) ? trim(strtolower($product['PropertyCode'])) : '';
    if ($code) {
        $uniqueProducts[$code] = $product; // last occurrence wins
    }
}
$allProducts = array_values($uniqueProducts); // reset keys

if (empty($allProducts)) {
    echo "No products found.\n";
    exit;
}

// Step 2: Truncate table
PDO_CONNECTION->exec("TRUNCATE TABLE hoseinparts_products");

// Step 3: Prepare for batch insert
$columns = [
    'api_id',
    'property_code',
    'similar_code',
    'brand',
    'offer_price',
    'online_price',
    'instant_offer_price',
    'stock',
    'last_sale_price',
    'last_update'
];

$chunkSize = 1000; // insert 1000 rows per batch
$totalProducts = count($allProducts);
$chunks = array_chunk($allProducts, $chunkSize);

PDO_CONNECTION->beginTransaction();
foreach ($chunks as $chunk) {
    $placeholders = [];
    $values = [];

    foreach ($chunk as $item) {
        $placeholders[] = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $values[] = $item['ID'] ?? 0;
        $values[] = $item['PropertyCode'] ?? '';
        $values[] = $item['SimilarCode'] ?? null;
        $values[] = $item['Brand'] ?? null;
        $values[] = $item['OfferPrice'] ?? 0;
        $values[] = $item['onlineprice'] ?? 0;
        $values[] = $item['InstantOfferPrice'] ?? 0;
        $values[] = $item['Stock'] ?? 0;
        $values[] = $item['LastSalePrice'] ?? 0;
        $values[] = date('Y-m-d H:i:s');
    }

    $insertSql = "INSERT INTO hoseinparts_products (" . implode(',', $columns) . ") VALUES " . implode(',', $placeholders);
    $stmt = PDO_CONNECTION->prepare($insertSql);
    $stmt->execute($values);
}
PDO_CONNECTION->commit();

echo "All products refreshed successfully. Total unique products: " . $totalProducts . "\n";
