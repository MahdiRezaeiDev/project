<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['saveAttendance'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['operation'];
    $date = $_POST['date'];

    echo saveAttendance($_POST);
}

function saveAttendance($data)
{
    $pdo = PDO_CONNECTION;

    // Find any record for this user on the same date
    $stmt = $pdo->prepare("
        SELECT id, action 
        FROM attendance_logs 
        WHERE user_id = :user_id AND DATE(created_at) = :date
        LIMIT 1
    ");
    $stmt->execute([
        ':user_id' => $data['user_id'],
        ':date'    => $data['date'], // e.g. '2025-08-19'
    ]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Case 1: if "off" exists -> delete and insert new action
        if ($existing['action'] === 'off') {
            $pdo->prepare("DELETE FROM attendance_logs WHERE id = :id")
                ->execute([':id' => $existing['id']]);

            $stmt = $pdo->prepare("
                INSERT INTO attendance_logs (user_id, action, timestamp, created_at) 
                VALUES (:user_id, :action, :timestamp, :created_at)
            ");
            return $stmt->execute([
                ':user_id'    => $data['user_id'],
                ':action'     => $data['operation'],
                ':timestamp'  => $data['time'],
                ':created_at' => $data['date'] . " 00:00:00", // normalize
            ]);
        }

        // Case 2: if same action exists -> update timestamp
        if ($existing['action'] === $data['operation']) {
            $stmt = $pdo->prepare("
                UPDATE attendance_logs 
                SET timestamp = :timestamp 
                WHERE id = :id
            ");
            return $stmt->execute([
                ':timestamp' => $data['time'],
                ':id'        => $existing['id'],
            ]);
        }
    }

    // Case 3: no conflict -> insert normally
    $stmt = $pdo->prepare("
        INSERT INTO attendance_logs (user_id, action, timestamp, created_at) 
        VALUES (:user_id, :action, :timestamp, :created_at)
    ");
    return $stmt->execute([
        ':user_id'    => $data['user_id'],
        ':action'     => $data['operation'],
        ':timestamp'  => $data['time'],
        ':created_at' => $data['date'] . " 00:00:00", // normalize
    ]);
}
