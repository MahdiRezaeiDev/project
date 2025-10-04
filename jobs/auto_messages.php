<?php
require_once '../config/constants.php';
require_once '../database/db_connect.php';
require_once '../utilities/callcenter/DollarRateHelper.php';
require_once '../app/controller/telegram/AutoMessageController.php';

$status = getStatus();

function boot()
{
    $now = date('H:i:s');
    echo "\n\n*************** Cron job started ( $now ) ************************\n\n";
?>
    <br>
    <?php

    // 👉 Toggle this flag to switch between mock JSON and real API
    $useMock = true;

    if ($useMock) {
        // --- Mock JSON (test data) ---
        $mockResponse = '{
            "169785118":{"info":[{"code":"256202g\n","message":"256202g\n\n\n\n????","date":1759586720}],"name":"فروشگاه ایران یدک (آقای رضا افشاری)","userName":169785118,"profile":"images.png"}        }';

        $response = json_decode($mockResponse, true);
        validateMessages($response);
    } else {
        // --- Real API call ---
        $apiUrl = 'http://auto.yadak.center/';

        $postData = [
            'getMessagesAuto' => 'getMessagesAuto'
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $errorMessage = curl_error($curl);
            echo "cURL error: $errorMessage";
        } else {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode >= 200 && $statusCode < 300) {
                $response = json_decode($response, true);
                validateMessages($response);
            } else {
                echo "Request failed with status code $statusCode";
            }
        }

        curl_close($curl);
    }

    $now = date('H:i:s');
    ?>
    <br>
<?php
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
            $rawCodes = explode("\n", $message['code']);
            array_pop($rawCodes);
            $rawCodes = array_map('strtoupper', $rawCodes);
            $rawCodes = array_map('trim', $rawCodes);
            $rawCodes = array_unique($rawCodes);
            $rawCodes = array_diff($rawCodes, $latestRequests);

            if (!count($rawCodes)) {
                continue;
            }

            $codes = isGoodSelected($rawCodes);

            if (count($codes)) {
                try {
                    $template = '';
                    $conversation = '';
                    $index = rand(0, count($separators) - 1);

                    foreach ($codes as $code) {
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
                                // saveConversation($sender, $code, $conversation);
                                $conversation = '';
                            }
                        }

                        if ($template !== '') {
                            $sentMessages[$sender][] = $code;
                            // sendMessageWithTemplate($sender, $template);
                            echo $template;
                            usleep(200000);
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
    $now = date('H:i:s');
    echo "\n\n*************** Cron job ENDED ( $now ) ************************\n\n";
}

function getSpecification($completeCode)
{
    $explodedCodes = explode("\n", $completeCode);
    $nonExistingCodes = [];

    $explodedCodes = array_filter($explodedCodes, function ($code) {
        return strlen($code) > 6;
    });

    $sanitizedCodes = array_map(function ($code) {
        return strtoupper(preg_replace('/[^a-z0-9]/i', '', $code));
    }, $explodedCodes);

    $explodedCodes = array_unique($sanitizedCodes);
    $existing_code = [];

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
        $finalResult[$partNumber]['finalPrice'] = $goodDetails[$partNumber]['finalPrice'];
    }

    return $finalResult;
}

if ($status) {
    boot();
} else {
    echo 'ارسال پیام خودکار غیرفعال است' . "\n";
}
