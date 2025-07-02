<?php
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $factor = intval($_POST['factor'] ?? 0);
    $factorInfo = getFactorInfo($factor);

    $errorMessage = '';
    $success = false;
    $remainingAmount = getRemainingAmount($factorInfo);

    $amount = intval(str_replace(',', '', $_POST['amount'] ?? 0));
    $account = trim($_POST['account_number']);
    $description = trim($_POST['description']);
    $date = trim($_POST['invoice_time']) . ' ' . trim($_POST['time']) . ':00';
    $customer_id = intval($_POST['customer_id']);
    $user_id = intval($_POST['user_id']);
    $bill_id = intval($_POST['bill_id'] ?? 0);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;
    $photoPath = null;

    $account = trim($_POST['account_number'] ?? '');

    // Validation
    if ($amount <= 0) {
        $errorMessage = "مبلغ وارد شده معتبر نیست.";
        header("Location: ../../../views/factor/addPayment.php?factor=" . urlencode($factor) . "&error=1");
        exit;
    } elseif ($amount > $remainingAmount) {
        $errorMessage = "مبلغ وارد شده بیشتر از باقیمانده فاکتور است.";
        header("Location: ../../../views/factor/addPayment.php?factor=" . urlencode($factor) . "&error=1");
        exit;
    } elseif (empty($account)) {
        header("Location: ../../../views/factor/addPayment.php?factor=" . urlencode($factor) . "&error=2");
        exit;
    } else {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $targetDir = '../../../uploads/payments/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $datePrefix = date('Ymd'); // e.g. 20250702
            $uniqueId = uniqid('payment_');
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);

            $fileName = $datePrefix . '_' . $uniqueId . '.' . $extension;
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $photoPath = $targetFile;
            }
        }


        // Store payment
        $result = storePayment($amount, $account, $date, $customer_id, $user_id, $bill_id, $photoPath, $created_at, $updated_at, $factor, $description);

        $remainingAmount = getRemainingAmount($factorInfo);
        if ($remainingAmount) {
            header("Location: ../../../views/factor/addPayment.php?factor=" . urlencode($factor) . "&success=1");
            exit;
        } else {
            header("Location: ../../../views/factor/addPayment.php?factor=" . urlencode($factor));
            exit;
        }
    }
}
// === Helper Functions ===

function storePayment(
    int $amount,
    string $account,
    string $date,
    int $customer_id,
    int $user_id,
    int $bill_id,
    ?string $photoPath,
    string $created_at,
    string $updated_at,
    int $factor,
    ?string $description
): bool {
    try {
        $stmt = PDO_CONNECTION->prepare("
            INSERT INTO factor.payments 
                (bill_id, amount, date, account, customer_id, user_id, photo, description, created_at, updated_at)
            VALUES 
                (:bill_id, :amount, :date, :account, :customer_id, :user_id, :photo, :description, :created_at, :updated_at)
        ");

        $stmt->execute([
            ':bill_id' => $bill_id,
            ':amount' => $amount,
            ':date' => $date,
            ':account' => $account,
            ':customer_id' => $customer_id,
            ':user_id' => $user_id,
            ':photo' => $photoPath,
            ':description' => $description,
            ':created_at' => $created_at,
            ':updated_at' => $updated_at,
        ]);

        return true;
    } catch (PDOException $e) {
        error_log("Error inserting payment: " . $e->getMessage());
        return false;
    }
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


function getRemainingAmount($factorInfo)
{
    $payments = getPayments($factorInfo['id']);
    $totalPayment = array_sum(array_column($payments, 'amount'));
    $remainingAmount = $factorInfo['total'] - $totalPayment;
    return $remainingAmount;
}
