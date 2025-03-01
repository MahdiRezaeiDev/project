<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

// This functions are related to the user management page
function getUsers()
{
    $users_sql = "SELECT users.id, name, family, settings.start_hour, settings.end_hour , 
                        settings.max_late_minutes, settings.user_id AS selectedUser
                    FROM yadakshop.users AS users
                    INNER JOIN yadakshop.attendance_settings AS settings ON yadakshop.settings.user_id = yadakshop.users.id
                    WHERE users.password IS NOT NULL AND users.password !='' AND username != 'tv'";

    $stmt = PDO_CONNECTION->prepare($users_sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}


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


function getUserAttendanceRule($user_id)
{
    $sql = "SELECT * FROM yadakshop.attendance_settings WHERE user_id = :user_id";
    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $attendance_rule = $stmt->fetch(PDO::FETCH_ASSOC);
    return $attendance_rule;
}
