<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/constants.php';
require_once '../database/db_connect.php';

// 2️⃣ آپدیت جدول bill_exit_status
$sql = "
REPLACE INTO factor.bill_exit_status (bill_id, bill_number, bill_quantity, exit_quantity, difference, status)
SELECT 
    b.id,
    b.bill_number,
    b.quantity AS bill_quantity,
    IFNULL(SUM(e.qty), 0) AS exit_quantity,
    (b.quantity - IFNULL(SUM(e.qty), 0)) AS difference,
    CASE
        WHEN SUM(e.qty) IS NULL THEN 'missing_exit'
        WHEN b.quantity <> SUM(e.qty) THEN 'mismatch'
        ELSE 'ok'
    END AS status
FROM factor.bill b
LEFT JOIN stock_1404.exitrecord e 
    ON b.bill_number = e.invoice_number
GROUP BY b.id, b.bill_number, b.quantity;
";

try {
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    echo "bill_exit_status table updated successfully.\n";
} catch (\PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
