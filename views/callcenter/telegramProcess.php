<?php
$pageTitle = "قیمت های تلگرام";
$iconUrl = 'b-t.png';
require_once './components/header.php';
require_once '../../app/controller/callcenter/TelegramAskedPricesController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<div id="full" class="h-screen flex flex-col justify-center items-center">
    <h1 class="text-6xl text-gray-600">لطفا صبور باشید</h1>
    <br>
    <img src="../../public/img/loading.png" class="w-20">
</div>
<script>
    const container = document.getElementById('full');
    const params = new URLSearchParams();
    params.append('getTelegramAutoMessage', 'getTelegramAutoMessage');
    axios
        .post("http://auto.yadak.center/", params)
        .then(function(response) {
            if (typeof response.data === 'object' && Object.keys(response.data).length !== 0) {
                try {
                    const jsonInput = document.createElement('input');
                    jsonInput.type = 'hidden';
                    jsonInput.name = 'jsonData';
                    jsonInput.value = JSON.stringify(response.data);

                    // Append the input field to the form
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.action = './orderedPriceTelegram.php'; // Leave empty to post to the same page
                    form.appendChild(jsonInput);

                    // Append the form to the body and submit it
                    document.body.appendChild(form);
                    form.submit();
                } catch (error) {
                    console.log(error);
                }
            } else {
                container.innerHTML = `
                            <h1 class="text-4xl text-gray-600">پیام جدیدی موجود نیست</h1>
                            <br>
                            <h3 class="text-3xl text-gray-600">لطفا بعدا تلاش نمایید</h3>
                            `;
            }
        })
        .catch(function(error) {});
</script>
<?php
require_once './components/footer.php';
