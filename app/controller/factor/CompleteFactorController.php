<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$factorInfo = null;
$customerInfo = null;
$billItems = [];

if (isset($_GET['factor_number']) && is_numeric($_GET['factor_number'])) {

    $bill_id = intval($_GET['factor_number']);

    $details = getFactorInfo($bill_id);

    if (!$details) {
        die('فاکتور شما در سیتستم موجود نیست');
    }

    if (!$details['status']) {
        header('Location: ./incomplete.php?factor_number=' . $details['id']);
    }

    $factorInfo = [
        'id' => $bill_id,
        'billNO' => $details['bill_number'],
        'customer_id' => $details['customer_id'],
        'date' => $details['bill_date'],
        'total' => $details['total'],
        'quantity' => $details['quantity'],
        'tax' => $details['tax'],
        'discount' => $details['discount'],
        'description' => $details['description'],
        'withdraw' => $details['withdraw'],
        'partner' => $details['partner'],
        'insurance' => $details['insurance'],
        'created_at' => $details['created_at'],
    ];

    if ($factorInfo['customer_id']) {
        $customerInfo = getCustomerInfo($factorInfo['customer_id']);
    } else {
        $customerInfo = [
            'id' => '',
            'name' => '',
            'displayName' => '',
            'family' => '',
            'car' => '',
            'phone' => '',
            'address' => '',
            'mode' => 'create'
        ];
    }
    $billItems = getBillItems($factorInfo['id']);

    $codes = implode("\n", array_column(json_decode($billItems, true), 'partNumber'));

    $billItemsBrandAndPrice = json_encode(getDetails($codes));

    // Assuming $factorInfo['created_at'] is in a format that strtotime() can parse
    $created_at_timestamp = strtotime($factorInfo['created_at']);
    $today_timestamp = strtotime('today');

    // Calculate the difference in seconds
    $date_difference_seconds = $today_timestamp - $created_at_timestamp;

    // Convert the difference to days
    $date_difference_days = ($date_difference_seconds / (60 * 60 * 24));
} else {
    die("Invalid factor number");
}



function getFactorInfo($billId)
{
    $sql = "SELECT * FROM factor.bill WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":id", $billId);

    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCustomerInfo($customerId)
{
    $sql = "SELECT id, name, name AS displayName, family, phone, car, address FROM callcenter.customer WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":id", $customerId);

    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['mode'] = 'update';
    return $data;
}

function getBillItems($bill_id)
{
    $sql = "SELECT * FROM factor.bill_details WHERE bill_id = :bill_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":bill_id", $bill_id);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data['billDetails'];
}
