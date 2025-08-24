<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

// Silence is Golden
function getUserById($id)
{
    $sql = "SELECT users.id,name,family 
            FROM yadakshop.users AS users 
            INNER JOIN yadakshop.attendance_settings AS settings ON settings.user_id=users.id
            WHERE users.id=:id AND settings.is_active!=0";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getUserAttendanceLogsMonth($user_id, $start, $end)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs 
            WHERE user_id=:user_id AND DATE(created_at) BETWEEN :start AND :end 
            ORDER BY created_at ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':start', $start, PDO::PARAM_STR);
    $stmt->bindParam(':end', $end, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function convertPersianToEnglish($string)
{
    $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persianNumbers, $englishNumbers, $string);
}

function getUserLeaveReport($user_id, $date)
{
    $sql = "SELECT * FROM yadakshop.leaves WHERE user_id=:user_id AND date=:date ORDER BY start_time ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getUserAttendanceRule($user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_settings WHERE user_id=:user_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['start_hour' => '09:00', 'end_hour' => '18:00', 'end_week' => '14:00'];
}

function getUserAttendanceLogs($user_id, $date)
{
    $sql = "SELECT t1.timestamp, t2.timestamp AS end_time 
            FROM yadakshop.attendance_logs t1
            LEFT JOIN yadakshop.attendance_logs t2 
                ON t2.user_id=t1.user_id 
                AND t2.action='LEAVE' 
                AND t2.id=(SELECT MIN(id) FROM yadakshop.attendance_logs WHERE user_id=t1.user_id AND action='LEAVE' AND id>t1.id)
            WHERE t1.user_id=:user_id AND DATE(t1.created_at)=:date AND t1.action='START'
            ORDER BY t1.timestamp ASC";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
