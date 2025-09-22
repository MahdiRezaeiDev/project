<?php
$pageTitle = "قیمت گیری هوشمند";
$iconUrl = 'bot.svg';
require_once './components/header.php';
require_once '../../layouts/callcenter/nav.php';
require_once '../../layouts/callcenter/sidebar.php';
?>
<section class="bg-gray-200 min-h-screen w-full p-6">

  <!-- Container -->
  <div class="w-full h-full bg-white rounded-xl shadow-md flex flex-col">

    <!-- Header -->
    <div class="p-4 border-b border-gray-300 text-center font-bold text-lg text-gray-800">
      📨 قیمت گیری هوشمند
    </div>

    <!-- Messages Grid -->
    <div id="messages" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6 overflow-y-auto flex-1">
      <p class="col-span-3 text-gray-500 text-center animate-pulse">در حال بارگیری ...</p>
    </div>

  </div>

  <!-- Column coloring -->
  <style>
    #messages>div:nth-child(3n+1) {
      background-color: #ebf8ff;
    }

    #messages>div:nth-child(3n+2) {
      background-color: #fefcbf;
    }

    #messages>div:nth-child(3n+3) {
      background-color: #f0fff4;
    }
  </style>

  <script>
    function isNegative(text) {
      const negatives = ['نه', 'نمیشه', 'مشکل', 'fail', 'no', 'noo', 'متاسفانه', 'نمیخوام', 'نمیخواد', 'ندارم', 'نداریم'];
      const exactNegatives = ['*', '**', '-', '0', 'خیر', 'ن', 'N', '00']; // exact-only

      text = (text || "").trim().toLowerCase();

      // Check substring matches
      if (negatives.some(word => text.includes(word))) return true;

      // Check exact matches
      if (exactNegatives.includes(text)) return true;

      return false;
    }


    async function loadReplies() {
      const params = new URLSearchParams();
      params.append("getMessagesReply", "getMessagesReply");

      try {
        const response = await axios.post("https://partners.yadak.center/api/replay", params);
        const data = response.data.data;
        const container = document.getElementById("messages");
        container.innerHTML = "";

        if (!data || data.length === 0) {
          container.innerHTML = `<p class="col-span-3 text-gray-500 text-center">پیامی برای امروز دریافت نشده است.</p>`;
          return;
        }

        data.forEach(thread => {
          const myDate = new Date((thread.date || 0) * 1000);
          const myTime = myDate.toLocaleTimeString("fa-IR", {
            hour: "2-digit",
            minute: "2-digit"
          });

          // Thread wrapper
          const threadWrapper = document.createElement("div");
          threadWrapper.className = "rounded-2xl shadow-sm p-4 flex flex-col";
          threadWrapper.setAttribute("dir", "ltr");

          // Outgoing message
          threadWrapper.innerHTML = `
            <div class="mb-3" dir="ltr">
              <div class="bg-blue-100 border-l-4 border-blue-500 pl-2 py-1 rounded">
                <p class="text-sm font-semibold text-left">${thread.my_msg}</p>
              </div>
              <p class="text-xs text-gray-500 mt-1 text-left">Sent at ${myTime}</p>
            </div>
          `;

          // Array for negative replies profiles
          const negativeProfiles = [];

          // Replies
          thread.replies.forEach(r => {
            const rDate = new Date((r.date || 0) * 1000);
            const rTime = rDate.toLocaleTimeString("fa-IR", {
              hour: "2-digit",
              minute: "2-digit"
            });
            const user = thread.users.find(u => u.user_id === r.user_id);

            let messageText = r.reply_msg || '';

            // Skip negative replies → only collect avatar + tooltip
            if (isNegative(messageText)) {
              if (user && user.photo_url) {
                negativeProfiles.push({
                  url: user.photo_url,
                  msg: messageText
                });
              }
              return; // don't render text
            }

            // Replace stickers/images with labels
            if (messageText === '[image]') messageText = '[تصویر]';
            if (messageText === '[Document]') messageText = '[تصویر]';
            if (messageText === '[sticker]') messageText = '[استیکر]';

            const reply = document.createElement("div");
            reply.className = "flex items-start gap-3 mb-3";
            reply.setAttribute("dir", "ltr");

            const avatar = user && user.photo_url ?
              `<img src="${user.photo_url}" class="w-10 h-10 rounded-full object-cover" />` :
              `<svg class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                 <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
               </svg>`;

            reply.innerHTML = `
              <div class="shrink-0">${avatar}</div>
              <div class="flex-1 bg-white border rounded-xl p-3 shadow-sm text-left">
                <p class="whitespace-pre-line">${messageText}</p>
                <div class="text-xs text-gray-500 mt-1 flex justify-between">
                  <span>${user ? (user.first_name || "") + " " + (user.last_name || "") : ""}</span>
                  <span>${rTime}</span>
                </div>
              </div>
            `;

            threadWrapper.appendChild(reply);
          });

          // Show only avatars of negative replies at the bottom (with tooltip)
          if (negativeProfiles.length > 0) {
            const negContainer = document.createElement("div");
            negContainer.className = "flex gap-2 mt-3 flex-wrap";

            negativeProfiles.forEach(item => {
              const wrapper = document.createElement("div");
              wrapper.className = "relative group";

              const img = document.createElement("img");
              img.src = item.url;
              img.className = "w-10 h-10 rounded-full object-cover border-2 border-red-400 cursor-pointer";

              // Tooltip element
              const tooltip = document.createElement("div");
              tooltip.textContent = item.msg;
              tooltip.className = `
      absolute bottom-full left-1/2 -translate-x-1/2 mb-2
      hidden group-hover:block
      bg-gray-800 text-white text-xs px-2 py-1 rounded-lg shadow-lg
      whitespace-nowrap z-50
    `;

              wrapper.appendChild(img);
              wrapper.appendChild(tooltip);
              negContainer.appendChild(wrapper);
            });

            threadWrapper.appendChild(negContainer);
          }


          container.appendChild(threadWrapper);
        });

        container.scrollTop = 0;

      } catch (error) {
        document.getElementById("messages").innerHTML =
          `<p class="col-span-3 text-red-500 text-center"> خطا در بارگیری پیام ها ❌</p>`;
      }
    }

    loadReplies();
  </script>
</section>

<?php
require_once './components/footer.php';
?>
