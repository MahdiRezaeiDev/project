<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$todayDeliveries = getDeliveries();

function getDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar FROM factor.deliveries
    INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
        INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare

    WHERE DATE(deliveries.created_at) = CURDATE() ORDER BY deliveries.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
