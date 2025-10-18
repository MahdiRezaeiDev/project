<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}
require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['deleteContact'])) {
    $id = $_POST['id'];
    echo deleteContact($id);
}

function deleteContact($id)
{
    // First, get the contact details from receiver
    $sql = "SELECT chat_id, name, username, profile FROM telegram.receiver WHERE id = :id";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->bindParam(':id', $id);
    $statement->execute();
    $contact = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        // Contact not found
        return false;
    }

    // Begin transaction to ensure atomicity
    PDO_CONNECTION->beginTransaction();
    try {
        // Insert into blocked table
        $insertSql = "INSERT INTO telegram.blocked (chat_id, name, username, profile) VALUES 
                      (:chat_id, :name, :username, :profile)";
        $statement = PDO_CONNECTION->prepare($insertSql);
        $statement->bindParam(':chat_id', $contact['chat_id']);
        $statement->bindParam(':name', $contact['name']);
        $statement->bindParam(':username', $contact['username']);
        $statement->bindParam(':profile', $contact['profile']);
        $statement->execute();

        // Delete from receiver table
        $deleteSql = "DELETE FROM telegram.receiver WHERE id = :id";
        $statement = PDO_CONNECTION->prepare($deleteSql);
        $statement->bindParam(':id', $id);
        $statement->execute();

        PDO_CONNECTION->commit();
        return true;
    } catch (PDOException $e) {
        PDO_CONNECTION->rollBack();
        return false;
    }
}

if (isset($_POST['addContact'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $profile = $_POST['profile'];
    $chat_id = $_POST['chat_id'];
    addContact($name, $username, $chat_id, $profile);
}

function addContact($name, $username, $chat_id, $profile)
{
    // Check if chat_id already exists in receiver
    $sql = "SELECT COUNT(chat_id) AS total FROM telegram.receiver WHERE chat_id = :chat_id";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->bindParam(':chat_id', $chat_id);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC)['total'];

    if (!$result) {
        // Begin transaction to ensure both actions succeed together
        PDO_CONNECTION->beginTransaction();

        try {
            // Insert into receiver
            $addSql = "INSERT INTO telegram.receiver (cat_id, chat_id, name, username, profile) VALUES 
                        ('1', :chat_id , :name , :username , :profile)";
            $statement = PDO_CONNECTION->prepare($addSql);
            $statement->bindParam(':chat_id', $chat_id);
            $statement->bindParam(':name', $name);
            $statement->bindParam(':username', $username);
            $statement->bindParam(':profile', $profile);
            $statement->execute();

            // Delete from blocked if exists
            $deleteSql = "DELETE FROM telegram.blocked WHERE chat_id = :chat_id";
            $statement = PDO_CONNECTION->prepare($deleteSql);
            $statement->bindParam(':chat_id', $chat_id);
            $statement->execute();

            // Commit transaction
            PDO_CONNECTION->commit();

            echo 'true';
        } catch (PDOException $e) {
            PDO_CONNECTION->rollBack();
            echo 'false';
        }
    } else {
        echo 'exist';
    }
}

if (isset($_POST['addAllContact'])) {

    $contacts = json_decode($_POST['contacts']);

    foreach ($contacts as $contact) {
        addAllContacts($contact);
    }

    echo true;
}

function addAllContacts($contact)
{
    $chat_id = $contact->chat_id;

    $clientName = $contact->name;
    $username = $contact->username ?? '';
    $profile = $contact->profile ?? ''; // corrected variable usage

    // Check if chat_id already exists in receiver
    $sql = "SELECT COUNT(chat_id) AS total FROM telegram.receiver WHERE chat_id = :chat_id";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->bindParam(':chat_id', $chat_id);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC)['total'];

    if (!$result) {
        // Begin transaction to ensure insert and delete happen together
        PDO_CONNECTION->beginTransaction();
        try {
            // Insert into receiver
            $addSql = "INSERT INTO telegram.receiver (cat_id, chat_id, name, username, profile) VALUES 
                        ('1', :chat_id, :name, :username, :profile)";
            $statement = PDO_CONNECTION->prepare($addSql);
            $statement->bindParam(':chat_id', $chat_id);
            $statement->bindParam(':name', $clientName);
            $statement->bindParam(':usefrname', $username);
            $statement->bindParam(':profile', $profile);
            $statement->execute();

            // Delete from blocked if exists
            $deleteSql = "DELETE FROM telegram.blocked WHERE chat_id = :chat_id";
            $statement = PDO_CONNECTION->prepare($deleteSql);
            $statement->bindParam(':chat_id', $chat_id);
            $statement->execute();

            // Commit transaction
            PDO_CONNECTION->commit();

            return true;
        } catch (PDOException $e) {
            PDO_CONNECTION->rollBack();
            return false;
        }
    } else {
        // Already exists
        return false;
    }
}

if (isset($_POST['getPartialContacts'])) {
    $page = $_POST['page'];

    header('Content-Type: application/json');
    echo getPartialContacts($page);
}

function getPartialContacts($page)
{
    $offset = ($page - 1) * 50;
    $sql = "SELECT * FROM telegram.receiver LIMIT 50 OFFSET :offset";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $contacts = $statement->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($contacts);
}

if (isset($_POST['getContactsCount'])) {

    header('Content-Type: application/json');
    echo getContactsCount();
}

function getContactsCount()
{
    $sql = "SELECT COUNT(id) AS total FROM telegram.receiver";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->execute();
    $contacts = $statement->fetch(PDO::FETCH_ASSOC);
    return json_encode($contacts['total']);
}


if (isset($_POST['saveConversation'])) {
    $receiver = $_POST['receiver'];
    $request = $_POST['request'];
    $response = $_POST['response'];

    header('Content-Type: application/json');
    echo saveConversation($receiver, $request, $response);
}


function saveConversation($receiver, $request, $response)
{
    // Prepare the SQL statement
    $sql = "INSERT INTO telegram.messages (receiver, request, response) VALUES (:receiver, :request , :response)";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->bindParam(':receiver', $receiver);
    $statement->bindParam(':request', $request);
    $statement->bindParam(':response', $response);
    // Check if the insertion was successful
    if ($statement->execute()) {
        return true; // Conversation saved successfully
    } else {
        return false; // Failed to save conversation
    }
}


if (isset($_POST['searchContact'])) {
    $pattern = $_POST['pattern'];

    header('Content-Type: application/json');
    echo searchContact($pattern);
}

function searchContact($pattern)
{
    $sql = "SELECT * FROM telegram.receiver WHERE name LIKE :pattern OR username LIKE :pattern";
    $statement = PDO_CONNECTION->prepare($sql);
    $pattern = '%' . $pattern . '%';
    $statement->bindParam(':pattern', $pattern);
    $statement->execute();
    $contacts = $statement->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($contacts);
}

if (isset($_POST['toggleStatus'])) {
    $status = $_POST['status'];

    header('Content-Type: application/json');
    echo toggleStatus();
}

function toggleStatus()
{
    $sql = "SELECT * FROM telegram.receiver_cat WHERE id = 1";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->execute();
    $status = $statement->fetch(PDO::FETCH_ASSOC)['status'];

    if ($status == 1) {
        $sql = "UPDATE telegram.receiver_cat SET status = 0 WHERE id = 1";
        $statement = PDO_CONNECTION->prepare($sql);
        $result = $statement->execute();
        if ($result) {
            return 0;
        } else {
            return 1;
        }
    } else {
        $sql = "UPDATE telegram.receiver_cat SET status = 1 WHERE id = 1";
        $statement = PDO_CONNECTION->prepare($sql);
        $result = $statement->execute();
        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }
}


if (isset($_POST['getBlockedContacts'])) {
    header('Content-Type: application/json');
    echo json_encode(getBlockedContacts(), JSON_UNESCAPED_UNICODE);
}

function getBlockedContacts()
{
    $sql = "SELECT * FROM telegram.blocked ORDER BY id";
    $statement = PDO_CONNECTION->prepare($sql);
    $statement->execute();
    $contacts = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $contacts;
}
