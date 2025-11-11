<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../Controller/factor/PrintFactorController.php';


header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);


if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'کاربر لاگین نکرده است']);
    exit;
}


if (getCurrentUserPrintRole()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'شما مجاز به چاپ فاکتور نیستید']);
}
