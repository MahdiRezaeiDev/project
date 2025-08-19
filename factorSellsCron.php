<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './config/constants.php';
require_once './database/db_connect.php';

$now = date('H:i:s');
echo "\n\n*************** Factor sells Report job started ( $now ) ************************\n\n";

$sells_report = getAllReports();
$shortage_report = getShortageReport();

// Process sales report
foreach ($sells_report as $sell) {
    $sent = sendSellsReportMessage(
        $sell['header'],
        $sell['factor_type'],
        $sell['selected_goods'],
        $sell['low_quantity'],
        $sell['destination'],
        $sell['is_completed']
    );

    // if ($sent) {
    updateStatus('factor.sells_report', $sell['id']);
    // }
}

// Process shortage report
foreach ($shortage_report as $shortage) {
    $sent = sendPurchaseReportMessage($shortage['low_quantity']);
    if ($sent) {
        updateStatus('factor.shortage_report', $shortage['id']);
    }
}

$now = date('H:i:s');
echo "\n\n*************** Factor sells Report job finished ( $now ) ************************\n\n";

// Send purchase (shortage) report message
function sendPurchaseReportMessage($lowQuantity)
{
    $postData = [
        "sendMessage" => "PurchaseReport",
        "lowQuantity" => $lowQuantity,
    ];

    $url = "http://sells.yadak.center/";
    return postAndWait($url, $postData);
}

// Send sells report message
function sendSellsReportMessage($header, $factorType, $selectedGoods, $lowQuantity, $destination, $isComplete)
{
    $typeID = !$isComplete ? ($factorType == 0 ? 3516 : 3514) : 17815;

    $postData = [
        "sendMessage" => "sellsReportTest",
        "header" => $header,
        "topic_id" => $typeID,
        "selectedGoods" => $selectedGoods,
        "lowQuantity" => $lowQuantity,
    ];

    return postAndWait($destination, $postData);
}

// Perform HTTP POST and wait for response
function postAndWait($url, $postData)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Capture response
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // Max execution time
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);    // Connection timeout

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        error_log("cURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    return $httpCode === 200;
}

// Fetch pending sells reports
function getAllReports()
{
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM factor.sells_report WHERE status = 0");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch pending shortage reports
function getShortageReport()
{
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM factor.shortage_report WHERE status = 0");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mark report as processed
function updateStatus($table, $id)
{
    $stmt = PDO_CONNECTION->prepare("UPDATE $table SET status = 1 WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}
