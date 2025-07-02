<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['filterRequest'])) {
    $conditions = [];
    $params = [];

    if (!empty($_POST['factor_date'])) {
        $conditions[] = 'DATE(bill.created_at) = :factor_date';
        $params[':factor_date'] = $_POST['factor_date'];
    }

    if (!empty($_POST['payment_date'])) {
        $conditions[] = 'DATE(payments.created_at) = :payment_date';
        $params[':payment_date'] = $_POST['payment_date'];
    }

    if (!empty($_POST['factor_number'])) {
        $conditions[] = 'bill.bill_number LIKE :factor_number';
        $params[':factor_number'] = '%' . $_POST['factor_number'] . '%';
    }

    if (!empty($_POST['customer_name'])) {
        $conditions[] = '(customer.name LIKE :customer_name OR customer.family LIKE :customer_name)';
        $params[':customer_name'] = '%' . $_POST['customer_name'] . '%';
    }

    $where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $query = "
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
    $where
    ORDER BY 
        payments.created_at DESC
    ";

    $stmt = PDO_CONNECTION->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    $AllPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($AllPayments) === 0) {
        echo "
        <tr>
            <td class='py-2 text-red-500 text-center font-semibold' colspan='9'>
               هیچ پرداختی یافت نشد.
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

        echo "<td class='px-3 py-1 text-center'>";
        if (!empty($p['photo'])) {
            echo "<a href='../../app/controller/payment/{$p['photo']}' target='_blank' class='text-blue-600'>نمایش</a>";
        } else {
            echo "<span class='text-gray-400'>ندارد</span>";
        }
        echo "</td>";

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
