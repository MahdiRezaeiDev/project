<ul class="action_menu">
    <li style="position: relative;">
        <a class="action_button print bg-white rounded-full flex justify-center items-center text-white text-sm" href="./yadakFactor.php?factorNumber=<?= $BillInfo['id'] ?>">
            <img src="./assets/img/logo.png" class="rounded-full" alt="">
        </a>
        <p class="action_tooltip text-sm">فاکتور یدک شاپ</p>
    </li>
    <li style="position: relative;">
        <a class="action_button print bg-green-500 rounded-full flex justify-center items-center text-white text-sm" href="./insuranceFactor.php?factorNumber=<?= $BillInfo['id'] ?>">بیمه</a>
        <p class="action_tooltip">فاکتور بیمه</p>
    </li>
    <li style="position: relative;">
        <a class="action_button print bg-blue-500 rounded-full flex justify-center items-center text-white text-sm" href="./partnerFactor.php?factorNumber=<?= $BillInfo['id'] ?>">همکار</a>
        <p class="action_tooltip">فاکتور همکار</p>
    </li>
    <li style="position: relative;">
        <a class="action_button print bg-gray-500 rounded-full flex justify-center items-center text-white text-sm" href="./koreaFactor.php?factorNumber=<?= $BillInfo['id'] ?>">کوریا</a>
        <p class="action_tooltip">فاکتور کوریا</p>
    </li>
    <li style="position: relative;">
        <img class="action_button print" onclick="handlePrint('<?= $factorNumber ?>')" src="./assets/img/print.svg" alt="print icon">
        <p class="action_tooltip">پرینت</p>
    </li>
    <li style="position: relative;">
        <img class="action_button share" src="./assets/img/share.svg" alt="print icon">
        <p class="action_tooltip">اشتراک گذاری</p>
    </li>
    <li style="position: relative;">
        <img class="action_button pdf" src="./assets/img/pdf.svg" onclick="handleSaveAsPdfClick()" alt="print icon">
        <p class="action_tooltip">پی دی اف</p>
    </li>
</ul>
<script>
    function handlePrint(factorNumber) {
        const params = new URLSearchParams();
        params.append('factorNumber', factorNumber);
        params.append('action', 'print');

        axios.post('../../app/api/factor/CompleteFactorApi.php', params).then(res => {
            console.log(res);
        }).catch(err => {
            console.log(err);
        });

        window.print();
    }
</script>