<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['operation'])) {

    $chat_id = $_POST['chat_id'];
    $name = $_POST["name"];
    $username = $_POST["username"];
    $profile = $_POST["profile"];
    $data = json_decode($_POST['data'], true);

    $data = array_filter($data, function ($row) {
        if ($row == 1) {
            return $row;
        }
    });

    if ($_POST['operation'] == 'update') {
        if (count($data) == 0) {
            $match = "DELETE FROM telegram.partner_category_match WHERE partner_id = :partner_id";
            $stmt = PDO_CONNECTION->prepare($match);
            $stmt->bindParam(':partner_id', $chat_id, PDO::PARAM_INT);
            $stmt->execute();

            $sql = "DELETE FROM telegram.telegram_partner WHERE chat_id = :chat_id";
            $stmt = PDO_CONNECTION->prepare($sql);
            $stmt->bindParam(':chat_id', $chat_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $existing_category = "SELECT cat_id  FROM telegram.partner_category_match WHERE partner_id = :partner_id";
            $stmt = PDO_CONNECTION->prepare($existing_category);
            $stmt->bindParam(':partner_id', $chat_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $current_cat = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $current_cat = array_values($current_cat);
            updatePartner($chat_id, $current_cat, array_keys($data));
        }
    } else {
        if (partnerExist($chat_id)) {
            if (count($data) == 0) {
                $match = "DELETE FROM telegram.partner_category_match WHERE partner_id = :partner_id";
                $stmt = PDO_CONNECTION->prepare($match);
                $stmt->bindParam(':partner_id', $chat_id, PDO::PARAM_INT);
                $stmt->execute();

                $sql = "DELETE FROM telegram.telegram_partner WHERE chat_id = :chat_id";
                $stmt = PDO_CONNECTION->prepare($sql);
                $stmt->bindParam(':chat_id', $chat_id, PDO::PARAM_INT);
                $stmt->execute();
                return;
            }

            $existing_category = "SELECT cat_id  FROM telegram.partner_category_match WHERE partner_id = :partner_id";
            $stmt = PDO_CONNECTION->prepare($existing_category);
            $stmt->bindParam(':partner_id', $chat_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $current_cat = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $current_cat = array_values($current_cat);
            updatePartner($chat_id, $current_cat, array_keys($data));
        } else {
            createPartner($chat_id, $name, $username, $profile, array_keys($data));
        }
    }
}

if (isset($_POST['getCategories'])) {
    $categories = json_decode($_POST['data'], true);
    // Use array_filter to keep only items with a value of 1
    $result = array_filter($categories, function ($value) {
        return $value == 1;
    });

    $users_Group = array();
    foreach ($result as $key => $value) {
        $users_Group[$key] = getPartners($key);
    }
    // Use array_filter with a callback to filter out arrays with empty values
    $filteredUsers = array_filter($users_Group, function ($user) {
        return array_reduce($user, function ($carry, $value) {
            return $carry || !empty($value);
        }, false);
    });

    // Send the JSON response with appropriate headers
    header('Content-Type: application/json');
    echo json_encode($filteredUsers);
}

if (isset($_POST['logAction'])) {
    $log_info = $_POST;

    // Convert the data to a string
    $log_data = json_encode($log_info, JSON_UNESCAPED_UNICODE);

    // Define the file path
    $log_file = 'telegram_partner_log.txt';

    // Open the file in write mode (create if it doesn't exist)
    $file_handle = fopen($log_file, 'a'); // 'a' for append

    if ($file_handle !== false) {
        // Write the data to the file
        fwrite($file_handle, $log_data . "\n");

        // Close the file
        fclose($file_handle);
    } else {
        // Handle file open error
        echo 'Error opening log file';
    }
}

if (isset($_POST['getInitialData'])) {
    echo json_encode(['partners' => getExistingTelegramPartners(), 'categories' => getCategories()]);
}

if (isset($_POST['getExistingCategories'])) {
    echo json_encode(getCategories());
}

if (isset($_POST['editCategory'])) {

    $id = $_POST['id'];
    $value = $_POST['value'];

    editCategory($id, $value);
}

if (isset($_POST['createCategory'])) {
    $value = $_POST['value'];
    createCategory($value);
}

if (isset($_POST['delete_category'])) {
    $id = $_POST['id'];
    deleteCategory($id);
}

function getCategories()
{
    $sql = "SELECT * FROM telegram.partner_categories";
    $stmt = PDO_CONNECTION->query($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function getPartners($key)
{
    $sql = "SELECT name, chat_id FROM telegram.partner_category_match 
            INNER JOIN telegram.telegram_partner ON telegram_partner.chat_id = partner_category_match.partner_id
            WHERE partner_category_match.cat_id = :cat_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':cat_id', $key, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function partnerExist($id)
{
    $sql = "SELECT * FROM telegram.telegram_partner WHERE chat_id = :chat_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':chat_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return !empty($result);
}

function updatePartner($chat_id, $current_cat, $data)
{
    $current_cat = array_map(function ($item) {
        return is_array($item) ? reset($item) : $item;
    }, $current_cat);

    $data = array_map(function ($item) {
        return is_array($item) ? reset($item) : $item;
    }, $data);

    $toDelete = array_unique(array_diff($current_cat, $data));
    $toAdd = array_unique(array_diff($data, $current_cat));


    if (count($toDelete) > 0) {
        $match = "DELETE FROM telegram.partner_category_match WHERE partner_id = :partner_id AND cat_id = :cat_id";
        $stmt = PDO_CONNECTION->prepare($match);
        foreach ($toDelete as $id) {
            $stmt->bindParam(':partner_id', $chat_id, PDO::PARAM_INT);
            $stmt->bindParam(':cat_id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    if (count($toAdd) > 0) {
        $match = "INSERT INTO telegram.partner_category_match (partner_id, cat_id) VALUES (:partner_id, :cat_id)";
        $stmt = PDO_CONNECTION->prepare($match);
        foreach ($toAdd as $id) {
            try {
                $stmt->bindParam(':partner_id', $chat_id, PDO::PARAM_INT);
                $stmt->bindParam(':cat_id', $id, PDO::PARAM_INT);
                $stmt->execute();
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }
}

function createPartner($chat_id, $name, $username, $profile, $data)
{
    try {
        $sql = "INSERT INTO telegram.telegram_partner (chat_id, name, username, profile) 
            VALUES (:chat_id, :name, :username, :profile)";
        $stmt = PDO_CONNECTION->prepare($sql);
        $stmt->bindParam(':chat_id', $chat_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':profile', $profile, PDO::PARAM_STR);
        $stmt->execute();

        $sql = "INSERT INTO telegram.partner_category_match (partner_id , cat_id) 
        VALUES (:chat_id, :cat_id)";
        $stmt = PDO_CONNECTION->prepare($sql);

        foreach ($data as $id) {
            $stmt->bindParam(':chat_id', $chat_id, PDO::PARAM_INT);
            $stmt->bindParam(':cat_id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
    } catch (\Throwable $th) {
        throw $th;
    }
}

function getExistingTelegramPartners()
{
    $sql = "SELECT
                tp.chat_id AS chat_id,
                tp.name AS telegram_partner_name,
                tp.username,
                tp.profile,
                GROUP_CONCAT(pc.name) AS category_names
            FROM
                telegram.telegram_partner tp
            JOIN
                telegram.partner_category_match pcm ON tp.chat_id = pcm.partner_id
            JOIN
                telegram.partner_categories pc ON pcm.cat_id = pc.id
            GROUP BY
                tp.chat_id, tp.name;";
    $stmt = PDO_CONNECTION->query($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function editCategory($id, $value)
{
    $sql = "UPDATE telegram.partner_categories SET name= :name WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':name', $value, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function createCategory($value)
{
    $sql = "INSERT INTO telegram.partner_categories (name) VALUES (:name)";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':name', $value, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function deleteCategory($id)
{
    $sql = "DELETE FROM telegram.partner_categories WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
