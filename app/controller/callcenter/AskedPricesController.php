<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

function getAskedPrices()
{
    $sql = "SELECT estelam.*, users.id As user_id,
            users.name, users.family, users.username, seller.name AS seller_name
            FROM callcenter.estelam
            JOIN yadakshop.users ON estelam.user = users.id
            JOIN yadakshop.seller ON estelam.seller = seller.id
            ORDER BY estelam.time DESC
            LIMIT 300";

    // Prepare the statement
    $stmt = PDO_CONNECTION->prepare($sql);

    // Execute the query
    $stmt->execute();

    // Fetch the results
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
