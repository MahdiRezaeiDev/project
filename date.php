<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jalali Date Picker with Gregorian Conversion</title>

    <!-- Tailwind CSS for styling (optional) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Jalali Datepicker CSS -->
    <link rel="stylesheet" href="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-6 rounded shadow max-w-md w-full">
        <label for="jalali-date" class="block text-gray-700 mb-2">تاریخ شمسی را انتخاب کنید:</label>
        <input
            type="text"
            data-jdp
            id="jalali-date"
            placeholder="مثلاً 1403/03/15"
            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring">

        <!-- Hidden Gregorian input -->
        <input type="hidden" id="gregorian-date" name="gregorian_date">

        <p class="mt-4 text-sm text-gray-600">
            تاریخ میلادی انتخاب‌شده:
            <span id="gregorian-output" class="font-medium text-blue-600">---</span>
        </p>
    </div>

    <!-- Scripts -->
    <!-- Jalali Date Picker -->
    <script src="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>

    <!-- moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <!-- moment-jalaali -->
    <script src="https://cdn.jsdelivr.net/npm/moment-jalaali@0.9.2/build/moment-jalaali.js"></script>

    <script>
        // Initialize jalaliDatepicker
        jalaliDatepicker.startWatch();

        // Correct conversion on change
        document.getElementById('jalali-date').addEventListener('change', function() {
            const jalaliDate = this.value; // e.g. "1403/03/15"

            // Correct conversion using moment-jalaali
            const gregorianDate = moment(jalaliDate, 'jYYYY/jMM/jDD').format('YYYY-MM-DD');

            // Set value in hidden input
            document.getElementById('gregorian-date').value = gregorianDate;

            // Show for display
            document.getElementById('gregorian-output').textContent = gregorianDate;
        });
    </script>
</body>

</html>