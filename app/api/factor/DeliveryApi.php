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
    $user = isset($_POST['user']) ? intval($_POST['user']) : null;

    function getDeliveries($date, $user, $typeCondition)
    {
        $sql = "SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar 
                FROM factor.deliveries
                INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
                INNER JOIN factor.shomarefaktor ON bill.bill_number = shomarefaktor.shomare
                WHERE DATE(deliveries.created_at) = :date 
                  AND $typeCondition";

        if ($user) {
            $sql .= " AND deliveries.user_id = :user";
        }

        $sql .= " ORDER BY deliveries.created_at DESC";

        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':date', $date);
        if ($user) {
            $stmt->bindParam(':user', $user, PDO::PARAM_INT);
        }
        $stmt->execute();
        $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch items from bill_details table and attach preview
        foreach ($deliveries as &$delivery) {
            $stmtItems = PDO_CONNECTION->prepare("SELECT billDetails FROM factor.bill_details WHERE bill_id = :bill_id");
            $stmtItems->bindParam(':bill_id', $delivery['bill_id'], PDO::PARAM_INT);
            $stmtItems->execute();
            $result = $stmtItems->fetch(PDO::FETCH_ASSOC);

            $items = [];
            if ($result && !empty($result['billDetails'])) {
                $allItems = json_decode($result['billDetails'], true);
                if ($allItems) {
                    $items = array_slice($allItems, 0, 3); // take up to 3 items
                }
            }
            $delivery['items_preview'] = $items;
        }

        return $deliveries;
    }

    $YadakDeliveries = getDeliveries($date, $user, "type = 'پیک یدک شاپ'");
    $customerDeliveries = getDeliveries($date, $user, "type = 'پیک خود مشتری بعد از اطلاع'");
    $allDeliveries = getDeliveries($date, $user, "type != 'پیک خود مشتری بعد از اطلاع' AND type != 'پیک یدک شاپ'");

    echo json_encode([
        'status' => 'success',
        'yadakDeliveries' => $YadakDeliveries,
        'customerDeliveries' => $customerDeliveries,
        'allDeliveries' => $allDeliveries
    ]);
}

if (isset($_POST['toggleStatus'])) {
    $is_ready = $_POST['status'];
    $id = $_POST['delivery'];
    $approved_by = $_POST['approved_by'];

    if ($is_ready) {
        $is_ready = $approved_by;
    }

    $stmt = PDO_CONNECTION->prepare("
    UPDATE factor.deliveries
    SET is_ready = :is_ready,
        updated_at = NOW()
    WHERE id = :id");
    $stmt->bindParam(':is_ready', $is_ready);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

if (isset($_POST['saveDelivery'])) {


    PDO_CONNECTION->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $billNumber   = isset($_POST['billNumber']) ? intval($_POST['billNumber']) : 0;
    $contactType  = isset($_POST['contactType']) ? trim($_POST['contactType']) : '';
    $address      = isset($_POST['address']) ? trim($_POST['address']) : '';
    $deliveryType = isset($_POST['deliveryType']) ? trim($_POST['deliveryType']) : '';
    $deliveryCost = isset($_POST['deliverycost']) ? floatval($_POST['deliverycost']) : null;
    $courierName  = isset($_POST['courier_name']) ? trim($_POST['courier_name']) : '';
    $description  = isset($_POST['description']) ? trim($_POST['description']) : '';
    $peymentother = isset($_POST['peymentother']) ? trim($_POST['peymentother']) : '';
    
    $needCall     = (isset($_POST['need_call']) && $_POST['need_call'] === 'YES') ? 'YES' : 'NO';
    $user_id      = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;

    if (empty($billNumber)) {
        echo json_encode(['status' => 'error', 'message' => 'شماره فاکتور ارسال نشده است.']);
        exit;
    }

    $sql = "INSERT INTO factor.deliveries 
            (bill_number, contact_type, destination, type, delivery_cost, courier_name, description, need_call, user_id,peymentother)
            VALUES 
            (:bill_number, :contact_type, :destination, :type, :delivery_cost, :courier_name, :description, :need_call, :user_id,:peymentother)
            ON DUPLICATE KEY UPDATE
            contact_type  = VALUES(contact_type),
            destination   = VALUES(destination),
            type          = VALUES(type),
            delivery_cost = VALUES(delivery_cost),
            courier_name  = VALUES(courier_name),
            description   = VALUES(description),
            need_call     = VALUES(need_call),
            user_id       = VALUES(user_id),
            peymentother    = VALUES(peymentother),
            updated_at    = NOW()";

    try {
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':bill_number', $billNumber, PDO::PARAM_INT);
        $stmt->bindParam(':contact_type', $contactType, PDO::PARAM_STR);
        $stmt->bindParam(':destination', $address, PDO::PARAM_STR);
        $stmt->bindParam(':type', $deliveryType, PDO::PARAM_STR);
        $stmt->bindParam(':delivery_cost', $deliveryCost, PDO::PARAM_STR);
        $stmt->bindParam(':courier_name', $courierName, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':need_call', $needCall, PDO::PARAM_STR);
        $stmt->bindParam(':peymentother', $peymentother, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'اطلاعات ارسال با موفقیت ثبت یا به‌روزرسانی شد.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
