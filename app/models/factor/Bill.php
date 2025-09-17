<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

class Bill
{
    public function getBill($billNumber)
    {
        $sql = "SELECT bill.*, customer.name, customer.family, customer.address, customer.phone,
        -- Delivery info
        CASE 
            WHEN deliveries.bill_number IS NOT NULL THEN TRUE
            ELSE FALSE
        END AS exists_in_deliveries,
        deliveries.contact_type,
        deliveries.destination,
        deliveries.type AS delivery_type,
        deliveries.user_id AS delivery_user_id,
        shomarefaktor.time AS factor_date,
        shomarefaktor.shomare AS shomare
        FROM factor.bill
        INNER JOIN callcenter.customer ON bill.customer_id = customer.id
        INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare
        LEFT JOIN factor.deliveries ON bill.bill_number = deliveries.bill_number

        WHERE bill.id = :billNumber ORDER BY bill_number DESC LIMIT 1";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':billNumber', $billNumber);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getBillItems($billNumber)
    {
        $sql = "SELECT * FROM factor.bill_details WHERE bill_id = :bill_id ORDER BY id DESC LIMIT 1";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':bill_id', $billNumber);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getCustomer($customerId)
    {
        $sql = "SELECT * FROM callcenter.customer WHERE id = :id ORDER BY id DESC LIMIT 1";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':id', $customerId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
}
