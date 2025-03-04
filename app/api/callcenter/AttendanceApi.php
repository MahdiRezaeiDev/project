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
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
}

function updateWorkHour()
{
    $user_id = $_POST['user_id'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $late = $_POST['late'];

    try {
        $pdo = PDO_CONNECTION; // Assuming PDO_CONNECTION is your PDO instance
        $sql = "UPDATE attendance_settings SET start_hour = :start, end_hour = :end, max_late_minutes = :late WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':start', $start, PDO::PARAM_STR);
        $stmt->bindParam(':end', $end, PDO::PARAM_STR);
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
            echo json_encode(['status' => 'success', 'message' => 'ساعت شروع کار با موفقیت به روز رسانی شد']);
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
