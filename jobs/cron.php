<?php
// --- URLs ---
$firstUrl  = 'http://check.cheraghbargh.ir/';           // Checks for new messages
$secondUrl = 'https://telegram.cheraghbargh.ir/views/telegram/cron.php'; // Heavy Telegram cron task

echo "[" . date('Y-m-d H:i:s') . "] Starting cron script...\n";

// --- 1. Call the first URL and get response ---
$ch1 = curl_init($firstUrl);
curl_setopt_array($ch1, [
    CURLOPT_RETURNTRANSFER => true,   // wait for response
    CURLOPT_HEADER         => false,
    CURLOPT_TIMEOUT        => 30,     // 30 seconds max
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch1);
$httpCode = curl_getinfo($ch1, CURLINFO_HTTP_CODE);

if (curl_errno($ch1)) {
    echo "❌ cURL error for $firstUrl: " . curl_error($ch1) . "\n";
    $response = 'false';
} elseif ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ Success for $firstUrl, HTTP code $httpCode\n";
    echo "Response: $response\n";
} else {
    echo "⚠️ Failed for $firstUrl, HTTP code $httpCode\n";
    echo "Response: $response\n";
}

curl_close($ch1);

// --- 2. Check if new messages were found ---
$response = trim($response);
if ($response === 'true' || $response === '1') {
    echo "[" . date('Y-m-d H:i:s') . "] New messages detected. Triggering second URL...\n";

    // --- 3. Trigger second URL (fire-and-forget) ---
    $ch2 = curl_init($secondUrl);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => false, // don’t wait for response
        CURLOPT_HEADER         => false,
        CURLOPT_NOBODY         => true,  // don’t download body
        CURLOPT_TIMEOUT_MS     => 500,   // very short
        CURLOPT_CONNECTTIMEOUT_MS => 500,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    curl_exec($ch2);
    curl_close($ch2);

    echo "[" . date('Y-m-d H:i:s') . "] Second URL triggered.\n";
} else {
    echo "[" . date('Y-m-d H:i:s') . "] No new messages. Second URL not called.\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Cron script finished.\n\n\n";
