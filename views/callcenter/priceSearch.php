<?php
$pageTitle = "تاریخچه کدهای جستجو شده";
$iconUrl = 'history.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/OrderedPriceSearchController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<!-- ------------------- DASHBOARD MESSAGES REPORTS SECTION ----------------------------- -->
<section class="mx-auto rtl bg-gray-100 mb-5">
    <div class="grid grid-cols-1 lg:grid-cols-3 px-5 gap-5">
        <div class="bg-white rounded-lg overflow-hidden border border-gray-800 shadow-md hover:shadow-xl">
            <div class="flex items-center justify-between bg-gray-800 p-5">
                <h1 class="text-lg font-semibold text-white">
                    آمار درخواست های یک ساعت اخیر</h1>
                <a href="./priceSearchDetails.php?type=hour" class="text-sm text-blue-500">مشاهده همه</a>
            </div>
            <div class="shadow-md sm:rounded-lg w-full h-full">
                <table class="w-full text-sm text-left rtl:text-center text-gray-800">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                شماره
                            </th>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                کد درخواستی
                            </th>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                دفعات درخواست
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($lastHourMostRequested as $index => $request) : ?>
                            <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                                <th class="px-6 py-3  font-semibold text-gray-800 text-center">
                                    <?= ++$index; ?>
                                </th>
                                <th class="px-6 py-3  font-semibold text-gray-800 text-center">
                                    <?= $request['partNumber'] ?>
                                </th>
                                <td class="px-6 py-3  font-semibold text-center text-gray-800">
                                    <?= $request['quantity'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-lg overflow-hidden border border-gray-800 shadow-md hover:shadow-xl">
            <div class="flex items-center justify-between bg-gray-800 p-5">
                <h1 class="text-lg font-semibold text-white">آمار درخواست های امروز
                </h1>
                <a href="./priceSearchDetails.php?type=today" class="text-sm text-blue-500">مشاهده همه</a>
            </div>
            <div class="shadow-md sm:rounded-lg w-full h-full">
                <table class="w-full text-sm text-left rtl:text-center text-gray-800">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                شماره
                            </th>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                کد درخواستی
                            </th>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                دفعات درخواست
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($todayMostRequested as $index => $request) : ?>
                            <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                                <th class="px-6 py-3  font-semibold text-gray-800 text-center">
                                    <?= ++$index; ?>
                                </th>
                                <th class="px-6 py-3  font-semibold text-gray-800 text-center">
                                    <?= $request['partNumber'] ?>
                                </th>
                                <td class="px-6 py-3  font-semibold text-center text-gray-800">
                                    <?= $request['quantity'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-lg overflow-hidden border border-gray-800 shadow-md hover:shadow-xl">
            <div class="flex items-center justify-between bg-gray-800 p-5">
                <h1 class="text-lg font-semibold text-white">آمار درخواست های کلی</h1>
                <a href="./priceSearchDetails.php?type=all" class="text-sm text-blue-500">مشاهده همه</a>
            </div>
            <div class="shadow-md sm:rounded-lg w-full h-full">
                <table class="w-full text-sm text-left rtl:text-center text-gray-800">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                شماره
                            </th>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                کد درخواستی
                            </th>
                            <th scope="col" class="font-semibold text-sm text-center text-gray-800 px-6 py-3">
                                دفعات درخواست
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($allTimeMostRequested as $index => $request) : ?>
                            <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                                <th class="px-6 py-3  font-semibold text-gray-800 text-center">
                                    <?= ++$index; ?>
                                </th>
                                <th class="px-6 py-3  font-semibold text-gray-800 text-center">
                                    <?= $request['partNumber'] ?>
                                </th>
                                <td class="px-6 py-3  font-semibold text-center text-gray-800">
                                    <?= $request['quantity'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php
require_once './components/footer.php';
?>