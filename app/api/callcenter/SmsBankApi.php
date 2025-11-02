<?php 
$dataFile = __DIR__ . '/../../../views/callcenter/messages.json';
$messages = json_decode(file_get_contents($dataFile), true);
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if ($input && isset($input['action'])) {
    $action = $input['action'];


    if ($action === 'add') {
        array_unshift($messages, [
            'title' => $input['title'],
            'message' => $input['message']
        ]);
    }

    elseif ($action === 'edit') {
        $index = $input['index'];
        if (isset($messages[$index])) {
            $messages[$index] = [
                'title' => $input['title'],
                'message' => $input['message']
            ];
        }
    }


    elseif ($action === 'delete') {
        $index = $input['index'];
        if (isset($messages[$index])) {
            array_splice($messages, $index, 1);
        }
    }


    elseif ($action === 'insert') {
        $index = $input['index'];
        array_splice($messages, $index, 0, [[
            'title' => $input['title'],
            'message' => $input['message']
        ]]);
    }


    file_put_contents($dataFile, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    echo json_encode(['status' => 'ok']);
    exit;
}

echo json_encode($messages);
