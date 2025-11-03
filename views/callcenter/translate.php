<?php
$pageTitle = "مترجم قیمت دستوری";
$iconUrl = 'translate.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>

<div class="max-w-5xl mx-auto p-6 lg:px-8 bg-gray-200 rounded-lg shadow mt-2">
    <form id="translatorForm" method="POST" target="_blank">
        <label for="code" class="block text-lg font-semibold text-gray-900 mb-3">کدهای مدنظر</label>

        <!-- Two Textareas Side by Side -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Input Codes -->
            <div>
                <textarea id="inputCodes" name="code" rows="20" required
                    class="border-2 border-gray-300 focus:border-gray-500 p-3 outline-none text-sm shadow-sm block w-full uppercase"
                    style="direction: ltr !important;"
                    placeholder="کدها را در اینجا وارد کنید (هر کد در یک خط)"></textarea>
            </div>

            <!-- Output Translated Codes -->
            <div class="relative">
                <textarea id="outputCodes" readonly rows="20"
                    class="border-2 border-gray-300 bg-gray-100 p-3 outline-none text-sm shadow-sm block w-full uppercase"
                    style="direction: rtl !important;"
                    placeholder="نتیجه ترجمه در اینجا نمایش داده می‌شود..."></textarea>

                <!-- Loading Spinner -->
                <div id="loadingSpinner"
                    class="absolute inset-0 flex items-center justify-center bg-white/70 backdrop-blur-sm rounded-lg hidden">
                    <div class="h-10 w-10 border-4 border-sky-600 border-t-transparent rounded-full animate-spin"></div>
                </div>

                <!-- Copy Button -->
                <button type="button" id="copyBtn"
                    class="absolute top-2 left-2 bg-sky-600 hover:bg-sky-700 text-white text-xs px-3 py-1 rounded shadow">
                    <img src="./assets/icons/clipboard.svg" />
                </button>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap items-center justify-between py-3">
            <button id="translateBtn" type="button"
                class="inline-flex items-center px-5 py-2 bg-gray-800 font-semibold text-xs text-white hover:bg-gray-700 rounded">
                ترجمه
            </button>
        </div>
    </form>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<script>
    const input = document.getElementById('inputCodes');
    const output = document.getElementById('outputCodes');
    const loading = document.getElementById('loadingSpinner');
    const translateBtn = document.getElementById('translateBtn');
    const copyBtn = document.getElementById('copyBtn');
    const toastContainer = document.getElementById('toastContainer');

    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500'
        };
        const toast = document.createElement('div');
        toast.className = `${colors[type]} text-white px-4 py-2 rounded shadow-md animate-fadeInOut`;
        toast.innerText = message;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-2');
        }, 2500);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    translateBtn.addEventListener('click', async () => {
        const codes = input.value.trim();
        if (!codes) {
            showToast('لطفا ابتدا کدها را وارد کنید', 'error');
            return;
        }

        output.value = '';
        loading.classList.remove('hidden');

        try {
            const response = await fetch('../../app/api/callcenter/TranslateApi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    codes
                })
            });

            const text = await response.text();
            output.value = text;
            showToast('ترجمه با موفقیت انجام شد', 'success');
        } catch (error) {
            output.value = '❌ خطا در دریافت نتیجه';
            showToast('خطا در دریافت نتیجه', 'error');
        } finally {
            loading.classList.add('hidden');
        }
    });

    // Copy to Clipboard with fallback
    copyBtn.addEventListener('click', () => {
        if (!output.value) {
            showToast('هیچ متنی برای کپی وجود ندارد', 'error');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(output.value)
                .then(() => showToast('متن با موفقیت کپی شد!', 'success'))
                .catch(() => fallbackCopy(output.value));
        } else {
            fallbackCopy(output.value);
        }
    });

    function fallbackCopy(text) {
        const tempTextarea = document.createElement('textarea');
        tempTextarea.value = text;
        tempTextarea.style.position = 'fixed';
        tempTextarea.style.opacity = '0';
        document.body.appendChild(tempTextarea);
        tempTextarea.select();
        try {
            const successful = document.execCommand('copy');
            if (successful) showToast('متن با موفقیت کپی شد!', 'success');
            else showToast('خطا در کپی متن', 'error');
        } catch (err) {
            showToast('خطا در کپی متن', 'error');
        }
        document.body.removeChild(tempTextarea);
    }
</script>

<style>
    /* Simple fade in/out animation for toast */
    @keyframes fadeInOut {
        0% {
            opacity: 0;
            transform: translateX(10px);
        }

        10% {
            opacity: 1;
            transform: translateX(0);
        }

        90% {
            opacity: 1;
            transform: translateX(0);
        }

        100% {
            opacity: 0;
            transform: translateX(10px);
        }
    }

    .animate-fadeInOut {
        animation: fadeInOut 3s ease forwards;
    }
</style>

<?php require_once './components/footer.php'; ?>