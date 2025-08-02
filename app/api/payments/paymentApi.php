<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/jdf.php';
$financeTeam = ['mahdi', 'babak', 'niyayesh', 'reyhan', 'ahmadiyan', 'sabahashemi', 'hadishasanpouri', 'rana'];

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

    if (!empty($_POST['card_number'])) {
        $conditions[] = '(payments.account LIKE :card_number)';
        $params[':card_number'] = '%' . $_POST['card_number'] . '%';
    }

    if (!empty($_POST['user'])) {
        $conditions[] = 'payments.user_id = :user_id';
        $params[':user_id'] = $_POST['user'];
    }

    $where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $query = "
    SELECT 
        payments.*,
        bill.total,
        bill.bill_date,
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

    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($payments)):
        $totalPayment = 0;
        foreach ($payments as $index => $payment):
            $totalPayment += $payment['amount']; ?>
            <tr class="border-t">
                <td class="px-3 py-1 print:text-xs text-center"><?= ++$index; ?></td>
                <td class="px-3 py-1 print:text-xs text-center"><?= $payment['bill_number'] ?></td>
                <td class="px-3 py-1 print:text-xs"><?= $payment['customer_name'] . ' ' . $payment['customer_family'] ?></td>
                <td class="px-3 py-1 print:text-xs"><?= number_format($payment['total']) ?>ریال</td>
                <td class="px-3 py-1 print:text-xs"><?= $payment['bill_date'] ?></td>
                <td class="px-3 py-1 print:text-xs"><?= $payment['user_name'] . ' ' . $payment['user_family'] ?></td>

                <?php if (in_array($_SESSION['username'], $financeTeam)): ?>
                    <!-- Editable amount -->
                    <td class="px-3 py-1 print:text-xs text-right">
                        <input class="border-2 p-2 text-xs w-full" type="text"
                            value="<?= number_format($payment['amount']) ?>"
                            onchange="updatePaymentProperty(this.value, <?= $payment['id'] ?>, 'amount')">
                    </td>

                    <!-- Editable date -->
                    <td class="px-3 py-1 print:text-xs text-right">
                        <input
                            type="text"
                            class="border-2 p-2 text-xs w-full jalali-date"
                            id="payment_date_<?= $payment['id'] ?>"
                            data-payment-id="<?= $payment['id'] ?>"
                            value="<?= jdate($payment['date']) ?>"
                            data-original-date="<?= $payment['date'] ?>"
                            readonly />
                    </td>


                    <!-- Editable account -->
                    <td class="px-3 py-1 print:text-xs text-right">
                        <input class="border-2 p-2 text-xs w-full" type="text"
                            value="<?= $payment['account'] ?>"
                            onchange="updatePaymentProperty(this.value, <?= $payment['id'] ?>)">
                    </td>
                <?php else: ?>
                    <!-- Read-only for non-finance -->
                    <td class="px-3 py-1 print:text-xs text-right"><?= number_format($payment['amount']) ?></td>
                    <td class="px-3 py-1 print:text-xs"><?= $payment['date'] ?></td>
                    <td class="px-3 py-1 print:text-xs"><?= $payment['account'] ?></td>
                <?php endif; ?>

                <td class="px-3 py-1 print:text-xs text-center hide_while_print">
                    <?php if (!empty($payment['photo'])): ?>
                        <a href="../../app/controller/payment/<?= $payment['photo'] ?>" target="_blank" class="text-blue-600">نمایش</a>
                    <?php else: ?>
                        <span class="text-gray-400">ندارد</span>
                    <?php endif; ?>
                </td>

                <!-- Description editable field -->
                <td class="px-3 py-1 print:text-xs relative">
                    <input
                        onkeyup="convertToPersian(this); searchCustomer(this.value, <?= $payment['id'] ?>)"
                        type="text"
                        name="customer"
                        data-payment-id="<?= $payment['id'] ?>"
                        class="py-3 px-3 w-full print:border-none border-2 text-xs border-gray-300 focus:outline-none text-gray-900 font-semibold"
                        id="customer_name_<?= $payment['id'] ?>"
                        value="<?= $payment['description'] ?>"
                        placeholder="اسم کامل مشتری را وارد نمایید ..." />

                    <div
                        id="customer_results_<?= $payment['id'] ?>"
                        class="absolute top-full mb-1 left-0 right-0 bg-white rounded-md shadow z-50 max-h-56 overflow-y-auto text-sm">
                    </div>
                </td>

                <!-- Approval -->
                <td class="text-center">
                    <input
                        type="checkbox"
                        <?= !empty($payment['approved_by']) ? 'checked' : '' ?>
                        onchange="updateApproval(this, <?= $payment['id'] ?>)"
                        name="approved">
                    <br>
                    <span class="text-xs text-gray-500">
                        <?= !empty($payment['approved_by_name']) ? $payment['approved_by_name'] . ' ' . $payment['approved_by_family'] : '—' ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        <!-- Total Row -->
        <tr class="border-t bg-gray-800 text-white">
            <td class="px-3 py-2 font-semibold text-left" colspan="6">
                مجموع واریزی
            </td>
            <td class="px-3 py-2 text-right font-semibold" colspan="6">
                <?= number_format($totalPayment); ?>
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <td class="py-2 text-red-500 text-center font-semibold" colspan="12">
                واریزی ای ثبت نشده است.
            </td>
        </tr>
<?php endif;
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

if (isset($_POST['updateDescription'])) {
    $id = intval($_POST['id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE factor.payments SET description = ? WHERE id = ?");
        $success = $stmt->execute([$description, $id]);

        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    echo json_encode(['status' => 'invalid_id']);
    exit;
}

if (isset($_POST['updateProperty'])) {
    $id = intval($_POST['id'] ?? 0);
    $owner = trim($_POST['owner'] ?? '');
    $property = trim($_POST['property'] ?? 'account');

    // Whitelist allowed columns
    $allowedProperties = ['account', 'owner', 'status', 'amount',]; // Add your real columns here
    if (!in_array($property, $allowedProperties)) {
        echo json_encode(['status' => 'invalid_property']);
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE factor.payments SET `$property` = ? WHERE id = ?");
        $success = $stmt->execute([$owner, $id]);

        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    echo json_encode(['status' => 'invalid_id']);
    exit;
}
