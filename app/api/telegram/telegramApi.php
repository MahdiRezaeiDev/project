<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['selectedGoodForMessage'])) {
    $partNumber = $_POST['partNumber'];
    $goodID = getGoodID($partNumber);
    echo addSelectedGoodForMessage($goodID, $partNumber);
}

if (isset($_POST['deleteGood'])) {
    $partNumber = $_POST['partNumber'];
    $goodID = getGoodID($partNumber);
    $nishaId = findRelation($goodID);

    if (!$nishaId) {
        echo excludeGood($goodID, $partNumber);
        return;
    } else {
        $relatedItems = getInRelationItems($nishaId);
        if ($relatedItems) {
            foreach ($relatedItems as $item) {
                excludeGood($item['id'], $item['partnumber']);
            }
        }
        echo true;
    }
}

function getGoodID($partNumber)
{
    $sql = "SELECT id FROM yadakshop.nisha WHERE partnumber = :partNumber LIMIT 1";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":partNumber", $partNumber);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['id'] : null;
}

function addSelectedGoodForMessage($goodID, $partNumber)
{
    if (checkIfAlreadyExist($partNumber)) {
        return false;
    }

    $nishaId = findRelation($goodID);

    if (!$nishaId) {
        $sql = "INSERT INTO telegram.goods_for_sell (good_id, partNumber) VALUES (?, ?)";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->execute([$goodID, $partNumber]);

        // ✅ Delete from excluded_goods
        $del = PDO_CONNECTION->prepare("DELETE FROM telegram.excluded_goods WHERE partNumber = ?");
        $del->execute([$partNumber]);

        return $stmt->rowCount() > 0;
    } else {
        $relatedItems = getInRelationItems($nishaId);
        if ($relatedItems) {
            foreach ($relatedItems as $item) {
                if (checkIfAlreadyExist($item['partnumber'])) continue;

                $sql = "INSERT INTO telegram.goods_for_sell (good_id, partNumber) VALUES (?, ?)";
                $stmt = PDO_CONNECTION->prepare($sql);
                $stmt->execute([$item['id'], $item['partnumber']]);

                // ✅ Delete from excluded_goods
                $del = PDO_CONNECTION->prepare("DELETE FROM telegram.excluded_goods WHERE partNumber = ?");
                $del->execute([$item['partnumber']]);
            }
            return true;
        }
    }
    return false;
}

function excludeGood($goodID, $partNumber)
{
    // ✅ Delete from goods_for_sell
    $del = PDO_CONNECTION->prepare("DELETE FROM telegram.goods_for_sell WHERE partNumber = ?");
    $del->execute([$partNumber]);

    // ✅ Insert into excluded_goods if not already there
    if (!checkIfExcluded($partNumber)) {
        $insert = PDO_CONNECTION->prepare("INSERT INTO telegram.excluded_goods (good_id, partNumber) VALUES (?, ?)");
        $insert->execute([$goodID, $partNumber]);
    }

    return true;
}

function checkIfAlreadyExist($partNumber)
{
    $sql = "SELECT * FROM telegram.goods_for_sell WHERE partNumber = ?";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute([$partNumber]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function checkIfExcluded($partNumber)
{
    $sql = "SELECT * FROM telegram.excluded_goods WHERE partNumber = ?";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute([$partNumber]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function findRelation($id)
{
    $sql = "SELECT pattern_id FROM shop.similars WHERE nisha_id = ? LIMIT 1";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['pattern_id'] : false;
}

function getInRelationItems($nisha_id)
{
    $sql = "SELECT nisha_id FROM shop.similars WHERE pattern_id = ?";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute([$nisha_id]);
    $goods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $all_ids = array_column($goods, 'nisha_id');
    if (count($all_ids) === 0) return false;

    $idList = implode(',', $all_ids);
    $partNumberSQL = "SELECT id, partnumber FROM yadakshop.nisha WHERE id IN ($idList)";
    $partStmt = PDO_CONNECTION->query($partNumberSQL);
    return $partStmt->fetchAll(PDO::FETCH_ASSOC);
}
