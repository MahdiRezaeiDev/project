<?php
$pageTitle = "قیمت گیری هوشمند";
$iconUrl = 'bot.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<section class="bg-gray-200 min-h-screen flex items-center justify-center p-6">

  <!-- Replies Section (LTR only) -->
  <div dir="ltr" class="w-full max-w-2xl bg-white rounded-xl shadow-md flex flex-col h-[80vh] mx-auto">

    <!-- Header -->
    <div class="p-4 border-b border-gray-300 text-center font-bold text-lg text-gray-800">
      📨 قیمت گیری هوشمند
    </div>

    <!-- Chat area -->
    <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-4">
      <p class="text-gray-500 text-center animate-pulse">در حال بارگیری ...</p>
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
          container.innerHTML = `<p class="text-gray-500 text-center">No replies found today.</p>`;
          return;
        }

        data.forEach(item => {
          const date = new Date((item.date || 0) * 1000);
          const hours = date.getHours().toString().padStart(2, "0");
          const minutes = date.getMinutes().toString().padStart(2, "0");
          const timeStr = `${hours}:${minutes}`;

          const wrapper = document.createElement("div");
          wrapper.className = "flex justify-end text-left";

          wrapper.innerHTML = `
            <div class="text-xs max-w-[80%]">

              <!-- Reply container -->
              <div class="bg-gray-50 text-gray-900 rounded-2xl shadow-sm p-3">

                <!-- Quoted original -->
                <div class="bg-gray-100 border-l-4 border-gray-400 pl-2 py-1 mb-2 rounded">
                  <p class="text-sm line-clamp-3 break-words">${item.original_msg}</p>
                </div>

                <!-- Reply message -->
                <p class="whitespace-pre-line leading-relaxed break-words">${item.reply_msg}</p>

              </div>

              <!-- User info and time -->
              <div class="text-xs text-gray-500 mt-1 flex justify-between">
                <span class="px-5">${timeStr}</span>
                <span>${item.first_name || ""} ${item.last_name || ""}</span>
              </div>

            </div>
          `;

          container.appendChild(wrapper);
        });

        container.scrollTop = container.scrollHeight;

      } catch (error) {
        document.getElementById("messages").innerHTML =
          `<p class="text-red-500 text-center">Error fetching messages ❌</p>`;
      }
    }

    loadReplies();
  </script>
</section>
<?php
require_once './components/footer.php';
