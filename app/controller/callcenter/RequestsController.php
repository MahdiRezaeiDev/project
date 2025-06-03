<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$type = $_GET['type'] ?? 'all';
$code = $_GET['code'] ??  null;

$titleMap = [
    'hour' => 'آمار درخواست های یک ساعت اخیر',
    'today' => 'آمار درخواست های امروز',
    'quarter' => 'آمار درخواست های ۳ روز اخیر',
    'week' => 'آمار درخواست های ۷ روز اخیر',
    'month' => 'آمار درخواست های ۳۰ روز اخیر',
    'all' => 'آمار درخواست های تمام زمان ها'
];

$title = $titleMap[$type] ?? $titleMap['all'];
$rangeMap = [
    'quarter' => 3,
    'week' => 7,
    'month' => 30
];

$requests = $type === 'hour' ? getLastHourMostRequested($code) : ($type === 'today' ? getTodayMostRequested($code) : ($type === 'all' ? getAllTimeMostRequested($code) :
    getInRangeMostRequested($rangeMap[$type], $code)));

// Functions

function getLastHourMostRequested($code = null)
{
    return getMostRequested($code, "NOW() - INTERVAL 1 HOUR", 'HOUR');
}

function getTodayMostRequested($code = null)
{
    return getMostRequested($code, "CURDATE()", 'DAY');
}

function getInRangeMostRequested($range, $partNumber = null)
{
    return getMostRequested($partNumber, "NOW() - INTERVAL $range DAY", 'DAY');
}

function getAllTimeMostRequested($code = null)
{
    return getMostRequested($code, null, 'ALL');
}

function getMostRequested($code, $interval, $type)
{
    $getGoodId = getGoodId(trim($code));
    $isInRelation = isInRelation($getGoodId);
    $codes = $isInRelation ? getInRelationItems($isInRelation) : [$code];

    $sql = "SELECT m.request, GROUP_CONCAT(r.name) AS receivers, COUNT(m.id) AS quantity
            FROM shop.partNumber m ";

    if ($type !== 'ALL') {
        $sql .= " WHERE m.created_at >= $interval";
    }

    if ($code) {
        $placeholders = implode(',', array_map(fn($i) => ":code$i", array_keys($codes)));
        $sql .= $type === 'ALL' ? " WHERE " : " AND ";
        $sql .= "TRIM(m.request) IN ($placeholders)";
    }

    $sql .= " GROUP BY m.request ORDER BY quantity DESC";

    $stmt = PDO_CONNECTION->prepare($sql);

    if ($code) {
        foreach ($codes as $index => $code) {
            $stmt->bindValue(":code$index", trim($code), PDO::PARAM_STR);
        }
    }

    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT r.name, m.created_at
            FROM shop.partNumber m
            JOIN shop.partNumber r ON m.receiver = r.chat_id";

    if ($type !== 'ALL') {
        $sql .= " WHERE m.created_at >= $interval";
    }

    if ($code) {
        $placeholders = implode(',', array_map(fn($i) => ":code$i", array_keys($codes)));
        $sql .= $type === 'ALL' ? " WHERE " : " AND ";
        $sql .= "TRIM(m.request) IN ($placeholders)";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = PDO_CONNECTION->prepare($sql);

    if ($code) {
        foreach ($codes as $index => $code) {
            $stmt->bindValue(":code$index", trim($code), PDO::PARAM_STR);
        }
    }

    $stmt->execute();
    $receivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'requests' => $requests,
        'receivers' => $receivers
    ];
}
