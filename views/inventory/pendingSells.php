<?php
$pageTitle = "فاکتورهای منتظر خروج";
$iconUrl = 'pending.svg';
require_once './components/header.php';
require_once '../../layouts/inventory/nav.php';
require_once '../../app/controller/inventory/pendingSellsController.php';
require_once '../../layouts/inventory/sidebar.php';
$dateTime = jdate('Y-m-d'); ?>
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <img src="../../public/img/<?= $iconUrl ?>" alt="icon" class="w-6 h-6">
            <?= $pageTitle ?>
        </h1>
        <span class="text-sm text-gray-500">
            تاریخ:
            <span class="inline-block" dir="rtl">
                <?= $dateTime ?>
            </span>
        </span>
    </div>

    <!-- Factors Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-lg text-gray-700">لیست فاکتورها</h2>
            <input type="text" placeholder="جستجو..."
                class="px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">شماره فاکتور</th>
                        <th class="px-4 py-3">مشتری</th>
                        <th class="px-4 py-3">تاریخ</th>
                        <th class="px-4 py-3">مبلغ فاکتور</th>
                        <th class="px-4 py-3">وضعیت</th>
                        <th class="px-4 py-3">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    // Example data - replace with DB fetch
                    $factors = $allPendingSells; // Assume this variable is populated with pending sells data

                    foreach ($factors as $i => $f):
                        $statusMatch = $f['bill_quantity'] == $f['difference']; // Example condition
                    ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3"><?= $i + 1 ?></td>
                            <td class="px-4 py-3 font-medium"><?= $f['bill_number'] ?></td>
                            <td class="px-4 py-3"><?= $f['name'] . ' ' . $f['family'] ?></td>
                            <td class="px-4 py-3"><?= $f['bill_date'] ?></td>
                            <td class="px-4 py-3 text-gray-700"><?= number_format($f['total']) ?> ریال</td>
                            <td class="px-4 py-3">
                                <?php if ($f['exit_quantity'] > 0): ?>
                                    <?php if ($statusMatch): ?>
                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                            مطابقت دارد
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                            مغایرت دارد
                                        </span an>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold text-yellow-600 bg-yellow-100 rounded-full">
                                        خروج نخورده
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 flex gap-2">
                                <a class="hide_while_print" href="../factor/complete.php?factor_number=<?= $f['id'] ?>">
                                    <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده فاکتور" src="./assets/icons/receipt.svg" />
                                </a>
                                <a class="hide_while_print" href="../factor/externalView.php?factorNumber=<?= $f['id'] ?>">
                                    <img class="w-6 mr-4 cursor-pointer d-block" title="مشاهده جزئیات" src="./assets/icons/telescope.svg" />
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once './components/footer.php';
?>