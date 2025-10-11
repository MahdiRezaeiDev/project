 <!-- Start the code info section -->
 <div class="w-full bg-white <?= $infoSize ?> shadow-md rounded-md p-2">
     <p class="text-sm text-center bg-gray-700 text-white p-3 font-bold">
         <?= strtoupper($index); ?>
     </p>
     <?php if ($information) : ?>
         <div class="bg-blue-400 p-3 text-sm text-white">
             <p class="my-2 text-rose-600 font-semibold"> قطعه:</p>
             <ul>
                 <li class=""><?= $information['relationInfo']['name'] . '<br>'; ?></li>
             </ul>

             <?php if (array_key_exists("status_name", $information['relationInfo'])) : ?>
                 <span class="my-2 text-rose-600 font-semibold"> وضعیت: </span> <span><?= $information['relationInfo']['status_name'] ?></span>
             <?php endif; ?>

             <p class="my-2 text-rose-600 font-semibold"> خودروها:</p>
             <ul class="mb-5">
                 <?php foreach ($information['cars'] as $item) :
                    ?>
                     <li class="">
                         <?= $item ?>
                     </li>
                 <?php endforeach; ?>
             </ul>
             <?php if (!empty($information['relationInfo']['description'])) : ?>
                 <p class="mt-3 text-rose-600 font-semibold">توضیحات:</p>
                 <p class="bg-red-500 text-white rounded-md p-2 shake">
                     <?= $information['relationInfo']['description'] ?>
                 </p>
             <?php endif; ?>
         </div>
         <p class="my-2 font-semibold">قطعات مرتبط: </p>
         <table>
             <?php $first = true; ?>
             <?php foreach ($goods as $key => $item) : ?>
                 <?php if ($first) : ?>
                     <!-- First Item (Soft Highlight) -->
                     <tr>
                         <td colspan="2">
                             <span class="text-xs text-gray-400">
                                 لطفا برای ویرایش اسم جنس در فاکتور به روی اسم اولین آینم دبل کلیک نمایید.
                             </span>
                         </td>
                     </tr>
                     <tr class="bg-blue-300 text-gray-900 font-semibold text-base shadow-sm">
                         <td class="p-3 w-80">
                             <div class="editable w-full">
                                 <span class=" block w-full text-xs" ondblclick="editPartName(this)">
                                     <?= empty($item['partName']) ? 'فاقد نام' : $item['partName'] ?>
                                 </span>
                                 <input
                                     type="text"
                                     class="mt-1 w-full rounded border-2 text-xs border-blue-200 bg-white p-2 text-gray-800 outline-none"
                                     value="<?= $item['partName'] ?>"
                                     onblur="savePartName(this)"
                                     onkeydown="checkEnter(event, this)"
                                     style="display: none;"
                                     data-id="<?= $item['id'] ?>">
                             </div>
                         </td>
                         <td class="p-3 text-sm text-left font-medium">
                             <?= $item['partnumber'] ?>
                         </td>
                     </tr>
                     <?php $first = false; ?>
                 <?php else : ?>
                     <!-- Normal Items -->
                     <tr class="text-sm even:bg-gray-100 odd:bg-orange-50 hover:bg-orange-100 transition">
                         <td class="p-2 w-80">
                             <div class="editable w-full">
                                 <span class="partname w-full block" ondblclick="editPartName(this)">
                                     <?= empty($item['partName']) ? 'فاقد نام' : $item['partName'] ?>
                                 </span>
                                 <input
                                     type="text"
                                     class="p-2 outline-none border-2 border-gray-200 rounded"
                                     value="<?= $item['partName'] ?>"
                                     onblur="savePartName(this)"
                                     onkeydown="checkEnter(event, this)"
                                     style="display: none;"
                                     data-id="<?= $item['id'] ?>">
                             </div>
                         </td>
                         <td class="p-2 text-left font-medium text-gray-700">
                             <?= $item['partnumber'] ?>
                         </td>
                     </tr>
                 <?php endif; ?>
             <?php endforeach; ?>
         </table>
         <?php else :
            if (count($goods) > 0) : ?>
             <p class="my-2 font-semibold">قطعات مرتبط: </p>
             <table>
                 <?php $first = true; ?>
                 <?php foreach ($goods as $key => $item) : ?>
                     <?php if ($first) : ?>
                         <tr>
                             <td colspan="2">
                                 <span class="text-xs text-gray-400">
                                     لطفا برای ویرایش اسم جنس در فاکتور به روی اسم اولین آینم دبل کلیک نمایید.
                                 </span>
                             </td>
                         </tr>
                         <!-- First Item (Soft Highlight) -->
                         <tr class="bg-blue-300 text-gray-900 font-semibold text-base shadow-sm">
                             <td class="p-3 w-80">
                                 <div class="editable w-full">
                                     <span class="text-xs partname block w-full" ondblclick="editPartName(this)">
                                         <?= empty($item['partName']) ? 'فاقد نام' : $item['partName'] ?>
                                     </span>
                                     <input
                                         type="text"
                                         class="mt-1 text-xs w-full rounded border-2 border-blue-200 bg-white p-2 text-gray-800 outline-none"
                                         value="<?= $item['partName'] ?>"
                                         onblur="savePartName(this)"
                                         onkeydown="checkEnter(event, this)"
                                         style="display: none;"
                                         data-id="<?= $item['id'] ?>">
                                 </div>
                             </td>
                             <td class="p-3 text-sm text-left font-medium">
                                 <?= $item['partnumber'] ?>
                             </td>
                         </tr>
                         <?php $first = false; ?>
                     <?php else : ?>
                         <!-- Normal Items -->
                         <tr class="text-sm even:bg-gray-100 odd:bg-orange-50 hover:bg-orange-100 transition">
                             <td class="p-2 w-80">
                                 <div class="editable w-full">
                                     <span class="partname w-full block" ondblclick="editPartName(this)">
                                         <?= empty($item['partName']) ? 'فاقد نام' : $item['partName'] ?>
                                     </span>
                                     <input
                                         type="text"
                                         class="p-2 outline-none border-2 border-gray-200 rounded"
                                         value="<?= $item['partName'] ?>"
                                         onblur="savePartName(this)"
                                         onkeydown="checkEnter(event, this)"
                                         style="display: none;"
                                         data-id="<?= $item['id'] ?>">
                                 </div>
                             </td>
                             <td class="p-2 text-left font-medium text-gray-700">
                                 <?= $item['partnumber'] ?>
                             </td>
                         </tr>
                     <?php endif; ?>
                 <?php endforeach; ?>


             </table>
         <?php endif; ?>
         <p class="text-sm font-semibold p-2 text-center">
             رابطه ای پیدا نشد
         </p>
     <?php endif; ?>
     <div class="mt-5">
         <label for="description">توضیحات</label>
         <textarea onblur="alterDescription(this.value, '<?= current($goods)['id'] ?>')" class="w-full border-2" name="description" id="description"><?= current($goods)['description'] ?></textarea>
     </div>
 </div>
 <script>
     // Show the input box and hide the span on click
     function editPartName(element) {
         const input = element.nextElementSibling;
         element.style.display = 'none';
         input.style.display = 'inline';
         input.focus();
     }

     // Save and switch back to the span on blur
     function savePartName(input) {
         const span = input.previousElementSibling;
         span.textContent = input.value;
         span.style.display = 'inline';
         input.style.display = 'none';
         if (!input.value) {
             input.value = "فاقد نام"
             return;
         }

         const id = input.getAttribute('data-id');

         const param = new URLSearchParams();
         param.append('changeName', 'changeName');
         param.append('id', id);
         param.append('value', input.value);

         axios.post('../../app/api/callcenter/OrderedPriceApi.php', param)
             .then((response) => {
                 form_success.style.bottom = "10px";
                 setTimeout(() => {
                     form_success.style.bottom = "-300px";
                 }, 2000);
             }).catch((e) => {
                 console.log(e);
             });
     }

     function alterDescription(value, id) {
         const param = new URLSearchParams();
         param.append('changeDescription', 'changeDescription');
         param.append('id', id);
         param.append('value', value);

         axios.post('../../app/api/callcenter/OrderedPriceApi.php', param)
             .then((response) => {
                 form_success.style.bottom = "10px";
                 setTimeout(() => {
                     form_success.style.bottom = "-300px";
                 }, 2000);
             }).catch((e) => {
                 console.log(e);
             });
     }

     // Handle Enter key to save on pressing Enter
     function checkEnter(event, input) {
         if (event.key === 'Enter') {
             savePartName(input);
         }
     }
 </script>