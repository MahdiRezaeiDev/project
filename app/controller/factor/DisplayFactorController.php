<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

require_once '../../app/models/factor/Bill.php';

if (isset($_GET['factorNumber']) && is_numeric($_GET['factorNumber'])) {
    $factorNumber = intval($_GET['factorNumber']);

    $bill = new Bill();
    $BillInfo = $bill->getBill($factorNumber);
    $billItems = [];
    $customerInfo = null;

    if ($BillInfo) {
        $billItems = $bill->getBillItems($factorNumber)['billDetails'];
        $customerInfo = $bill->getCustomer($BillInfo['customer_id']);

        $preSellFactor = null;
        $preSellFactorItems = '{}';
        $preSellFactorItemsDescription = '{}';

        if (hasPreSellFactor($factorNumber)) {
            $preSellFactor = getPreSellFactor($factorNumber);
            $preSellFactorItems = $preSellFactor['selected_items'];
            $preSellFactorItemsDescription = $preSellFactor['details'];
        }
    } else {
        echo "Bill not found";
        die();
    }
}


function hasPreSellFactor($factorId)
{
    $sql = "SELECT * FROM factor.pre_sell WHERE bill_id = :billId";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":billId", $factorId);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

function getPreSellFactor($factorId)
{
    $sql = "SELECT * FROM factor.pre_sell WHERE bill_id = :billId";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":billId", $factorId);
    $stmt->execute();
    return $stmt->fetch();
}
