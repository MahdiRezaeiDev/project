<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

$date = $_POST['date'] ?? null;
$factors = getPendingSells($date);

if (empty($factors)) {
    echo '<div class="p-6 text-center text-gray-500">هیچ فاکتوری یافت نشد</div>';
    exit;
}
?>

<div class="overflow-x-auto">
    <table class="w-full text-sm text-right">
        <thead class="bg-gray-100 text-gray-600">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">شماره فاکتور</th>
                <th class="px-4 py-3">مشتری</th>
                <th class="px-4 py-3">تاریخ</th>
                <th class="px-4 py-3">مبلغ فاکتور</th>
                <th class="px-4 py-3">وضعیت</th>
                <th class="px-4 py-3">مقدار فاکتور/خروج</th>
                <th class="px-4 py-3">عملیات</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($factors as $i => $f): 
                $statusMatch = ($f['bill_quantity'] == $f['exit_quantity']);
            ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3"><?= $i + 1 ?></td>
                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($f['bill_number']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($f['customer_name'].' '.$f['customer_family']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($f['bill_date']) ?></td>
                <td class="px-4 py-3 text-gray-700"><?= number_format($f['total']) ?> ریال</td>
                <td class="px-4 py-3">
                    <?php if ($f['exit_quantity'] > 0): ?>
                        <?php if ($statusMatch): ?>
                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                مطابقت دارد
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                مغایرت دارد
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="px-2 py-1 text-xs font-semibold text-yellow-600 bg-yellow-100 rounded-full">
                            خروج نخورده
                        </span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <?= $f['bill_quantity'].' / '.$f['exit_quantity'] ?>
                </td>
                <td class="px-4 py-3 flex gap-2">
                    <a href="../factor/complete.php?factor_number=<?= $f['id'] ?>">
                        <img class="w-6 mr-4 cursor-pointer" title="مشاهده فاکتور" src="./assets/icons/receipt.svg" />
                    </a>
                    <a href="../factor/externalView.php?factorNumber=<?= $f['id'] ?>">
                        <img class="w-6 mr-4 cursor-pointer" title="مشاهده جزئیات" src="./assets/icons/telescope.svg" />
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
function getPendingSells($date = null)
{
    if ($date) {
        $date = str_replace('/', '-', $date);
        $start = date('Y-m-d 00:00:00', strtotime($date));
        $end   = date('Y-m-d 00:00:00', strtotime($date . ' +1 day'));
    } else {
        $start = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $end   = date('Y-m-d 00:00:00');
    }

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
?>