<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['toggleActivation'])) {
    toggleActivation($_POST['rate_id'], $_POST['type']);
}

if (isset($_POST['getItem'])) {
    $Item = getItem($_POST['rate_id']);

    echo json_encode($Item);
}

if (isset($_POST['updateItem'])) {

    $id = $_POST['id'];
    $rate = $_POST['rate'];
    $discount = $_POST['discount'];
    $date = $_POST['date'];

    echo updateItem($id, $rate, $discount, $date);
}

if (isset($_POST['createItem'])) {
    $rate = $_POST['rate'];
    $discount = $_POST['discount'];
    $date = $_POST['date'];

    echo createItem($rate, $discount, $date);
}



function toggleActivation($rate_id, $type)
{
    $sql = "UPDATE shop.dollarrate SET status = :type WHERE id = :rate_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':rate_id', $rate_id);
    $stmt->execute();
    echo true;
}

function getItem($rate_id)
{
    $sql = "SELECT * FROM shop.dollarrate WHERE id = :rate_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindValue(':rate_id', $rate_id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function updateItem($id, $rate, $discount, $date)
{
    $sql = "UPDATE shop.dollarrate SET rate = :rate, discount = :discount, created_at = :created_at WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);

    // Bind parameters to statement
    $stmt->bindValue(':rate', $rate, PDO::PARAM_STR);
    $stmt->bindValue(':discount', $discount, PDO::PARAM_STR);
    $stmt->bindValue(':created_at', $date, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    $result = $stmt->execute();

    return $result; // Returns true if update was successful, false otherwise

}


function createItem($rate, $discount, $date)
{
    $sql = "INSERT INTO shop.dollarrate (rate, discount, created_at) VALUES (:rate, :discount, :created_at)";
    $stmt = PDO_CONNECTION->prepare($sql);

    // Bind parameters to statement
    $stmt->bindValue(':rate', $rate, PDO::PARAM_STR);
    $stmt->bindValue(':discount', $discount, PDO::PARAM_STR);
    $stmt->bindValue(':created_at', $date, PDO::PARAM_STR);

    // Execute the statement
    $result = $stmt->execute();

    return $result; // Returns true if insertion was successful, false otherwise
}
