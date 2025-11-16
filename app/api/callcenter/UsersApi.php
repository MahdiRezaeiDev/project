<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "POST ONLY"]);
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

header('Content-Type: application/json');

$operation = $_POST['operation'] ?? '';
$user      = intval($_POST['user'] ?? 0);

if (!$user) {
    echo json_encode(["success" => false, "message" => "user id is missing"]);
    exit;
}

try {

    switch ($operation) {

        // ========================================
        //      UPDATE ROLE
        // ========================================
        case 'updateRole':
            $role = $_POST['role'] ?? null;

            if ($role === null) {
                echo json_encode(["success" => false, "message" => "role is missing"]);
                exit;
            }

            $stmt = PDO_CONNECTION->prepare(
                "UPDATE yadakshop.users SET roll = :role WHERE id = :id"
            );

            $stmt->bindValue(':role', $role, PDO::PARAM_STR);
            $stmt->bindValue(':id', $user, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode([
                "success" => true,
                "affectedRows" => $stmt->rowCount()
            ]);
            exit;


        // ========================================
        //      UPDATE AUTHORITY LIST
        // ========================================
        case 'update':
            $data = $_POST['data'] ?? null;

            if ($data === null) {
                echo json_encode(["success" => false, "message" => "data is missing"]);
                exit;
            }

      
            if (!function_exists('updateUserAuthorityList')) {
                echo json_encode(["success" => false, "message" => "Function updateUserAuthorityList not found"]);
                exit;
            }

            updateUserAuthorityList($user, $data);

            echo json_encode(["success" => true]);
            exit;


        // ========================================
        //      DEFAULT
        // ========================================
        default:
            echo json_encode(["success" => false, "message" => "Invalid operation"]);
            exit;
    }

} catch (\Throwable $th) {
    echo json_encode([
        "success" => false,
        "message" => $th->getMessage()
    ]);
    exit;
}



function updateUserAuthorityList($userId, $data)
{
    if (!$data) return false;

    $dataArray = json_decode($data, true);

    if (isset($dataArray['role'])) {
        $stmt = PDO_CONNECTION->prepare("UPDATE yadakshop.users SET roll = :roll WHERE id = :id");
        $stmt->execute([
            ':roll' => $dataArray['role'],
            ':id' => $userId
        ]);
        unset($dataArray['role']); 
    }

    $stmt = PDO_CONNECTION->prepare("UPDATE yadakshop.authorities SET user_authorities = :data, modified = 1 WHERE user_id = :id");
    $stmt->execute([
        ':data' => json_encode($dataArray),
        ':id' => $userId
    ]);

    echo json_encode(['success' => true]);
}
