<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

// This functions are related to the user management page
function getUsers()
{
    $users_sql = "SELECT users.id, name, family, username, access_token,roll , authorities.user_authorities AS auth FROM yadakshop.users AS users
                    INNER JOIN yadakshop.authorities AS authorities ON yadakshop.authorities.user_id = yadakshop.users.id
                    WHERE users.password IS NOT NULL AND users.password !=''";

    $stmt = PDO_CONNECTION->prepare($users_sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}
// This functions are related to the create user account page
$success = false;
$username_error = false;
$type_error = false;
$exist_file_error = false;

$authority = [
    "usersManagement" => false,
    "sell" => false,
    "purchase" => false,
    "sellsReport" => false,
    "purchaseReport" => false,
    "transferGoods" => false,
    "transferReport" => false,
    "requiredGoods" => false,
    "generalRequiredGoods" => false,
    "stockAdjustment" => false,
    //"telegramProcess" => false,
    "givePrice" => false,
    "priceRates" => false,
    "relationships" => false,
    "defineExchangeRate" => false,
    "telegramPartner" => false,
    "hamkarprint" => false,
    "customerprint" =>false,
    "readonly" =>false
    
];


function getAuthority($type)
{
    switch ($type) {
        case '1':
            return [
                "usersManagement" => false,
                "sell" => false,
                "purchase" => false,
                "sellsReport" => true,
                "purchaseReport" => true,
                "transferGoods" => false,
                "transferReport" => false,
                "requiredGoods" => false,
                "generalRequiredGoods" => false,
                "stockAdjustment" => false,
                //"telegramProcess" => false,
                "givePrice" => true,
                "priceRates" => false,
                "relationships" => false,
                "defineExchangeRate" => false,
                "telegramPartner" => true,
                "hamkarprint" => false,
                "customerprint" =>false,
                 "readonly" =>false
            ];
            break;
        case '2':
            return [
                "usersManagement" => false,
                "sell" => false,
                "purchase" => false,
                "sellsReport" => false,
                "purchaseReport" => false,
                "transferGoods" => false,
                "transferReport" => false,
                "requiredGoods" => false,
                "generalRequiredGoods" => false,
                "stockAdjustment" => false,
                "telegramProcess" => false,
                "givePrice" => true,
                "priceRates" => false,
                "relationships" => false,
                "defineExchangeRate" => false,
                "telegramPartner" => false,
                "hamkarprint" => false,
                "customerprint" =>false,
                 "readonly" =>false
            ];
            break;
        case '3':
            return [
                "usersManagement" => false,
                "sell" => true,
                "purchase" => true,
                "sellsReport" => false,
                "purchaseReport" => false,
                "transferGoods" => false,
                "transferReport" => true,
                "requiredGoods" => false,
                "generalRequiredGoods" => true,
                "stockAdjustment" => true,
                //"telegramProcess" => false,
                "givePrice" => true,
                "priceRates" => false,
                "relationships" => false,
                "defineExchangeRate" => false,
                "telegramPartner" => false,
                "hamkarprint" => false,
                "customerprint" =>false,
               "readonly" =>false
            ];
            break;
        case '4':
            return [
                "usersManagement" => true,
                "sell" => true,
                "purchase" => true,
                "sellsReport" => true,
                "purchaseReport" => true,
                "transferGoods" => true,
                "transferReport" => true,
                "requiredGoods" => true,
                "generalRequiredGoods" => true,
                "stockAdjustment" => true,
                //"telegramProcess" => true,
                "givePrice" => true,
                "priceRates" => false,
                "relationships" => false,
                "defineExchangeRate" => false,
                "telegramPartner" => false,
                "hamkarprint" => false,
                "customerprint" =>false,
                "readonly" =>false
            ];
            break;
        case '5':
            return [
                "usersManagement" => true,
                "sell" => true,
                "purchase" => true,
                "sellsReport" => true,
                "purchaseReport" => true,
                "transferGoods" => true,
                "transferReport" => true,
                "requiredGoods" => true,
                "generalRequiredGoods" => true,
                "stockAdjustment" => true,
                //"telegramProcess" => true,
                "givePrice" => true,
                "priceRates" => true,
                "relationships" => true,
                "defineExchangeRate" => true,
                "telegramPartner" => true,
                "hamkarprint" => true,
                "customerprint" =>true,
                "readonly" =>false
            ];
            break;
    }
}

if (isset($_POST['createUser'])) {
    $name = trim($_POST['name']) ?? '';
    $family = trim($_POST['family']) ?? '';
    $username = strtolower(trim($_POST['username']));
    $password = trim($_POST['password']);
    $type = $_POST['type'];
    $authority = getAuthority($type);
    $hash_pass = password_hash($password, PASSWORD_DEFAULT);

    $errors = array();

    try {
        $result = false;
        PDO_CONNECTION->beginTransaction();
        try {
            $sql = "INSERT INTO yadakshop.users (username, password, roll, internal, ip, name, family, isLogin) 
                    VALUES (:username, :password, :roll, '', '', :name, :family, '0')";
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hash_pass);
              $stmt->bindParam(':roll', $type);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':family', $family);
            $result = $stmt->execute();
        } catch (\Throwable $th) {
            $username_error = true;
        }

        if ($result === true) {
            $last_id = PDO_CONNECTION->lastInsertId();
            // Convert the array to a JSON string
            $userAuthoritiesJson = json_encode($authority);
            $authority_sql = "INSERT INTO yadakshop.authorities (user_id, user_authorities, modified) 
                                VALUES (:user_id, :authority, 0)";
            $stmt = PDO_CONNECTION->prepare($authority_sql);
            $stmt->bindParam(':user_id', $last_id);
            $stmt->bindParam(':authority', $userAuthoritiesJson);
            $stmt->execute();

            $sql = "INSERT INTO attendance_settings (user_id, start_hour, end_hour, end_week, max_late_minutes, is_active )
                    VALUES (:user_id, '09:00', '18:00', '14:00', '0', 1)";
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':user_id', $last_id);
            $stmt->execute();

            if ($_FILES['profile']['size'] > 0) {
                if (uploadFile($last_id, $_FILES['profile'])) {
                    $success = true;
                }
            } else {
                $success = true;
            }
        }
        PDO_CONNECTION->commit();
    } catch (\Throwable $th) {
        throw $th;
    }
}

function uploadFile($last_id, $file)
{
    try {
        $allowed = ['jpg'];
        $type = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!in_array($type, $allowed)) {
            print_r('عکس مورد نظر باید دارای فورمت jpg باشد.');
        } else {
            $targetDirectory = "../../public/userimg/"; // Directory where you want to store the uploaded files
            $targetFile = $targetDirectory . $last_id . "." . $type;

            // Attempt to move the uploaded file
            if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
                $GLOBALS['upload_error'] = true;
            }
        }
    } catch (\Throwable $th) {
        // Log the error or handle it according to your needs
        error_log($th->getMessage());
        throw $th;
    }
}

if (isset($_POST['id']) && !empty($_POST['username'])) {
    $name = trim($_POST['name']);
    $family = trim($_POST['family']);
    $username = strtolower(trim($_POST['username']));
    $password = trim($_POST['password']);
    $type = $_POST['type'];
    $id = $_POST['id'];
    $roll = $type;
    $authority = getAuthority($type);

    $hash_pass = password_hash($password, PASSWORD_DEFAULT);
    try {
        $result = false;
        PDO_CONNECTION->beginTransaction();
        try {
            if (!empty($password)) {
                $sql = "UPDATE yadakshop.users SET username = :username, password = :password, roll = :roll,
                        name = :name, family = :family WHERE id = :id";
                $stmt = PDO_CONNECTION->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hash_pass);
                $stmt->bindParam(':roll', $roll);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':family', $family);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            } else {
                $sql = "UPDATE yadakshop.users SET username = :username, roll = :roll,
                        name = :name, family = :family WHERE id = :id";
                $stmt = PDO_CONNECTION->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':roll', $roll);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':family', $family);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }
            if ($_FILES['profile']['size'] > 0) {
                if (uploadFile($id, $_FILES['profile'])) {
                    $success = true;
                }
            } else {
                $success = true;
            }
            $result = true;
        } catch (\Throwable $th) {
            echo $th;
        }

        $sql = "UPDATE yadakshop.authorities SET user_authorities = :authority WHERE user_id = :id";
        $stmt = PDO_CONNECTION->prepare($sql);
        $authority = json_encode($authority);
        $stmt->bindParam(':authority', $authority);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($result === true) {
            isset($_FILES['profile']) ?? uploadFile($last_id, $_FILES['profile']);
            $success = true;
        }
        PDO_CONNECTION->commit();
    } catch (\Throwable $th) {
        throw $th;
    }
}

// This functions are related to the update user account page
if (isset($_GET['user'])) {
    $user_id = $_GET['user'];
    $user = getUser($user_id);
    $success = $_GET['success'] ?? false;
}

function getUser(int $id)
{
    $sql = "SELECT * FROM yadakshop.users WHERE id = :user_id LIMIT 1";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->execute(['user_id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
