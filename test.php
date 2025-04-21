<?php
require_once './init.php';
require_once './config/const.php';
require_once './utilities/helper.php';

if (isset($_POST['sendMessage'])) {
    $value = $_POST['sendMessage'];
    switch ($value) {
        case 'sellsReport':
            sellsReport($MadelineProto, $_POST);
            break;
        case 'sellsReportTest':
            sellsReportTest($MadelineProto, $_POST);
            break;
        case "PurchaseReport":
            lowQuantityReport($MadelineProto, $_POST);
            break;
        case "sendDeliveryReport":
            sendDeliveryReport($MadelineProto, $_POST);
            break;
        case "sellsReportButtons":
            sellsReportButtonsFormat($MadelineProto, $_POST);
            break;
    }
}

function sellsReportButtonsFormat($MadelineProto, $data)
{
    $topicID = $data['topic_id'];
    $header = $data['header'];
    $footer = str_repeat('➖', 8) . PHP_EOL;
    $selectedGoods = json_decode($data['selectedGoods'], true);
    $lowQuantity = json_decode($data['lowQuantity'], true);

    $markup = [
        '_' => 'replyInlineMarkup',
        'rows' => []
    ];

    // Add goods rows
    foreach ([['items' => $selectedGoods, 'type' => 'normal'], ['items' => $lowQuantity, 'type' => 'low']] as $group) {
        foreach ($group['items'] as $good) {
            $markup['rows'] = array_merge($markup['rows'], buildGoodRows($good, $group['type']));
        }
    }

    $inputReplyToMessage = [
        '_' => 'inputReplyToMessage',
        'reply_to_msg_id' => $topicID,
    ];

    // Send header + table
    $MadelineProto->messages->sendMessage([
        'peer' => '-1002320490188',
        'reply_to' => $inputReplyToMessage,
        'message' => $header,
        'reply_markup' => $markup,
        'parse_mode' => 'html',
    ]);

    // Send footer
    $MadelineProto->messages->sendMessage([
        'peer' => '-1002320490188',
        'reply_to' => $inputReplyToMessage,
        'message' => $footer,
        'parse_mode' => 'html',
    ]);
}

function buildGoodRows($good, $type = 'normal')
{
    $brand = htmlspecialchars($good['brandName'], ENT_XML1, 'UTF-8');
    $dotColor = in_array($brand, ['GEN', 'MOB', 'اصلی']) ? '🔷' : '🔶';

    $rows = [];

    // First row
    $firstRowButtons = [
        ['_' => 'keyboardButtonCallback', 'text' => $good['partNumber'], 'data' => 0],
        ['_' => 'keyboardButtonCallback', 'text' => "$dotColor $brand", 'data' => 0],
    ];

    if ($type === 'low') {
        $firstRowButtons[] = ['_' => 'keyboardButtonCallback', 'text' => $good['quantity'] . ' | مورد نیاز: ' . $good['required'], 'data' => 0];
    } else {
        $firstRowButtons[] = ['_' => 'keyboardButtonCallback', 'text' => $good['quantity'], 'data' => 0];
    }

    $rows[] = [
        '_' => 'keyboardButtonRow',
        'buttons' => $firstRowButtons,
    ];

    // Second row
    $rows[] = [
        '_' => 'keyboardButtonRow',
        'buttons' => [
            ['_' => 'keyboardButtonCallback', 'text' => $good['persianName'], 'data' => 0],
            ['_' => 'keyboardButtonCallback', 'text' => $good['pos1'] . ' ' . $good['pos2'], 'data' => 0],
            ['_' => 'keyboardButtonCallback', 'text' => 'باقی مانده: ' . $good['remaining_qty'], 'data' => 0],
        ],
    ];

    return $rows;
}

function sendTableFormattedMessage($MadelineProto, $data)
{
    $topicID = $data['topic_id'];
    $header = $data['header'];
    $selectedGoods = json_decode($data['selectedGoods'], true);
    $lowQuantity = json_decode($data['lowQuantity'], true);
    $footer = str_repeat('➖', 8) . PHP_EOL;
    $markup = [
        '_' => 'replyInlineMarkup',
        'rows' => []
    ];

    foreach ($selectedGoods as $good) {
        $brand = htmlspecialchars($good['brandName'], ENT_XML1, 'UTF-8');
        $dotColor = in_array($brand, ['GEN', 'MOB', 'اصلی']) ? '🔷' : '🔶';

        array_push($markup['rows'], [
            '_' => 'keyboardButtonRow',
            'buttons' => [
                ['_' => 'keyboardButtonCallback', 'text' => $good['partNumber'], 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $brand . ' ' . $dotColor, 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $good['quantity'], 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $good['pos1'], 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $good['pos2'], 'data' => 0],
            ]
        ]);
    }

    foreach ($lowQuantity as $good) {
        array_push($markup['rows'], [
            '_' => 'keyboardButtonRow',
            'buttons' => [
                ['_' => 'keyboardButtonCallback', 'text' => $good['partNumber'], 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $brand . ' ' . $dotColor, 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $good['quantity'], 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $good['pos1'], 'data' => 0],
                ['_' => 'keyboardButtonCallback', 'text' . $good['pos2'], 'data' => 0],
            ]
        ]);
    }

    $inputReplyToMessage = [
        '_' => 'inputReplyToMessage',
        'reply_to_msg_id' => $topicID,
    ];

    // Sending the message
    $MadelineProto->messages->sendMessage([
        'peer' => 'https://t.me/+Z3c56mn7IQ0xNjI0',
        'reply_to' => $inputReplyToMessage,
        'message' => $header,
        'reply_markup' => $markup,
        'parse_mode' => 'html',
    ]);
    sendReportMessage($topicID, $MadelineProto, $footer);
}

function sellsReport($MadelineProto, $data)
{
    $message = $data['message'];
    $topicID = $data['topic_id'];

    $inputReplyToMessage = [
        '_' => 'inputReplyToMessage',
        'reply_to_msg_id' => $topicID,
    ];

    // Sending the message
    $MadelineProto->messages->sendMessage([
        'peer' => 'https://t.me/+Z3c56mn7IQ0xNjI0',
        'reply_to' => $inputReplyToMessage,
        'message' => $message,
        'parse_mode' => 'html',
    ]);
}

function lowQuantityReport($MadelineProto, $data)
{
    $topicID = 28985;
    $lowQuantity = json_decode($data['lowQuantity'], true);
    $footer = str_repeat('➖', 8) . PHP_EOL;
    $messageBody = null;

    if (count($lowQuantity) > 0) {
        foreach ($lowQuantity as $good) {
            $good['quantity'] = $good['required'];
            $messageBody .= formatLowQuantityMessageBody($good);
        }
        $messageBody .= $footer;
        sendPurchaseReport($topicID, $MadelineProto, $messageBody);
    }
}

function sellsReportTest($MadelineProto, $data)
{
    $topicID = $data['topic_id'];
    $header = $data['header'];
    $selectedGoods = json_decode($data['selectedGoods'], true);
    $lowQuantity = json_decode($data['lowQuantity'], true);
    $footer = str_repeat('➖', 8) . PHP_EOL;

    $goodsTotal = count($selectedGoods + $lowQuantity);
    sendReportMessage($topicID, $MadelineProto, $header);
    if ($goodsTotal > 10) {
        $messageBody = null;
        foreach ($selectedGoods as $good) {
            $messageBody .= formatMessageBody($good);
        }

        foreach ($lowQuantity as $good) {
            $messageBody .= formatMessageBody($good)
                . "مقدار مورد نیاز: {$good['required']} ❌❌ \n";
        }

        sendReportMessage($topicID, $MadelineProto, $messageBody);
    } else {
        foreach ($selectedGoods as $good) {
            $messageBody = formatMessageBody($good);
            sendReportMessage($topicID, $MadelineProto, $messageBody);
        }

        foreach ($lowQuantity as $good) {
            $messageBody = formatMessageBody($good)
                . "مقدار مورد نیاز: {$good['required']} ❌❌ \n\n";
            sendReportMessage($topicID, $MadelineProto, $messageBody);
        }
    }

    sendReportMessage($topicID, $MadelineProto, $footer);
}

function formatMessageBody($good)
{
    $brand = htmlspecialchars($good['brandName'], ENT_XML1, 'UTF-8');
    $dotColor = in_array($brand, ['GEN', 'MOB', 'اصلی']) ? '🔷' : '🔶';

    return PHP_EOL
        . str_pad(htmlspecialchars($good['partNumber'], ENT_XML1, 'UTF-8'), 18, ' ', STR_PAD_RIGHT)
        . $brand . ' ' . $dotColor . ' '
        . str_pad(htmlspecialchars($good['quantity'], ENT_XML1, 'UTF-8'), 8, ' ', STR_PAD_RIGHT)
        . htmlspecialchars($good['pos1'], ENT_XML1, 'UTF-8') . ' '
        . htmlspecialchars($good['pos2'], ENT_XML1, 'UTF-8') . PHP_EOL;
}

function formatLowQuantityMessageBody($good)
{
    $brand = htmlspecialchars($good['brandName'], ENT_XML1, 'UTF-8');
    $dotColor = in_array($brand, ['GEN', 'MOB', 'اصلی']) ? '🔷' : '🔶';

    return PHP_EOL
        . str_pad(htmlspecialchars($good['partNumber'], ENT_XML1, 'UTF-8'), 18, ' ', STR_PAD_RIGHT)
        . $brand . ' ' . $dotColor . ' '
        . str_pad(htmlspecialchars($good['quantity'], ENT_XML1, 'UTF-8'), 8, ' ', STR_PAD_RIGHT) . PHP_EOL;
}

function sendReportMessage($topicID, $MadelineProto, $message)
{
    $inputReplyToMessage = [
        '_' => 'inputReplyToMessage',
        'reply_to_msg_id' => $topicID,
    ];

    // Sending the message
    $MadelineProto->messages->sendMessage([
        'peer' => 'https://t.me/+Z3c56mn7IQ0xNjI0',
        'reply_to' => $inputReplyToMessage,
        'message' => $message,
        'parse_mode' => 'html',
    ]);
}

function sendPurchaseReport($topicID, $MadelineProto, $message)
{
    $inputReplyToMessage = [
        '_' => 'inputReplyToMessage',
        'reply_to_msg_id' => $topicID,
    ];

    // Sending the message
    $MadelineProto->messages->sendMessage([
        'peer' => 'https://t.me/+swCvruDsax1hMmQ0',
        'reply_to' => $inputReplyToMessage,
        'message' => $message,
        'parse_mode' => 'html',
    ]);
}

function sendDeliveryReport($MadelineProto, $data)
{
    $message = $data['message'];
    $topicID = 37411;
    $inputReplyToMessage = [
        '_' => 'inputReplyToMessage',
        'reply_to_msg_id' => $topicID,
    ];

    // Sending the message
    $MadelineProto->messages->sendMessage([
        'peer' => 'https://t.me/+Z3c56mn7IQ0xNjI0',
        'reply_to' => $inputReplyToMessage,
        'message' => $message,
        'parse_mode' => 'html',
    ]);
}
