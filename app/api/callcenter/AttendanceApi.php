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
        case 'DELETEAttendance':
            DeleteAttendance();
            break;
        case 'SetOffDay':
            SetOffDay();
            break;
        case 'toggleActivation':
            echo toggleActivation();
            break;
    }
}

function toggleActivation()
{
    $status = isset($_POST['status']) ? (int) $_POST['status'] : null; // ✅ cast to int
    $userID = $_POST['userID'] ?? null;

    if ($status === null || $userID === null) {
        http_response_code(400);
        echo json_encode(['message' => 'Missing required fields']);
        exit;
    }

    $stmt = PDO_CONNECTION->prepare("UPDATE attendance_settings SET is_Active = :status WHERE user_id = :userID");
    $stmt->bindParam(":status", $status, PDO::PARAM_INT); // ✅ explicitly bind as integer
    $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'وضعیت با موفقیت تغییر کرد']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'خطا در اجرای درخواست']);
    }

    exit;
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

function DeleteAttendance()
{
    if (!isset($_POST['start_id'], $_POST['end_id'])) {
        echo json_encode([
            "success" => false,
            "message" => "Missing parameters"
        ]);
        exit;
    }

    $start_id = (int) $_POST['start_id'];
    $end_id   = (int) $_POST['end_id'];

    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM attendance_logs WHERE id IN (:start_id, :end_id)");
    $success = $stmt->execute([
        ":start_id" => $start_id,
        ":end_id"   => $end_id
    ]);

    echo json_encode([
        "success" => $success,
        "message" => $success ? "ساعات کاری موفقانه حذف شد." : "عملیات حذف ناموفق بود."
    ]);
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

if (isset($_POST['saveLeave'])) {
    $user_id     = $_POST['leave_user'];
    $leave_date  = $_POST['leave_date'];
    $reason      = $_POST['reason'];
    $daily       = $_POST['daily'] ?? '0';
    $startingTime = $_POST['startingTime'] ?? null;
    $endingTime   = $_POST['endingTime'] ?? null;

    try {
        $pdo = PDO_CONNECTION;

        // نوع مرخصی
        $type = $daily == "1" ? "daily" : "hourly";
        $start_time = null;
        $end_time   = null;

        if ($daily == "1") {
            // گرفتن ساعت کاری کاربر
            $sql = "SELECT start_hour, end_hour FROM attendance_settings WHERE user_id = :user_id LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($schedule) {
                $start_time = $schedule['start_hour'];
                $end_time   = $schedule['end_hour'];
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ساعت کاری کاربر یافت نشد.']);
                exit;
            }
        } else {
            // مرخصی ساعتی → باید زمان شروع و پایان داده شود
            if (!$startingTime || !$endingTime) {
                echo json_encode(['status' => 'error', 'message' => 'لطفاً ساعت شروع و پایان مرخصی را وارد کنید.']);
                exit;
            }

            // اعتبارسنجی زمان
            if (strtotime($endingTime) <= strtotime($startingTime)) {
                echo json_encode(['status' => 'error', 'message' => 'ساعت پایان باید بزرگ‌تر از ساعت شروع باشد.']);
                exit;
            }

            $start_time = $startingTime;
            $end_time   = $endingTime;
        }

        // ثبت مرخصی
        $sql = "INSERT INTO leaves 
                    (user_id, type, date, start_time, end_time, reason, approved_by, created_at) 
                VALUES 
                    (:user_id, :type, :date, :start_time, :end_time, :reason, :approved_by ,NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'    => $user_id,
            ':type'       => $type,
            ':date'       => $leave_date,
            ':start_time' => $start_time,
            ':end_time'   => $end_time,
            ':reason'     => $reason,
            ':approved_by' => $_SESSION['id']
        ]);

        if ($stmt->rowCount() === 1) {
            echo json_encode(['status' => 'success', 'message' => 'مرخصی با موفقیت ثبت شد.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ثبت مرخصی انجام نشد.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'خطای پایگاه داده: ' . $e->getMessage()]);
    }
}
