<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
    exit;
}

$factor_number = $_GET['factor'] ?? null;

if (!$factor_number) {
    header("Location: ../../../views/factor/list.php");
    exit;
}

// Get factor info
$factorInfo = getFactorInfo($factor_number);

if (!$factorInfo) {
    die("فاکتور پیدا نشد.");
}

function getFactorInfo($factorNumber)
{
    $stmt = PDO_CONNECTION->prepare("SELECT bill.*, customer.name, customer.family,
        customer.phone, customer.id AS customer_id, user.name AS user_name, user.family AS user_family
        FROM factor.bill AS bill
        JOIN callcenter.customer AS customer ON bill.customer_id = customer.id
        JOIN yadakshop.users AS user ON bill.user_id = user.id
        WHERE bill.bill_number = ?");
    $stmt->execute([$factorNumber]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPayments($billId)
{
    $stmt = PDO_CONNECTION->prepare("SELECT payments.*, user.name AS user_name, user.family AS user_family FROM factor.payments
    JOIN yadakshop.users AS user ON payments.user_id = user.id
     WHERE bill_id = ?");
    $stmt->execute([$billId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

