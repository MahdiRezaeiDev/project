<?php
require_once './config/constants.php';
require_once "./database/db_connect.php";
require_once "./app/middleware/Authentication.php";

if (!isLogin()) {
    header("Location: ./views/inventory/index.php");
    exit;
} else {
    header("Location: ./views/auth/login.php");
    exit;
}