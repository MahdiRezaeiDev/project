let messages = [];

function fetchMessages() {
    fetch('../../app/api/callcenter/SmsBankApi.php')
        .then(res => res.json())
        .then(data => {
            messages = data;
            renderMessages(data);
        });
}

const presetTags = [{
        label: 'وبسایت',
        value: 'https://yadak.shop'
        },
    {
        label: 'برند',
        value: 'یدک شاپ'
        },
    {
        label: 'مشتری',
        value: 'مشتری گرامی'
        }

    ];



function renderMessages(messages) {
    const container = document.getElementById('messages');
    container.innerHTML = '';

    if (messages.length === 0) {
        showAlert('هیچ پیامی وجود ندارد', 'info');
    }

    messages.forEach((msg, index) => {
        const div = document.createElement('div');
        div.className = "bg-white p-4 rounded shadow mb-2";

        div.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="w-full">
                    <input class="border-b border-gray-300 mb-1 w-full text-lg font-semibold"
                           value="${msg.title}" id="title-${index}" disabled>
                    <textarea class="border border-gray-300 p-2 rounded w-full" id="msg-${index}" disabled>${msg.message}</textarea>
                    <p id="counter-${index}" class="text-sm text-gray-500 mt-1">0 کاراکتر - 1 SMS</p>

                    <!-- نمایش تگ‌ها -->
                    <div class="flex flex-wrap gap-2 mt-2">
                        ${(msg.tags || []).map(tag => `
                            <span class="bg-blue-200 text-blue-800 px-2 py-1 rounded-full text-sm">
                                ${tag} <button onclick="removeTag(${index}, '${tag}')">×</button>
                            </span>
                        `).join('')}
                    </div>

<div class="flex flex-wrap gap-2 mt-2">
    ${presetTags.map(tag => `
        <button class="bg-gray-200 text-gray-800 px-2 py-1 rounded-full text-sm hover:bg-gray-300"
                onclick="addPresetTag(${index}, '${tag.value}')">
            ${tag.label}
        </button>
    `).join('')}
</div>


                </div>
                <div class="flex flex-col ml-2 space-y-2">
                    <button id="edit-btn-${index}"
                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">ادیت</button>
                    <button id="save-btn-${index}" style="display:none;"
                            class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">ذخیره</button>
                    <button id="delete-btn-${index}"
                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">حذف</button>
                </div>
            </div>
        `;
        container.appendChild(div);

        // شمارشگر کاراکتر
        const textarea = document.getElementById(`msg-${index}`);
        const counter = document.getElementById(`counter-${index}`);
        counter.innerText = `${textarea.value.length} کاراکتر - ${Math.max(1, Math.ceil(textarea.value.length/70))} SMS`;

        textarea.addEventListener('input', () => {
            const length = textarea.value.length;
            const smsCount = Math.max(1, Math.ceil(length / 70));
            counter.innerText = `${length} کاراکتر - ${smsCount} SMS`;
            messages[index].message = textarea.value; // بروزرسانی متن پیام
        });

        // دکمه ادیت
        const editBtn = document.getElementById(`edit-btn-${index}`);
        const saveBtn = document.getElementById(`save-btn-${index}`);
        const deleteBtn = document.getElementById(`delete-btn-${index}`);

        editBtn.addEventListener('click', () => {
            textarea.disabled = false;
            document.getElementById(`title-${index}`).disabled = false;
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
            deleteBtn.disabled = true;

        });


        saveBtn.addEventListener('click', () => {
            updateMessage(index);
            textarea.disabled = true;
            document.getElementById(`title-${index}`).disabled = true;
            saveBtn.style.display = 'none';
            editBtn.style.display = 'inline-block';
            deleteBtn.disabled = false;
        });

        // حذف پیام
        deleteBtn.addEventListener('click', () => deleteMessage(index));
    });
}


function addPresetTag(index, value) {
    const textarea = document.getElementById(`msg-${index}`);
    const counter = document.getElementById(`counter-${index}`);

    // فقط زمانی که ادیت فعال باشد
    if (textarea.disabled) {
        showAlert('ابتدا روی دکمه ادیت پیام کلیک کنید', 'error');
        return;
    }

    const msg = messages[index];

    // اضافه کردن تگ بدون محدودیت
    msg.message = msg.message.trim() + ` ${value}`;
    textarea.value = msg.message;

    // بروزرسانی شمارشگر
    counter.innerText = `${textarea.value.length} کاراکتر - ${Math.max(1, Math.ceil(textarea.value.length / 70))} SMS`;

    // ذخیره روی سرور
    fetch('../../app/api/callcenter/SmsBankApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'edit',
            index,
            title: msg.title,
            message: msg.message
        })
    }).then(res => res.json());
}


function addMessage() {
    const title = document.getElementById('newTitle').value.trim();
    const message = document.getElementById('newMessage').value.trim();

    if (!title || !message) {
        showAlert('لطفاً همه فیلدها را پر کنید', 'error');
        return;
    }

    // شرط بررسی تکراری بودن عنوان
    const existingTitles = Array.from(document.querySelectorAll('[id^=title-]'))
        .map(input => input.value.trim().toLowerCase());
    if (existingTitles.includes(title.toLowerCase())) {
        showAlert('عنوان وارد شده تکراری است، لطفاً عنوان دیگری انتخاب کنید', 'error');
        return;
    }

    fetch('../../app/api/callcenter/SmsBankApi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'add',
            title,
            message
        })
    }).then(() => {
        document.getElementById('newTitle').value = '';
        document.getElementById('newMessage').value = '';
        fetchMessages();
        showAlert('پیام با موفقیت اضافه شد', 'success');
    });
}

function updateMessage(index) {
    const title = document.getElementById(`title-${index}`).value.trim();
    const message = document.getElementById(`msg-${index}`).value.trim();

    if (!title || !message) {
        showAlert('لطفاً همه فیلدها را پر کنید', 'error');
        return;
    }

    // بررسی تکراری بودن عنوان (به جز خودش)
    const existingTitles = Array.from(document.querySelectorAll('[id^=title-]'))
        .map((input, i) => i !== index ? input.value.trim().toLowerCase() : null)
        .filter(t => t);
    if (existingTitles.includes(title.toLowerCase())) {
        showAlert('عنوان وارد شده تکراری است، لطفاً عنوان دیگری انتخاب کنید', 'error');
        return;
    }

    // بروزرسانی local
    messages[index].title = title;
    messages[index].message = message;

    // ارسال به API
    fetch('../../app/api/callcenter/SmsBankApi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'edit',
                index,
                title,
                message
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                showAlert('پیام با موفقیت ویرایش شد', 'success');
                // فقط counter را بروزرسانی می‌کنیم بدون disable شدن textarea
                const textarea = document.getElementById(`msg-${index}`);
                const counter = document.getElementById(`counter-${index}`);
                counter.innerText = `${textarea.value.length} کاراکتر - ${Math.max(1, Math.ceil(textarea.value.length/70))} SMS`;
            } else {
                showAlert('خطا در ذخیره پیام', 'error');
            }
        });
}

let deletedMessage = null;
let deletedIndex = null;
let undoTimeout = null;

function deleteMessage(index) {
    const msg = messages[index];
    if (!msg) return showAlert('پیام مورد نظر یافت نشد', 'error');

    if (confirm(`آیا از حذف "${msg.title}" اطمینان دارید؟`)) {
        const deleted = messages[index];

        // حذف از سرور
        fetch('../../app/api/callcenter/SmsBankApi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete',
                    index
                })
            })
            .then(() => {
                fetchMessages(); // بازخوانی لیست

                // نمایش Toast با دکمه Undo
                const alertDiv = document.getElementById('alert');
                const toast = document.createElement('div');
                toast.className = `bg-yellow-500 text-white px-4 py-2 rounded shadow flex justify-between items-center animate-fade-in-out`;
                toast.innerHTML = `
                <span>پیام "${deleted.title}" حذف شد.</span>
                <button class="ml-4 px-2 py-1 rounded text-sm font-semibold hover:bg-yellow-100 transition"
                    onclick="undoDelete(${index}, '${encodeURIComponent(JSON.stringify(deleted))}', this)">
                    بازگردانی
                </button>
            `;
                alertDiv.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 5000); // بعد ۵ ثانیه محو شود
            });
    }
}

function undoDelete(index, deletedData, btn) {
    const data = JSON.parse(decodeURIComponent(deletedData));

    fetch('../../app/api/callcenter/SmsBankApi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'insert',
                index,
                title: data.title,
                message: data.message
            })
        })
        .then(() => {
            btn.closest('div').remove();
            fetchMessages();
            showAlert('پیام با موفقیت بازگردانی شد', 'success');
        });
}

function showAlert(message, type = 'success', duration = 3000) {
    const alertDiv = document.getElementById('alert');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };

    const div = document.createElement('div');
    div.className = `${colors[type]} text-white px-4 py-2 rounded shadow mb-2 transition transform duration-500 ease-in-out translate-y-0 opacity-100`;
    div.innerText = message;

    alertDiv.appendChild(div);

    // fade out animation
    setTimeout(() => {
        div.classList.add('opacity-0', '-translate-y-3');
        setTimeout(() => div.remove(), 500);
    }, duration);
}

function renderNewMessageTags() {
    const container = document.getElementById('new-preset-tags');
    container.innerHTML = '';

    presetTags.forEach(tag => {
        const btn = document.createElement('button');
        btn.className = "bg-gray-200 text-gray-800 px-2 py-1 rounded-full text-sm hover:bg-gray-300";
        btn.innerText = tag.label;
        btn.addEventListener('click', () => {
            addTagToNewMessage(tag.value);
        });
        container.appendChild(btn);
    });
}

function addTagToNewMessage(value) {
    const textarea = document.getElementById('newMessage');
    textarea.value = textarea.value.trim() + ' ' + value;
}


renderNewMessageTags()
fetchMessages();


const newTextarea = document.getElementById('newMessage');
const newCounter = document.getElementById('newCounter');

newTextarea.addEventListener('input', () => {
    const length = newTextarea.value.length;
    const smsCount = Math.max(1, Math.ceil(length / 70));
    newCounter.innerText = `${length} کاراکتر - ${smsCount} SMS`;
});
