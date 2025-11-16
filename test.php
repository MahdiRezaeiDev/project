<?php
require_once './config/constants.php';
require_once './database/db_connect.php';

// -----------------------------------------------------------
// API CONFIG
// -----------------------------------------------------------
$apiUrl      = "http://wsrest.hoseinparts.ir/ShowList";
$pageLength  = 50;
$startRecord = 0;
$allProducts = [];

$authToken = "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWQiOiJKV1RTZXJ2aWNlQWNjZXNzVG9rZW4iLCJqdGkiOiI1M2M0YWFmZi01MTk2LTRlMGUtYWJmMC04ZGViOWY3ZWM0YjMiLCJpYXQiOiI5LzIyLzIwMjUgNjowNzozNiBQTSIsIlVzZXJOYW1lIjoid3N1c2VyIiwiZXhwIjoxNzU4NTY1MDU2LCJpc3MiOiJKV1RBdXRoZW50aWNhdGlvblNlcnZlciIsImF1ZCI6IkpXVFNlcnZpY2VDbGllbnQifQ.bWVVrwrVvhFJjWKASVUz83xPj2EnlJDg9z_bDz-JNW8";

// -----------------------------------------------------------
// STEP 1 — Fetch All Products (Paginated)
// -----------------------------------------------------------
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
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            "accept: */*",
            "Authorization: $authToken",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES)
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        echo "❌ cURL Error: $curlErr\n";
        break;
    }

    $json = json_decode($response, true);
    if (!$json || !isset($json['result'])) {
        echo "❌ Invalid API response.\n";
        break;
    }

    $resultData = json_decode($json['result'], true);
    if (!isset($resultData['Data']) || empty($resultData['Data'])) {
        break;
    }

    // Collect all products (DO NOT REMOVE DUPLICATES)
    $allProducts = array_merge($allProducts, $resultData['Data']);

    if (count($resultData['Data']) < $pageLength) {
        break; // no more pages
    }

    $startRecord += $pageLength;
}

$totalProducts = count($allProducts);

if ($totalProducts == 0) {
    echo "⚠ No products received from API.\n";
    exit;
}

// -----------------------------------------------------------
// STEP 2 — Truncate Table
// -----------------------------------------------------------
PDO_CONNECTION->exec("TRUNCATE TABLE hoseinparts_products");

// -----------------------------------------------------------
// STEP 3 — Batch Insert
// -----------------------------------------------------------
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

$chunkSize = 1000;
$chunks = array_chunk($allProducts, $chunkSize);

PDO_CONNECTION->beginTransaction();

foreach ($chunks as $chunk) {

    $placeholders = [];
    $values = [];

    foreach ($chunk as $item) {

        // skip products without OfferPrice
        if (!isset($item['OfferPrice']) || (int)$item['OfferPrice'] === 0) {
            continue;
        }

        $placeholders[] = '(' . rtrim(str_repeat('?,', count($columns)), ',') . ')';

        $values[] = $item['ID'] ?? 0;
        $values[] = trim($item['PropertyCode'] ?? '');
        $values[] = $item['SimilarCode'] ?? null;
        $values[] = $item['Brand'] ?? null;
        $values[] = $item['OfferPrice'] ?? 0;
        $values[] = $item['onlineprice'] ?? 0;
        $values[] = $item['InstantOfferPrice'] ?? 0;
        $values[] = $item['Stock'] ?? 0;
        $values[] = $item['LastSalePrice'] ?? 0;
        $values[] = date('Y-m-d H:i:s');
    }

    if (empty($placeholders)) continue;

    $sql = "INSERT INTO hoseinparts_products (" . implode(',', $columns) . ")
            VALUES " . implode(',', $placeholders);

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute($values);
}

PDO_CONNECTION->commit();

// -----------------------------------------------------------
// DONE
// -----------------------------------------------------------
echo "✅ Successfully inserted $totalProducts products (including duplicates)\n";
