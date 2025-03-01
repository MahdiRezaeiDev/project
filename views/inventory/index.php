<?php
$pageTitle = "داشبورد";
$iconUrl = 'logo.jpg';
require_once './components/header.php';
require_once '../../app/controller/inventory/InventoryDashboardController.php';
require_once '../../app/controller/components/DashboardCardsController.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php';
$dateTime = jdate('Y-m-d'); ?>

<!-- ------------------------------------------------ Dashboard card section ------------------------------------------------------>
<section class="mx-auto px-5 pb-5 bg-gray-100">
    <?php include_once '../components/dashboardCards.php'; ?>
</section>
<!-- ---------------------------------------------- Dashboard users and calender -------------------------------------------------->
<section class="mx-auto rtl bg-gray-100 mb-5">
    <div class="grid grid-cols-1 lg:grid-cols-2 px-5 gap-5">
        <?php
        include_once '../components/latestFactors.php';
        include_once '../components/calender.php'; ?>
    </div>
</section>

<script>
    
</script>
<?php
require_once './components/footer.php';
?>