<?php
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
$token = $_GET['token'] ?? '';

$stmt = PDO_CONNECTION->prepare("SELECT id, name, family, username FROM users WHERE access_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode([
        "status" => "ok",
        "id" => $user['id'],
        "name" => $user['name'],
        "family" => $user['family'],
        "username" => $user['username'],
    ]);
} else {
    echo json_encode(["status" => "fail"]);
}
