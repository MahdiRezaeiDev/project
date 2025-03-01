<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

function getLatestFactors()
{
    $stmt = PDO_CONNECTION->prepare("SELECT bill.*, customer.name, customer.family FROM factor.bill
    INNER JOIN callcenter.customer ON bill.customer_id = customer.id
    WHERE status = 1 ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatAsMoney($number)
{
    $formattedNumber = number_format($number);
    return $formattedNumber . ' ریال';
}

function getStocks()
{
    $sql = "SELECT SCHEMA_NAME AS database_name
            FROM information_schema.SCHEMATA
            WHERE SCHEMA_NAME LIKE 'stock_%' ";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $stocks;
}

$myAttendanceReportStart = getUserAttendanceReport('start', $_SESSION['id']);
$myAttendanceReportEnd = getUserAttendanceReport('leave', $_SESSION['id']);

function getUserAttendanceReport($action, $user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs WHERE user_id = :user_id AND DATE(created_at) = CURDATE() AND action = :action";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    $attendance_report = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $attendance_report;
}
