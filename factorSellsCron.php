<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './config/constants.php';
require_once './database/db_connect.php';

try {
    echo " -------------------- STARTED " . date("H:m:i");
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
    echo " -------------------- DONE " . date("H:m:i");
} catch (\Throwable $th) {
    throw $th;
}

function sendPurchaseReportMessage($lowQuantity)
{
    $postData = [
        "sendMessage" => "PurchaseReport",
        "lowQuantity" => $lowQuantity,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://delivery.yadak.center/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result !== false;
}

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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $destination);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result !== false;
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
