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

    $sql = "SELECT partNumber, COUNT(id) AS quantity
            FROM shop.searches ";

    if ($type !== 'ALL') {
        $sql .= " WHERE created_at >= $interval";
    }

    if ($code) {
        $placeholders = implode(',', array_map(fn($i) => ":code$i", array_keys($codes)));
        $sql .= $type === 'ALL' ? " WHERE " : " AND ";
        $sql .= "TRIM(partNumber) IN ($placeholders)";
    }

    $sql .= " GROUP BY partNumber ORDER BY quantity DESC";

    $stmt = PDO_CONNECTION->prepare($sql);

    if ($code) {
        foreach ($codes as $index => $code) {
            $stmt->bindValue(":code$index", trim($code), PDO::PARAM_STR);
        }
    }

    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $requests;
}



function getGoodId($partNumber)
{
    $stmt = PDO_CONNECTION->prepare("SELECT id FROM nisha WHERE partNumber LIKE :partNumber LIMIT 1");
    $stmt->bindValue(':partNumber', $partNumber . '%', PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? false;
}

function isInRelation($id)
{
    $stmt = PDO_CONNECTION->prepare("SELECT pattern_id FROM shop.similars WHERE nisha_id = :nisha_id");
    $stmt->bindValue(':nisha_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['pattern_id'] ?? false;
}

function getInRelationItems($nisha_id)
{
    $stmt = PDO_CONNECTION->prepare("SELECT nisha_id FROM shop.similars WHERE pattern_id = :id");
    $stmt->bindValue(':id', $nisha_id, PDO::PARAM_INT);
    $stmt->execute();
    $all_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nisha_id');

    if (empty($all_ids)) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($all_ids), '?'));
    $stmt = PDO_CONNECTION->prepare("SELECT partnumber FROM yadakshop.nisha WHERE id IN ($placeholders)");
    foreach ($all_ids as $index => $id) {
        $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
    }
    $stmt->execute();
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'partnumber');
}
