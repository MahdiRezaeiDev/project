<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

// This functions are related to the user management page
function getUsers()
{
    $users_sql = "SELECT users.id, name, family, settings.start_hour, settings.end_hour , settings.end_week, settings.max_late_minutes, settings.user_id AS selectedUser
                    FROM yadakshop.users AS users
                    INNER JOIN yadakshop.attendance_settings AS settings ON yadakshop.settings.user_id = yadakshop.users.id
                    WHERE users.password IS NOT NULL AND users.password !='' AND username != 'tv'";

    $stmt = PDO_CONNECTION->prepare($users_sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}
