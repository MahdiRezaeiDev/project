<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

// Get the factor ID from the POST request
if (isset($_POST['submitDelivery'])) {
    $billNumber   = isset($_POST['billNumber']) ? intval($_POST['billNumber']) : 0;
    $contactType  = isset($_POST['contactType']) ? $_POST['contactType'] : '';
    $address      = isset($_POST['address']) ? $_POST['address'] : '';
    $deliveryType = isset($_POST['deliveryType']) ? $_POST['deliveryType'] : '';
    $user_id      = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;

    // Make sure bill_number is UNIQUE in DB for ON DUPLICATE to work
    $sql = "INSERT INTO factor.deliveries (bill_number, contact_type, destination, type, user_id)
            VALUES (:bill_number, :contact_type, :destination, :type, :user_id)
            ON DUPLICATE KEY UPDATE 
                contact_type = VALUES(contact_type),
                destination  = VALUES(destination),
                type         = VALUES(type),
                user_id      = VALUES(user_id)";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':bill_number', $billNumber, PDO::PARAM_INT);
    $stmt->bindParam(':contact_type', $contactType, PDO::PARAM_STR);
    $stmt->bindParam(':destination', $address, PDO::PARAM_STR);
    $stmt->bindParam(':type', $deliveryType, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Delivery details saved/updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save/update delivery details.']);
    }
}

if (isset($_POST['deleteDelivery'])) {
    $billNumber = isset($_POST['billNumber']) ? intval($_POST['billNumber']) : 0;
    $sql = "DELETE FROM factor.deliveries WHERE bill_number = :bill_number";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':bill_number', $billNumber, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Delivery deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete delivery.']);
    }
}

if (isset($_POST['getPreviousDeliveries'])) {
    $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');

    $stmt = PDO_CONNECTION->prepare("SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar FROM factor.deliveries
    INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
    INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare
    WHERE DATE(deliveries.created_at) = :date AND type = 'پیک یدک شاپ'
    ORDER BY deliveries.created_at DESC");
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $YadakDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = PDO_CONNECTION->prepare("SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar FROM factor.deliveries
    INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
    INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare
    WHERE DATE(deliveries.created_at) = :date AND type = 'پیک خود مشتری بعد از اطلاع'
    ORDER BY deliveries.created_at DESC");
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $customerDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = PDO_CONNECTION->prepare("SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar FROM factor.deliveries
    INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
    INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare
    WHERE DATE(deliveries.created_at) = :date AND type != 'پیک خود مشتری بعد از اطلاع' AND type !=  'پیک یدک شاپ'
    ORDER BY deliveries.created_at DESC");
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $allDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($YadakDeliveries) {
        echo json_encode([
            'status' => 'success',
            'yadakDeliveries' => $YadakDeliveries,
            'customerDeliveries' => $customerDeliveries,
            'allDeliveries' => $allDeliveries
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No deliveries found for this date.']);
    }
}
