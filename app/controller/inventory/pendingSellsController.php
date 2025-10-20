<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$allPendingSells = getPendingSells();
function getPendingSells($date = null)
{
    if ($date) {
        // تبدیل تاریخ Y/m/d به Y-m-d اگر لازم باشه
        $date = str_replace('/', '-', $date);
        $start = date('Y-m-d 00:00:00', strtotime($date));
        $end   = date('Y-m-d 00:00:00', strtotime($date . ' +1 day'));
    } else {
        // روز قبل
        $start = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $end   = date('Y-m-d 00:00:00');
    }

    $sql = "SELECT 
                b.id,
                b.bill_number,
                b.quantity AS bill_quantity,
                b.total,
                b.bill_date,
                s.status as orderStatus,
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
            LEFT JOIN factor.shomarefaktor s ON b.bill_number = s.shomare
            WHERE b.created_at >= :start
              AND b.created_at < :end
              AND (
                    e.exit_quantity IS NULL 
                    OR e.exit_quantity <> b.quantity
                  )
              AND b.status = 1
            ORDER BY b.id DESC;";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute([
        ':start' => $start,
        ':end'   => $end
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
