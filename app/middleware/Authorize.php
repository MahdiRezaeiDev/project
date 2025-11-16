<?php

if (session_status() === PHP_SESSION_NONE) {
session_name('MyAppSession');
session_set_cookie_params(0, '/'); 
 session_start();
}


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
    $role = $_SESSION["roll"];

    $publicPages = [
        "koreaFactor","insuranceFactor","index","incomplete","factorInsurance",
        "externalView","delivery","deliveries","createPreCompleteBill","createIncomplete",
        "complete","checkIncompleteSell","addPayment","usersManagement","updateUserProfile",
        "translate","telegramProcess","smsbank","simple_html_dom","searchGoods","saveBazarPrice",
        "requests","relationships","registerRates","registerGoods","pricesHistory","priceSearchDetails",
        "priceSearch","priceRates","personalCartable","orderedPriceTelegram","orderedPrice","modal",
        "messagesReply","main","hussainAPI","goodsList","goodMobisPrice","givenPrice","generalCall",
        "factor","defineExchangeRate","customersPhone","customersList","createUserProfile","cartable",
        "callToBazar","attendanceReportExcell","attendanceReport","attendance","attendance-display",
        "askedPrices","price_check","priceRates","partnerFactor","paymentDetails","payments",
        "preSellFactorDetails","telegramPartner","dashboard","last-calling-time","main",
        "singleItemReport","goodsDetailsManagement","pendingSells","newSell","callcenter","Factor",
        "dashboard", "index", "profile", "adminStats","callcenter", "updateUserProfile","reports"
    ];

    $rolePages = [
        "10" => $publicPages,
        "1"  => array_merge(["dashboard", "index", "profile", "adminStats","callcenter", "updateUserProfile"], $publicPages),
        "2"  => array_merge(["dashboard", "index", "reports", "profile","callcenter","updateUserProfile"], $publicPages),
        "3"  => array_merge(["dashboard", "stockList", "transferReport","callcenter","updateUserProfile"], $publicPages),
        "4"  => array_merge(["dashboard", "stockList", "transferReport","callcenter","updateUserProfile"], $publicPages),
        "5"  => array_merge(["dashboard", "purchaseReport", "sellReport","callcenter","updateUserProfile"], $publicPages),
    ];

    if (isset($rolePages[$role]) && in_array($current_page, $rolePages[$role])) {
        return true;
    }

    if (in_array($current_page, $_SESSION['not_allowed'])) {
        return false;
    }

    if (isset($_SESSION['authority'][$current_page]) && $_SESSION['authority'][$current_page] == true) {
        return true;
    }

    return false;
}



function redirectToNotAllowed()
{
    header("location: ../auth/403.php");
    exit;
}

if (!isAllowedToVisit()) {
    redirectToNotAllowed();
}
