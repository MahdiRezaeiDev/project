<div id="desktopOnlyWarning" class="hidden fixed inset-0 z-50 bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 text-center space-y-6 border border-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto w-20 h-20 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 17h2" />
        </svg>
        <h1 class="text-3xl font-bold text-gray-800">دسترسی فقط از طریق موبایل</h1>
        <p class="text-gray-600 text-base">
            لطفاً برای استفاده از این صفحه، با استفاده از گوشی موبایل خود وارد شوید.
        </p>
    </div>
</div>
<script>
    function isRealMobileDevice() {
        return ('ontouchstart' in window || navigator.maxTouchPoints > 0) && window.innerWidth <= 768;
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (!isRealMobileDevice()) {
            document.getElementById('attendanceCard')?.classList.add('hidden');
            document.getElementById('desktopOnlyWarning')?.classList.remove('hidden');
        }
    });
</script>

</body>

</html>