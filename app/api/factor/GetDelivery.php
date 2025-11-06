<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../Controller/factor/DeliveriesController.php';

if (!isset($_POST['billNumber'])) {
echo json_encode(['status' => 'error', 'message' => 'billNumber not provided']);
exit;
}

$billNumber = $_POST['billNumber'];
$result = getDeliveryByBillNumber($billNumber);
$factorinformation = getFactorInfoView($billNumber);
$peyment = getPaymentsAmountByBillNumber($billNumber);

$mergedData = [];

// delivery
if (!empty($result)) {
    $mergedData = array_merge($mergedData, (array)$result);
}

// factor
if (!empty($factorinformation)) {
    $mergedData = array_merge($mergedData, (array)$factorinformation);
}

// payments
if (!empty($peyment)) {
    $mergedData = array_merge($mergedData, (array)$peyment);
}


$response = [];

// delivery
$response[] = [
    'status' => !empty($result) ? 'success' : 'error',
    'type' => 'delivery',
    'data' => !empty($result) ? $result : null,
    'message' => !empty($result) ? 'Delivery data found' : 'No delivery found'
];

// factor
$response[] = [
    'status' => !empty($factorinformation) ? 'success' : 'error',
    'type' => 'factor',
    'data' => !empty($factorinformation) ? $factorinformation : null,
    'message' => !empty($factorinformation) ? 'Factor data found' : 'No factor found'
];

// payments
$response[] = [
    'status' => !empty($peyment) ? 'success' : 'error',
    'type' => 'payments',
    'data' => !empty($peyment) ? $peyment : null,
    'message' => !empty($peyment) ? 'Payments data found' : 'No payments found'
];


// merged
$response[] = [
    'status' => !empty($mergedData) ? 'success' : 'error',
    'type' => 'merged',
    'data' => !empty($mergedData) ? $mergedData : null,
    'message' => !empty($mergedData)
        ? 'Combined data from all available sources'
        : 'No data found in any source'
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
