<?php
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.sms.ir/v1/send/bulk',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        "lineNumber" => "90004752",
        "MessageText" => "نیایش رحیمی عزیز،\nخرید شما با مبلغ xxx ریال در تاریخ 18/9 با موفقیت ثبت شد.\nشماره فاکتور شما: xxxx\n\nیدک شاپ از انتخاب شما سپاسگزار است و خوشحالیم که همراه شما هستیم.\nبرای مشاهده محصولات بیشتر و اطلاعات کامل، به سایت ما مراجعه کنید:\n www.yadak.shop\nمنتظر خریدهای بعدی شما هستیم",
        "Mobiles" => [
            "09123612779"
        ]
    ]),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: text/plain',
        'x-api-key: ' . '9tK8aP3sHQ5wWt8nBLi2D6CyoBoR8qTbKJkwujbCUrMsTUXw',
    ),
));
$response = curl_exec($curl);
curl_close($curl);
echo $response;
