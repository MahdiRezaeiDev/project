<?php
require_once '../../config/constants.php';
require_once '../../database/db_connect.php';
?>
<!DOCTYPE html>
<html lang="fe" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="This is a simple CMS for tracing goods based on their serial or part number.">
    <meta name="author" content="Mahdi Rezaei">
    <title id="customer_factor"><?= $pageTitle ?></title>
    <link rel="icon" href="../../public/img/<?= $iconUrl ?>" sizes="32x32">


    <link href="../../public/css/output.css" rel="stylesheet">
    <link href="../../public/css/material_icons.css" rel="stylesheet">

    <script src="../../public/js/assets/jquery.min.js"></script>
    <script src="../../public/js/assets/axios.js"></script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out forwards;
        }

        @media (min-width: 640px) {
            .hide_while_print .fixed.inset-0 {
                justify-content: flex-end;
                align-items: flex-end;
                padding: 1rem;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 pt-14">
    <?php
    function showAlertAndExit($message, $type = 'error')
    {
        // Define color schemes for alert types
        $colors = [
            'error' => ['bg' => 'bg-red-50', 'text' => 'text-red-800', 'border' => 'border-red-400'],
            'success' => ['bg' => 'bg-green-50', 'text' => 'text-green-800', 'border' => 'border-green-400'],
            'info' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-400'],
        ];

        $color = $colors[$type] ?? $colors['info'];
    ?>

        <div class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
            <div class="max-w-md w-full <?= $color['bg'] ?> border <?= $color['border'] ?> rounded-xl shadow-md p-8 text-center">
                <h1 class="text-2xl font-bold mb-4 <?= $color['text'] ?>">پیغام</h1>
                <p class="text-lg text-gray-700 mb-6"><?= htmlspecialchars($message) ?></p>
            </div>
        </div>
    <?php
        exit;
    }

    $allowedHost = '192.168.9.14';
    // $allowedHost = '84.241.41.22:9002';

    if ($_SERVER['HTTP_HOST'] !== $allowedHost) {
        http_response_code(403); // Forbidden
        showAlertAndExit("دسترسی غیرمجاز. فقط از طریق $allowedHost مجاز است.");
    }
