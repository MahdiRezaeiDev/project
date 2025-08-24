<div id="tvMessage" style="position:fixed;
padding: 5px 10px;
border-radius:5px;
top:120%;
left:50%;
transform:translateX(-50%);
transition: all 0.5s;
background-color:#f1f1f1;">
    عملیات موفقانه صورت گرفت</div>
<script>
    function toggleTV() {
        const tvMessage = document.getElementById('tvMessage');
        const params = new URLSearchParams();
        params.append("action", "toggleTV");
        axios.post('../../app/api/tv/ToggleApi.php', params)
            .then(function(response) {
                console.log(response.data.status);
                if (response.data.status === 'on') {
                    tvMessage.style.top = '90%';
                    tvMessage.style.backgroundColor = 'green';
                    tvMessage.style.color = 'white';
                    tvMessage.innerHTML = "تلوزیون روشن شد.";
                    setTimeout(() => {
                        tvMessage.style.top = '120%';
                    }, 1000);
                } else {
                    tvMessage.style.top = '90%';
                    tvMessage.style.backgroundColor = 'rgb(15 23 42)';
                    tvMessage.style.color = 'white';
                    tvMessage.innerHTML = "تلوزیون خاموش شد.";
                    setTimeout(() => {
                        tvMessage.style.top = '120%';
                    }, 1000);
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    }
</script>
</body>

</html>