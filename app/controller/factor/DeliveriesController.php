<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$todayDeliveries = getDeliveries();
$customerDeliveries = getCustomerDeliveries();
$deliveries = getAllDeliveries();
$users = getAllUsers();

$yadakRemaining = getYadakShopNotReadyDeliveries();
$customerRemaining = getCustomerNotReadyDeliveries();

function getDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("
        SELECT d.*, 
               b.id as bill_id, 
               s.kharidar,
               bd.billDetails
        FROM factor.deliveries d
        INNER JOIN factor.bill b 
            ON d.bill_number = b.bill_number
        INNER JOIN factor.shomarefaktor s 
            ON b.bill_number = s.shomare
        LEFT JOIN factor.bill_details bd 
            ON bd.bill_id = b.id
        WHERE DATE(d.created_at) = CURDATE() 
          AND d.type = 'پیک یدک شاپ'
        ORDER BY d.created_at DESC
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['items_preview'] = [];
        if (!empty($row['billDetails'])) {
            $details = json_decode($row['billDetails'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($details)) {
                // Take up to 3 items only
                $preview = array_slice($details, 0, 3);
                foreach ($preview as $item) {
                    $row['items_preview'][] = [
                        'partName' => $item['partName'] ?? '',
                        'quantity' => $item['quantity'] ?? '',
                        'price'    => $item['price_per'] ?? ''
                    ];
                }
            }
        }
    }

    return $rows;
}

function getCustomerDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("
        SELECT d.*, b.id AS bill_id, s.kharidar, bd.billDetails
        FROM factor.deliveries d
        INNER JOIN factor.bill b ON d.bill_number = b.bill_number
        INNER JOIN factor.shomarefaktor s ON b.bill_number = s.shomare
        LEFT JOIN factor.bill_details bd ON b.id = bd.bill_id
        WHERE DATE(d.created_at) = CURDATE()
          AND d.type = 'پیک خود مشتری بعد از اطلاع'
        ORDER BY d.created_at DESC
    ");
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // decode bill_details JSON and keep up to 3 items
    foreach ($deliveries as &$delivery) {
        $items = [];
        if (!empty($delivery['billDetails'])) {
            $decoded = json_decode($delivery['billDetails'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = array_slice($decoded, 0, 3); // max 3 items
            }
        }
        $delivery['items_preview'] = $items;
    }

    return $deliveries;
}

function getAllDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("
        SELECT deliveries.*, bill.id as bill_id, shomarefaktor.kharidar 
        FROM factor.deliveries
        INNER JOIN factor.bill ON deliveries.bill_number = bill.bill_number
        INNER JOIN factor.shomarefaktor ON bill.bill_number = shomare
        WHERE DATE(deliveries.created_at) = CURDATE() 
          AND type != 'پیک خود مشتری بعد از اطلاع' 
          AND type != 'پیک یدک شاپ' 
        ORDER BY deliveries.created_at DESC
    ");
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach up to 3 items from bill_details
    foreach ($deliveries as &$delivery) {
        $stmtItems = PDO_CONNECTION->prepare("SELECT billDetails FROM factor.bill_details WHERE bill_id = :bill_id");
        $stmtItems->bindParam(':bill_id', $delivery['bill_id'], PDO::PARAM_INT);
        $stmtItems->execute();
        $result = $stmtItems->fetch(PDO::FETCH_ASSOC);

        $items = [];
        if ($result && !empty($result['billDetails'])) {
            $allItems = json_decode($result['billDetails'], true);
            if ($allItems) {
                $items = array_slice($allItems, 0, 3); // up to 3 items
            }
        }
        $delivery['items_preview'] = $items;
    }

    return $deliveries;
}

function getYadakShopNotReadyDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("
    SELECT d.*, 
           b.id as bill_id, 
           s.kharidar,
           bd.billDetails
    FROM factor.deliveries d
    INNER JOIN factor.bill b 
        ON d.bill_number = b.bill_number
    INNER JOIN factor.shomarefaktor s 
        ON b.bill_number = s.shomare
    LEFT JOIN factor.bill_details bd 
        ON bd.bill_id = b.id
    WHERE (
              (d.is_ready = 0 AND DATE(d.created_at) < CURDATE())
           OR (DATE(d.updated_at) = CURDATE() AND DATE(d.created_at) < CURDATE())
          )
      AND d.type = 'پیک یدک شاپ'
    ORDER BY d.created_at DESC ");

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['items_preview'] = [];
        if (!empty($row['billDetails'])) {
            $details = json_decode($row['billDetails'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($details)) {
                // Take up to 3 items only
                $preview = array_slice($details, 0, 3);
                foreach ($preview as $item) {
                    $row['items_preview'][] = [
                        'partName' => $item['partName'] ?? '',
                        'quantity' => $item['quantity'] ?? '',
                        'price'    => $item['price_per'] ?? ''
                    ];
                }
            }
        }
    }

    return $rows;
}

function getCustomerNotReadyDeliveries()
{
    $stmt = PDO_CONNECTION->prepare("
    SELECT d.*, 
           b.id as bill_id, 
           s.kharidar,
           bd.billDetails
    FROM factor.deliveries d
    INNER JOIN factor.bill b 
        ON d.bill_number = b.bill_number
    INNER JOIN factor.shomarefaktor s 
        ON b.bill_number = s.shomare
    LEFT JOIN factor.bill_details bd 
        ON bd.bill_id = b.id
    WHERE (
              (d.is_ready = 0 AND DATE(d.created_at) < CURDATE())
           OR (DATE(d.updated_at) = CURDATE() AND DATE(d.created_at) < CURDATE())
          )
      AND d.type = 'پیک خود مشتری بعد از اطلاع'
    ORDER BY d.created_at DESC ");

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['items_preview'] = [];
        if (!empty($row['billDetails'])) {
            $details = json_decode($row['billDetails'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($details)) {
                // Take up to 3 items only
                $preview = array_slice($details, 0, 3);
                foreach ($preview as $item) {
                    $row['items_preview'][] = [
                        'partName' => $item['partName'] ?? '',
                        'quantity' => $item['quantity'] ?? '',
                        'price'    => $item['price_per'] ?? ''
                    ];
                }
            }
        }
    }

    return $rows;
}

function getAllUsers()
{
    $stmt = PDO_CONNECTION->prepare("
    SELECT u.id, u.name, u.family
    FROM users u
    WHERE u.name != '' 
      AND u.username IS NOT NULL 
      AND u.password IS NOT NULL 
      AND u.password != ''
      AND EXISTS (
          SELECT 1 FROM factor.deliveries d WHERE d.user_id = u.id
      )");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
