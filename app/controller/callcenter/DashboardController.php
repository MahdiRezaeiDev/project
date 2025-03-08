<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}


function getCallCenterUsers()
{
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM yadakshop.users WHERE internal ORDER BY internal");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
