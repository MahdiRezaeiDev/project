<?php
header('Content-Type: application/json');

function sendSMS($phone, $message)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.sms.ir/v1/send/bulk',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            "lineNumber" => "90004752",
            "MessageText" => $message,
            "Mobiles" => [$phone]
        ]),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: text/plain',
            'x-api-key: ' . '***',
        ),
    ));
    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return ['status' => 'error', 'message' => $error];
    } else {
        return ['status' => 'success', 'response' => $response];
    }
}

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';
$message = $data['message'] ?? '';

if (!$phone || !$message) {
    echo json_encode(['status' => 'error', 'message' => 'شماره یا پیام خالی است']);
    exit;
}


$result = sendSMS($phone, $message);
echo json_encode($result);
