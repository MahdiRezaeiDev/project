<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

// START ------------------ THE SEARCHING FOR EXISTING CUSTOMER IN CUSTOMER LIST -----------------------------
if (isset($_POST['customer_search'])) {
    $pattern = $_POST['pattern'];
    echo json_encode(search_customer($pattern));
}

function search_customer($pattern)
{
    $name_family = explode(' ', $pattern);
    $name = $name_family[0] ?? '';
    $family = $name_family[1] ?? $name;

    if (isset($name_family[1])) {
        // both name and family provided
        $sql = "
            SELECT c.id, c.name, c.family, c.phone, c.address, c.car, COUNT(b.id) AS bill_count
            FROM callcenter.customer c
            LEFT JOIN factor.bill b ON b.customer_id = c.id
            WHERE c.name LIKE :name AND c.family LIKE :family
            GROUP BY c.id
            ORDER BY bill_count DESC
        ";
    } else {
        // only one input provided
        $sql = "
            SELECT c.id, c.name, c.family, c.phone, c.address, c.car, COUNT(b.id) AS bill_count
            FROM callcenter.customer c
            LEFT JOIN factor.bill b ON b.customer_id = c.id
            WHERE c.name LIKE :name OR c.family LIKE :family
            GROUP BY c.id
            ORDER BY bill_count DESC
        ";
    }

    $stmt = PDO_CONNECTION->prepare($sql);
    $likeName = '%' . $name . '%';
    $likeFamily = '%' . $family . '%';
    $stmt->bindParam(':name', $likeName, PDO::PARAM_STR);
    $stmt->bindParam(':family', $likeFamily, PDO::PARAM_STR);
    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }

    return $data;
}



// END ------------------ THE SEARCHING FOR EXISTING CUSTOMER IN CUSTOMER LIST -----------------------------




// START ------------------ SEARCH FOR GOODS BASE ON THE REGISTERED NISHA PART NUMBERS IN DATABASE -----------------------------
if (isset($_POST['partNumber'])) {
    $pattern = $_POST['partNumber'];
    echo json_encode(searchPartNumber($pattern));
}

function searchPartNumber($pattern)
{
    $sql = "SELECT * 
            FROM yadakshop.nisha
            WHERE partnumber LIKE :pattern";

    $stmt = PDO_CONNECTION->query($sql);

    $pattern = $pattern . "%";
    $stmt->bindParam(':pattern', $pattern);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
// END ------------------ SEARCH FOR GOODS BASE ON THE REGISTERED NISHA PART NUMBERS IN DATABASE -----------------------------




//START ------------------ SEARCH FOR GOODS BASE ON OUR EXISTING GOODS IN STOCK -----------------------------
if (isset($_POST['searchInStock'])) {
    $pattern = $_POST['searchInStock'];
    echo json_encode(searchPartNumberInStock($pattern));
}

function searchPartNumberInStock($pattern)
{
    global $stock;
    $sql = "SELECT
            qtybank.id AS id,
            nisha.id AS nisha_id,
            nisha.partnumber,
            stock.id AS stock_id,
            stock.name AS stock_name,
            seller.name AS seller_name,
            brand.name AS brand_name,
            qtybank.qty AS existing,
            qtybank.des AS description
        FROM
            $stock.qtybank
        LEFT JOIN
            yadakshop.nisha ON qtybank.codeid = nisha.id
        LEFT JOIN
            yadakshop.seller ON qtybank.seller = seller.id
        LEFT JOIN
            yadakshop.stock ON qtybank.stock_id = stock.id
        LEFT JOIN
            yadakshop.brand ON qtybank.brand = brand.id
        WHERE
            partnumber LIKE :pattern
        ORDER BY
            nisha.partnumber DESC";


    $stmt = PDO_CONNECTION->prepare($sql);
    $pattern = $pattern . "%";
    $stmt->bindParam(':pattern', $pattern);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sanitized = [];

    foreach ($data as $item) {
        $finalQuantity = $item["existing"];

        $sql2 = "SELECT qty 
                FROM $stock.exitrecord 
                WHERE qtyid = :id";

        $stmt2 = PDO_CONNECTION->prepare($sql2);
        $stmt2->bindParam(':id', $item['id']);
        $stmt2->execute();
        $result = $stmt2->fetchAll(PDO::FETCH_ASSOC);


        if (count($result) > 0) {
            foreach ($result as $row2) {
                $finalQuantity -= $row2["qty"];
            }
        }

        $item['existing'] = $finalQuantity;
        if ($finalQuantity > 0) {
            array_push($sanitized, $item);
        }
    }
    return $sanitized;
}
//END ------------------ SEARCH FOR GOODS BASE ON OUR EXISTING GOODS IN STOCK -----------------------------



//START ------------------ SEARCH IF THE CUSTOMER WITH THE PHONE NUMBER EXIST -----------------------------
if (isset($_POST['isPhoneExist'])) {
    $phone = $_POST['phone'];
    echo json_encode(checkPhoneNumber($phone));
}

function checkPhoneNumber($phone)
{
    $sql = "SELECT id , name, family, address, car 
            FROM callcenter.customer 
            WHERE phone = :phone ORDER BY id DESC LIMIT 1";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->closeCursor(); // Optional: If you need to explicitly close the cursor

    if ($result) {
        return $result;
    } else {
        return 0;
    }
}
//END ------------------ SEARCH IF THE CUSTOMER WITH THE PHONE NUMBER EXIST -----------------------------
