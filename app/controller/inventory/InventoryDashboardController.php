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
