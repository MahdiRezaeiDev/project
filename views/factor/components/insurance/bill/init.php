<?php
require_once './bootstrap/init.php';
require_once './app/controllers/DisplayFactorController.php';
require_once './layouts/header.php';
?>
<link rel="stylesheet" href="./public/css/bill.css?v=<?= rand() ?>" />
<script src="./public/js/html2pdf.js"></script>
<script>
    let bill_number = null;
    const customerInfo = <?= json_encode($customerInfo) ?>;
    const BillInfo = <?= json_encode($BillInfo) ?>;
    const billItems = <?= ($billItems) ?>;
</script>