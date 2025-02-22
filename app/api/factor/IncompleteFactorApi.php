<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../app/models/factor/Bill.php';

// START ------------------ THE FIRST STEP OF FACTOR ( CREATE INCOMPLETE BILL ) -----------------------------
if (isset($_POST['create_incomplete_bill'])) {

    $factor_id =  $_POST['factor_id'];

    $type = $_POST['type'] ?? 0;

    if ($factor_id !== 'null') {
        $bill = new Bill();
        $billDetails = $bill->getBill($factor_id);

        $incompleteBillId = createBill([
            'customer_id' => 0,
            'bill_number' => 0,
            'quantity' => $billDetails['quantity'],
            'discount' => 0,
            'tax' => 0,
            'withdraw' => 0,
            'total' => $billDetails['total'],
            'date' => $_POST['date'],
            'partner' => $type,
            'totalInWords' => null
        ]);

        $billDetails = getBillItems($factor_id);
        createBillItemsTable(
            $incompleteBillId,
            $billDetails['billDetails']
        );

        echo $incompleteBillId;
    } else {
        $incompleteBillId = createBill([
            'customer_id' => 0,
            'bill_number' => 0,
            'quantity' => 0,
            'discount' => 0,
            'tax' => 0,
            'withdraw' => 0,
            'total' => 0,
            'date' => $_POST['date'],
            'partner' => $type,
            'totalInWords' => null
        ]);

        $incompleteBillDetails = createBillItemsTable(
            $incompleteBillId,
            '[{
            "id": 5892295,
            "partName": "اسم قطعه",
            "price_per": 0,
            "quantity": 1,
            "max": "undefined",
            "partNumber": "NOTPART"}]'
        );
        echo $incompleteBillId;
    }
}

function createBill($billInfo)
{
    try {
        $sql = "INSERT INTO factor.bill 
                (customer_id, bill_number, quantity, discount, tax, withdraw, total, bill_date, user_id, status, partner) 
                VALUES (:customer_id, :bill_number, :quantity, :discount, :tax, :withdraw, :total, :bill_date, :user_id, :status, :partner)";

        $status = 0;
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':customer_id', $billInfo['customer_id'], PDO::PARAM_INT);
        $stmt->bindParam(':bill_number', $billInfo['bill_number'], PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $billInfo['quantity'], PDO::PARAM_INT);
        $stmt->bindParam(':discount', $billInfo['discount'], PDO::PARAM_STR);
        $stmt->bindParam(':tax', $billInfo['tax'], PDO::PARAM_STR);
        $stmt->bindParam(':withdraw', $billInfo['withdraw'], PDO::PARAM_STR);
        $stmt->bindParam(':total', $billInfo['total'], PDO::PARAM_STR);
        $stmt->bindParam(':bill_date', $billInfo['date'], PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':partner', $billInfo['partner'], PDO::PARAM_INT);

        $stmt->execute();

        $lastInsertedId = PDO_CONNECTION->lastInsertId();
        $stmt->closeCursor();

        return $lastInsertedId;
    } catch (PDOException $e) {
        return false;
    }
}

function createBillItemsTable($billId, $billItems)
{
    try {
        $sql = "INSERT INTO factor.bill_details (bill_id, billDetails) VALUES (?, ?)";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->execute([$billId, $billItems]);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        // Handle exception here, if needed
    }
}

function getBillItems($billId)
{
    try {
        $sql = "SELECT billDetails FROM factor.bill_details WHERE bill_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->execute([$billId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    } catch (PDOException $e) {
        // Handle exception here, if needed
        return false;
    }
}
// END ------------------ THE FIRST STEP OF FACTOR ( CREATE INCOMPLETE BILL ) -----------------------------




// START ------------------ UPDATE THE INCOMPLETE BILL -----------------------------
if (isset($_POST['updateIncompleteFactor'])) {
    $customerInfo = json_decode($_POST['customerInfo']);
    $factorInfo = json_decode($_POST['factorInfo']);
    $factorItems = json_decode($_POST['factorItems']);

    $success = true; // Initialize success variable

    try {
        // Start a transaction
        PDO_CONNECTION->beginTransaction();

        // Get or create the customer
        $customer_id = getCustomerId($customerInfo);
        if (!$customer_id) {
            $customer_id = createCustomer($customerInfo);
        } else {
            updateCustomer($customerInfo, $customer_id);
        }

        // If customer_id is still null, throw an exception
        if ($customer_id == null) {
            throw new Exception("Customer ID is null");
        }

        // Update the bill and bill items
        UpdateIncompleteBill($factorInfo, $customer_id);
        updateBillItems($factorInfo, $factorItems);

        // Commit the transaction
        PDO_CONNECTION->commit();
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        PDO_CONNECTION->rollBack();
        $success = false; // Set success to false if an error occurred

        // Optionally, log the error message for debugging
        error_log($e->getMessage());
    }

    // Output based on transaction success
    if ($success) {
        echo "true";
    } else {
        echo "false";
    }
}

function getCustomerId($customer)
{
    try {
        $sql = "SELECT id FROM callcenter.customer WHERE phone = ? ORDER BY id DESC LIMIT 1";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->execute([$customer->phone]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($result) {
            return $result['id'];
        } else {
            return false;
        }
    } catch (PDOException $e) {
        // Handle exception here, if needed
        return false;
    }
}

function createCustomer($customer)
{
    try {
        $name = $customer->displayName ?? "";
        $family = $customer->family ?? "";
        $phone = $customer->phone ?? "";
        $address = $customer->address ?? "";
        $car = $customer->car ?? "";
        // SQL query to insert customer details
        $sql = "INSERT INTO callcenter.customer (name, family, phone, address, car) VALUES (:name, :family, :phone, :address, :car)";

        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':family', $family, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':car', $car, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        // Return the last inserted ID
        return PDO_CONNECTION->lastInsertId();
    } catch (PDOException $e) {
        // Handle exception here, if needed
        return false;
    }
}

function updateCustomer($customer)
{
    try {
        $name = $customer->displayName ?? "";
        $family = $customer->family ?? "";
        $phone = $customer->phone ?? "";
        $address = $customer->address ?? "";
        $car = $customer->car ?? "";
        $id = $customer->id ?? "";

        // SQL query to update customer details
        $sql = "UPDATE callcenter.customer SET name = :name, family = :family, 
                phone = :phone, address = :address,
                car = :car WHERE id = :id";

        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':family', $family, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':car', $car, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();
    } catch (PDOException $e) {
        // Handle exception here, if needed
    }
}

function UpdateIncompleteBill($billInfo, $customerId)
{
    try {
        $user_id = $_SESSION['id'];

        $sql = "UPDATE factor.bill SET 
            customer_id = :customer_id,
            quantity = :quantity,
            discount = :discount,
            tax = :tax,
            withdraw = :withdraw,
            total = :total,
            bill_date = :bill_date,
            user_id = :user_id,
            description = :description,
            status = :status
            WHERE id = :id";

        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($sql);

        $status = 0;

        // Bind parameters
        $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $billInfo->quantity, PDO::PARAM_INT);
        $stmt->bindParam(':discount', $billInfo->discount, PDO::PARAM_STR);
        $stmt->bindParam(':tax', $billInfo->tax, PDO::PARAM_STR);
        $stmt->bindParam(':withdraw', $billInfo->withdraw, PDO::PARAM_STR);
        $stmt->bindParam(':total', $billInfo->totalPrice, PDO::PARAM_STR);
        $stmt->bindParam(':bill_date', $billInfo->date, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':description', $billInfo->description, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':id', $billInfo->id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Check if the update was successful
        $success = $stmt->rowCount() > 0;

        // Return success status
        return $success;
    } catch (PDOException $e) {
        // Handle exception here, if needed
        return false;
    }
}

function updateBillItems($billInfo, $billItems)
{
    try {
        // Prepared statement
        $sql = "UPDATE factor.bill_details SET billDetails = :billDetails WHERE bill_id = :bill_id";

        // Create a prepared statement
        $stmt = PDO_CONNECTION->prepare($sql);

        $billItems = json_encode($billItems);

        // Bind parameters
        $stmt->bindParam(':billDetails', $billItems, PDO::PARAM_STR);
        $stmt->bindParam(':bill_id', $billInfo->id, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();
    } catch (PDOException $e) {
        // Handle exception here, if needed
    }
}
