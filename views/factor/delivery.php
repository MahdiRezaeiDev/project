<div id="deliveryModal" class="hidden fixed inset-0 bg-gray-900/75 flex justify-center items-center">
    <div class="bg-white p-4 rounded w-2/3">
        <div class="flex  items-center">
            <h2 class="font-semibold text-xl mb-2" style="flex: 1;">ارسال اجناس</h2>
            <div class="flex justify-between items-center">

                <button id="printBtn" command="show-modal" commandfor="dialog">
                    <img id="printIcon" class="printfactor rounded-md p-2" src="./assets/img/print-factor.svg" alt="print icon" onclick="document.getElementById('dialog').classList.remove('hidden') , printDeliveryInfo(); ">
                </button>

                <img class="cursor-pointer closedelivery rounded-md p-2" src="./assets/img/close.svg" alt="close icon" onclick="closeDelivery(); ">

            </div>

        </div>

        <div class="modal-body">
            <table class="w-full my-4 ">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="text-xs text-white font-semibold p-3">شماره فاکتور</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-100">
                        <td class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_billNumber"></td>
                    </tr>
                </tbody>
            </table>


            <form action="" id="deliveryForm" onsubmit="saveDelivery(event)" class="mt-4">
                <input type="hidden" name="billNumber" id="deliveryBillNumber" value="">



                <div class="grid grid-cols-3 gap-4">
                    <div class="mt-4">
                        <label class="block text-sm font-semibold mb-2" for="deliveryType">روش ارسال:</label>
                        <select required id="deliveryType" name="deliveryType" class="w-full border-2 border-gray-300 p-2 rounded">
                            <option value="پیک مشتری">پیک خود مشتری</option>
                            <option value="پیک خود مشتری بعد از اطلاع">پیک خود مشتری بعد از اطلاع </option>
                            <option value="پیک یدک شاپ">پیک یدک شاپ</option>
                            <option value="اتوبوس">اتوبوس</option>
                            <option value="تیپاکس">تیپاکس</option>
                            <option value="سواری">سواری</option>
                            <option value="باربری">باربری</option>
                            <option value="هوایی">هوایی</option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold mb-2" for="deliverycost">هزینه ارسال:</label>
                        <select required id="deliverycost" name="deliverycost" class="w-full border-2 border-gray-300 p-2 rounded">
                            <option value="">انتخاب کنید</option>
                            <option value="70">70 هزار تومان</option>
                            <option value="100">100 هزار تومان </option>
                            <option value="150">150 هزار تومان</option>
                            <option value="200">200 هزار تومان</option>
                            <option value="other">سایر</option>
                        </select>

                        <input type="number" placeholder="هزینه دیگر را وارد کنید" name="deliverycostother" id="deliverycostother" class="w-full border-2 border-gray-300 p-2 rounded mt-2" style="display:none;">
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold mb-2" for="courier">پیک</label>
                        <select id="courier" name="courier" class="w-full border-2 border-gray-300 p-2 rounded">
                            <option value="">انتخاب کنید</option>
                            <option value="آقای عباسی">آقای عباسی</option>
                            <option value="آقای امیر دوست">اقای امیردوست</option>
                            <option value="other">سایر</option>
                        </select>

                        <input type="text" placeholder="نام پیک را وارد کنید" name="courier" id="courierother" class="w-full border-2 border-gray-300 p-2 rounded mt-2" style="display:none;">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-2" for="address">آدرس مقصد:</label>
                    <input type="text" id="address" name="address" class="w-full border-2 border-gray-300 p-2 rounded" placeholder="آدرس ارسال را وارد کنید...">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="mt-4">
                        <p><strong>مبلغ کل</strong>
                            <span class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_total"></span>
                        </p>

                    </div>
                    <div class="mt-4">
                        <p><strong>مبلغ پرداخت شده</strong>

                            <span class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_paid"></span>
                        </p>
                    </div>
                    <div class="mt-4">
                        <p><strong>مبلغ باقی مانده</strong>
                            <span class="text-gray-600 text-xs p-3 text-center font-semibold" id="display_remaining">
                            </span>
                        </p>
                        <input type="number" placeholder="مبلغ باقی مانده واقعی " name="peymentother" id="peymentother" class="w-full border-2 border-gray-300 p-2 rounded mt-2" readonly>

                    </div>


                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-2" for="explain"> <span id="requiredfild" style="color:red; display:none;">*</span>

                        توضیحات</label>
                    <textarea type="text" id="explain" name="explain" class="w-full border-2 border-gray-300 p-2 rounded" placeholder="توضیحات را وارد کنید"></textarea>
                </div>

                <div class="mt-4">
                    <input type="checkbox" id="needcall" name="needcall" />
                    <label for="needcall">نیاز به تماس با مشتری</label>
                </div>


                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">ثبت ارسال</button>
                </div>


            </form>
        </div>

        <el-dialog>
            <dialog id="dialog" aria-labelledby="dialog-title" class="fixed inset-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent hidden">
                <el-dialog-backdrop class="fixed inset-0 bg-gray-500/75 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>

                <div tabindex="0" class="flex min-h-full items-end justify-center p-4 text-center focus:outline-none sm:items-center sm:p-0">
                    <el-dialog-panel class="relative transform overflow-hidden rounded-lg bg-white text-left transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in sm:my-8 sm:w-full sm:max-w-lg data-closed:sm:translate-y-0 data-closed:sm:scale-95">
                        <div class="bg-white  sm:p-6 sm:pb-2">

                            <button type="button" command="close" onclick="closeDelivery();" commandfor="dialog">
                                <img class="hide_while_print cursor-pointer closedelivery rounded-md p-2" src="./assets/img/close.svg" alt="close icon">
                            </button>
                            <div id="printArea" style="display:none;"></div>
                            <div id="deliveryPrint" dir="rtl" class="print-area font-[tahoma] text-gray-800 text-sm leading-relaxed"> </div>

                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 hide_while_print">

                            <button onclick="window.print()" class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 shadow-sm hover:bg-gray-200 transition-all sm:w-auto">
                                <img src="./assets/img/print-factor.svg" alt="print icon" class="w-5 h-5">
                                چاپ
                            </button>

                        </div>

                    </el-dialog-panel>
                </div>
            </dialog>
        </el-dialog>
    </div>
</div>

<script type="text/javascript" src="../../views/factor/assets/js/Delivery.js">
</script>
