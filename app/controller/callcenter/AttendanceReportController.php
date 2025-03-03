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

function getUserAttendanceReport($action, $user_id, $date)
{
    $sql = "SELECT * FROM yadakshop.attendance_logs 
            WHERE user_id = :user_id 
            AND DATE(created_at) = :date 
            AND action = :action";

    $stmt = PDO_CONNECTION->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR); // Use string format (Y-m-d)
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


function getStartOfTheWeek()
{
    $persianDay =  jdate('w');

    $englishDay = null;
    switch ($persianDay) {
        case '۰':
            $englishDay = 0;
            break;
        case '۱':
            $englishDay = 1;
            break;
        case '۲':
            $englishDay = 2;
            break;
        case '۳':
            $englishDay = 3;
            break;
        case '۴':
            $englishDay = 4;
            break;
        case '۵':
            $englishDay = 5;
            break;
        case '۶':
            $englishDay = 6;
            break;
    }

    // Get today's timestamp
    $today = time();

    // Calculate the timestamp for the previous days
    $previousDateTimestamp = strtotime("-$englishDay days", $today);

    // Convert to Jalali date
    $previousDate = $previousDateTimestamp;
    return  $previousDate;
}

$startDate = getStartOfTheWeek();
