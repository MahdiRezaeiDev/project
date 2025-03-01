<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}


function getCallCenterUsers()
{
    $stmt = PDO_CONNECTION->prepare("SELECT * FROM yadakshop.users WHERE internal ORDER BY internal");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$myAttendanceReportStart = getUserAttendanceReport('start', $_SESSION['id']);
$myAttendanceReportEnd = getUserAttendanceReport('leave', $_SESSION['id']);

function getUserAttendanceReport($action, $user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs WHERE user_id = :user_id AND DATE(created_at) = CURDATE() AND action = :action";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->execute();
    $attendance_report = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $attendance_report;
}