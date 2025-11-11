<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

function getCurrentUserPrintRole()
{
if (session_status() === PHP_SESSION_NONE) {
session_name('MyAppSession');
session_set_cookie_params(0, '/');
session_start();
}

if (empty($_SESSION['id'])) {
return false;
}

$userId = (int) $_SESSION['id'];

try {


$sql = "SELECT user_authorities AS auth
FROM yadakshop.authorities
WHERE user_id = :userId
LIMIT 1";

$stmt = PDO_CONNECTION->prepare($sql);
$stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
return false;
}

$auth = json_decode($result['auth'], true);

return (
!empty($auth['hamkarprint']) ||
!empty($auth['customerprint'])
);

} catch (PDOException $e) {
error_log("DB Error in getCurrentUserPrintRole: " . $e->getMessage());
return false;
}
}
