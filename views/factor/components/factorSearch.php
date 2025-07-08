<details>
    <summary class="bg-gray-800 text-white p-4 cursor-pointer">جستجو</summary>
    <!-- search and initialing data section -->
    <div style="height: 350px !important;" class="h-1/3 grid grid-cols-1 md:grid-cols-3 gap-2 lg:gap-3 px-2 p-2">
        <!-- Search for customer section -->
        <section class="bg-white min-h-full shadow-md">
            <div class="flex items-center justify-between p-3">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <img src="./assets/img/customer.svg" alt="customer icon">
                    انتخاب مشتری
                </h2>
            </div>
            <div class="relative flex justify-center px-3">
                <input onkeyup="convertToPersian(this); searchCustomer(this.value)" type="text" name="customer" class="py-3 px-3 w-full border-2 text-sm border-gray-300 focus:outline-none text-gray-500" id="customer_name" min="0" max="30" placeholder=" اسم کامل مشتری را وارد نمایید ..." />
                <img class="absolute left-5 top-3 cursor-pointer" onclick="(() => {searchCustomer('');document.getElementById('customer_name').value = '';})();" src="./assets/img/clear.svg" alt="customer icon">
            </div>
            <div class="hidden sm:block">
                <div class="py-2">
                    <div class="border-t border-gray-200"></div>
                </div>
            </div>
<div id="customer_results" style="overflow-y: auto; height:220px" class="p-3 overflow-y-auto">
                            <!-- Search Results are going to be appended here -->
            </div>
        </section>

        <!-- search for goods base on the part number section -->
        <section class="bg-white min-h-full shadow-md">
            <div class="flex items-center justify-between p-3">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <img src="./assets/img/barcode.svg" alt="customer icon">
                    انتخاب کد فنی
                </h2>
            </div>
            <div class="relative flex justify-center px-3">
                <input onkeyup="convertToEnglish(this); searchPartNumber(this.value)" type="text" name="serial" id="serial" class="py-3 px-3 w-full border border-2 text-sm border-gray-300 focus:outline-none text-gray-500" min="0" max="30" placeholder="کد فنی قطعه مورد نظر را وارد کنید..." />
                <img class="absolute left-5 top-3 cursor-pointer" onclick="(() => {searchPartNumber('');document.getElementById('serial').value = '';})();" src="./assets/img/clear.svg" alt="customer icon">
            </div>
            <div class="hidden sm:block">
                <div class="py-2">
                    <div class="border-t border-gray-200"></div>
                </div>
            </div>
            <p id="select_box_error" class="px-3 tiny-text text-red-500 hidden">
                لیست اجناس انتخاب شده برای افزودن به رابطه خالی بوده نمیتواند!
            </p>
            <div id="selected_box" class="p-3" style="overflow-y: auto; height:220px">
                <!-- selected items are going to be added here -->
            </div>
        </section>

        <!-- Search in the stock base on existing using part number section -->
        <section class="bg-white min-h-full shadow-md">
            <div class="p-3">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <img src="./assets/img/inventory.svg" alt="inventory icon">
                    انتخاب کالای موجود
                </h2>
            </div>

            <div class="relative flex justify-center px-3">
                <input onkeyup="convertToEnglish(this); searchInStock(this.value)" type="text" name="stock_partNumber" id="stock_partNumber" class="py-3 px-3 w-full border border-2 text-sm border-gray-300 focus:outline-none text-gray-500" min="0" max="30" placeholder="کدفنی قطعه مورد نظر خویش را وارد کنید..." />
                <img class="absolute left-5 top-3 cursor-pointer" onclick="(() => {
                                                                                    searchInStock('');
                                                                                    document.getElementById('stock_partNumber').value = '';
                                                                                })();" src="./assets/img/clear.svg" alt="customer icon">

            </div>

            <div class="hidden sm:block">
                <div class="py-2">
                    <div class="border-t border-gray-200"></div>
                </div>
            </div>
            <div id="stock_result" class="p-3" style="overflow-y: auto; height:300px"></div>
        </section>
    </div>
</details>