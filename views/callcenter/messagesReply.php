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
      /* light blue */
    }

    #messages>div:nth-child(3n+2) {
      background-color: #fefcbf;
      /* light yellow */
    }

    #messages>div:nth-child(3n+3) {
      background-color: #f0fff4;
      /* light green */
    }
  </style>

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
          container.innerHTML = `<p class="col-span-3 text-gray-500 text-center">No replies found today.</p>`;
          return;
        }

        data.forEach(thread => {
          const myDate = new Date((thread.date || 0) * 1000);
          const myTime = myDate.toLocaleTimeString("fa-IR", {
            hour: "2-digit",
            minute: "2-digit"
          });

          // Wrapper for each thread
          const threadWrapper = document.createElement("div");
          threadWrapper.className = "rounded-2xl shadow-sm p-4 flex flex-col";
          threadWrapper.setAttribute("dir", "ltr");

          // My message (sent once)
          threadWrapper.innerHTML = `
            <div class="mb-3" dir="ltr">
              <div class="bg-blue-100 border-l-4 border-blue-500 pl-2 py-1 rounded">
                <p class="text-sm font-semibold text-left">${thread.my_msg}</p>
              </div>
              <p class="text-xs text-gray-500 mt-1 text-left">Sent at ${myTime}</p>
            </div>
          `;

          // Replies from users
          thread.replies.forEach(r => {
            const rDate = new Date((r.date || 0) * 1000);
            const rTime = rDate.toLocaleTimeString("fa-IR", {
              hour: "2-digit",
              minute: "2-digit"
            });
            const user = thread.users.find(u => u.user_id === r.user_id);

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
                <p class="whitespace-pre-line">${r.reply_msg}</p>
                <div class="text-xs text-gray-500 mt-1 flex justify-between">
                  <span>${user ? (user.first_name || "") + " " + (user.last_name || "") : ""}</span>
                  <span>${rTime}</span>
                </div>
              </div>
            `;

            threadWrapper.appendChild(reply);
          });

          container.appendChild(threadWrapper);
        });

        // Scroll to top
        container.scrollTop = 0;
      } catch (error) {
        document.getElementById("messages").innerHTML =
          `<p class="col-span-3 text-red-500 text-center">Error fetching messages ❌</p>`;
      }
    }

    loadReplies();
  </script>
</section>

<?php
require_once './components/footer.php';
?>