<?php
// Initialize the session
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
require_once '../../app/middleware/Authentication.php';
require_once '../../app/middleware/Authorize.php';
require_once '../../utilities/jdf.php';
function convertPersianToEnglish($string)
{
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persianDigits, $englishDigits, $string);
}
?>
<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="This is a simple CMS for tracing goods based on their serial or part number.">
    <meta name="author" content="Mahdi Rezaei">
    <meta name="keywords" content="CMS, Inventory, Goods, Serial, Part Number">

    <title><?= $pageTitle ?></title>
    <link rel="icon" href="../../public/img/<?= $iconUrl ?>" sizes="32x32">

    <link href="../../public/css/output.css" rel="stylesheet">
    <link href="../../public/css/assets/persianDatepicker.css" rel="stylesheet" />
    <link href="../../public/css/material_icons.css" rel="stylesheet">

    <script src="../../public/js/assets/jquery.min.js"></script>
    <script src="../../public/js/assets/axios.js"></script>
    <script src="../../public/js/assets/persianDatepicker.min.js"></script>
</head>

<body class="pt-14">
    <?php
    require_once '../../utilities/attendance/attendanceHelper.php';
    ?>