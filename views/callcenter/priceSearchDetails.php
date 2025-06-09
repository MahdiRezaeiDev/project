<?php
$pageTitle = "اطلاعات کدهای دستوری جستجو شده";
$iconUrl = 'telegram.svg';
require_once './components/header.php';
require_once '../../app/controller/callcenter/OrderedPriceSearchDetailsController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php'; ?>
<div class="max-w-7xl mx-auto my-5 bg-white rounded-lg overflow-hidden border border-gray-800 shadow-md hover:shadow-xl">
    <div class="flex items-center justify-between bg-gray-800 p-5">
        <h1 class="text-lg font-semibold text-white">
            <?= $title ?>
        </h1>
        <ul class="flex gap-2">
            <li>
                <a href="./priceSearchDetails.php?type=hour&code=<?= $code ?>" class="bg-blue-500 text-xs text-white rounded-sm px-2 py-1">یک ساعت اخیر</a>
            </li>
            <li>
                <a href="./priceSearchDetails.php?type=today&code=<?= $code ?>" class="bg-blue-500 text-xs text-white rounded-sm px-2 py-1"> امروز </a>
            </li>
            <li>
                <a href="./priceSearchDetails.php?type=quarter&code=<?= $code ?>" class="bg-blue-500 text-xs text-white rounded-sm px-2 py-1"> ۳ روز اخیر </a>
            </li>
            <li>
                <a href="./priceSearchDetails.php?type=week&code=<?= $code ?>" class="bg-blue-500 text-xs text-white rounded-sm px-2 py-1"> یک هفته اخیر </a>
            </li>
            <li>
                <a href="./priceSearchDetails.php?type=month&code=<?= $code ?>" class="bg-blue-500 text-xs text-white rounded-sm px-2 py-1"> یک ماه اخیر </a>
            </li>
            <li>
                <a href="./priceSearchDetails.php?type=all&code=<?= $code ?>" class="bg-blue-500 text-xs text-white rounded-sm px-2 py-1">مشاهده همه</a>
            </li>
        </ul>
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
                if (count($requests)) :
                    foreach ($requests as $index => $request) : ?>
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
                    <?php
                    endforeach;
                else : ?>
                    <tr>
                        <td colspan="3" class="text-center text-red-500 font-semibold p-3">هیچ اطلاعاتی برای نمایش وجود ندارد</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
require_once './components/footer.php';
?>