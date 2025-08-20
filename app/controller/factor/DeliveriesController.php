<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$todayDeliveries = getDeliveries();
$customerDeliveries = getCustomerDeliveries();
$deliveries = getAllDeliveries();
$users = getAllUsers();

function getDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar FROM factor.deliveries
    INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
        INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare

    WHERE DATE(deliveries.created_at) = CURDATE() AND type = 'پیک یدک شاپ' ORDER BY deliveries.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCustomerDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar FROM factor.deliveries
    INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
        INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare

    WHERE DATE(deliveries.created_at) = CURDATE() AND type = 'پیک خود مشتری بعد از اطلاع' ORDER BY deliveries.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar FROM factor.deliveries
    INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
        INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare

    WHERE DATE(deliveries.created_at) = CURDATE() AND type != 'پیک خود مشتری بعد از اطلاع' AND type != 'پیک یدک شاپ' ORDER BY deliveries.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllUsers()
{
    $stmt = PDO_CONNECTION->prepare("
    SELECT u.id, u.name, u.family
    FROM users u
    WHERE u.name != '' 
      AND u.username IS NOT NULL 
      AND u.password IS NOT NULL 
      AND u.password != ''
      AND EXISTS (
          SELECT 1 FROM factor.deliveries d WHERE d.user_id = u.id
      )");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
