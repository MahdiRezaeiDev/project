<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/callcenter/DollarRateHelper.php';
require_once '../../../utilities/callcenter/GivenPriceHelper.php';
require_once '../../../app/partials/inventory/similarGoods.php';


// START ------------------ UPDATE THE COMPLETE BILL -----------------------------
if (isset($_POST['GenerateCompleteFactor'])) {
    $customerInfo = json_decode($_POST['customerInfo']);
    $factorInfo = json_decode($_POST['factorInfo']);
    $factorItems = json_decode($_POST['factorItems']);

    $customer_id = getCustomerId($customerInfo) ? getCustomerId($customerInfo) : null;
    $success = true; // Initialize success variable

    try {
        if (!$customer_id) {
            $customer_id = createCustomer($customerInfo);
        } else {
            updateCustomer($customerInfo, $customer_id);
        }

        if ($customer_id == null) {
            throw new Exception("Customer ID is null");
        }

        $factorNumber = registerFactorNumber($customerInfo);

        CreateCompleteBill($factorInfo, $customer_id, $factorNumber);
        CreateBillItems($factorInfo, $factorItems);
        getSimilarGoods($factorItems, $factorInfo->id, $customerInfo, $factorNumber, $factorInfo->partner, $factorInfo->totalPrice, $factorInfo->date, false);
        sendSMS($customerInfo, $factorInfo, $factorItems, $factorNumber);
    } catch (Exception $e) {
        $success = false; // Set success to false if an error occurred
    }
}

function sendSMS($customer, $factor, $factorItems, $factorNumber)
{
    // Prepare data for POST request
    $postData = array(
        "GenerateCompleteFactor" => "GenerateCompleteFactor",
        "customer" => json_encode($customer),
        "factor" => json_encode($factor),
        "factorItems" => json_encode($factorItems),
        "user_id" => $_SESSION['id'],
        "factorNumber" => $factorNumber
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    // curl_setopt($ch, CURLOPT_URL, "http://sells.yadak.shop/");
    curl_setopt($ch, CURLOPT_URL, "http://yadak.test/");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Execute cURL request
    $result = curl_exec($ch);
    print_r($result);
    // Close cURL session
    curl_close($ch);
    exit();
}

if (isset($_POST['updateCompleteFactor'])) {
    $customerInfo = json_decode($_POST['customerInfo']);
    $factorInfo = json_decode($_POST['factorInfo']);
    $factorItems = json_decode($_POST['factorItems']);

    $customer_id = getCustomerId($customerInfo) ? getCustomerId($customerInfo) : null;
    $success = true; // Initialize success variable

    try {

        if (!$customer_id) {
            $customer_id = createCustomer($customerInfo);
        } else {
            updateCustomer($customerInfo, $customer_id);
        }

        if ($customer_id == null) {
            throw new Exception("Customer ID is null");
        }
        updateRegisteredFactorNumber($factorInfo->billNO, $customerInfo);
        UpdateCompletedBill($factorInfo, $customer_id);
        CreateBillItems($factorInfo, $factorItems);
        getSimilarGoods($factorItems, $factorInfo->id, $customerInfo, $factorInfo->billNO, $factorInfo->partner, $factorInfo->totalPrice, $factorInfo->date, true);
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        PDO_CONNECTION->rollback();
        $success = false; // Set success to false if an error occurred
    }
    echo $success;
    // Output based on transaction success
    if ($success) {
        echo "true";
    } else {
        echo "false";
    }
}

if (isset($_POST['getFactorItems'])) {
    $billId = $_POST['factorNo'];
    $factorID = getCompletedFactorId($billId);

    if (!$factorID) {
        return false;
    }

    $factorDetails = getFactorDetails($factorID);

    header('Content-Type: application/json');
    echo $factorDetails['billDetails'];
}

function getFactorDetails($billId)
{
    $sql = "SELECT * FROM factor.bill_details WHERE bill_id = :billId";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':billId', $billId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function getCompletedFactorId($billId)
{
    $sql = "SELECT id, status FROM factor.bill WHERE bill_number = :billId LIMIT 1";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':billId', $billId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        return false;
    }

    return $result['id'];
}

function getCustomerId($customer)
{
    $sql = "SELECT id FROM callcenter.customer WHERE phone = :phone ORDER BY id DESC LIMIT 1";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(":phone", $customer->phone);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        return $result['id'];
    } else {
        return false;
    }
}

function createCustomer($customer)
{
    // Extract customer details with fallback to empty strings if properties are not set
    $name = $customer->name ?? "";
    $family = $customer->family ?? "";
    $phone = $customer->phone ?? "";
    $address = $customer->address ?? "";
    $car = $customer->car ?? "";

    // Prepare the SQL query
    $sql = "INSERT INTO callcenter.customer (name, family, phone, address, car) VALUES (:name, :family, :phone, :address, :car)";

    try {
        // Prepare the PDO statement
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
        // Handle any errors (e.g., log them, rethrow them, return a specific error message, etc.)
        // For demonstration purposes, we'll just rethrow the exception
        throw $e;
    }
}

function updateCustomer($customer)
{
    // Extract customer details with fallback to empty strings if properties are not set
    $name = $customer->name ?? "";
    $family = $customer->family ?? "";
    $phone = $customer->phone ?? "";
    $address = $customer->address ?? "";
    $car = $customer->car ?? "";
    $id = $customer->id ?? "";

    // Prepare the SQL query
    $sql = "UPDATE callcenter.customer
                    SET name = :name, family = :family, phone = :phone,
                    address = :address, car = :car
                    WHERE id = :id";

    try {
        // Prepare the PDO statement
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
        // Handle any errors (e.g., log them, rethrow them, return a specific error message, etc.)
        // For demonstration purposes, we'll just rethrow the exception
        throw $e;
    }
}

function UpdateCompletedBill($billInfo, $customerId)
{
    try {
        $user_id = $_SESSION['id'];

        $sql = "UPDATE factor.bill SET 
                customer_id = :customerId,
                quantity = :quantity,
                discount = :discount,
                tax = :tax,
                withdraw = :withdraw,
                total = :totalPrice,
                bill_date = :billDate,
                user_id = :userId,
                description = :description,
                status = 1
                WHERE id = :billId";

        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $billInfo->quantity, PDO::PARAM_INT);
        $stmt->bindParam(':discount', $billInfo->discount, PDO::PARAM_STR);
        $stmt->bindParam(':tax', $billInfo->tax, PDO::PARAM_STR);
        $stmt->bindParam(':withdraw', $billInfo->withdraw, PDO::PARAM_STR);
        $stmt->bindParam(':totalPrice', $billInfo->totalPrice, PDO::PARAM_STR);
        $stmt->bindParam(':billDate', $billInfo->date, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':description', $billInfo->description, PDO::PARAM_STR);
        $stmt->bindParam(':billId', $billInfo->id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Check if the update was successful
        $success = $stmt->rowCount() > 0;

        // Return success status
        return $success;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function CreateCompleteBill($billInfo, $customerId, $factorNumber)
{
    try {
        $user_id = $_SESSION['id'];

        $sql = "UPDATE factor.bill SET 
                customer_id = :customerId,
                bill_number = :billNumber,
                quantity = :quantity,
                discount = :discount,
                tax = :tax,
                withdraw = :withdraw,
                total = :totalPrice,
                bill_date = :billDate,
                user_id = :userId,
                description = :description,
                status = 1,
                created_at = CURRENT_TIMESTAMP
                WHERE id = :billId";

        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->bindParam(':billNumber', $factorNumber, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $billInfo->quantity, PDO::PARAM_INT);
        $stmt->bindParam(':discount', $billInfo->discount, PDO::PARAM_STR);
        $stmt->bindParam(':tax', $billInfo->tax, PDO::PARAM_STR);
        $stmt->bindParam(':withdraw', $billInfo->withdraw, PDO::PARAM_STR);
        $stmt->bindParam(':totalPrice', $billInfo->totalPrice, PDO::PARAM_STR);
        $stmt->bindParam(':billDate', $billInfo->date, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':description', $billInfo->description, PDO::PARAM_STR);
        $stmt->bindParam(':billId', $billInfo->id, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Check if the update was successful
        $success = $stmt->rowCount() > 0;

        // Return success status
        return $success;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function CreateBillItems($billInfo, $billItems)
{
    try {
        // Convert bill items to JSON
        $billItemsJson = json_encode($billItems);

        // SQL query with named placeholders
        $sql = "UPDATE factor.bill_details
                SET billDetails = :billItems
                WHERE bill_id = :billId";

        // Prepare the PDO statement
        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':billItems', $billItemsJson, PDO::PARAM_STR);
        $stmt->bindParam(':billId', $billInfo->id, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Optionally, check if the update was successful
        $success = $stmt->rowCount() > 0;

        // Return success status (optional)
        return $success;
    } catch (PDOException $e) {
        echo $e->getMessage();
        // Handle the exception (log it, return a specific error code, etc.)
        return false;
    }
}

function registerFactorNumber($customer)
{
    try {
        $user_id = $_SESSION['id'];
        $name = $customer->name ?? '';
        $family = $customer->family ?? '';
        $fullName = $name . ' ' . $family;

        // Start a transaction
        PDO_CONNECTION->beginTransaction();

        // Lock the table row while fetching the last shomare
        $stmt = PDO_CONNECTION->prepare("SELECT shomare FROM factor.shomarefaktor ORDER BY id DESC LIMIT 1 FOR UPDATE");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $shomare = $row ? $row['shomare'] + 1 : 1;

        // Prepare the INSERT statement
        $insertStmt = PDO_CONNECTION->prepare("INSERT INTO factor.shomarefaktor (shomare, kharidar, user) VALUES (:shomare, :kharidar, :user)");
        $insertStmt->bindParam(":shomare", $shomare, PDO::PARAM_INT);
        $insertStmt->bindParam(":kharidar", $fullName, PDO::PARAM_STR);
        $insertStmt->bindParam(":user", $user_id, PDO::PARAM_INT);

        // Execute the INSERT statement
        $insertStmt->execute();

        // Commit the transaction
        PDO_CONNECTION->commit();

        return $shomare;
    } catch (PDOException $e) {
        PDO_CONNECTION->rollBack();
        echo $e->getMessage();
        return false;
    }
}

function updateRegisteredFactorNumber($factorNumber, $customer)
{
    try {
        $name = $customer->name ?? '';
        $family = $customer->family ?? '';

        $fullName = $name . ' ' . $family;

        // Prepare the SQL statement with placeholders
        $sql = "UPDATE factor.shomarefaktor 
                SET kharidar = :kharidar
                WHERE shomare = :shomare";

        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind parameters to the statement
        $stmt->bindParam(':kharidar', $fullName);
        $stmt->bindParam(':shomare', $factorNumber);

        // Execute the statement
        $stmt->execute();
    } catch (PDOException $e) {
        // Handle the exception (log it, return a specific error code, etc.)
        echo $e->getMessage();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'print') {
    $factorNumber = $_POST['factorNumber'];
    updatePrintedStatus($factorNumber);
}

function updatePrintedStatus($factorNumber)
{
    try {
        $sql = "UPDATE factor.bill SET printed = 1 WHERE id = :billNumber";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':billNumber', $factorNumber, PDO::PARAM_INT);
        echo $stmt->execute();
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
