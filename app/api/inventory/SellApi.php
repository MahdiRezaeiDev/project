<?php
    // Check if the request is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../../../views/auth/403.php");
        exit;
    }
    require_once '../../../config/constants.php';
    require_once '../../../database/db_connect.php';
    require_once '../../../utilities/inventory/ExistingHelper.php';

    if (isset($_POST['searchGoods'])) {
        $pattern = trim($_POST['pattern']);
        $purchasedGoods = getPurchaseReport($pattern);

        echo json_encode($purchasedGoods);
    }

    if (isset($_POST['getAllowedBrands'])) {
        $brands = trim($_POST['brands']);
        $purchasedGoods = getAllowedBrands($brands);
        echo "Hello";

        echo json_encode($purchasedGoods);
    }

    function getAllowedBrands($brands)
    {
        // Map of brands to their related brands
        $brandAssociations = [
            'HI Q' => ['HIQ', 'HI'],
            'MOB' => ['MOB', 'GEN'],
            'GEN' => ['MOB', 'GEN'],
            'OEMAX' => ['CHINA'],
            'JYR' => ['CHINA'],
            'RB2' => ['CHINA'],
            'Rb2' => ['CHINA'], // Added (case-sensitive key)
            'IRAN' => ['CHINA'],
            'FAKE MOB' => ['CHINA', 'KOREA'], // Merged
            'FAKE GEN' => ['CHINA', 'KOREA'], // Merged
            'OE MAX' => ['CHINA'], // Added
            'MAXFIT' => ['CHINA'], // Added (note: no trailing space)
            'ICBRI' => ['CHINA'], // Added
            'HOH' => ['CHINA'], // Added
            'DOOWON' => ['HCC', 'HANON', 'DOOWON'],
            'HANON' => ['HCC', 'HANON', 'DOOWON'],
            'HCC' => ['HCC', 'HANON', 'DOOWON'],

            // KOREA
            'YONG' => ['KOREA'],
            'YONG HOO' => ['KOREA'],
            'OEM' => ['KOREA'],
            'ONNURI' => ['KOREA'],
            'GY' => ['KOREA'],
            'MIDO' => ['KOREA'],
            'MIRE' => ['KOREA'],
            'CARDEX' => ['KOREA'],
            'MANDO' => ['KOREA'],
            'OSUNG' => ['KOREA'],
            'DONGNAM' => ['KOREA'],
            'HYUNDAI BRAKE' => ['KOREA'],
            'SAM YUNG' => ['KOREA'],
            'BRC' => ['KOREA'],
            'GEO SUNG' => ['KOREA'],
            'YULIM' => ['KOREA'],
            'CARTECH' => ['KOREA'],
            'HSC' => ['KOREA'],
            'KOREA STAR' => ['KOREA'],
            'DONI TEC' => ['KOREA'], // Added
            'ATC' => ['KOREA'], // Added
            'VALEO' => ['KOREA'], // Added
            'MB KOREA' => ['KOREA'], // Added
            'IACE' => ['KOREA'], // Added
            'MB' => ['KOREA'], // Added
            'PH' => ['KOREA'], // Added
            'CAP' => ['KOREA'], // Added
            'BRG' => ['KOREA'], // Added
            'GMB' => ['KOREA'], // Added
            'KGC' => ['KOREA'], // Added
            'GATES' => ['KOREA'], // Added
            'KOART' => ['KOREA'], // Added
            'SAEHAN' => ['KOREA'], // Added
            'FORCEONE' => ['KOREA'], // Added
            'DAEWHA' => ['KOREA'], // Added
            'AUTOFIX' => ['KOREA'], // Added
        ];


        // Normalize brand names to uppercase
        $brands = array_map('strtoupper', $brands);
        $brands = array_map('trim', $brands);

        foreach ($brands as $brand) {
            if (isset($brandAssociations[$brand])) {
                $brands = array_merge($brands, $brandAssociations[$brand]);
            }
        }

        // Remove duplicates and return the result
        return array_unique($brands);
    }

    if (isset($_POST['saveFactor'])) {
        $billItems = json_decode($_POST['billItems'], true);
        $factorInfo = json_decode($_POST['factorInfo'], true);
        global $stock;

        try {
            $bill_number = $factorInfo['number'];
            PDO_CONNECTION->beginTransaction();
            // Insert the main factor information into the database
            $sql = "INSERT INTO {$stock}.track_bills (bill_number)
                VALUES (:bill_number)";
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':bill_number', $bill_number);
            $stmt->execute();


            foreach ($billItems as $index => $item) {
                insertSellsRecord($factorInfo, $item, $stock);
            }
            PDO_CONNECTION->commit();
            echo 'success';
        } catch (PDOException $e) {
            PDO_CONNECTION->rollBack();
            echo 'error';
        }
    }

    function insertSellsRecord($factorInfo, $item, $stock)
    {
        // Extract relevant data from $factorInfo
        $customer = $factorInfo['client'];
        $getter = $factorInfo['receiver'];
        $qty = $item['quantity'];
        $qty_id = $item['quantityId'];
        $id = $factorInfo['user'];
        $invoice_number = $factorInfo['number'];
        $description = $factorInfo['description'];
        $collector = $factorInfo['collector'];
        $invoice_time = $factorInfo['date'];

        // Prepare the SQL statement
        $sql = "INSERT INTO {$stock}.exitrecord (customer, getter, qty, qtyid, user, invoice_number, des, jamkon, invoice_date)
            VALUES (:customer, :getter, :qty, :qty_id, :id, :invoice_number, :description, :collector, :invoice_time)";

        try {
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':customer', $customer);
            $stmt->bindParam(':getter', $getter);
            $stmt->bindParam(':qty', $qty);
            $stmt->bindParam(':qty_id', $qty_id);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':invoice_number', $invoice_number);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':collector', $collector);
            $stmt->bindParam(':invoice_time', $invoice_time);

            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    if (isset($_POST['getClientName'])) {
        $factorNo = $_POST['factorNo'];
        $purchasedGoods = getClientName($factorNo);
        echo $purchasedGoods;
    }

    function getClientName($factorNO)
    {
        $sql = "SELECT kharidar FROM factor.shomarefaktor WHERE shomare = :shomarefaktor LIMIT 1";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':shomarefaktor', $factorNO);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['kharidar'];
        } else {
            return false;
        }
    }
