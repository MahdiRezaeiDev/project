<?php
$pageTitle = "بانک اسمس";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';

define('JSON_FILE', 'messages.json');
?>


<style>
    @keyframes fade-in-out {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }

        10% {
            opacity: 1;
            transform: translateY(0);
        }

        90% {
            opacity: 1;
            transform: translateY(0);
        }

        100% {
            opacity: 0;
            transform: translateY(-10px);
        }
    }

    .animate-fade-in-out {
        animation: fade-in-out 3s ease forwards;
    }

</style>
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center">مدیریت اسمس ها‌</h1>

    <div id="alert" class="fixed top-4 right-4 z-50 flex flex-col items-end space-y-2"></div>

    <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="text-xl font-semibold mb-3">افزودن پیام جدید</h2>
        <input id="newTitle" type="text" placeholder="عنوان" class="w-full p-2 border border-gray-300 rounded mb-2">
        <textarea id="newMessage" placeholder="پیام" class="w-full p-2 border border-gray-300 rounded mb-1"></textarea>
        <p id="newCounter" class="text-sm text-gray-500 mb-2">0 کاراکتر - 1 SMS</p>


        <div id="new-preset-tags" class="flex gap-2 mb-2"></div>




        <button onclick="addMessage()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            افزودن
        </button>
    </div>

    <div id="messages" class="space-y-4"></div>
</div>

<script src="assets/js/smsbank.js"></script>
