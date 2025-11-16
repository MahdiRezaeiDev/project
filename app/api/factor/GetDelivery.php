<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';


if (!isset($_POST['billNumber'])) {
    echo json_encode(['status' => 'error', 'message' => 'billNumber not provided']);
    exit;
}

$billNumber = trim($_POST['billNumber']); 

$conn = PDO_CONNECTION;


$query = "
    SELECT 
        d.id AS delivery_id,
        d.bill_number AS delivery_bill_number,
        d.delivery_cost,
        d.courier_name,
        d.description,
        d.need_call,
        d.type AS delivery_type,
        d.destination,
        d.peymentother,

        b.id AS bill_id,
        b.bill_number,
        b.customer_id,
        b.user_id,
        b.quantity,
        b.discount,
        b.withdraw,
        b.total,

        -- total paid 
        (
            SELECT COALESCE(SUM(amount), 0)
            FROM factor.payments
            WHERE bill_id = b.id
        ) AS total_paid,

        c.name AS customer_name,
        c.family AS customer_family,
        c.phone,
        c.id AS customer_id,

        u.family AS user_family

    FROM factor.bill AS b
    LEFT JOIN factor.deliveries AS d 
        ON d.bill_number = b.bill_number
    LEFT JOIN callcenter.customer AS c 
        ON b.customer_id = c.id
    LEFT JOIN yadakshop.users AS u 
        ON b.user_id = u.id

    WHERE b.bill_number = :bill_number
    LIMIT 1
";

$stmt = $conn->prepare($query);

$startTime = microtime(true);

$stmt->bindParam(':bill_number', $billNumber, PDO::PARAM_STR);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

$endTime = microtime(true);
$duration = $endTime - $startTime;

error_log("⏱ Optimized query time: " . round($duration, 4) . " seconds");

$response = [
    [
        'status' => $result ? 'success' : 'error',
        'type' => 'result',
        'data' => $result ?: null,
        'message' => $result ? 'Delivery, factor and payment data found' : 'No result found',
        'query_time_seconds' => round($duration, 4)
    ],
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
