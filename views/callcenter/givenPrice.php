<?php
$pageTitle = "قیمت دستوری";
$iconUrl = 'ordered.png';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<div class="max-w-2xl mx-auto py-14 px-6 lg:px-8 bg-gray-200 rounded-lg shadow-s mt-32">
    <form target="_blank" action="./orderedPrice.php" method="post">
        <input type="text" name="givenPrice" value="givenPrice" id="form" hidden>
        <input type="text" name="user" value="<?= $_SESSION["id"] ?>" hidden>
        <input type="text" name="customer" value="1" id="target_customer" hidden>
        <div class="">
            <!-- Korea section -->
            <div class="col-span-6 sm:col-span-4">
                <label for="code" class="block text-lg font-semibold text-gray-900">
                    کدهای مدنظر
                </label>
                <textarea onkeyup="convertToEnglish(this)" onchange="filterCode(this)" rows="9" id="code" name="code" required class="border-2 border-gray-300 focus:border-gray-500 p-3 outline-none  text-sm mt-1 shadow-sm block w-full uppercase" style="direction: ltr !important;" placeholder="لطفا کد های مود نظر خود را در خط های مجزا قرار دهید"></textarea>
            </div>
        </div>
        <div class="py-4 flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="discount" value="0">
            <input id="discount" type="checkbox" name="discount" value="1">
            <label for="discount" class="text-sm">اعمال تخفیف</label>
        </div>

        <div class="flex items-center justify-between py-3 text-right sm:rounded-bl-md sm:rounded-br-md">
            <div class="flex gap-2 items-center">
                <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=0" class="cursor-pointer  text-white rounded bg-sky-600 hover:bg-sky-500 px-3 py-2 text-xs">پیش فاکتور مصرف کننده</button>
                <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=1" class="cursor-pointer bg-green-600 hover:bg-green-700 text-white rounded px-3 py-2 text-xs">پیش فاکتور همکار</button>
                <button type="submit" formaction="../factor/createPreCompleteBill.php?partner=1&insurance=1" class="cursor-pointer bg-rose-600 hover:bg-rose-700 text-white rounded px-3 py-2 text-xs">امانت نامه</button>
            </div>
            <button type="submit" class="inline-flex items-center px-5 py-2 bg-gray-800 font-semibold text-xs text-white hover:bg-gray-700 rounded"> جستجو
            </button>
        </div>
    </form>
</div>
<?php
require_once './components/footer.php';
