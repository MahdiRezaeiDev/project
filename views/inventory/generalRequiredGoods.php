<?php
$pageTitle = "کالاهای مورد نیاز";
$iconUrl = 'purchase.svg';
require_once './components/header.php';
require_once '../../app/controller/inventory/GeneralRequiredGoodsController.php';
require_once '../../utilities/inventory/InventoryHelpers.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../layouts/inventory/sidebar.php';
?>
<div class="w-2/3 mx-auto overflow-hidden">
    <ul class="flex gap-2 mb-4 bg-gray-600">
        <li class="bg-gray-900">
            <a class="flex justify-between items-center border-t-2 border-orange-600 gap-3 text-sm font-semibold text-white p-3" href="#/one">
                لیست کسرات کلی اجناس
                <span class="bg-red-600 p-2 rounded-full w-10 h-10"><?= count($needToMove) ?></span>
            </a>
        </li>
    </ul>
    <div>
        <?php
        foreach ($needToMove as $index => $row) :
            $counter = 1;
            $original = $row['original'];
            $sumOriginal = $row['sumOriginal'];
            $fakeNeed = $row['fake'];
            $sumFake = $row['sumFake'];
            $isSingle = $row['IsSingle'];
        ?>
            <table class="w-full mb-10">
                <thead>
                    <tr class="bg-gray-800">
                        <th class="text-white p-3 text-sm">#</th>
                        <th class="text-white p-3 text-sm">شماره فنی</th>
                        <th class="text-white p-3 text-sm">اصلی موجود</th>
                        <th class="text-white p-3 text-sm">کپی موجود</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($row['goods'] as $key => $element) :
                        $original_limit = $element['original'];
                        $fake = $element['fake'] ?>
                        <tr>
                            <td class="text-xs text-center bg-lime-200 w-2.5 font-semibold"><?= $counter ?></td>
                            <td class="p-2 text-center text-lg p-3 text-white font-semibold uppercase bg-sky-500"><?= getPartNumber($key) ?></td>
                            <td class="p-2 text-center text-sm font-semibold bg-lime-200"><?= $original_limit ?></td>
                            <td class="p-2 text-center text-sm font-semibold bg-lime-200"><?= $fake ?></td>
                        </tr>
                    <?php
                        $counter++;
                    endforeach; ?>
                    <tr style="background-color: #fea901;">
                        <td rowspan="2" style=" writing-mode: vertical-lr;">
                            <small class="font-bold p-2" style="text-orientation: mixed !important; font-size:12px">
                                توضیحات
                            </small>
                        </td>
                        <td class="font-bold text-center text-sm text-white" rowspan="2">
                            <?= $isSingle ? getPartNumber($key) : getRelationInfo($index); ?></td>
                        <td class="font-bold text-center text-sm text-white" style="background-color: <?= $sumOriginal < $original  ? 'red' :  'green' ?>;">
                            موجود :
                            <?= $sumOriginal ?>
                        </td>
                        <td class="font-bold text-center text-sm text-white" style="background-color: <?= $fakeNeed > $sumFake ? 'red' :  'green' ?>;">
                            موجود:
                            <?= $sumFake ?>
                        </td>
                    </tr>
                    <tr style="background-color: #fea901;">
                        <td class="font-bold text-center text-sm text-white" style="background-color: <?= $original <= $sumOriginal ? 'green' : 'red' ?>;">
                            مورد نیاز:
                            <?= $original ?>
                        </td>
                        <td class="font-bold text-center text-sm text-white" style="background-color: <?= $fakeNeed > $sumFake ? 'red' :  'green' ?>;">
                            مورد نیاز:
                            <?= $fakeNeed ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php

        endforeach;
        ?>
    </div>
</div>
<?php
require_once './components/footer.php';
?>