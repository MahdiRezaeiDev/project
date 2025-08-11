<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$todayDeliveries = getDeliveries();

function getDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM factor.deliveries WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
