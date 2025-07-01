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

foreach ($sells_report as $sell) {
    $sent = sendSellsReportMessage(
        $sell['header'],
        $sell['factor_type'],
        $sell['selected_goods'],
        $sell['low_quantity'],
        $sell['destination'],
        $sell['is_completed']
    );

    if ($sent) {
        updateStatus('factor.sells_report', $sell['id']);
    }
}

foreach ($shortage_report as $shortage) {
    $sent = sendPurchaseReportMessage($shortage['low_quantity']);
    if ($sent) {
        updateStatus('factor.shortage_report', $shortage['id']);
    }
}

$now = date('H:i:s');
echo "\n\n*************** Factor sells Report job finished ( $now ) ************************\n\n";


function sendPurchaseReportMessage($lowQuantity)
{
    $postData = http_build_query([
        "sendMessage" => "PurchaseReport",
        "lowQuantity" => $lowQuantity,
    ]);

    $url = "http://delivery.yadak.center/";
    return fireAndForget($url, $postData);
}

function sendSellsReportMessage($header, $factorType, $selectedGoods, $lowQuantity, $destination, $isComplete)
{
    $typeID = !$isComplete ? ($factorType == 0 ? 3516 : 3514) : 17815;

    $postData = http_build_query([
        "sendMessage" => "sellsReportTest",
        "header" => $header,
        "topic_id" => $typeID,
        "selectedGoods" => $selectedGoods,
        "lowQuantity" => $lowQuantity,
    ]);

    return fireAndForget($destination, $postData);
}

function fireAndForget($url, $postData)
{
    $parts = parse_url($url);
    $scheme = $parts['scheme'] ?? 'http';
    $host = $parts['host'] ?? '';
    $port = $scheme === 'https' ? 443 : 80;
    $path = $parts['path'] ?? '/';

    $fp = fsockopen(
        ($scheme === 'https' ? 'ssl://' : '') . $host,
        $port,
        $errno,
        $errstr,
        2
    );

    if (!$fp) {
        echo "Failed to connect: $errstr ($errno)\n";
        return false;
    }

    $out = "POST $path HTTP/1.1\r\n";
    $out .= "Host: $host\r\n";
    $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out .= "Content-Length: " . strlen($postData) . "\r\n";
    $out .= "Connection: Close\r\n\r\n";
    $out .= $postData;

    fwrite($fp, $out);
    fclose($fp); // Immediately close connection

    return true;
}

function getAllReports()
{
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM factor.sells_report WHERE status = 0");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getShortageReport()
{
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM factor.shortage_report WHERE status = 0");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateStatus($table, $id)
{
    $stmt = PDO_CONNECTION->prepare("UPDATE $table SET status = 1 WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}
