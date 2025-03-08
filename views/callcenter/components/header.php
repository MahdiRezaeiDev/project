<?php
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
require_once '../../app/middleware/Authentication.php';
require_once '../../app/middleware/Authorize.php';
require_once '../../utilities/jdf.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../../public/img/<?= $iconUrl ?>" sizes="32x32">
    <meta name="description" content="This is a simple CMS for tracing goods based on their serial or part number.">
    <meta name="author" content="Mahdi Rezaei">
    <title><?= $pageTitle ?></title>

    <!-- Our custom style -->
    <link href="../../public/css/output.css" rel="stylesheet">
    <link href="./assets/css/persianDatepicker.css" rel="stylesheet" />
    <link href="../../public/css/material_icons.css" rel="stylesheet">


    <script src="./assets/js/jquery.min.js"></script>
    <script src="../../public/js/assets/axios.js"></script>
    <script src="./assets/js/persianDatepicker.min.js"></script>
    <script src="../../public/js/helper.js"></script>
</head>

<body class="pt-16">
    <?php
    require_once '../../utilities/attendance/attendanceHelper.php';
    ?>