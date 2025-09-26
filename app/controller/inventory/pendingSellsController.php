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
                c.name,
                c.family,
                IFNULL(SUM(e.qty), 0) AS exit_quantity,
                (b.quantity - IFNULL(SUM(e.qty), 0)) AS difference
            FROM factor.bill b
            LEFT JOIN stock_1404.exitrecord e 
                ON e.invoice_number = b.bill_number
            INNER JOIN callcenter.customer as c
                ON c.id = b.customer_id
            WHERE DATE(b.created_at) < CURDATE()   -- فقط فاکتورهای قبل از امروز
            GROUP BY b.id, b.bill_number, b.quantity
            HAVING difference <> 0   -- یا اختلاف داره یا کلاً خروج نخوردن
            ORDER BY b.id DESC;";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
