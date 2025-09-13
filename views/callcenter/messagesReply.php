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

        data.forEach(thread => {
          // For each of my messages
          const myDate = new Date((thread.date || 0) * 1000);
          const myTime = myDate.toLocaleTimeString("fa-IR", {
            hour: "2-digit",
            minute: "2-digit"
          });

          // Show each reply under it
          thread.replies.forEach(r => {
            const rDate = new Date((r.date || 0) * 1000);
            const rTime = rDate.toLocaleTimeString("fa-IR", {
              hour: "2-digit",
              minute: "2-digit"
            });

            const wrapper = document.createElement("div");
            wrapper.className = "flex justify-end text-left";

            wrapper.innerHTML = `
              <div class="flex items-start text-xs max-w-[80%] space-x-2">
                <div class="flex-1">

                  <!-- Reply container -->
                  <div class="bg-gray-50 text-gray-900 rounded-2xl shadow-sm p-3">

                    <!-- Quoted my message -->
                    <div class="bg-gray-100 border-l-4 border-gray-400 pl-2 py-1 mb-2 rounded">
                      <p class="text-sm line-clamp-3 break-words">${thread.my_msg}</p>
                    </div>

                    <!-- Reply message -->
                    <p class="whitespace-pre-line leading-relaxed break-words">${r.reply_msg}</p>

                  </div>

                  <!-- User info and time -->
                  <div class="text-xs text-gray-500 mt-1 flex justify-between">
                    <span class="px-5">${rTime}</span>
                    <span>${thread.first_name || ""} ${thread.last_name || ""}</span>
                  </div>

                </div>
                
                <!-- Profile picture -->
                                ${thread.photo_url 
                  ? `<img src="${thread.photo_url}" 
                          class="w-8 h-8 rounded-full object-cover" 
                          alt="${thread.first_name || ''}">`
                  : `<svg class="w-8 h-8 rounded-full bg-gray-300 p-1 text-gray-500" 
                         fill="currentColor" viewBox="0 0 24 24">
                       <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                     </svg>`}

              </div>
            `;

            container.appendChild(wrapper);
          });
        });

        // Keep scroll at top (oldest first visible)
        container.scrollTop = 0;

      } catch (error) {
        console.error(error);
        document.getElementById("messages").innerHTML =
          `<p class="text-red-500 text-center">Error fetching messages ❌</p>`;
      }
    }

    loadReplies();
  </script>
</section>

<?php
require_once './components/footer.php';
?>