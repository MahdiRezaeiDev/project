<?php
$pageTitle = "ویرایش حساب کاربری";
$iconUrl = 'report.png';
require_once './components/header.php';
require_once '../../app/controller/callcenter/ManageUsersController.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
if ($user) : ?>
    <div class="bg-gray-200 shadow-md w-1/2 mx-auto overflow-hidden">
        <div class=" flex items-center justify-between bg-gray-800 p-5">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="material-icons font-semibold text-cyan-600">person_add</i>
                ویرایش حساب کاربری
            </h2>
        </div>
        <div class="p-5">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $user_id ?>">
                <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
                    <div class="col-span-9">
                        <di class="grid grid-cols-2 gap-2">
                            <div class="mb-3">
                                <label class="block font-medium text-sm text-gray-700">
                                    نام
                                </label>
                                <input value="<?= $user['name'] ?>" name="name" class="border-2 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="serial" type="text" />
                                <p class="mt-2"></p>
                            </div>
                            <!-- Price -->
                            <div class="mb-3">
                                <label class="block font-medium text-sm text-gray-700">
                                    نام خانوادگی
                                </label>
                                <input value="<?= $user['family'] ?>" name="family" class="border-2 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="price" type="text" />
                                <p class="mt-2"> </p>
                            </div>
                            <!-- Weight -->
                            <div class="mb-3">
                                <label class="block font-medium text-sm text-gray-700">
                                    نام کاربری
                                </label>
                                <input style="direction: ltr !important;" value="<?= $user['username'] ?>" name="username" class="border-2 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="weight" type="text" />
                                <p class="mt-2"> </p>
                            </div>
                            <div class="mb-3">
                                <label class="block font-medium text-sm text-gray-700">
                                    پروفایل
                                </label>
                                <input name="profile" class="border-2 bg-white text-sm mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="profile" type="file" />
                                <p class="mt-2"> </p>
                            </div>
                            <!-- Mobis -->
                            <div class=" relative">
                                <label class="block font-medium text-sm text-gray-700">
                                    رمزعبور
                                </label>
                                <i onclick="togglePass(this)" class="material-icons cursor-pointer" style="position: absolute; left:5px; top: 40%">remove_red_eye</i>
                                <input style="direction: ltr !important;" name="password" minlength="5" maxlength="50" class="border-2 pl-10 mt-1 block w-full border-gray-300 shadow-sm px-3 py-2" id="mobis" type="password" />
                            </div>
                            <!-- Korea section -->
                            <div class="mb-3">
                                <label class="block font-medium text-sm text-gray-700">
                                    نوعیت حساب کاربری
                                </label>
                                <select name="type" class="border-2 p-2 text-sm mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" id="status">
                                    <option value="1" class="text-sm">
                                        تماس با بازار </option>

                                    <option value="2" class="text-sm">
                                        حسابداری </option>
                                    <option value="3" class="text-sm">
                                        انبار </option>

                                    <option value="4" class="text-sm">
                                        مدیر </option>

                                </select>
                                <p class="mt-2"> </p>
                            </div>
                            <div class=" flex justify-between items-center gap-2">
                                <button class="button bg-gray-700 text-white py-2 px-3 hover:bg-gray-800 text-sm" type="submit">ویرایش </button>
                                <?php if ($success) : ?>
                                    <p class="bg-green-700 text-white text-sm py-2 px-5 mr-2">عملیات موفقانه صورت گرفت</p>
                                <?php endif; ?>
                            </div>
                        </di>
                    </div>
                    <?php
                    $profile = '../../public/userimg/default.png';
                    if (file_exists("../../public/userimg/" . $user_id . ".jpg")) {
                        $profile = "../../public/userimg/" . $user_id . ".jpg";
                    }
                    ?>
                    <img id="imagePreview" class="w-72" src="<?= $profile ?>" alt="userimage">
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePass(element) {
            const target = element.nextElementSibling;
            const inputType = target.type;

            if (inputType === 'password') {
                target.type = 'test';
                return;
            }

            target.type = 'password';
        }

        // Get references to the input and image elements
        const imageInput = document.getElementById('profile');
        const imagePreview = document.getElementById('imagePreview');

        // Add an event listener to the input element
        imageInput.addEventListener('change', function() {
            // Check if a file is selected
            if (imageInput.files && imageInput.files[0]) {
                // Get the selected file
                const selectedImage = imageInput.files[0];

                // Create a FileReader to read the image file
                const reader = new FileReader();

                // Define a callback function to be executed when the image is loaded
                reader.onload = function(e) {
                    // Set the source of the image element to the loaded image data
                    imagePreview.src = e.target.result;
                };

                // Read the selected image file as a data URL
                reader.readAsDataURL(selectedImage);
            } else {
                // Clear the image preview if no file is selected
                imagePreview.src = '';
            }
        });
    </script>
<?php
endif;
require_once './components/footer.php';
