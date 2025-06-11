<div class="bill_items">
    <table>
        <thead>
            <tr class="bg-gray">
                <th class="text-sm text-right" style="width: 15px !important;">ردیف</th>
                <th class="text-sm text-right" style="width: 70%;" colspan="2">نام قطعه</th>
                <th class="text-sm text-center" style="width: 10px !important;"> تعداد</th>
                <th class="text-sm text-center" style="width: 80px !important;"> قیمت واحد</th>
                <th class="text-sm text-center" style="width: 80px !important;"> مجموع (ریال) </th>
            </tr>
        </thead>
        <tbody id="owner_bill_body">
        </tbody>
    </table>
</div>
<div class="bill_footer">
    <table class="w-full">
        <tbody>
            <tr class="bg-gray border-b border-gray-800">
                <td class="text-right w-8"></td>
                <td class="text-right text-sm font-semibold py-2" style="width: 80%; padding-block:10px !important;">جمع فاکتور</td>
                <td class="text-center w-12 border-r border-l-2 border-gray-800 text-sm font-semibold py-2">
                    <span id="quantity_owner" class="w-full p-2 text-sm font-semibold"></span>
                </td>
                <td class="text-right w-24 py-2" colspan="2">
                    <span id="totalPrice_owner" class="w-full py-2 text-sm font-semibold"></span>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="w-8 border-l-2 border-gray-800 text-left">تخفیف : </td>
                <td colspan="2" class="text-right w-8">
                    <span id="discount_owner"></span>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="padding:15px;" class="border-t border-b border-gray-800"></td>
                <td colspan="2" style="padding:15px;" class="border-t border-b border-gray-800"></td>
            </tr>
            <tr>
                <td class="text-right w-8"></td>
                <td class="text-right text-sm font-semibold" style="padding-block:10px !important;">
                    <p>مبلغ قابل پرداخت:
                        <span id="total_in_word_owner"></span>
                    </p>
                </td>
                <td class="text-center w-12 border-l-2 border-gray-800">

                </td>
                <td class="text-right w-28">
                    <span id="totalPrice2_owner" class="w-full font-bold"></span>
                </td>
                <td class="text-right w-28">
                </td>
            </tr>
        </tbody>
    </table>
</div>