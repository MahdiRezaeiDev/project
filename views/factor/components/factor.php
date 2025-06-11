<!-- A modal to preview the bill to show the user it's requested items -->
<div id="previewBill" style="display:none;" class="bg-gray-100 justify-center" style="z-index: 10000000000;">
    <div id="bill_body_pdf" class="rtl bill bg-white ">
        <ul id="heading" class="flex mb-5 absolute right-0">
            <li onclick="changeLayout('yadak')" class="text-sm text-white bg-gray-900 rounded-md mx-1 px-2 cursor-pointer">یدک شاپ</li>
            <li onclick="changeLayout('insurance')" class="text-sm text-white bg-gray-900 rounded-md mx-1 px-2 cursor-pointer">بیمه</li>
            <li onclick="changeLayout('partner')" class="text-sm text-white bg-gray-900 rounded-md mx-1 px-2 cursor-pointer">همکار</li>
            <li onclick="changeLayout('fiduciary')" class="text-sm text-white bg-gray-900 rounded-md mx-1 px-2 cursor-pointer">امانت نامه</li>
            <li onclick="changeLayout('korea')" class="text-sm text-white bg-gray-900 rounded-md mx-1 px-2 cursor-pointer">آتوپارت</li>
        </ul>
        <div id="capture">
            <div class="bill_header">
                <div class="bill_info">
                    <div class="nisha-bill-info">
                        <div class="A-main">
                            <div class="A-1">شماره</div>
                            <div class="A-2"><span id="billNO_bill">5555</span></div>

                        </div>
                        <div class="B-main">
                            <div class="B-1">تاریخ</div>
                            <div class="B-2"><span id="date_bill">1402-10-30</span></div>

                        </div>
                    </div>
                </div>
                <div class="headline">
                    <h2 id="factor_heading" style="margin-bottom: 7px;"> پیش فاکتور یدک شاپ</h2>
                    <h2 id="factor_heading" style="margin-bottom: 7px;">لوازم یدکی هیوندای و کیا</h2>
                </div>
                <div class="log_section">
                    <img id="factor_logo" class="logo" src="./assets/img/logo.png" alt="logo of yadakshop">
                </div>
            </div>
            <div class="customer_info flex justify-between">
                <ul>
                    <li class="text-sm">
                        نام :
                        <span id="name_bill"></span>
                    </li>
                    <li class="text-sm">
                        شماره تماس:
                        <span id="phone_bill"></span>
                    </li>
                </ul>
                <ul>
                    <li class="text-xs">
                        نشانی :
                        <span id="userAddress"></span>
                    </li>
                    <!-- <li class="text-xs">
                        ماشین :
                        <span id="car_bill"></span>
                    </li> -->
                </ul>
                <img id="copy_icon" class="cursor-pointer" src="./assets/img/copy.svg" alt="copy customer info" onclick="copyInfo(this)">
            </div>
            <div class="bill_items">
                <table>
                    <thead>
                        <tr style="padding: 10px !important;">
                            <th class="text-right w-8">ردیف</th>
                            <th class="text-right">نام قطعه</th>
                            <th class="text-center w-12 border-r border-l-2 border-gray-800"> تعداد</th>
                            <th class="text-right w-28"> قیمت واحد</th>
                            <th class="text-right w-28"> قیمت کل (ریال)</th>
                        </tr>
                    </thead>
                    <tbody id="bill_body_result">
                    </tbody>
                </table>
            </div>
            <div class="bill_footer">
                <table class="w-full">
                    <tbody>
                        <tr class="bg-gray border-b border-gray-800">
                            <td class="text-right w-8"></td>
                            <td class="text-right">جمع فاکتور</td>
                            <td class="text-center w-12 border-r border-l-2 border-gray-800">
                                <span id="quantity_bill" class="w-full"></span>
                            </td>
                            <td class="text-right w-28">
                                <span id="totalPrice_bill" class="w-full"></span>
                            </td>
                            <td class="text-right w-28">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="w-8 border-l-2 border-gray-800 text-left">تخفیف : </td>
                            <td colspan="2" class="text-right w-8">
                                <span id="discount_bill"></span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding:15px;" class="border-t border-b border-gray-800"></td>
                            <td colspan="2" style="padding:15px;" class="border-t border-b border-gray-800"></td>
                        </tr>
                        <tr>
                            <td class="text-right w-8"></td>
                            <td class="text-right">
                                <p>مبلغ قابل پرداخت:
                                    <span id="total_in_word_bill"></span>
                                </p>
                            </td>
                            <td class="text-center w-12 border-l-2 border-gray-800">

                            </td>
                            <td class="text-right w-28">
                                <span id="totalPrice2" class="w-full font-bold"></span>
                            </td>
                            <td class="text-right w-28">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex gap-5" style="margin-top: 20px;">
                <div class="tahvilgirande-box">
                    <div class="tahvilgirande-box-header">مشخصات تحویل گیرنده</div>
                    <div class="tahvilgirande-box-inner">
                        <div>نام</div>
                        <div>شماره تماس</div>
                        <div>امضا</div>
                    </div>
                </div>
                <div id="factor_description" class="description-box flex-grow">
                    <div class="tahvilgirande-box-header">توضیحات فاکتور</div>
                    <div class="tahvilgirande-box-inner">
                    </div>
                </div>
            </div>
            <div class="footer-box">
                <p id="factor_address" class="footer-box-adress">
                    تهران ، میدان بهارستان ، خیابان مصطفی خمینی ، خیابان نظامیه ، بن بست ویژه ، پلاک ۴
                </p>
                <p id="factor_phone" style="direction: ltr !important;" class="footer-box-tell">
                    <span style="direction: ltr !important;">
                        ۰۲۱ - ۳۳ ۹۷ ۹۳ ۷۰
                    </span>
                    <span style="direction: ltr !important;">
                        ۰۲۱ - ۳۳ ۹۴ ۶۷ ۸۸
                    </span>
                    <span style="direction: ltr !important;">
                        ۰۹۱۲ - ۰۸۱ ۸۳ ۵۵
                    </span>
                </p>
            </div>
        </div>
    </div>
    <ul class="action_menu">
        <li style="position: relative;">
            <img class="action_button print" id="download" src="./assets/img/download.svg" alt="print icon">
            <p class="action_tooltip">دانلود</p>
        </li>
        <li style="position: relative;">
            <img class="action_button print" onclick="window.print();" src="./assets/img/print.svg" alt="print icon">
            <p class="action_tooltip">پرینت</p>
        </li>
        <li style="position: relative;">
            <img class="action_button share" src="./assets/img/share.svg" alt="print icon">
            <p class="action_tooltip">اشتراک گذاری</p>
        </li>
        <li style="position: relative;">
            <img class="action_button pdf" src="./assets/img/pdf.svg" onclick="saveAsPDF()" alt="print icon">
            <p class="action_tooltip">پی دی اف</p>
        </li>
    </ul>
</div>
<script>
    const factorInfo = <?= json_encode($factorInfo); ?>;
    let displayLayout = null;

    document.getElementById('download').addEventListener('click', function() {
        html2canvas(document.querySelector("#capture")).then(canvas => {
            // Create an anchor element to trigger the download
            let link = document.createElement('a');
            const number = document.getElementById('billNO_bill').innerText.trim();
            const name = document.getElementById('name_bill').innerText.trim();
            link.download = number + ' ' + name + '.png';
            link.href = canvas.toDataURL();
            link.click();
        });
    });
</script>
<style>
    #previewBill {
        overflow: hidden;
        background-color: #f7dada;
    }

    #bill_body_pdf {
        margin-top: 0px;
    }

    @media print {

        #previewBill,
        #bill_body_pdf {
            display: block !important;
            margin-top: 0 !important;
        }

        nav {
            display: none !important;
        }

        body,
        main,
        #previewBill {
            padding: 0 !important;
            margin: 0 !important;
        }

        #heading,
        #wholePage {
            display: none !important;
        }

        * {
            overflow: hidden !important;
            background-color: white !important;
        }

        ;
    }

    @media print {
        * {
            font-size: 12px !important;
        }

        main,
        body,
        #wrapper {
            background-color: white !important;
        }

        .bill {
            width: 100% !important;
        }

        #page_header {
            display: none;
        }

        * {
            direction: rtl !important;
        }

        @page :footer {
            display: none !important;
        }

        @page :header {
            display: none !important;
        }

        @page {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        main {
            padding-block: 10px !important;
            margin: 0 !important;
        }

        #nav {
            display: none !important;
        }

        #side_nav {
            display: none !important;
        }

        .customer_info {
            background-color: white !important;
            border: 1px solid black !important;
        }

        .bill {
            padding: 0 !important;
        }

        .action_menu {
            display: none;
        }

        .bill_items>table tr {
            background-color: white !important;
        }

        .bill_items>table tr:not(:last-child) {
            border-bottom: 1px solid black !important;
        }

        .bill_items>table tr th {
            color: black !important;
            border-bottom: 1px solid black !important;
        }

        .bill_items>table td {
            padding: 5px;
        }

        .bill_items>table td span {
            font-size: 10px !important;
        }

        .bill_footer>table td {
            padding: 3px 5px;
            font-size: 12px !important;
        }

        .bill_footer thead tr {
            background-color: white !important;
            border-bottom: 1px solid black !important;
        }

        .bill_info_footer {
            background-color: white !important;
        }

        #copy_icon {
            display: none !important;
        }

        #action_message {
            display: none !important;
        }

    }

    /* nisha css */
    .tahvilgirande-box,
    .description-box {
        border: 1px dashed #dddddd;
        padding: 2px 10px;
        border-radius: 10px;
    }

    .tahvilgirande-box {
        width: 200px;
    }

    .tahvilgirande-box-header {
        font-weight: bold;
        font-size: 13px;
        text-align: center;
        color: #616060;
        margin-bottom: 18px;
        border-bottom: 1px solid #dddddd;
        line-height: 25px;
    }

    .tahvilgirande-box-inner div {
        width: 50%;
        height: 25px;
        color: #7d7c7c;
        font-size: 12px;
    }

    .footer-box {
        border-top: 1px solid #dddddd;
        text-align: center;
        margin-top: 10px;
    }

    .footer-box-adress {
        line-height: 35px;
        font-size: 13px;
    }

    p.footer-box-tell span {
        padding: 5px;
        font-size: 14px;
    }

    .nisha-bill-info {
        border: 1px solid #dddddd;
        border-radius: 10px;
        width: 160px;
        height: 60px;

    }

    .A-main div,
    .B-main div {
        height: 29px;
        float: right;

    }

    .A-1 {
        border-radius: 0 10px 0 0;
    }

    .B-1 {
        border-radius: 0 0 10px 0;
    }

    .A-1,
    .B-1 {
        font-weight: bold;
        width: 40%;
        background: #efefef;
        text-align: center;
        line-height: 30px;
        font-size: 12px
    }

    .A-2,
    .B-2 {
        text-align: center;
        width: 60%;
        line-height: 34px;
        font-size: 12px
    }

    .B-2 {
        border-top: 1px solid #dddddd;
    }
</style>