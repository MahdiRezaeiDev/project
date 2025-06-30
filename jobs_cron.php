<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './config/constants.php';
require_once './database/db_connect.php';

echo "\n\n*************** Cron job started ( $now ) ************************\n\n";
$queuedJobs = getJobs();

foreach ($queuedJobs as $job) {
    $customer = json_decode($job['customer_info'], true);
    $factor = json_decode($job['factor_info'], true);
    $factorItems = json_decode($job['factor_items'], true);
    $factorNumber = $job['factor_number'];
    $user_id = $job['user_id'];

    // Send data to the external service
    $response = sendData($customer, $factor, $factorItems, $factorNumber, $user_id);
    if ($response === 'success') {
        // Update job status to completed
        markAsCompleted($job['id']);
        // Optionally, mark SMS as sent
        markSMSAsSent($job['id']);
    } else {
        markAsCompleted($job['id']);
    }
}

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
    $postData = array(
        "GenerateCompleteFactor" => "GenerateCompleteFactor",
        "customer" => json_encode($customer),
        "factor" => json_encode($factor),
        "factorItems" => json_encode($factorItems),
        "user_id" => $user_id,
        "factorNumber" => $factorNumber
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://sells.yadak.shop/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return 'error';
    }

    curl_close($ch);

    // Decode response and check status
    $decoded = json_decode($response, true);
    if (is_array($decoded) && isset($decoded['status']) && $decoded['status'] == '1') {
        return 'success';
    } else {
        error_log("Unexpected response: " . $response);
        return 'error';
    }
}

function getJobs()
{
    $sql = "SELECT * FROM factor.bill_jobs WHERE status = 0 ORDER BY created_at ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
