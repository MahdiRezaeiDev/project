<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../views/auth/403.php");
    exit;
}

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    switch ($action) {
        case 'updateWorkHour':
            updateWorkHour();
            break;
        case 'setWorkingHour':
            setWorkingHour();
            break;
        case 'UpdateAttendance':
            UpdateAttendance();
            break;
        case 'SetOffDay':
            SetOffDay();
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
}


if (isset($_POST['createRegistrationToken'])) {
    $user_id = $_POST['user_id'];
    $token = createRegistrationToken($user_id);
    echo json_encode(['status' => 'success', 'token' => $token]);
}

if (isset($_POST['delete_token'])) {
    $user_id = $_POST['user_id'];
    echo deleteAccessToken($user_id);
}


function deleteAccessToken($userID)
{
    $stmt = PDO_CONNECTION->prepare("DELETE FROM yadakshop.registration_tokens WHERE user_id = :user_id");
    $stmt->bindParam('user_id', $userID);
    $stmt->execute();

    $stmt = PDO_CONNECTION->prepare("UPDATE yadakshop.users SET access_token = NULL WHERE id = :id");
    $stmt->bindParam(':id', $userID);
    $stmt->execute();

    return true;
}

function createRegistrationToken(int $userId): string
{

    $token = bin2hex(random_bytes(32)); // 64-character secure token
    date_default_timezone_set("Asia/Tehran");
    $expiresAt = (new DateTime())->modify("+1 day")->format('Y-m-d H:i:s');

    $stmt = PDO_CONNECTION->prepare("
        INSERT INTO yadakshop.registration_tokens (user_id, token, expires_at)
        VALUES (:user_id, :token, :expires_at)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':token' => $token,
        ':expires_at' => $expiresAt
    ]);

    return $token;
}

function SetOffDay()
{
    $user_id = $_POST['selectedUser'];
    $time = date('H:i:s');
    $action = 'off';


    try {
        $pdo = PDO_CONNECTION;
        $sql = "INSERT INTO attendance_logs (user_id, action, timestamp) VALUES (:user_id, :action, :time) 
                ON DUPLICATE KEY UPDATE action = :action, timestamp = :time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            echo json_encode(['status' => 'success', 'message' => 'ساعت کار با موفقیت به روز رسانی شد']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا در به روز رسانی ساعت شروع کار']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateWorkHour()
{
    $user_id = $_POST['user_id'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $endWeek = $_POST['endWeek'];
    $late = $_POST['late'];

    try {
        $pdo = PDO_CONNECTION; // Assuming PDO_CONNECTION is your PDO instance
        $sql = "UPDATE attendance_settings SET start_hour = :start, end_hour = :end, end_week = :endWeek, max_late_minutes = :late WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':start', $start, PDO::PARAM_STR);
        $stmt->bindParam(':end', $end, PDO::PARAM_STR);
        $stmt->bindParam(':endWeek', $endWeek, PDO::PARAM_STR);
        $stmt->bindParam(':late', $late, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            echo json_encode(['status' => 'success', 'message' => 'ساعت کاری با موفقیت به روز رسانی شد']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا در به روز رسانی ساعت کاری']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function setWorkingHour()
{
    $user_id = $_POST['user_id'];
    $action = $_POST['preform'];

    try {
        $pdo = PDO_CONNECTION;
        $sql = "INSERT INTO attendance_logs (user_id, action) VALUES (:user_id, :action)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            echo json_encode(['status' => 'success', 'message' => 'ساعت کار با موفقیت به روز رسانی شد']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا در به روز رسانی ساعت شروع کار']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function UpdateAttendance()
{
    $user_id = $_POST['user_id'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $start_id = $_POST['start_id'];
    $end_id = $_POST['end_id'];

    try {
        updateStartHour($start_id, $start);
        updateEndHour($end_id, $end);
        echo json_encode(['status' => 'success', 'message' => 'ساعت پایان کار با موفقیت به روز رسانی شد']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateStartHour($id, $start)
{
    $sql = "UPDATE attendance_logs SET timestamp = :start WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':start', $start, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function updateEndHour($id, $end)
{
    $sql = "UPDATE attendance_logs SET timestamp = :end WHERE id = :id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':end', $end, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function getUserAttendanceReport($action, $user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs 
            WHERE user_id = :user_id AND DATE(created_at) = CURDATE() 
            AND action = :action ORDER BY id DESC LIMIT 1";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    $attendance_report = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $attendance_report;
}
