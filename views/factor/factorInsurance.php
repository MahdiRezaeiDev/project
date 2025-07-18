<?php
$pageTitle = 'امانت نامه';
$iconUrl = 'factor.svg';
$logo = "./assets/img/fiduciary.webp";
$title = 'امانت نامه';
$subTitle = 'امانت نامه';
$factorType = 'partner';

require_once './components/header.php';
require_once '../../app/controller/factor/DisplayFactorController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<style>
    .dashed:first-of-type {
        border-top: 4px dashed black;
        padding: 10px 0 0 0;
    }


    .element {
        margin-bottom: 7px;
        border: 3px solid black;
        padding: 5px 0;
        border-radius: 7px;
        font-weight: bold;
    }
</style>
<style>
    .bill {
        position: relative !important;
    }

    .bill::before {
        content: "امانت نامه";
        position: absolute;
        top: 40%;
        left: 25%;
        /* Default for screen */
        transform: rotate(-30deg);
        font-size: 80px;
        color: rgba(0, 0, 0, 0.4);
        z-index: 9999;
        pointer-events: none;
        white-space: nowrap;
    }

    @media print {
        .bill::before {
            left: 25%;
            color: rgba(0, 0, 0, 0.2);
        }
    }
</style>

<link rel="stylesheet" href="./assets/css/bill.css" />
<script src="./assets/js/html2pdf.js"></script>
<script>
    const factorType = '<?= $factorType ?>';
    let bill_number = null;
    const customerInfo = <?= json_encode($customerInfo) ?>;
    const BillInfo = <?= json_encode($BillInfo) ?>;
    const billItems = <?= ($billItems) ?>;
</script>
<div id="bill_body_pdf" class="bill partnerBill mb-3" style="min-height: 210mm;">
    <?php
    require_once './components/insurance/bill/header.php';
    require_once './components/insurance/bill/body.php';
    require_once './components/insurance/bill/generalDetails.php';
    require_once './components/insurance/bill/actionMenu.php';
    ?>
</div>
<div class="bill mb-3" style="min-height: 210mm;">
    <?php
    require './components/insurance/owner/header.php';
    require './components/insurance/owner/body.php';
    require './components/insurance/owner/generalDetails.php';
    ?>
</div>
<div class="bill mb-3" style="min-height: 210mm;">
    <?php
    require './components/insurance/finance/header.php';
    require './components/insurance/finance/body.php';
    require './components/insurance/finance/generalDetails.php';
    ?>
</div>
<div class="bill mb-3" style="min-height: 210mm;">
    <?php
    require './components/insurance/inventory/header.php';
    require './components/insurance/inventory/body.php';
    require './components/insurance/inventory/generalDetails.php';
    ?>
</div>
<script src="./assets/js/displayFactor/factorInsurance.js"></script>
<script>
    const time = '<?= $BillInfo['created_at']; ?>';
    const now = moment(time).locale('fa').format('h:ma');
    displayFinanceBill();
    displayFinanceCustomer();
    displayFinanceBillDetails();

    displayInventoryBill();
    displayInventoryCustomer();
    displayInventoryBillDetails();

    displayOwnerBill();
    displayOwnerCustomer();
    displayOwnerBillDetails();

    <?php if ($preSellFactor) { ?>
        let preBillItems = <?= json_decode($preSellFactorItems, true) ?? [] ?>;
        preBillItems = Object.values(preBillItems);

        let billItemsDescription = <?= json_decode($preSellFactorItemsDescription, true) ?>;

        const specialClass = "text-white bg-gray-700 p-1 rounded-sm ";
        const specialBrands = ['MOB', 'GEN', 'اصلی'];

        for (item of preBillItems) {
            document.getElementById(item.id).innerHTML += `
           <tr>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.partNumber}</td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important;">
                    <p class="text-xs text-center ${ !specialBrands.includes(item.brandName) ? specialClass : ''}">
                        ${item.brandName}
                    </p>
                </td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">
                    <p class="text-xs text-center bg-gray-300 px-1 py-1 rounded-sm">
                        ${item.quantity}
                    </p>
                </td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.pos1}</td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.pos2}</td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.required ? '<svg width="20px" height="20px" viewBox="0 0 72 72" id="emoji" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="color"> <path fill="#ea5a47" d="m58.14 21.78-7.76-8.013-14.29 14.22-14.22-14.22-8.013 8.013 14.36 14.22-14.36 14.22 8.014 8.013 14.22-14.22 14.29 14.22 7.76-8.013-14.22-14.22z"></path> </g> <g id="hair"></g> <g id="skin"></g> <g id="skin-shadow"></g> <g id="line"> <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" d="m58.14 21.78-7.76-8.013-14.29 14.22-14.22-14.22-8.013 8.013 14.35 14.22-14.35 14.22 8.014 8.013 14.22-14.22 14.29 14.22 7.76-8.013-14.22-14.22z"></path> </g> </g></svg>' : ''}</td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.required ?? ''}</td>
            </tr>`;
            if (item.required) {
                document.getElementById('template_' + item.id).classList.add('hidden');
                document.getElementById('owner_' + item.id).innerHTML += `
                    <tr>
                            <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.partNumber}</td>
                            <td style="padding: 3px !important; border: none !important; text-align: center !important;">
                                <p class="text-xs text-center ${ !specialBrands.includes(item.brandName) ? specialClass : ''}">
                                    ${item.brandName}
                                </p>
                            </td>
                            <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">
                                <p class="text-xs text-center bg-gray-300 px-1 py-1 rounded-sm">
                                    ${item.quantity}
                                </p>
                            </td>
                            <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.required ? '<svg width="20px" height="20px" viewBox="0 0 72 72" id="emoji" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="color"> <path fill="#ea5a47" d="m58.14 21.78-7.76-8.013-14.29 14.22-14.22-14.22-8.013 8.013 14.36 14.22-14.36 14.22 8.014 8.013 14.22-14.22 14.29 14.22 7.76-8.013-14.22-14.22z"></path> </g> <g id="hair"></g> <g id="skin"></g> <g id="skin-shadow"></g> <g id="line"> <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" d="m58.14 21.78-7.76-8.013-14.29 14.22-14.22-14.22-8.013 8.013 14.35 14.22-14.35 14.22 8.014 8.013 14.22-14.22 14.29 14.22 7.76-8.013-14.22-14.22z"></path> </g> </g></svg>' : ''}</td>
                            <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">
                                <p class="text-xs text-center px-1 py-1 rounded-sm">
                                ${item.required ?? ''}
                                </p>
                            </td>
                        </tr>`;
            }
            document.getElementById(item.id + '_finance').innerHTML += `
           <tr>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.partNumber}</td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important;">
                    <p class="text-xs text-center ${ !specialBrands.includes(item.brandName) ? specialClass : ''}">
                        ${item.brandName}
                    </p>
                </td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">
                    <p class="text-xs text-center bg-gray-300 px-1 py-1 rounded-sm">
                        ${item.quantity}
                    </p>
                </td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.required ? '<svg width="20px" height="20px" viewBox="0 0 72 72" id="emoji" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="color"> <path fill="#ea5a47" d="m58.14 21.78-7.76-8.013-14.29 14.22-14.22-14.22-8.013 8.013 14.36 14.22-14.36 14.22 8.014 8.013 14.22-14.22 14.29 14.22 7.76-8.013-14.22-14.22z"></path> </g> <g id="hair"></g> <g id="skin"></g> <g id="skin-shadow"></g> <g id="line"> <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" d="m58.14 21.78-7.76-8.013-14.29 14.22-14.22-14.22-8.013 8.013 14.35 14.22-14.35 14.22 8.014 8.013 14.22-14.22 14.29 14.22 7.76-8.013-14.22-14.22z"></path> </g> </g></svg>' : ''}</td>
                <td style="padding: 3px !important; border: none !important; text-align: center !important; font-size:13px">${item.required ?? ''}</td>
            </tr>`;
        }

        for (item in billItemsDescription) {
            if (document.getElementById('des_' + item)) {
                document.getElementById('des_' + item).innerHTML += `<span class="pl-3 text-xs">${billItemsDescription[item]}</span>`;
                document.getElementById('des_' + item + '_finance').innerHTML += `<span class="pl-3 text-xs">${billItemsDescription[item]}</span>`;
            }
        }

    <?php } else { ?>
        preBillItems = {};
        billItemsDescription = {};
    <?php } ?>
</script>
<?php
require_once './components/footer.php';
