<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/constants.php';
require_once '../database/db_connect.php';

$now = date('H:i:s');
echo "\n\n*************** Bill cron jobs started ( $now ) ************************\n\n";

$queuedJobs = getJobs();
foreach ($queuedJobs as $job) {
    $customer = json_decode($job['customer_info'], true);
    $factor = json_decode($job['factor_info'], true);
    $factorItems = json_decode($job['factor_items'], true);
    $factorNumber = $job['factor_number'];
    $user_id = $job['user_id'];

    // Send data to the external service and wait for response
    $success = sendData($customer, $factor, $factorItems, $factorNumber, $user_id);

    // Mark the job as completed regardless of success
    markAsCompleted($job['id']);

    if ($success) {
        markSMSAsSent($job['id']);
    }
}

$now = date('H:i:s');
echo "\n\n*************** Bill cron jobs finished ( $now ) ************************\n\n";

// ─────────────────────────────────────────────────────────────────────────────

function markAsCompleted($jobId)
{
    $sql = "UPDATE factor.bill_jobs SET status = 1 WHERE id = :jobId";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
    return $stmt->execute();
}

function markSMSAsSent($jobId)
{
    $sql = "UPDATE factor.bill_jobs SET is_sms_sent = 1 WHERE id = :jobId";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
    return $stmt->execute();
}

function sendData($customer, $factor, $factorItems, $factorNumber, $user_id)
{
    $postData = [
        "GenerateCompleteFactor" => "GenerateCompleteFactor",
        "customer" => json_encode($customer),
        "factor" => json_encode($factor),
        "factorItems" => json_encode($factorItems),
        "user_id" => $user_id,
        "factorNumber" => $factorNumber
    ];

    $url = "http://sells.yadak.shop/";
    return postAndWait($url, $postData);
}

function postAndWait($url, $postData)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Wait for response
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout (seconds)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Connection timeout

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

function getJobs()
{
    $sql = "SELECT * FROM factor.bill_jobs WHERE status = 0 OR is_sms_sent = 0 ORDER BY created_at ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
