<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST["getNewFactorNumber"])) {
    $customer = $_POST["customer"];
    $user = $_SESSION["id"];
    $latestFactorNumber = getLatestFactorNumber();
    if ($latestFactorNumber === 0) {
        return false;
    }
    if (saveFactorNumber($customer, $user, $latestFactorNumber)) {
        echo $latestFactorNumber;
    } else {
        return false;
    }
}

if (isset($_POST["saveChanges"])) {
    $customer = $_POST['customer'];
    $factor_id = $_POST['factor'];
    $user = $_POST['edit_user_id'];
    UpdateFactor($customer, $factor_id, $user);
}

if (isset($_POST["changeStatus"])) {
    $factor_id = $_POST['factor'];
    $status = $_POST['status'];
    UpdateStatus($factor_id, $status);
}

if (isset($_POST['cancelFactor'])) {
    $factor_id = $_POST['factor'];
    echo cancelFactor($factor_id);
}

function cancelFactor($factor_id)
{
    try {
        $sql = "UPDATE factor.shomarefaktor SET status = 0 WHERE id = :factor_id";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':factor_id', $factor_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    } catch (\Throwable $th) {
        throw $th;
    }
}



function UpdateStatus($factor_id, $status)
{
    $sql = "UPDATE factor.shomarefaktor SET approved=:status WHERE shomare = :factor_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':factor_id', $factor_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function UpdateFactor($customer, $factor_id, $user)
{
    $sql = "UPDATE factor.shomarefaktor SET kharidar=:customer, user=:user WHERE id = :factor_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':customer', $customer, PDO::PARAM_STR);
    $stmt->bindParam(':user', $user, PDO::PARAM_INT);
    $stmt->bindParam(':factor_id', $factor_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function getLatestFactorNumber()
{
    $sql = "SELECT MAX(shomare) AS latest FROM factor.shomarefaktor;";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extract the latest factor number from the result
    $latestFactorNumber = isset($result['latest']) ? $result['latest'] + 1 : 0;

    return $latestFactorNumber;
}

function saveFactorNumber($customer, $user_id, $latestFactorNumber)
{
    $sql = "INSERT INTO factor.shomarefaktor (kharidar, user, shomare) VALUES (:customer, :user_id, :latestFactorNumber);";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':customer', $customer, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':latestFactorNumber', $latestFactorNumber, PDO::PARAM_INT);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
