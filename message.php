<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>Replies Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body class="bg-gray-200 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-lg flex flex-col h-[90vh]">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 text-center font-bold text-lg text-gray-700">
            📨 پیام‌های ریپلای شده
        </div>

        <!-- Chat area -->
        <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-4">
            <p class="text-gray-500 text-center animate-pulse">در حال بارگذاری...</p>
        </div>
    </div>

    <script>
        async function loadReplies() {
            const params = new URLSearchParams();
            params.append("getMessagesReply", "getMessagesReply");

            try {
                const response = await axios.post("https://partners.yadak.center/", params);
                const data = response.data;

                const container = document.getElementById("messages");
                container.innerHTML = "";

                if (!data || data.length === 0) {
                    container.innerHTML = `<p class="text-gray-500 text-center">هیچ پیامی یافت نشد.</p>`;
                    return;
                }

                data.forEach(item => {
                    // decide side: if user has username = "me" → right side
                    const isSelf = !item.username || item.username.toLowerCase() === "me"; // adjust for your logic

                    const wrapper = document.createElement("div");
                    wrapper.className = `flex ${isSelf ? "justify-end" : "justify-start"}`;

                    wrapper.innerHTML = `
            <div class="max-w-[75%]">
              <div class="${isSelf
              ? "bg-blue-500 text-white rounded-2xl rounded-br-sm"
              : "bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm"} shadow-md p-3">

                <!-- Quoted original -->
                <div class="${isSelf
              ? "bg-blue-600/40 border-r-4 border-white"
              : "bg-gray-200 border-r-4 border-gray-400"} pr-2 pl-1 py-1 mb-2 rounded">
                  <p class="text-sm ${isSelf ? "text-white/90" : "text-gray-700"} line-clamp-3">
                    ${item.original_msg}
                  </p>
                </div>

                <!-- Reply text -->
                <p class="whitespace-pre-line leading-relaxed">${item.reply_msg}</p>
              </div>

              <!-- User info -->
              <div class="text-xs text-gray-500 mt-1 ${isSelf ? "text-right pr-2" : "text-left pl-2"}">
                ${item.first_name || ""} ${item.last_name || ""} ${item.username ? "(@" + item.username + ")" : ""}
              </div>
            </div>
          `;

                    container.appendChild(wrapper);
                });
            } catch (error) {
                document.getElementById("messages").innerHTML =
                    `<p class="text-red-500 text-center">خطا در دریافت اطلاعات ❌</p>`;
            }
        }

        loadReplies();
    </script>
</body>

</html>