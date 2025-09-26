<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$allPendingSells = getPendingSells();

function getPendingSells()
{
    $sql = "SELECT 
    b.id,
    b.bill_number,
    b.quantity AS bill_quantity,
    b.total,
    b.bill_date,
    c.name AS customer_name,
    c.family AS customer_family,
    c.address AS customer_address,
    IFNULL(e.exit_quantity, 0) AS exit_quantity,
    (b.quantity - IFNULL(e.exit_quantity, 0)) AS difference
FROM factor.bill b
LEFT JOIN (
    SELECT invoice_number, SUM(qty) AS exit_quantity
    FROM stock_1404.exitrecord
    GROUP BY invoice_number
) e ON b.bill_number = e.invoice_number
LEFT JOIN callcenter.customer c ON b.customer_id = c.id
WHERE b.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
  AND b.created_at < CURDATE()
  AND (
        e.exit_quantity IS NULL 
        OR e.exit_quantity <> b.quantity
      )
ORDER BY b.id DESC;
";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
