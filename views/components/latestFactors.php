<div class="bg-white rounded-lg p-5 shadow-md hover:shadow-xl">
    <div class="border border-dashed border-gray-800 flex flex-col items-center h-full rounded-lg">
        <div class="overflow-x-auto shadow-md sm:rounded-lg w-full h-full">
            <table class="w-full text-sm text-left rtl:text-right text-gray-800 h-full">
                <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                    <tr>
                        <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                            مشتری
                        </th>
                        <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                            شماره فاکتور
                        </th>
                        <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                            قیمت کل
                        </th>
                        <th scope="col" class="font-semibold text-sm text-right text-gray-800 px-6 py-3">
                            تاریخ فاکتور
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach (getLatestFactors() as $factor) : ?>
                        <tr class="border-b/10 hover:bg-gray-50 even:bg-gray-100">
                            <th class="px-6 py-3  font-semibold text-gray-800 text-right">
                                <?= $factor['name'] . ' ' . $factor['family'] ?>
                            </th>
                            <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                <a class="text-blue-500 underline" title="مشاهده فاکتور" href="../factor/complete.php?factor_number=<?= $factor['id'] ?>">
                                    <?= $factor['bill_number'] ?>
                                </a>
                            </td>
                            <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                <?= formatAsMoney($factor['total']) ?>
                            </td>
                            <td class="px-6 py-3  font-semibold text-right text-gray-800">
                                <?= $factor['bill_date'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>