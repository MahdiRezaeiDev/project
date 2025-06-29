<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['getPaymentReports'])) {
    $date = trim($_POST['date']);
    $AllPayments = getAllPaymentsByDate($date);

    if (count($AllPayments) === 0) {
        echo "
        <tr>
            <td class='py-2 text-red-500 text-center font-semibold' colspan='9'>
               هیچ پرداختی برای این تاریخ ثبت نشده است.
            </td>
        </tr>";
        exit;
    };

    foreach ($AllPayments as $p) {
        echo "<tr class='border-t hover:bg-gray-50'>";
        echo "<td class='border px-2 py-1 text-center'>{$p['bill_number']}</td>";
        echo "<td class='border px-2 py-1'>{$p['customer_name']} {$p['customer_family']}</td>";
        echo "<td class='border px-2 py-1 text-right'>" . number_format($p['total']) . " تومان</td>";
        echo "<td class='border px-2 py-1'>{$p['user_name']} {$p['user_family']}</td>";
        echo "<td class='border px-2 py-1 text-right'>" . number_format($p['amount']) . " تومان</td>";
        echo "<td class='border px-2 py-1'>{$p['date']}</td>";
        echo "<td class='border px-2 py-1'>{$p['account']}</td>";

        // Photo display
        echo "<td class='px-3 py-1 text-center'>";
        if (!empty($p['photo'])) {
            echo "<a href='../../app/controller/payment/{$p['photo']}' target='_blank' class='text-blue-600'>نمایش</a>";
        } else {
            echo "<span class='text-gray-400'>ندارد</span>";
        }
        echo "</td>";

        // Approval checkbox + name
        echo "<td class='border px-2 py-1 text-center'>";
        echo "<input type='checkbox' ";
        echo !empty($p['approved_by']) ? 'checked ' : '';
        echo "onchange='updateApproval(this, {$p['id']})' name='approved'> <br />";
        echo "<span class='text-xs text-gray-500'>";
        echo !empty($p['approved_by_name']) ? "{$p['approved_by_name']} {$p['approved_by_family']}" : '—';
        echo "</span>";
        echo "</td>";

        echo "</tr>";
    }
}

function getAllPaymentsByDate(string $date)
{
    // Ensure $date is in Y-m-d format (e.g., '2025-06-26')
    $startOfDay = $date . ' 00:00:00';
    $endOfDay = $date . ' 23:59:59';

    $stmt = PDO_CONNECTION->prepare("
    SELECT 
        payments.*,
        bill.total,
        bill.bill_number,
        user.name AS user_name, 
        user.family AS user_family, 
        approved_user.name AS approved_by_name,
        approved_user.family AS approved_by_family,
        customer.name AS customer_name, 
        customer.family AS customer_family
    FROM 
        factor.payments
    JOIN 
        factor.bill AS bill ON payments.bill_id = bill.id
    JOIN 
        yadakshop.users AS user ON payments.user_id = user.id
    LEFT JOIN 
        yadakshop.users AS approved_user ON payments.approved_by = approved_user.id
    JOIN 
        callcenter.customer AS customer ON payments.customer_id = customer.id
    WHERE 
        payments.created_at >= :startOfDay AND payments.created_at <= :endOfDay
    ORDER BY 
        payments.created_at DESC
");

    $stmt->bindValue(':startOfDay', $startOfDay);
    $stmt->bindValue(':endOfDay', $endOfDay);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


if (isset($_POST['updateApproval'])) {
    $paymentId = intval($_POST['payment_id']);
    $approved = intval($_POST['approved']);
    $approvedBy = $_SESSION['id'] ?? null;

    if (!$paymentId) {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }

    if ($approved) {
        $query = "UPDATE factor.payments SET approved_by = :approved_by WHERE id = :id";
        $stmt = PDO_CONNECTION->prepare($query);
        $success = $stmt->execute([
            ':id' => $paymentId,
            ':approved_by' => $approvedBy
        ]);
    } else {
        $query = "UPDATE factor.payments SET approved_by = NULL WHERE id = :id";
        $stmt = PDO_CONNECTION->prepare($query);
        $success = $stmt->execute([
            ':id' => $paymentId
        ]);
    }

    echo json_encode(['success' => $success]);
}
