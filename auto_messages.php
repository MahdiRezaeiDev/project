<?php
require_once './config/constants.php';
require_once './database/db_connect.php';
require_once './utilities/callcenter/DollarRateHelper.php';
require_once './app/controller/telegram/AutoMessageController.php';
$status = getStatus();
function boot()
{
    $now = date('H:i:s');
    echo "\n\n*************** Cron job started ( $now ) ************************\n\n";
    // API endpoint URL
    $apiUrl = 'http://auto.yadak.center/';

    $postData = [
        'getMessagesAuto' => 'getMessagesAuto'
    ];

    // Initialize curl
    $curl = curl_init();

    // Set the curl options
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true, // Return response as a string instead of outputting it
        CURLOPT_FOLLOWLOCATION => true, // Follow redirects
        CURLOPT_MAXREDIRS => 10, // Maximum number of redirects to follow
        CURLOPT_TIMEOUT => 600, // Timeout in seconds
        CURLOPT_POST => true, // Set as POST request
        CURLOPT_POSTFIELDS => http_build_query($postData), // Encode data as URL-encoded format
        CURLOPT_HTTPHEADER => [ // Optional headers
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);

    // Execute the request
    $response = curl_exec($curl);

    // Check for errors
    if (curl_errno($curl)) {
        $errorMessage = curl_error($curl);
        // Handle the error
        echo "cURL error: $errorMessage";
    } else {
        // Handle the response` 
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Get HTTP status code
        if ($statusCode >= 200 && $statusCode < 300) {

            // Request was successful
            $response = json_decode($response, true);
            validateMessages($response);
        } else {
            // Request failed
            echo "Request failed with status code $statusCode";
            // You can handle different status codes here
        }
    }

    // Close curl
    curl_close($curl);
}

function validateMessages($messages)
{
    $separators = [
        " ",
        "  ",
        "     ",
        "           ",
        " - ",
        " -- ",
        " : ",
        " = ",
        " == ",
        " \n",
        " \n\n",
        " \n\n\n",
        " => ",
        " / ",
        " __ ",
        " **** ",
    ];

    $sentMessages = [];

    foreach ($messages as $sender => $message) {
        if (!checkIfValidSender($sender)) {
            continue;
        }

        $latestRequests = getReceiverLatestRequests($sender);

        array_walk($latestRequests, function (&$request) {
            $request = explode(' ', $request);
        });

        $latestRequests = array_merge(...$latestRequests);

        $allMessages = $message['info'];

        foreach ($allMessages as $message) {

            // Step 1: Explode the message codes into an array
            $rawCodes = explode("\n", $message['code']);

            // Step 2: Remove the last element of the array
            array_pop($rawCodes);
            // Step 3: Convert all codes to uppercase
            $rawCodes = array_map('strtoupper', $rawCodes);

            // Step 4: Trim whitespace from each code
            $rawCodes = array_map('trim', $rawCodes);

            // Step 5: Ensure all codes are unique
            $rawCodes = array_unique($rawCodes);

            // Step 6: Remove codes that are already in the latest requests
            $rawCodes = array_diff($rawCodes, $latestRequests);

            if (!count($rawCodes)) {
                continue;
            }

            $codes = isGoodSelected($rawCodes);
            // Now $codes contains the filtered codes
            if (count($codes)) {
                try {
                    $template = '';
                    $conversation = '';
                    $index = rand(0, count($separators) - 1);

                    foreach ($codes as $code) {
                        // Check if the code has already been sent to this sender
                        if (isset($sentMessages[$sender]) && in_array($code, $sentMessages[$sender])) {
                            continue;
                        }

                        $data = getSpecification($code);

                        if ($data) {
                            foreach ($data as $itemCode => $item) {
                                if (trim($item['finalPrice']) == 'موجود نیست' || empty($item['finalPrice'])) {
                                    echo $code . "  قیمت نهایی موجود نیست " . "\n";
                                    continue;
                                }
                                $template .= $code . $separators[$index] . $item['finalPrice'] . "\n";
                                $conversation .= $code . $separators[$index] . $item['finalPrice'] . "\n";
                                saveConversation($sender, $code, $conversation);
                                $conversation = '';
                            }
                        }

                        if ($template !== '') {
                            // Add the code to sentMessages before sending the template
                            $sentMessages[$sender][] = $code;
                            sendMessageWithTemplate($sender, $template);
                            $template = '';
                        }
                    }
                } catch (Exception $error) {
                    echo 'Error fetching price: ' . $error->getMessage();
                }
            } else {
                if (count($rawCodes) > 0) {
                    $codes = implode(', ', $rawCodes);
                    echo $codes . " کد مدنظر اضافه نشده " . "\n";
                }
            }
        }
    }
}


function getSpecification($completeCode)
{
    $explodedCodes = explode("\n", $completeCode);

    $nonExistingCodes = [];

    $explodedCodes = array_filter($explodedCodes, function ($code) {
        return strlen($code) > 6;
    });

    // Cleaning and filtering codes
    $sanitizedCodes = array_map(function ($code) {
        return strtoupper(preg_replace('/[^a-z0-9]/i', '', $code));
    }, $explodedCodes);

    // Remove duplicate codes
    $explodedCodes = array_unique($sanitizedCodes);

    $existing_code = []; // This array will hold the id and partNumber of the existing codes in DB

    // Prepare SQL statement outside the loop for better performance
    $sql = "SELECT id, partnumber FROM yadakshop.nisha WHERE partnumber LIKE :partNumber";
    $stmt = PDO_CONNECTION->prepare($sql);

    foreach ($explodedCodes as $code) {
        $param = $code . '%';
        $stmt->bindParam(':partNumber', $param, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $existing_code[$code] = $result;
        } else {
            $nonExistingCodes[] = $code;
        }
    }

    $goodDetails = [];
    $relation_id = [];
    foreach ($explodedCodes as $code) {
        if (!in_array($code, $nonExistingCodes)) {
            foreach ($existing_code[$code] as $item) {
                $relation_exist = isInRelation($item['id']);

                if ($relation_exist) {
                    if (!in_array($relation_exist, $relation_id)) {
                        array_push($relation_id, $relation_exist);
                        $goodDescription = relations($relation_exist, true);
                        $goodDetails[$item['partnumber']]['existing'] = $goodDescription['existing'];
                        $goodDetails[$item['partnumber']]['givenPrice'] = givenPrice(array_keys($goodDescription['goods']), $relation_exist);
                        break;
                    }
                } else {
                    $goodDescription = relations($item['partnumber'], false);
                    $goodDetails[$item['partnumber']]['existing'] = $goodDescription['existing'];
                    $goodDetails[$item['partnumber']]['givenPrice'] = givenPrice(array_keys($goodDescription['goods']));
                }
            }
        }
    }

    $finalResult = [];

    foreach ($goodDetails as $partNumber => $goodDetail) {
        $brands = [];
        foreach ($goodDetail['existing'] as $item) {
            if (count($item)) {
                array_push($brands, array_keys($item));
            }
        }
        $brands = [...array_unique(array_merge(...$brands))];
        $goodDetails[$partNumber]['brands'] = addRelatedBrands($brands);
        $goodDetails[$partNumber]['finalPrice'] = getFinalSanitizedPrice($goodDetail['givenPrice'], $goodDetails[$partNumber]['brands']);
        $finalResult[$partNumber]['finalPrice'] = getFinalSanitizedPrice($goodDetail['givenPrice'], $goodDetails[$partNumber]['brands']);
    }

    return $finalResult;
}

if ($status) {
    boot();
} else {
    echo 'ارسال پیام خودکار غیرفعال است' . "\n";
}
