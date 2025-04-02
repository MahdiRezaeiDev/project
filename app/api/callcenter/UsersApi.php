<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

// Check is the request is valid and submitted operation type
if (isset($_POST['operation']) and $_POST['operation'] == 'update') :

    try {
        $user = $_POST['user'] ?? 0;
        $data = $_POST['data'] ?? null;

        updateUserAuthorityList($user, $data);
    } catch (\Throwable $th) {
        return $th;
    }
endif;


function updateUserAuthorityList($id, $data)
{
    $stmt = PDO_CONNECTION->prepare("UPDATE yadakshop.authorities SET user_authorities= :data , modified = 1
                                    WHERE user_id = :id");
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return true;
}
