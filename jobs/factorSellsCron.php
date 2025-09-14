<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './config/constants.php';
require_once './database/db_connect.php';

define('REPORT_ENDPOINT_URL', 'http://sells.yadak.center');

$now = date('H:i:s');
echo "\n\n*************** Factor sells Report job started ($now) ************************\n\n";

// Fetch pending reports
$sells_report    = getAllReports();
$shortage_report = getShortageReport();

// --------------------
// Process sells reports
// --------------------
foreach ($sells_report as $sell) {

    $postData = [
        "sendMessage"   => "sellsReportTest",
        "header"        => $sell['header'],
        "topic_id"      => $sell['factor_type'] == 0 ? 3516 : 3514,
        "selectedGoods" => $sell['selected_goods'],
        "lowQuantity"   => $sell['low_quantity'],
    ];

    $response = postToEndpoint($sell['destination'], $postData);

    if ($response['success']) {
        updateStatus('factor.sells_report', $sell['id']);
    } else {
        updateError('factor.sells_report', $sell['id'], $response['error'] ?? 'Unknown error');
    }

    updateTries('factor.sells_report', $sell['id']);
}

// -----------------------
// Process shortage reports
// -----------------------
foreach ($shortage_report as $shortage) {

    $postData = [
        "sendMessage" => "PurchaseReport",
        "lowQuantity" => $shortage['low_quantity'],
    ];

    $response = postToEndpoint(REPORT_ENDPOINT_URL, $postData);

    if ($response['success']) {
        updateStatus('factor.shortage_report', $shortage['id']);
    } else {
        updateError('factor.shortage_report', $shortage['id'], $response['error'] ?? 'Unknown error');
    }

    updateTries('factor.shortage_report', $shortage['id']);
}

$now = date('H:i:s');
echo "\n\n*************** Factor sells Report job finished ($now) ************************\n\n";

// ============================
// Helper functions
// ============================

function postToEndpoint($url, $postData)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['success' => false, 'error' => 'cURL error: ' . curl_error($ch)];
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "HTTP error: $httpCode"];
    }

    $json = json_decode($response, true);

    if (!$json || !isset($json['success'])) {
        return ['success' => false, 'error' => "Invalid response: $response"];
    }

    return $json;
}

// ---------------------------
// Database helpers
// ---------------------------

function getAllReports()
{
    $sql = "
        SELECT * 
        FROM factor.sells_report 
        WHERE status = 0 
          AND (tries < 2 OR error_message IS NOT NULL)
    ";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getShortageReport()
{
    $sql = "
        SELECT * 
        FROM factor.shortage_report 
        WHERE status = 0 
          AND (tries < 2 OR error_message IS NOT NULL)
    ";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateStatus($table, $id)
{
    $stmt = PDO_CONNECTION->prepare("UPDATE $table SET status = 1, error_message = NULL WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}


function updateTries($table, $id)
{
    $stmt = PDO_CONNECTION->prepare("UPDATE $table SET tries = tries + 1 WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function updateError($table, $id, $error)
{
    $stmt = PDO_CONNECTION->prepare("UPDATE $table SET error_message = :error WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':error', $error, PDO::PARAM_STR);
    $stmt->execute();
}
