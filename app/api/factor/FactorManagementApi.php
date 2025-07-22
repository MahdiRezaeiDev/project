<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

// Get the list of all users who registered a bill
if (isset($_POST['getUsers'])) {

    $pattern = $_POST['getUsers'];
    sendResponse(getUsers($pattern));
}

function getUsers()
{
    $stmt = PDO_CONNECTION->prepare("SELECT DISTINCT(user_id) AS id, name, family FROM callcenter.bill
            INNER JOIN yadakshop.users ON user_id = yadakshop.users.id ORDER BY bill.created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// get the list of all completed bills for specific user and date
if (isset($_POST['getUserCompleteBills'])) {
    $user = trim($_POST['user']);
    $date = trim($_POST['date']);
    sendResponse(getUsersCompleteBills($user, $date));
}

function getUsersCompleteBills($user, $date)
{
    try {
        $baseSql = "SELECT customer.name, customer.family, bill.id, bill.bill_number, bill.quantity, bill.bill_date,
                            bill.total, bill.user_id, bill.partner ,bill_details.billDetails
                    FROM factor.bill
                    INNER JOIN callcenter.customer ON customer_id = customer.id
                    INNER JOIN factor.bill_details ON bill.id = bill_details.bill_id
                    WHERE DATE(bill.created_at) = :date
                    AND status = 1";

        if ($user !== 'all') {
            $baseSql .= " AND bill.user_id = :user";
        }

        $baseSql .= " ORDER BY bill.created_at DESC";

        $stmt = PDO_CONNECTION->prepare($baseSql);
        $stmt->bindParam(':date', $date);

        if ($user !== 'all') {
            $stmt->bindParam(':user', $user);
        }

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    } catch (PDOException $e) {
        // Log the error and handle it appropriately
        error_log('Database error: ' . $e->getMessage());
        return []; // or throw an exception
    }
}

// get the list of all incomplete bills for specific user and date
if (isset($_POST['getUserIncompleteBills'])) {
    $user = trim($_POST['user']);
    $date = trim($_POST['date']);
    sendResponse(getUsersInCompleteBills($user, $date));
}

function getUsersInCompleteBills($user, $date)
{
    try {
        // Base SQL query with placeholders for dynamic parts
        $baseSql = "SELECT customer.name, customer.family, bill.id, bill.bill_number, bill.bill_date, bill.total,
                            bill.quantity, bill.user_id, bill.partner
                    FROM factor.bill
                    LEFT JOIN callcenter.customer ON bill.customer_id = customer.id
                    WHERE DATE(bill.created_at) = :date
                    AND bill.status = 0";

        // Add user condition if not querying for all users
        if ($user !== 'all') {
            $baseSql .= " AND bill.user_id = :user";
        }

        // Append order clause
        $baseSql .= " ORDER BY bill.created_at DESC";

        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($baseSql);

        // Bind common parameters
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);

        // Bind user parameter only if necessary
        if ($user !== 'all') {
            $stmt->bindParam(':user', $user, PDO::PARAM_INT);
        }

        // Execute and fetch results
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    } catch (PDOException $e) {
        // Log the error and handle it appropriately
        error_log('Database error: ' . $e->getMessage());
        return []; // Return an empty array if an exception occurs
    }
}

// delete an specified  bill from the database
if (isset($_POST['deleteFactor'])) {
    $factor_id = trim(intval($_POST['factorId']));
    sendResponse(deleteFactor($factor_id));
}

function deleteFactor($factor_id)
{
    try {
        PDO_CONNECTION->beginTransaction();

        // Delete from the bill table
        $deleteBillSQL = "DELETE FROM factor.bill WHERE id = :factor_id";
        $deleteBillStmt = PDO_CONNECTION->prepare($deleteBillSQL);
        $deleteBillStmt->bindParam(':factor_id', $factor_id, PDO::PARAM_INT);
        $deleteBillStmt->execute();

        // Delete from the bill_details table
        $deleteBillDetailsSQL = "DELETE FROM factor.bill_details WHERE bill_id = :factor_id";
        $deleteBillDetailsStmt = PDO_CONNECTION->prepare($deleteBillDetailsSQL);
        $deleteBillDetailsStmt->bindParam(':factor_id', $factor_id, PDO::PARAM_INT);
        $deleteBillDetailsStmt->execute();

        // Commit the transaction
        PDO_CONNECTION->commit();

        return true;
    } catch (Exception $e) {
        // Log the error message
        error_log('Error deleting factor: ' . $e->getMessage());

        // Rollback the transaction
        PDO_CONNECTION->rollBack();

        return false;
    }
}

if (isset($_POST['searchForBill'])) {
    $pattern = trim($_POST['pattern']);
    $mode = trim($_POST['mode']);
    $isPartNumber = trim($_POST['isPartNumber']);


    if ($isPartNumber == 'true') {
        sendResponse(searchByPartNumber($pattern, $mode));
    } else {
        if (ctype_digit($pattern)) {
            // If the pattern is all numbers, search by bill number
            sendResponse(searchByBillNumber($pattern));
        } else {
            // Otherwise, search based on customer name or family
            $customers = getMatchedCustomers($pattern);
            sendResponse(getMatchedBills($customers, $mode));
        }
    }
}

function searchByBillNumber($pattern)
{
    try {
        $stmt = PDO_CONNECTION->prepare("SELECT customer.name, customer.family, bill.id, bill.bill_number, bill.quantity, bill.bill_date, bill.total, bill.user_id,
                           bill_details.billDetails
                    FROM factor.bill
                    INNER JOIN callcenter.customer ON customer_id = customer.id
                    INNER JOIN factor.bill_details ON bill.id = bill_details.bill_id
                    WHERE bill.bill_number = :pattern
                    AND status = 1");
        $stmt->bindParam(':pattern', $pattern, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return [];
    }
}

function searchByPartNumber($pattern, $mode)
{
    $pattern = strtoupper($pattern);
    try {
        $stmt = PDO_CONNECTION->prepare("SELECT customer.name, customer.family, bill.id, bill.bill_number, bill.quantity, bill.bill_date, bill.total,
                        bill.user_id, bill.partner, bill_details.billDetails
                    FROM factor.bill
                    INNER JOIN callcenter.customer ON customer_id = customer.id
                    INNER JOIN factor.bill_details ON bill.id = bill_details.bill_id
                    WHERE bill_details.billDetails LIKE :pattern
                    AND status = :mode ORDER BY bill.created_at DESC");
        $stmt->bindValue(':pattern', '%' . $pattern . '%', PDO::PARAM_STR);
        $stmt->bindValue(':mode', $mode, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return [];
    }
}

function getMatchedCustomers($pattern)
{
    try {
        $parts = mb_split(' ', $pattern);
        $name = $parts[0];
        $family = isset($parts[1]) ? $parts[1] : '';

        $sql = "SELECT id FROM callcenter.customer WHERE (name LIKE :namePattern OR family LIKE :namePattern)";

        if (!empty($family)) {
            $sql .= " AND (family LIKE :familyPattern)";
        }

        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindValue(':namePattern', '%' . $name . '%', PDO::PARAM_STR);

        if (!empty($family)) {
            $stmt->bindValue(':familyPattern', '%' . $family . '%', PDO::PARAM_STR);
        }

        $stmt->execute();
        $customersId = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $customersId;
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return [];
    }
}

function getMatchedBills($customers, $mode)
{
    try {

        if (empty($customers)) {
            return [];  // No customers matched
        }

        // Prepare placeholders for customer IDs
        $placeholders = implode(',', array_fill(0, count($customers), '?'));

        // Prepare the SQL statement with placeholders
        $sql = "SELECT customer.name, customer.family, bill.id, bill.bill_number, bill.bill_date, bill.total,
                        bill.quantity, bill.user_id, bill.partner, bill_details.billDetails
                FROM factor.bill
                LEFT JOIN callcenter.customer ON customer_id = customer.id
                INNER JOIN factor.bill_details ON bill.id = bill_details.bill_id
                WHERE customer_id IN ($placeholders) 
                AND status = ?
                ORDER BY bill.created_at DESC";

        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind customer IDs
        foreach ($customers as $key => $customer) {
            $stmt->bindValue(($key + 1), $customer, PDO::PARAM_INT);
        }

        // Bind status
        $stmt->bindValue((count($customers) + 1), $mode, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Fetch the results
        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $bills;
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return [];  // Return an empty array if an exception occurs
    }
}

// Send the response back to the client in JSON format
function sendResponse($response)
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
    echo json_encode($response);
}
