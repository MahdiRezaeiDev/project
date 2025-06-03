<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

$lastHourMostRequested = getLastHourMostRequested(); //
$todayMostRequested = getTodayMostRequested(); //
$allTimeMostRequested = getAllTimeMostRequested();

function getLastHourMostRequested()
{
    $sql = "SELECT partNumber, COUNT(id) AS quantity
            FROM shop.searches 
            WHERE created_at >= NOW() - INTERVAL 1 HOUR 
            GROUP BY partNumber
            ORDER BY quantity DESC LIMIT 10";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function getTodayMostRequested()
{
    // SQL query to count the number of requests for the current date, grouped by the request
    $sql = "SELECT partNumber, COUNT(id) AS quantity 
            FROM shop.searches 
            WHERE DATE(created_at) = CURDATE() 
            GROUP BY partNumber
            ORDER BY quantity DESC LIMIT 10"; // Order by quantity to get the most requested items at the top

    // Prepare the SQL statement
    $stmt = PDO_CONNECTION->prepare($sql);

    // Execute the SQL statement
    $stmt->execute();

    // Fetch all results as associative arrays
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the result
    return $result;
}

function getAllTimeMostRequested()
{
    // SQL query to count the number of requests for all time, grouped by the request
    $sql = "SELECT partNumber, COUNT(id) AS quantity 
            FROM shop.searches 
            GROUP BY partNumber
            ORDER BY quantity DESC LIMIT 10"; // Order by quantity to get the most requested items at the top

    // Prepare the SQL statement
    $stmt = PDO_CONNECTION->prepare($sql);

    // Execute the SQL statement
    $stmt->execute();

    // Fetch all results as associative arrays
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the result
    return $result;
}
