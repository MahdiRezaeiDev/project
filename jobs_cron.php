<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './config/constants.php';
require_once './database/db_connect.php';

$now = date('H:i:s');
echo "\n\n*************** Bill cron jobs started ( $now ) ************************\n\n";

$queuedJobs = getJobs();
foreach ($queuedJobs as $job) {
    $customer = json_decode($job['customer_info'], true);
    $factor = json_decode($job['factor_info'], true);
    $factorItems = json_decode($job['factor_items'], true);
    $factorNumber = $job['factor_number'];
    $user_id = $job['user_id'];

    // Send data to the external service
    $success = sendData($customer, $factor, $factorItems, $factorNumber, $user_id);

    // Mark the job as completed regardless of response
    markAsCompleted($job['id']);

    if ($success) {
        // Optionally, mark SMS as sent
        markSMSAsSent($job['id']);
    }
}

$now = date('H:i:s');
echo "\n\n*************** Bill cron jobs finished ( $now ) ************************\n\n";

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
    $postData = http_build_query([
        "GenerateCompleteFactor" => "GenerateCompleteFactor",
        "customer" => json_encode($customer),
        "factor" => json_encode($factor),
        "factorItems" => json_encode($factorItems),
        "user_id" => $user_id,
        "factorNumber" => $factorNumber
    ]);

    $url = "http://sells.yadak.shop/";
    return fireAndForget($url, $postData);
}

function fireAndForget($url, $postData)
{
    $parts = parse_url($url);
    $scheme = $parts['scheme'] ?? 'http';
    $host = $parts['host'] ?? '';
    $port = ($scheme === 'https') ? 443 : 80;
    $path = $parts['path'] ?? '/';

    $fp = fsockopen(
        ($scheme === 'https' ? 'ssl://' : '') . $host,
        $port,
        $errno,
        $errstr,
        2
    );

    if (!$fp) {
        error_log("Connection failed: $errstr ($errno)");
        return false;
    }

    $out = "POST $path HTTP/1.1\r\n";
    $out .= "Host: $host\r\n";
    $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out .= "Content-Length: " . strlen($postData) . "\r\n";
    $out .= "Connection: Close\r\n\r\n";
    $out .= $postData;

    fwrite($fp, $out);
    fclose($fp); // No response wait
    return true;
}

function getJobs()
{
    $sql = "SELECT * FROM factor.bill_jobs WHERE status = 0 ORDER BY created_at ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
