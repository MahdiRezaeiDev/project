<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$factorInfo = null;
$customerInfo = null;
$billItems = [];
$billItemsBrandAndPrice = [];
$brands = array_column(getBrands(), 'name');

if (isset($_GET['factor_number'])) {

    $bill_id = $_GET['factor_number'];


    $details = getFactorInfo($bill_id);
    if (!$details) {
        die('فاکتور شما در سیتستم موجود نیست');
    }

    if ($details['status']) {
        header('Location: ./complete.php?factor_number=' . $details['id']);
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
        'partner' => $details['partner'],
        'insurance' => $details['insurance'],
        'withdraw' => $details['withdraw'],
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

    $goodBrandsDetails = getDetails($codes);
    $billItemsBrandAndPrice = json_encode($goodBrandsDetails['brandsPrices']);
    $originalPrices = json_encode($goodBrandsDetails['OriginalPrice']);
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
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $result['mode'] = 'update';
    return $result;
}

function getBillItems($bill_id)
{
    $sql = "SELECT * FROM factor.bill_details WHERE bill_id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":id", $bill_id);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data['billDetails'];
}
