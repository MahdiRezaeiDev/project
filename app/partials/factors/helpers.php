<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}
/**
 * Get the factors from database for specific time period
 * @param string $start is the starting time period
 * @param string $end is the ending time period
 */
function getFactors($start, $end, $user = null)
{
    $query = "SELECT
    shomarefaktor.*,
    bill.id AS bill_id,
    bill.printed,
    bill.partner,
    CASE 
        WHEN bill.bill_number IS NOT NULL THEN TRUE 
        ELSE FALSE 
    END AS exists_in_bill,
    
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM callcenter.phones p 
            WHERE p.customer_id = bill.customer_id
        ) THEN TRUE 
        ELSE FALSE 
    END AS exists_in_phones,

    bill.total,
    bill.partner AS isPartner

FROM
    factor.shomarefaktor
LEFT JOIN
    factor.bill ON shomarefaktor.shomare = bill.bill_number
WHERE
    shomarefaktor.time < '$end' 
    AND shomarefaktor.time >= '$start' 
";

    if ($user !== null) {
        $query .= " AND shomarefaktor.user = '$user'";
    }

    $query .= " ORDER BY shomarefaktor.shomare DESC";
    $statement = PDO_CONNECTION->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function getCountFactorByUser($start, $end = null, $user = null)
{
    // Base query
    $sql = "SELECT COUNT(shomare) as count_shomare, user FROM factor.shomarefaktor 
            WHERE time < '$end' AND time >= '$start'";
    if ($user !== null) {
        $sql .= " AND user = '$user'";
    }

    $sql .= " GROUP BY user ORDER BY count_shomare DESC";

    // Append the WHERE clause based on the condition
    if ($end !== null) {
        $sql .= " ";
    } else {
        $sql .= " WHERE time >= CURDATE()";
    }

    // Prepare and execute the query
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->execute();

    // Fetch the result
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Return the result
    return $result;
}

function getUserProfile($user_id, $append = "")
{
    $profile = $append . "../../public/userimg/$user_id.jpg";
    if (!file_exists($profile)) :
        $profile = '../../public/userimg/default.png';
    else :
        $profile = "../../public/userimg/$user_id.jpg";
    endif;
    return $profile;
}

function getUserInfo($user_id)
{
    $stmt = PDO_CONNECTION->prepare("SELECT username, name, family FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['name'] . ' ' . $result['family'];
}

function getRankingBadge($ranking)
{
    $badge = '';
    switch ($ranking) {
        case 1:
            $badge = 'golden-star.svg';
            break;
        case 2:
            $badge = 'silver-star.svg';
            break;
        case 3:
            $badge = 'bronze-star.svg';
            break;
        default:
            $badge = 'black-star.svg';
            break;
    }
    return $badge;
}

function displayAsMoney($inputInstance)
{
    // Convert input to string
    $inputInstance = (string) $inputInstance;

    // Remove non-digit characters
    $originalValue = preg_replace("/\D/", "", $inputInstance);

    // Remove leading zeros
    $originalValue = ltrim($originalValue, '0');

    // Insert commas every three digits
    $formattedValue = preg_replace("/\B(?=(\d{3})+(?!\d))/", ",", $originalValue);

    // Return formatted value with "ریال" or an empty string if no value
    return $formattedValue ? $formattedValue . " ریال" : '';
}
