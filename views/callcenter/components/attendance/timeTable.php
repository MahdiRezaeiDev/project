<td class="py-3 text-center font-semibold text-gray-800 align-top">
    <?php
    $date = strtotime("+$counter days", $startDate);
    $reportDate = date("Y-m-d", $date);
    $day = jdate("l", $date);

    $START_HOUR = getUserAttendanceReport('start', $user['selectedUser'], $reportDate);
    $END_HOUR   = getUserAttendanceReport('leave', $user['selectedUser'], $reportDate);
    $IS_OFF     = getUserAttendanceReport('off', $user['selectedUser'], $reportDate);
    $leaveRecords = getUserLeaveReport($user['selectedUser'], $reportDate);

    // اگر مرخصی روزانه
    if (count($IS_OFF) > 0) {
        echo '<p class="py-2 text-center bg-sky-600 text-white">مرخص</p>';
    }

    // جدول ورود و خروج
    if (count($START_HOUR) > 0 && count($IS_OFF) <= 0) {
        echo '<div class="border border-gray-400 mb-2 rounded overflow-hidden">';
        echo '<div class="bg-gray-500 text-white text-xs flex justify-around p-1">
                <span>ورود</span>
                <span>خروج</span>
                <span>*</span>
              </div>';

        $Rule = getUserAttendanceRule($user['selectedUser']);
        $startTime = $Rule['start_hour'];
        $endTime   = $Rule['end_hour'];
        $endWeek   = $Rule['end_week'];

        if ($day == 'پنجشنبه') {
            $endTime = $endWeek;
        }

        foreach ($START_HOUR as $index => $item) {
            echo '<div class="flex text-xs border-b">';
            // ورود
            echo '<div class="flex-1 bg-green-200 p-1">';
            echo date('H:i', strtotime($item['timestamp']));
            $delay = floor((strtotime($item['timestamp']) - strtotime($startTime)) / 60);
            if ($delay > 0) {
                echo '<p class="text-[10px] text-white py-1 bg-gray-400">' . $delay . ' دقیقه تاخیر</p>';
            } elseif ($delay < 0) {
                echo '<p class="text-[10px] text-white py-1 bg-gray-400">' . abs($delay) . ' دقیقه اضافه‌کار</p>';
            }
            echo '</div>';

            // خروج
            echo '<div class="flex-1 bg-rose-300 p-1">';
            if (array_key_exists($index, $END_HOUR)) {
                echo date('H:i', strtotime($END_HOUR[$index]['timestamp']));
                $calculate = floor((strtotime($endTime) - strtotime($END_HOUR[$index]['timestamp'])) / 60);
                if (strtotime($END_HOUR[$index]['timestamp']) > strtotime($endTime)) {
                    echo '<p class="text-[10px] text-white py-1 bg-gray-400">اضافه‌کار ' . abs($calculate) . '</p>';
                } elseif ($calculate < 0) {
                    echo '<p class="text-[10px] text-white py-1 bg-gray-400">' . abs($calculate) . ' دقیقه تعجیل</p>';
                }
            }
            echo '</div>';

            // اکشن
            echo '<div class="flex-1 bg-sky-200 p-1 text-center">';
            $endId = isset($END_HOUR[$index]['id']) ? (int)$END_HOUR[$index]['id'] : '';
            $endTime = isset($END_HOUR[$index]['timestamp']) ? date('H:i', strtotime($END_HOUR[$index]['timestamp'])) : '';

            echo '<div 
                    data-user="' . htmlspecialchars($user['name'] . ' ' . $user['family']) . '" 
                    data-selectedUser="' . htmlspecialchars($user['selectedUser']) . '" 
                    data-start_id="' . (int)$item['id'] . '" 
                    data-end_id="' . $endId . '" 
                    data-start="' . date('H:i', strtotime($item['timestamp'])) . '" 
                    data-end="' . $endTime . '"
                    onclick="editWorkHour(this)"
                    >
                    
                    <img class="w-4 h-4 mx-auto cursor-pointer" 
                        title="ویرایش" 
                        src="./assets/icons/edit.svg" 
                        alt="edit icon" 
                        >
                </div>';

            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } elseif (count($IS_OFF) <= 0) {
        // غیبت / تعطیل
        if ($counter == 6) {
            echo '<p class="text-center text-red-500">تعطیل</p>';
        } elseif (strtotime($reportDate) > strtotime($today)) {
            echo '<p class="text-center text-green-700">ثبت نشده</p>';
        } elseif (count($leaveRecords) < 0) {
            echo '<p class="text-rose-500">غایب</p>';
        }
    }

    // مرخصی‌های ساعتی
    if (count($leaveRecords) > 0) {
        echo '<div class="border border-yellow-400 rounded overflow-hidden">';
        echo '<div class="bg-gray-500 text-white text-xs flex justify-around p-1">
                <span>نوع</span>
                <span>ساعات</span>
                <span>علت</span>
                <span>*</span>
              </div>';
        foreach ($leaveRecords as $record) {
            echo '<div class="flex text-xs border-b">';
            echo '<div class="flex-1 bg-yellow-200 p-1">مرخص</div>';
            echo '<div class="flex-1 bg-yellow-200 p-1">'
                . date('H:i', strtotime($record['start_time'])) . ' - '
                . date('H:i', strtotime($record['end_time'])) . '</div>';
            echo '<div class="flex-1 bg-yellow-200 p-1">' . ($record['reason'] ?: '-') . '</div>';
            echo '<div class="flex-1 bg-yellow-200 p-1 text-center" onclick="deleteLeave(' . $record['id'] . ')">
                    <img class="w-4 h-4 mx-auto cursor-pointer" 
                        title="حذف" 
                        src="./assets/img/deleteBill.svg" 
                        alt="delete icon">
            </div>';
            echo '</div>';
        }
        echo '</div>';
    }
    ?>
</td>