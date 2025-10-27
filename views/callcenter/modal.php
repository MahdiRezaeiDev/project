<?php
$pageTitle = "اطلاعات مشتری";
$iconUrl = 'callcenter.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/MainController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
function sendSMS($customer, $factorNumber, $factor, $uniqueId)
{
    $name = '';
    if (!empty($customer->displayName) || !empty($customer->family)) {
        $name = trim(($customer->displayName ?? '') . ' ' . ($customer->family ?? ''));
    }

    $phone = $customer->phone ?? '';
    $total = number_format($factor->totalPrice);
    $date = $factor->date;

    if ($name) {
        $message = "$name عزیز،\n";
    } else {
        $message = "کاربر گرامی،\n";
    }

    $message .= "خرید شما با مبلغ $total ریال در تاریخ $date با موفقیت ثبت شد.\n";
    $message .= "شماره فاکتور شما: $factorNumber\n";
    $message .= "مشاهده فاکتور: https://sells.yadak.shop/$uniqueId\n";
    $message .= "یدک شاپ از انتخاب شما سپاسگزار است و خوشحالیم که همراه شما هستیم.\n";
    $message .= "برای مشاهده محصولات بیشتر به سایت ما مراجعه کنید:\n";
    $message .= "www.yadak.shop";


    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.sms.ir/v1/send/bulk',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            "lineNumber" => "90004752",
            "MessageText" => $message,
            "Mobiles" => [$phone]
        ]),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: text/plain',
            'x-api-key: ' . '****',
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}
?>
<button onclick="toggleModal()">click</button>
<div id="smsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-lg w-96 p-6 text-right relative">

        <h2 class="text-xl font-bold mb-4 text-gray-700">ارسال پیامک</h2>

        <form method="POST">
            <label class="block mb-2 text-sm font-medium text-gray-700">شماره موبایل:</label>
            <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg p-2 mb-4 focus:ring-2 focus:ring-yellow-400 outline-none" placeholder="مثلاً 09123456789" value="<?php echo htmlspecialchars($phone ?? ''); ?>">

            <label class="block mb-2 text-sm font-medium text-gray-700">متن پیام:</label>
            <textarea id="messageInput" name="message" class="w-full border border-gray-300 rounded-lg p-2 mb-3 h-28 resize-none focus:ring-2 focus:ring-yellow-400 outline-none" placeholder="متن پیام خود را وارد کنید"></textarea>

            <!-- دکمه‌های متن آماده -->
            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" onclick="setMessage('سفارش شما با موفقیت ثبت شد. از خرید شما سپاسگزاریم 🙏')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-lg text-sm">
                    پیام ثبت سفارش
                </button>
                <button type="button" onclick="setMessage('سفارش شما ارسال شد 🚚. از همراهی شما سپاسگزاریم.')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-lg text-sm">
                    پیام ارسال سفارش
                </button>
                <button type="button" onclick="setMessage('با تشکر از خرید شما 🌟. لطفاً نظر خود را با ما به اشتراک بگذارید.')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-lg text-sm">
                    پیام تشکر
                </button>
            </div>

            <div class="flex justify-between">
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
                    ارسال
                </button>
                <button type="button" onclick="toggleModal()" class="px-4 py-2 border border-gray-400 rounded-lg hover:bg-gray-200 transition">
                    بستن
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('smsModal');
    const messageInput = document.getElementById('messageInput');

    function toggleModal() {
        modal.classList.toggle('hidden')
    }

    function setMessage(text) {
        messageInput.value = text;
    }
    window.onclick = function(e) {

        if (e.target === modal) toggleModal();
    }
</script>

<?php
require_once './components/footer.php';
