<?php
$apiUrl = "http://wsrest.hoseinparts.ir/ShowList";

$payload = [
    'menuID'        => 87,
    'userName'      => 'string',
    'pagelenth'     => 10,
    'order_column'  => 1,
    'order_dir'     => 'string',
    'startRecord'   => 0,
    'userCondition' => '$APP$PropertyCode=\'546512L601\''
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
}

curl_close($ch);

echo "HTTP Code: $httpCode\n\n";
print_r(json_decode($response, true));
