<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

function getAskedPrices()
{
    $sql = "SELECT 
                e.id,
                e.product_code,
                e.time,
                e.price,
                e.status,
                u.id AS user_id,
                u.name,
                u.family,
                u.username,
                s.name AS seller_name
            FROM callcenter.estelam AS e
            JOIN yadakshop.users AS u ON e.user = u.id
            JOIN yadakshop.seller AS s ON e.seller = s.id
            ORDER BY 
                e.time DESC,
                CASE 
                    WHEN e.price REGEXP '^[0-9]+$' THEN 0
                    ELSE 1
                END,
                CAST(e.price AS UNSIGNED) ASC
            LIMIT 300";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
