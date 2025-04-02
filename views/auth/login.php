<?php
$pageTitle = "ورود به حساب کاربری";
require_once './components/header.php';
?>
<section class="rtl">
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto min-h-screen lg:py-0">
        <a href="javascript:void(0)" class="flex items-center mb-6 text-4xl font-semibold text-white rtl">
            مجموعه یدک شاپ
        </a>

        <div class="w-full bg-white rounded-lg md:mt-0 sm:max-w-md xl:p-0 shadow-lg">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 text-center">
                    ورود به حساب کاربری
                </h1>
                <form class="space-y-4 md:space-y-6" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div>
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-900"> نام کاربری</label>
                        <input onkeyup="convertToEnglish(this)" type="text" name="username" id="username" minlength="3" maxlength="15" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 placeholder-gray-400  focus:ring-blue-500 focus:border-blue-500" placeholder="نام کاربری خود را وارد نمایید." required>
                    </div>
                    <div class="relative">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">رمز عبور</label>
                        <!-- <img onclick="togglePasswordInputType(this)" title="مشاهده/ پنهان کردن رمز عبور" src="../../public/icons/eye.svg" alt="eye icon" class="material-icons cursor-pointer" style="position: absolute; left:5px; top: 50%"> -->
                        <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-900">سال مالی</label>
                        <select name="financialYear" id="financialYear" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 placeholder-gray-400  focus:ring-blue-500 focus:border-blue-500" required>
                            <option selected value="1404">1404</option>
                            <option value="1403">1403</option>
                            <option value="1402">1402</option>
                            <option value="1401">1401</option>
                            <option value="1400">1400</option>
                        </select>
                    </div>
                    <div>
                        <?= !empty($login_err) ? "<p class='text-sm text-red-700'>نام کاربری و یا رمز عبور اشتباه است.</p>" : "" ?>
                    </div>
                    <button type="submit" id="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">ورود به حساب</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php
require_once './components/footer.php';
