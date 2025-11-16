<?php

header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../Controller/factor/PrintFactorController.php';

function updatePrintedStatus($factorNumber)
{
    try {
        $sql = "UPDATE factor.bill SET printed = 1 WHERE id = :billNumber";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':billNumber', $factorNumber, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        $e->getMessage();
    }
}


header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);


if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'کاربر لاگین نکرده است']);
    exit;
}


if (getCurrentUserPrintRole()) {
    echo json_encode(['status' => 'success']);

    $factorNumber = $_POST['factorNumber'] ?? null;
    if ($factorNumber)
        updatePrintedStatus($factorNumber);
} else {
    echo json_encode(['status' => 'error', 'message' => 'شما مجاز به چاپ فاکتور نیستید']);
}
