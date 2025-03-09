<?php
if (!isset($dbname)) {
    header("Location: ../../../views/auth/403.php");
}

if (!isLogin()) {
    header("Location: ../auth/login.php");
    exit;
}

function isAllowedToVisit()
{
    $current_page = explode(".", basename($_SERVER['PHP_SELF']))[0];

    if (in_array($current_page, $_SESSION['not_allowed'])) {
        return true;
    }
    return true;
}

function redirectToNotAllowed()
{
    header("location: ../auth/403.php");
    exit;
}

if (!isAllowedToVisit()) {
    redirectToNotAllowed();
}
