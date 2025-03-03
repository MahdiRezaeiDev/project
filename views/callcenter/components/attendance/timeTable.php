<td class="py-3 text-center  font-semibold text-gray-800">
    <?php
    $date = strtotime("+$counter days", $startDate);

    // Convert to Jalali date
    $reportDate = date("Y-m-d", $date);

    $START_HOUR = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
    $END_HOUR = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);
    ?>
    <table class="w-full text-sm text-left rtl:text-right text-gray-800 h-full">
        <?php if (count($START_HOUR) > 0) { ?>
            <thead class="text-sm text-gray-700 uppercase bg-gray-500">
                <tr>
                    <th class="text-xs text-center p-2 text-white">ورود</th>
                    <th class="text-xs text-center p-2 text-white">خروج</th>
                    <th class="text-xs text-center p-2 text-white">*</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $Rule = getUserAttendanceRule($user['selectedUser']);
                $startTime = $Rule['start_hour'];
                $endTime = $Rule['end_hour'];

                foreach ($START_HOUR as $index => $item): ?>
                    <tr class="text-sm text-gray-800 border-b">
                        <td class="text-xs text-center p-1 bg-green-200">
                            <?= date('H:i', strtotime($item['timestamp'])) ?>
                            <?php
                            if (strtotime($item['timestamp']) > strtotime($startTime)) {
                                $delay = round((strtotime($item['timestamp']) - strtotime($startTime)) / 60);

                                if ($delay > 0) {
                                    echo '<p class="text-xs text-white py-1 bg-gray-400">' . $delay . ' دقیقه</p>';
                                }
                            }
                            ?>
                        </td>
                        <td class="text-xs text-center p-1 bg-rose-300">
                            <?php

                            if (array_key_exists($index, $END_HOUR)) {
                                echo date('H:i', strtotime($END_HOUR[$index]['timestamp']));
                                $calculate = round((strtotime($endTime) - strtotime($END_HOUR[$index]['timestamp'])) / 60);
                                if (strtotime($END_HOUR[$index]['timestamp']) > strtotime($endTime)) {
                                    echo '<p class="text-xs text-white py-1 bg-gray-400">اضافه کار ' . abs($calculate) . ' دقیقه</p>';
                                }
                            } else {
                                echo '';
                            }
                            ?>
                        </td>
                        <td class="text-xs text-center p-1 bg-sky-200">
                            <span
                                data-user="<?= $user['name'] . ' ' . $user['family'] ?>"
                                data-selectedUser="<?= $user['selectedUser'] ?>"
                                data-start_id="<?= $item['id'] ?>"
                                data-end_id="<?= $END_HOUR[$index]['id'] ?>"
                                data-start="<?= date('h:i', strtotime($item['timestamp'])) ?>"
                                data-end="<?= date('h:i', strtotime($END_HOUR[$index]['timestamp'])) ?>"
                                onclick="editWorkHour(this)"
                                class="text-blue-500 hover:text-blue-700 cursor-pointer">
                                <img class="w-4 h-4" src="./assets/icons/edit.svg" alt="">
                            </span>
                        </td>
                    </tr>
            <?php
                endforeach;
            } else {
                $today = date('Y-m-d'); // Get today's date

                if (strtotime($reportDate ) > strtotime($today)) {
                    echo '<tr><td colspan="4" class="text-center text-green-700">ثبت نشده</td></tr>';
                } else {
                    echo '<tr><td colspan="4" class="text-center text-red-500">غایب</td></tr>';
                }
            }
            ?>
    </table>
</td>