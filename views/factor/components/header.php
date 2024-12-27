<?php
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
require_once '../../app/middleware/Authentication.php';
require_once '../../app/middleware/Authorize.php';
require_once '../../utilities/jdf.php';
?>
<!DOCTYPE html>
<html lang="fe" dir="rtl">

<head>
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta name="description" content="This is a simple CMS for tracing goods based on their serial or part number.">
    <meta name="author" content="Mahdi Rezaei">
    <title id="customer_factor"><?= $pageTitle ?></title>
    <link rel="icon" href="../../public/img/<?= $iconUrl ?>" sizes="32x32">


    <link href="../../public/css/output.css" rel="stylesheet">
    <link href="../../public/css/material_icons.css" rel="stylesheet">
    <link href="../../public/css/assets/persianDatepicker.css" rel="stylesheet" />
    <link href="./assets/css/factor.css" rel="stylesheet">

    <script src="../../public/js/assets/jquery.min.js"></script>
    <script src="../../public/js/assets/axios.js"></script>
    <script src="../../public/js/assets/persianDatepicker.min.js"></script>
    <script src="./assets/js/helpers.js"></script>
    <script src="./assets/js/jalaliMoment.js"></script>
    <script src="./assets/js/html2canvas.js"></script>
</head>

<body class="min-h-screen bg-gray-50 pt-14">