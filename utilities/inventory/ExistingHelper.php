<?php
function getPurchaseReportById($id)
{
    global $stock;

    // Construct the base SQL query with LEFT JOIN to exitrecord and necessary calculations
    $sql = "SELECT nisha.partnumber AS partNumber, stock.name AS stockName, stock.id AS stockId,
                nisha.id AS goodId, seller.id AS seller_id, seller.name AS sellerName, brand.id AS brandId, brand.name AS brandName,
                qtybank.des AS quantityDescription, qtybank.id AS quantityId,
                qtybank.qty AS quantity,
                qtybank.is_transfered,
                qtybank.pos1,
                qtybank.pos2,
                IFNULL(SUM(exitrecord.qty), 0) AS total_sold,
                qtybank.qty - IFNULL(SUM(exitrecord.qty), 0) AS remaining_qty
            FROM $stock.qtybank
            LEFT JOIN nisha ON qtybank.codeid = nisha.id
            LEFT JOIN seller ON qtybank.seller = seller.id
            LEFT JOIN stock ON qtybank.stock_id = stock.id
            INNER JOIN brand ON qtybank.brand = brand.id
            LEFT JOIN $stock.exitrecord ON qtybank.id = exitrecord.qtyid
            WHERE qtybank.id = :id
            GROUP BY qtybank.id
            HAVING remaining_qty > 0";

    try {
        // Prepare the statement
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Fetch the results
        $purchasedGoods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $purchasedGoods;
    } catch (PDOException $e) {
        // Handle any errors
        echo 'Error: ' . $e->getMessage();
        return [];
    }
}

function getPurchaseReport($pattern = null)
{
    global $stock;

    // SQL query to compute remaining quantity accurately
    $sql = "SELECT 
                nisha.partnumber AS partNumber, 
                stock.name AS stockName, 
                stock.id AS stockId,
                nisha.id AS goodId, 
                seller.id AS seller_id, 
                seller.name AS sellerName, 
                brand.id AS brandId, 
                brand.name AS brandName,
                qtybank.des AS quantityDescription, 
                qtybank.id AS quantityId,
                qtybank.qty AS quantity,
                qtybank.is_transfered,
                qtybank.user AS insertedBy,
                qtybank.deliverer AS deliverer,
                qtybank.anbarenter AS anbarenter,
                qtybank.invoice AS invoice_qty,
                qtybank.invoice_number AS invoice_number,
                qtybank.invoice_date AS qty_date,
                qtybank.is_transfered AS qty_transfer,
                qtybank.pos1,
                qtybank.pos2,
                IFNULL(SUM(exitrecord.qty), 0) AS total_sold,
                (qtybank.qty - IFNULL(SUM(exitrecord.qty), 0)) AS remaining_qty
            FROM $stock.qtybank
            LEFT JOIN nisha ON qtybank.codeid = nisha.id
            LEFT JOIN seller ON qtybank.seller = seller.id
            LEFT JOIN stock ON qtybank.stock_id = stock.id
            INNER JOIN brand ON qtybank.brand = brand.id
            LEFT JOIN $stock.exitrecord ON qtybank.id = exitrecord.qtyid 
            WHERE qtybank.sold_out = 0 ";

    // Apply search filter if provided
    if ($pattern) {
        $sql .= " AND nisha.partnumber LIKE :pattern";
    }

    // Ensure proper grouping to avoid duplication of sums
    $sql .= " GROUP BY qtybank.id";

    // Only include items with remaining stock
    $sql .= " HAVING remaining_qty > 0 ORDER BY nisha.partnumber DESC";

    try {
        $stmt = PDO_CONNECTION->prepare($sql);

        // Bind pattern parameter if applicable
        if ($pattern) {
            $stmt->bindValue(':pattern', '%' . $pattern . '%', PDO::PARAM_STR);
        }

        // Execute query
        $stmt->execute();
        $purchasedGoods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $purchasedGoods;
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
        return [];
    }
}

function insertIntoNewQtyBank($data)
{
    try {
        global $pdo; // Assuming $pdo is your PDO database connection
        global $stock; // Assuming $pdo is your PDO database connection

        // Prepare the SQL query with placeholders
        $insertQuery = "INSERT INTO $stock.newqtybank 
                        (codeid, brand, des, qty, pos1, pos2, create_time, seller, deliverer, invoice, anbarenter, user, invoice_number, stock_id, invoice_date, is_transfered, sold_out) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($insertQuery);

        // Loop through each row and insert
        foreach ($data as $row) {
            $stmt->execute([
                $row['goodId'],              // codeid (matches goodId)
                $row['brandId'],             // brand
                $row['quantityDescription'], // des
                $row['remaining_qty'],  // qty
                $row['pos1'],           // pos1
                $row['pos2'],           // pos2
                date('Y-m-d H:i:s'),    // create_time (current timestamp)
                $row['seller_id'],       // seller
                $row['deliverer'],       // deliverer (if not available)
                $row['invoice_qty'],         // invoice (if not available)
                $row['anbarenter'],      // anbarenter (if not available)
                $row['insertedBy'],      // user (if not available)
                $row['invoice_number'],  // invoice_number (if not available)
                $row['stockId'],         // stock_id
                $row['qty_date'],        // invoice_date (current date)
                $row['qty_transfer'],    // is_transfered (default: 0)
                0                        // sold_out (default: 0)
            ]);
        }

        echo "Data inserted successfully!";
    } catch (Exception $e) {
        echo "Insert Error: " . $e->getMessage();
    }
}
